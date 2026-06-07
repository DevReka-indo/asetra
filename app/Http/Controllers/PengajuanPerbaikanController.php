<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Models\PengajuanPerbaikan;
use App\Models\DataAset;
use App\Models\LogAset;
use App\Models\User;
use App\Notifications\SystemNotification;

class PengajuanPerbaikanController extends Controller
{
    use \App\Traits\HandlesImageUploads;
    /**
     * Cek apakah user yang sedang login berhak memproses pengajuan (bagian umum/ga).
     */
    private function canProcess(): bool
    {
        $user = Auth::user();

        // Superadmin bypass Bagian Umum check
        if ($user->role_id_role === 1 || strtolower($user->role->nm_role ?? '') === 'superadmin') {
            return true;
        }

        $adminRoles = ['admin', 'superadmin'];
        $isAdmin = in_array(strtolower($user->role->nm_role ?? ''), $adminRoles);

        return $isAdmin && $user->isBagianUmum();
    }

    /**
     * Daftar pengajuan:
     * - Bagian Umum → lihat SEMUA
     * - Staff dept lain → hanya milik sendiri
     */
    public function index(Request $request)
    {
        $sortBy  = $request->input('sort_by', 'created_at');
        $orderBy = $request->input('order_by', 'desc');

        $allowedSortColumns = ['created_at', 'tanggal_pengajuan', 'tingkat_urgensi', 'status', 'aset_id', 'nama_aset', 'nomor_aset', 'pengaju_name'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }
        if (!in_array($orderBy, ['asc', 'desc'])) {
            $orderBy = 'desc';
        }

        $user  = Auth::user();
        $query = PengajuanPerbaikan::with(['aset', 'pengaju', 'pemroses']);

        if (!$this->canProcess()) {
            // Staf lihat pengajuan untuk aset di departemennya yang ia ajukan sendiri
            $query->where(function($q) use ($user) {
                $q->where('diajukan_oleh', $user->id)
                  ->orWhereHas('aset', function($qAset) use ($user) {
                      $qAset->where(function($subQ) use ($user) {
                          if ($user->unit_id_unit) {
                              $subQ->where('id_unit', $user->unit_id_unit);
                          } elseif ($user->section_id_section) {
                              $subQ->where('id_section', $user->section_id_section);
                          } elseif ($user->department_id_department) {
                              $subQ->where('id_department', $user->department_id_department);
                          } elseif ($user->divisi_id_divisi) {
                              $subQ->where('id_divisi', $user->divisi_id_divisi);
                          } elseif ($user->director_id_director) {
                              $subQ->where('id_director', $user->director_id_director);
                          }
                          
                          $subQ->orWhere('pic_id', $user->id);
                      });
                  });
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter urgensi
        if ($request->filled('urgensi')) {
            $query->where('tingkat_urgensi', $request->urgensi);
        }

        // Search by asset name or number
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('aset', function($q) use ($searchTerm) {
                $q->where('nomor_aset', 'like', "%{$searchTerm}%")
                  ->orWhere('nama_aset', 'like', "%{$searchTerm}%");
            });
        }

        // Apply sorting
        if ($sortBy === 'nama_aset' || $sortBy === 'nomor_aset') {
            $query->leftJoin('data_aset', 'pengajuan_perbaikan.aset_id', '=', 'data_aset.id')
                  ->select('pengajuan_perbaikan.*')
                  ->orderBy('data_aset.' . $sortBy, $orderBy);
        } elseif ($sortBy === 'pengaju_name') {
            $query->leftJoin('users', 'pengajuan_perbaikan.diajukan_oleh', '=', 'users.id')
                  ->select('pengajuan_perbaikan.*')
                  ->orderBy('users.firstname', $orderBy);
        } else {
            $query->orderBy('pengajuan_perbaikan.' . $sortBy, $orderBy);
        }

        $pengajuans = $query->paginate(15)->withQueryString();

        return view('perbaikan.index', compact('pengajuans'));
    }

    /**
     * Simpan pengajuan baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'aset_id'             => 'required|exists:data_aset,id',
            'deskripsi_kerusakan' => 'required|string|max:2000',
            'tingkat_urgensi'     => 'required|in:rendah,sedang,tinggi',
            'foto_kerusakan'      => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Cegah pengajuan ganda untuk aset yang masih menunggu/disetujui
        $sudahAda = PengajuanPerbaikan::where('aset_id', $request->aset_id)
            ->whereIn('status', ['menunggu', 'disetujui'])
            ->exists();

        if ($sudahAda) {
            return back()->with('error', 'Aset ini sudah memiliki pengajuan perbaikan yang sedang diproses. Silakan tunggu hingga selesai.');
        }

        $data = [
            'aset_id'             => $request->aset_id,
            'diajukan_oleh'       => Auth::id(),
            'tanggal_pengajuan'   => now()->toDateString(),
            'deskripsi_kerusakan' => $request->deskripsi_kerusakan,
            'tingkat_urgensi'     => $request->tingkat_urgensi,
            'status'              => 'menunggu',
        ];

        if ($request->hasFile('foto_kerusakan')) {
            $data['foto_kerusakan'] = $this->compressAndStore($request->file('foto_kerusakan'), 'perbaikan');
        }

        $pengajuan = PengajuanPerbaikan::create($data);

        // Update status aset menjadi "Dalam Perbaikan" saat pengajuan dibuat
        DataAset::findOrFail($request->aset_id)->update([
            'status_aset' => 'Dalam Perbaikan',
        ]);

        // Notify GA / Superadmin
        $aset = DataAset::find($request->aset_id);
        $asetName = $aset ? $aset->nama_aset : 'Aset';
        $userFullName = Auth::user()->fullname;
        
        $adminGas = User::where('role_id_role', 1)->get()->merge(
            User::all()->filter(fn($u) => $u->isGeneralAffairs())
        )->unique('id');

        $title = 'Pengajuan Perbaikan Baru';
        $message = "{$userFullName} mengajukan perbaikan untuk aset {$asetName}.";
        $url = route('perbaikan.show', $pengajuan->id);
        $type = 'perbaikan';

        foreach ($adminGas as $adminGa) {
            $adminGa->notify(new SystemNotification($title, $message, $url, $type));
        }

        return back()->with('success', 'Pengajuan perbaikan aset berhasil dikirim! Menunggu review dari Bagian Umum.');
    }

    /**
     * Detail satu pengajuan.
     */
    public function show($id)
    {
        $pengajuan = PengajuanPerbaikan::with(['aset', 'pengaju', 'pemroses'])
            ->findOrFail($id);

        $user = Auth::user();

        // Staf hanya lihat miliknya sendiri dan milik departemennya
        if (!$this->canProcess() && $pengajuan->diajukan_oleh !== $user->id) {
            $aset = $pengajuan->aset;
            $isAuthorized = false;
            
            if ($aset) {
                if ($user->unit_id_unit && $aset->id_unit == $user->unit_id_unit) $isAuthorized = true;
                elseif ($user->section_id_section && $aset->id_section == $user->section_id_section) $isAuthorized = true;
                elseif ($user->department_id_department && $aset->id_department == $user->department_id_department) $isAuthorized = true;
                elseif ($user->divisi_id_divisi && $aset->id_divisi == $user->divisi_id_divisi) $isAuthorized = true;
                elseif ($user->director_id_director && $aset->id_director == $user->director_id_director) $isAuthorized = true;
                elseif ($aset->pic_id == $user->id) $isAuthorized = true;
            }

            if (!$isAuthorized) {
                abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
            }
        }

        // Ambil semua pengajuan untuk aset yang sama
        $riwayatPengajuan = PengajuanPerbaikan::where('aset_id', $pengajuan->aset_id)
            ->with(['pengaju', 'pemroses'])
            ->oldest()
            ->get();

        return view('perbaikan.show', compact('pengajuan', 'riwayatPengajuan'));
    }

    /**
     * Bagian Umum menyetujui atau menolak pengajuan.
     */
    public function proses(Request $request, $id)
    {
        if (!$this->canProcess()) {
            abort(403, 'Hanya Bagian Umum yang dapat memproses pengajuan ini.');
        }

        $request->validate([
            'aksi'    => 'required|in:disetujui,ditolak',
            'catatan' => 'required_if:aksi,ditolak|nullable|string|max:1000',
        ]);

        $pengajuan = PengajuanPerbaikan::findOrFail($id);

        if (!$pengajuan->isPending()) {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $pengajuan->update([
            'status'          => $request->aksi,
            'catatan'         => $request->catatan,
            'diproses_oleh'   => Auth::id(),
            'tanggal_diproses'=> now()->toDateString(),
        ]);

        // Jika ditolak: kembalikan status_aset ke "Aktif"
        if ($request->aksi === 'ditolak') {
            if ($pengajuan->aset) {
                $pengajuan->aset->update(['status_aset' => 'Aktif']);
            }
        }
        // Jika disetujui: pastikan status_aset tetap "Dalam Perbaikan"
        
        // Notify the user who requested
        $pengajuUser = $pengajuan->pengaju;
        if ($pengajuUser) {
            $asetName = $pengajuan->aset ? $pengajuan->aset->nama_aset : 'Aset';
            $pemrosesName = Auth::user()->fullname;
            $statusLabel = $request->aksi === 'disetujui' ? 'disetujui' : 'ditolak';
            
            $title = 'Status Perbaikan Diperbarui';
            $message = "Pengajuan perbaikan Anda untuk aset {$asetName} telah {$statusLabel} oleh {$pemrosesName}.";
            $url = route('perbaikan.show', $pengajuan->id);
            $type = 'perbaikan';

            $pengajuUser->notify(new SystemNotification($title, $message, $url, $type));
        }

        $label = $request->aksi === 'disetujui' ? 'disetujui' : 'ditolak';

        return redirect()
            ->route('perbaikan.show', $pengajuan->id)
            ->with('success', "Pengajuan berhasil {$label}.");
    }

    /**
     * Bagian Umum menandai perbaikan selesai
     */
    public function selesai(Request $request, $id)
    {
        if (!$this->canProcess()) {
            abort(403, 'Hanya Bagian Umum yang dapat menandai perbaikan selesai.');
        }

        $request->validate([
            'kondisi_setelah' => 'required|string|max:50',
            'catatan'         => 'nullable|string|max:1000',
        ]);

        $pengajuan = PengajuanPerbaikan::with('aset')->findOrFail($id);

        if (!$pengajuan->isApproved()) {
            return back()->with('error', 'Hanya pengajuan yang sudah disetujui yang bisa ditandai selesai.');
        }

        //Update pengajuan
        $pengajuan->update([
            'status'          => 'selesai',
            'kondisi_setelah' => $request->kondisi_setelah,
            'catatan'         => $request->catatan ?? $pengajuan->catatan,
            'tanggal_selesai' => now()->toDateString(),
        ]);

        //Update master data_aset
        if ($pengajuan->aset) {
            $pengajuan->aset->update([
                'status_kondisi' => $request->kondisi_setelah,
                'status_aset'    => 'Aktif',
            ]);

            //log_aset otomatis sebagai histori
            LogAset::create([
                'aset_id'      => $pengajuan->aset_id,
                'tanggal_cek'  => now()->toDateString(),
                'kondisi'      => $request->kondisi_setelah,
                'status_aset'  => 'Aktif',
                'keterangan'   => 'Selesai perbaikan — ref. Pengajuan #' . $pengajuan->id
                                  . ($request->catatan ? ' | Catatan: ' . $request->catatan : ''),
                'dicatat_oleh' => Auth::id(),
            ]);
        }

        // Notify the user who requested
        $pengajuUser = $pengajuan->pengaju;
        if ($pengajuUser) {
            $asetName = $pengajuan->aset ? $pengajuan->aset->nama_aset : 'Aset';
            $pemrosesName = Auth::user()->fullname;
            
            $title = 'Perbaikan Aset Selesai';
            $message = "Perbaikan untuk aset {$asetName} yang Anda ajukan telah diselesaikan oleh {$pemrosesName}.";
            $url = route('perbaikan.show', $pengajuan->id);
            $type = 'perbaikan';

            $pengajuUser->notify(new SystemNotification($title, $message, $url, $type));
        }

        return redirect()
            ->route('perbaikan.show', $pengajuan->id)
            ->with('success', 'Perbaikan aset berhasil ditandai selesai. Log monitoring diperbarui secara otomatis.');
    }
}
