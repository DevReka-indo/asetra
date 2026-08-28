<?php

namespace App\Http\Controllers\Api;

use App\Models\DataAset;
use App\Models\LogAset;
use App\Models\PengajuanPerbaikan;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PengajuanPerbaikanApiController extends BaseApiController
{
    use \App\Traits\HandlesImageUploads;

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PengajuanPerbaikan::with(['aset', 'pengaju', 'pemroses'])->latest();

        if (Gate::denies('manage_perbaikan_aset')) {
            $query->where(function ($q) use ($user) {
                $q->where('diajukan_oleh', $user->id)
                    ->orWhereHas('aset', function ($qAset) use ($user) {
                        $qAset->where(function ($subQ) use ($user) {
                            $subQ->forUser($user);
                            $subQ->orWhere('pic_id', $user->id);
                        });
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 15);
        $pengajuans = $query->paginate($perPage);

        return $this->success($pengajuans, 'Data pengajuan perbaikan retrieved successfully.');
    }

    /**
     * Store new repair submission
     */
    public function store(Request $request)
    {
        $request->validate([
            'aset_id' => 'required|exists:data_aset,id',
            'deskripsi_kerusakan' => 'required|string|max:2000',
            'tingkat_urgensi' => 'required|in:rendah,sedang,tinggi',
            'foto_kerusakan' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Mencegah duplikasi pengajuan untuk aset yang sama yang masih dalam proses
        $sudahAda = PengajuanPerbaikan::where('aset_id', $request->aset_id)
            ->whereIn('status', ['menunggu', 'disetujui'])
            ->exists();

        if ($sudahAda) {
            return $this->error('Aset ini sudah memiliki pengajuan perbaikan yang sedang diproses. Silakan tunggu hingga selesai.', 422);
        }

        $data = [
            'aset_id' => $request->aset_id,
            'diajukan_oleh' => Auth::id(),
            'tanggal_pengajuan' => now()->toDateString(),
            'deskripsi_kerusakan' => $request->deskripsi_kerusakan,
            'tingkat_urgensi' => $request->tingkat_urgensi,
            'status' => 'menunggu',
        ];

        if ($request->hasFile('foto_kerusakan')) {
            $data['foto_kerusakan'] = $this->compressAndStore($request->file('foto_kerusakan'), 'perbaikan');
        }

        $pengajuan = PengajuanPerbaikan::create($data);

        // Update status_aset to "Dalam Perbaikan"
        $aset = DataAset::findOrFail($request->aset_id);
        $aset->update(['status_aset' => 'Dalam Perbaikan']);

        // Kirim Notification
        try {
            $userFullName = Auth::user()->fullname;
            $adminGas = User::where('role_id_role', 1)->get()->merge(
                User::all()->filter(fn ($u) => $u->isGeneralAffairs())
            )->unique('id');

            $title = 'Pengajuan Perbaikan Baru';
            $message = "{$userFullName} mengajukan perbaikan untuk aset {$aset->nama_aset}.";
            $url = route('perbaikan.show', $pengajuan->id);
            $type = 'perbaikan';

            foreach ($adminGas as $adminGa) {
                $adminGa->notify(new SystemNotification($title, $message, $url, $type));
            }
        } catch (\Exception $e) {
            Log::warning('Repair submission notifications failed: '.$e->getMessage());
        }

        $pengajuan->load(['aset', 'pengaju']);

        return $this->success($pengajuan, 'Pengajuan perbaikan aset berhasil dikirim!', 210);
    }

    /**
     * Show detail of single repair request
     */
    public function show($id)
    {
        $pengajuan = PengajuanPerbaikan::with(['aset.lokasi', 'pengaju', 'pemroses'])->findOrFail($id);
        $user = Auth::user();

        // Access control
        if (Gate::denies('manage_perbaikan_aset') && $pengajuan->diajukan_oleh !== $user->id) {
            $aset = $pengajuan->aset;
            $isAuthorized = false;

            if ($aset) {
                $isAuthorized = DataAset::where('id', $aset->id)->forUser($user)->exists() || $aset->pic_id == $user->id;
            }

            if (! $isAuthorized) {
                return $this->error('Anda tidak memiliki akses ke pengajuan ini.', 403);
            }
        }

        return $this->success($pengajuan, 'Detail pengajuan perbaikan retrieved successfully.');
    }

    /**
     * Approve or reject a repair submission.
     */
    public function proses(Request $request, $id)
    {
        Gate::authorize('manage_perbaikan_aset');

        $request->validate([
            'aksi' => 'required|in:disetujui,ditolak',
            'catatan' => 'required_if:aksi,ditolak|nullable|string|max:1000',
        ]);

        $pengajuan = PengajuanPerbaikan::findOrFail($id);

        if (! $pengajuan->isPending()) {
            return $this->error('Pengajuan ini sudah diproses sebelumnya.', 422);
        }

        $pengajuan->update([
            'status' => $request->aksi,
            'catatan' => $request->catatan,
            'diproses_oleh' => Auth::id(),
            'tanggal_diproses' => now()->toDateString(),
        ]);

        // If rejected, set status_aset back to "Aktif"
        if ($request->aksi === 'ditolak') {
            if ($pengajuan->aset) {
                $pengajuan->aset->update(['status_aset' => 'Aktif']);
            }
        }

        // Notify
        try {
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
        } catch (\Exception $e) {
            Log::warning('Repair process status notifications failed: '.$e->getMessage());
        }

        $pengajuan->load(['aset', 'pengaju', 'pemroses']);

        return $this->success($pengajuan, "Pengajuan berhasil {$request->aksi}.");
    }

    /**
     * Mark a repair as completed.
     */
    public function selesai(Request $request, $id)
    {
        Gate::authorize('manage_perbaikan_aset');

        $request->validate([
            'kondisi_setelah' => 'required|string|max:50',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $pengajuan = PengajuanPerbaikan::with('aset')->findOrFail($id);

        if (! $pengajuan->isApproved()) {
            return $this->error('Hanya pengajuan yang sudah disetujui yang bisa ditandai selesai.', 422);
        }

        $pengajuan->update([
            'status' => 'selesai',
            'kondisi_setelah' => $request->kondisi_setelah,
            'catatan' => $request->catatan ?? $pengajuan->catatan,
            'tanggal_selesai' => now()->toDateString(),
        ]);

        // Update master asset data
        if ($pengajuan->aset) {
            $pengajuan->aset->update([
                'status_kondisi' => $request->kondisi_setelah,
                'status_aset' => 'Aktif',
            ]);

            // Create LogAset record
            LogAset::create([
                'aset_id' => $pengajuan->aset_id,
                'tanggal_cek' => now()->toDateString(),
                'kondisi' => $request->kondisi_setelah,
                'status_aset' => 'Aktif',
                'keterangan' => 'Selesai perbaikan — ref. Pengajuan #'.$pengajuan->id
                                  .($request->catatan ? ' | Catatan: '.$request->catatan : ''),
                'dicatat_oleh' => Auth::id(),
            ]);
        }

        // Notify submitter
        try {
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
        } catch (\Exception $e) {
            Log::warning('Repair completion notifications failed: '.$e->getMessage());
        }

        $pengajuan->load(['aset', 'pengaju', 'pemroses']);

        return $this->success($pengajuan, 'Perbaikan aset berhasil ditandai selesai.');
    }
}
