<?php

namespace App\Http\Controllers;

use App\Domain\StockOpname\Exceptions\StockOpnameStateException;
use App\Domain\StockOpname\StockOpnameLifecycle;
use App\Exports\StockOpnameExport;
use App\Models\DataAset;
use App\Models\LokasiAset;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class StockOpnameController extends Controller
{
    use \App\Traits\HandlesImageUploads;

    public function __construct(private readonly StockOpnameLifecycle $lifecycle) {}

    /**
     * Menampilkan daftar periode Stock Opname
     */
    public function index()
    {
        Gate::authorize('manage_stock_opname');

        $sessions = StockOpname::with('createdBy')->latest()->get();

        return view('stock-opname.index', compact('sessions'));
    }

    /**
     * Membuat jadwal Stock Opname baru
     */
    public function store(Request $request)
    {
        Gate::authorize('manage_stock_opname');

        // Cek apakah ada jadwal opname yang masih aktif
        $activeOpname = StockOpname::where('status', 'aktif')->first();
        if ($activeOpname) {
            $msg = 'Gagal membuat jadwal baru. Masih ada jadwal Stock Opname ('.$activeOpname->periode.') yang sedang berjalan.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $msg);
        }

        $request->validate([
            'periode' => 'required|string|max:20',
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string',
        ]);

        $session = StockOpname::create([
            'periode' => $request->periode,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_berakhir' => $request->tanggal_berakhir,
            'keterangan' => $request->keterangan,
            'created_by' => Auth::id(),
            'status' => 'aktif',
        ]);

        // Notify only General Affairs and Superadmins
        $users = User::where('role_id_role', 1)->get()->merge(
            User::all()->filter(fn ($u) => $u->isGeneralAffairs())
        )->unique('id');
        $title = 'Jadwal Stock Opname Baru';
        $formattedMulai = \Carbon\Carbon::parse($request->tanggal_mulai)->format('d M');
        $formattedAkhir = \Carbon\Carbon::parse($request->tanggal_berakhir)->format('d M Y');
        $message = "Periode Stock Opname {$request->periode} telah dibuat. Pelaksanaan mulai {$formattedMulai} s/d {$formattedAkhir}.";
        $url = route('stock-opname.index');
        $type = 'stock_opname';

        foreach ($users as $u) {
            $u->notify(new SystemNotification($title, $message, $url, $type));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal Stock Opname berhasil dibuat.',
            ]);
        }

        return redirect()->route('stock-opname.index')->with('success', 'Jadwal Stock Opname berhasil dibuat.');
    }

    /**
     * Menampilkan Dashboard Detail Stock Opname (Sisi GA/Admin)
     */
    public function show($id)
    {
        Gate::authorize('manage_stock_opname');

        $session = StockOpname::findOrFail($id);

        // 1. Ambil SEMUA Aset Aktif yang seharusnya dicek
        $allAsets = DataAset::with([
            'lokasi',
            'department',
            'section.department',
            'unit.section.department',
            'unit.department',
            'divisi',
            'director',
        ])->get();
        $totalAset = $allAsets->count();

        // 2. Ambil SEMUA Temuan untuk sesi ini
        $allFindings = StockOpnameDetail::with(['aset.lokasi', 'dicekOleh'])
            ->where('stock_opname_id', $id)
            ->get();

        $totalChecked = $allFindings->count();
        $checkedAsetIds = $allFindings->pluck('aset_id')->toArray();

        // 3. Deteksi Anomali
        $anomaliLokasi = [];
        $anomaliKondisi = [];

        foreach ($allFindings as $finding) {
            if (! $finding->aset) {
                continue;
            }

            // Anomali Lokasi
            if (is_numeric($finding->lokasi_temuan) && $finding->lokasi_temuan != $finding->aset->lokasi_id) {
                $anomaliLokasi[] = $finding;
            }

            // Anomali Kondisi (Misal: dari Baik turun ke Rusak/Hilang)
            $kondisiBuruk = ['Rusak', 'Hilang', 'Bongkar', 'Tidak Teridentifikasi'];
            if (in_array($finding->kondisi_temuan, $kondisiBuruk) && $finding->aset->status_kondisi == 'Baik') {
                $anomaliKondisi[] = $finding;
            }
        }

        // 4. Hitung Progres per Divisi/Departemen
        $deptStats = [];
        $asetsByGroup = $allAsets->groupBy(function ($a) {
            $divisiName = $a->resolved_divisi_name;
            if ($divisiName && $divisiName !== 'Tanpa Divisi') {
                return $divisiName;
            }
            $deptName = $a->resolved_department_name;
            if ($deptName && $deptName !== 'Tanpa Departemen') {
                return $deptName;
            }

            return 'Tanpa Divisi';
        });

        foreach ($asetsByGroup as $groupName => $groupAsets) {
            $groupAsetIds = $groupAsets->pluck('id')->toArray();
            $groupCheckedCount = $allFindings->whereIn('aset_id', $groupAsetIds)->count();
            $totalInGroup = $groupAsets->count();

            $deptStats[] = [
                'name' => $groupName,
                'total' => $totalInGroup,
                'checked' => $groupCheckedCount,
                'progress' => $totalInGroup > 0 ? round(($groupCheckedCount / $totalInGroup) * 100) : 0,
                'findings' => $allFindings->whereIn('aset_id', $groupAsetIds),
            ];
        }

        // 5. Aset yang belum dicek sama sekali (Hilang/Belum discan)
        $belumDicek = $allAsets->whereNotIn('id', $checkedAsetIds);

        return view('stock-opname.show', compact(
            'session',
            'totalAset',
            'totalChecked',
            'deptStats',
            'anomaliLokasi',
            'anomaliKondisi',
            'belumDicek'
        ));
    }

    /**
     * Update status jadwal (Aktif / Selesai)
     */
    public function updateStatus($id, Request $request)
    {
        Gate::authorize('manage_stock_opname');

        $session = StockOpname::findOrFail($id);

        $request->validate([
            'status' => 'required|in:aktif,selesai',
        ]);

        try {
            $this->lifecycle->update($session, ['status' => $request->status]);
        } catch (StockOpnameStateException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->back()->with('success', 'Status Stock Opname berhasil diperbarui.');
    }

    /**
     * Memperbarui jadwal Stock Opname
     */
    public function update(Request $request, $id)
    {
        Gate::authorize('manage_stock_opname');

        $session = StockOpname::findOrFail($id);

        $request->validate([
            'periode' => 'required|string|max:20',
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:aktif,selesai',
        ]);

        try {
            $this->lifecycle->update($session, [
                'periode' => $request->periode,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_berakhir' => $request->tanggal_berakhir,
                'keterangan' => $request->keterangan,
                'status' => $request->status,
            ]);
        } catch (StockOpnameStateException $exception) {
            return redirect()->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal Stock Opname berhasil diperbarui.',
            ]);
        }

        return redirect()->route('stock-opname.index')->with('success', 'Jadwal Stock Opname berhasil diperbarui.');
    }

    /**
     * Menghapus jadwal Stock Opname beserta temuan-temuannya (jika ada)
     */
    public function destroy($id)
    {
        Gate::authorize('manage_stock_opname');

        $session = StockOpname::findOrFail($id);

        try {
            $findingPhotoPaths = $this->lifecycle->delete($session);
        } catch (StockOpnameStateException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Stock opname deletion failed.', [
                'stock_opname_id' => $session->id,
                'exception' => $exception,
            ]);

            return redirect()->back()->with('error', 'Gagal menghapus jadwal Stock Opname.');
        }

        foreach ($findingPhotoPaths as $findingPhotoPath) {
            Storage::disk('public')->delete($findingPhotoPath);
        }

        return redirect()->route('stock-opname.index')->with('success', 'Jadwal Stock Opname berhasil dihapus.');
    }

    /**
     * Sinkronisasi data temuan ke master data aset
     */
    public function syncData($id)
    {
        Gate::authorize('manage_stock_opname');

        $session = StockOpname::findOrFail($id);

        try {
            $session = $this->lifecycle->synchronize($session);
            $this->notifyMissingAssetPics($session);

            return redirect()->back()->with('success', 'Master data berhasil disinkronisasi dengan temuan Stock Opname.');
        } catch (StockOpnameStateException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Stock opname synchronization failed.', [
                'stock_opname_id' => $session->id,
                'exception' => $exception,
            ]);

            return redirect()->back()->with('error', 'Gagal menyinkronkan data Stock Opname.');
        }
    }

    /**
     * SISI USER: Menampilkan jadwal aktif untuk dipilih
     */
    public function userIndex()
    {
        // Tampilkan hanya jadwal yang statusnya aktif
        $sessions = StockOpname::where('status', 'aktif')->latest()->get();

        return view('stock-opname.user-index', compact('sessions'));
    }

    /**
     * SISI USER: Menampilkan daftar aset perlu dicek dan sudah dicek berdasarkan scope departemen
     */
    public function userShow($id)
    {
        $session = StockOpname::findOrFail($id);
        if ($session->status != 'aktif') {
            return redirect()->route('stock-opname.user-index')->with('error', 'Jadwal Stock Opname tersebut sudah tidak aktif.');
        }

        $user = Auth::user();
        $isAdmin = Gate::allows('manage_stock_opname');

        $queryAset = DataAset::with(['lokasi', 'kategoriAset']);

        if (! $isAdmin) {
            $queryAset->where(function ($query) use ($user) {
                $query->forUser($user)
                    ->orWhere('pic_id', $user->id);
            });
        }

        // Semua aset yang masuk scope user
        $allAsetIds = $queryAset->pluck('id')->toArray();

        // Aset yang telah dicek pada sesi ini
        $telahDicek = StockOpnameDetail::with([
            'aset.lokasi',
            'aset.kategoriAset',
            'aset.department',
            'aset.section.department',
            'aset.unit.section.department',
            'aset.unit.department',
            'aset.divisi',
            'aset.director',
            'dicekOleh',
        ])
            ->where('stock_opname_id', $id)
            ->whereIn('aset_id', $allAsetIds)
            ->latest()
            ->get();

        $telahDicekIds = $telahDicek->pluck('aset_id')->toArray();

        // Aset yang belum dicek pada sesi ini
        $belumDicek = DataAset::with([
            'lokasi',
            'kategoriAset',
            'department',
            'section.department',
            'unit.section.department',
            'unit.department',
            'divisi',
            'director',
        ])
            ->whereIn('id', $allAsetIds)
            ->whereNotIn('id', $telahDicekIds)
            ->get();

        $lokasis = LokasiAset::oldest()->get();

        // Daftar Divisi & Departemen unik untuk filter GA/Admin
        $availableDivisis = [];
        $availableDepts = [];
        if ($isAdmin) {
            $availableDivisis = \App\Models\Divisi::orderBy('nm_divisi')
                ->pluck('nm_divisi')
                ->unique()
                ->filter()
                ->values()
                ->toArray();

            $availableDepts = \App\Models\Department::orderBy('name_department')
                ->pluck('name_department')
                ->unique()
                ->filter()
                ->values()
                ->toArray();
        }

        return view('stock-opname.user-show', compact('session', 'telahDicek', 'belumDicek', 'lokasis', 'availableDivisis', 'availableDepts', 'isAdmin'));
    }

    /**
     * API: Menerima data scan dari halaman scanner
     */
    public function scanStore(Request $request)
    {
        $request->validate([
            'stock_opname_id' => 'required|exists:stock_opname,id',
            'aset_id' => 'required',
            'kondisi_temuan' => 'required|string',
            'lokasi_temuan' => 'required_unless:kondisi_temuan,Hilang|nullable|string',
            'foto_temuan' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        $session = StockOpname::findOrFail($request->integer('stock_opname_id'));

        if (! $session->canAcceptFindings()) {
            $exception = StockOpnameStateException::cannotAcceptFindings($session);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], $exception->httpStatus);
        }

        $asetId = $request->aset_id;
        $aset = DataAset::find($asetId);

        if (! $aset) {
            // cari berdasarkan nomor aset
            $aset = DataAset::where('nomor_aset', $asetId)->first();
            if (! $aset) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aset tidak ditemukan di database.',
                ], 404);
            }
            $asetId = $aset->id;
        }

        $user = Auth::user();
        $isAdmin = Gate::allows('manage_stock_opname');

        if (! $isAdmin) {
            $isAuthorized = DataAset::where('id', $aset->id)->forUser($user)->exists() || $aset->pic_id == $user->id;
            if (! $isAuthorized) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk melakukan stock opname pada aset dari divisi/departemen lain.',
                ], 403);
            }
        }

        // Cek apakah aset ini sudah di-scan di sesi ini
        $existing = StockOpnameDetail::where('stock_opname_id', $session->id)
            ->where('aset_id', $asetId)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Aset ini sudah di-scan pada sesi Stock Opname ini.',
            ], 422);
        }

        $fotoPath = null;
        if ($request->hasFile('foto_temuan')) {
            $fotoPath = $this->compressAndStore($request->file('foto_temuan'), 'stock_opname_foto');
        }

        try {
            $this->lifecycle->recordFinding($session, [
                'aset_id' => $asetId,
                'dicek_oleh' => Auth::id(),
                'tanggal_cek' => now(),
                'kondisi_temuan' => $request->kondisi_temuan,
                'lokasi_temuan' => $request->lokasi_temuan,
                'foto_temuan' => $fotoPath,
                'keterangan' => $request->keterangan,
            ]);
        } catch (StockOpnameStateException $exception) {
            if ($fotoPath !== null) {
                Storage::disk('public')->delete($fotoPath);
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], $exception->httpStatus);
        } catch (Throwable $exception) {
            if ($fotoPath !== null) {
                Storage::disk('public')->delete($fotoPath);
            }

            throw $exception;
        }

        return response()->json([
            'success' => true,
            'message' => 'Temuan aset berhasil disimpan.',
        ]);
    }

    /**
     * Export laporan stock opname ke Excel
     */
    public function export($id)
    {
        Gate::authorize('manage_stock_opname');

        $session = StockOpname::findOrFail($id);
        $fileName = 'Laporan_StockOpname_'.preg_replace('/[^A-Za-z0-9_\-]/', '_', $session->periode).'.xlsx';

        return Excel::download(new StockOpnameExport($id), $fileName);
    }

    private function notifyMissingAssetPics(StockOpname $session): void
    {
        $missingAssetFindings = $session->detail()
            ->where('kondisi_temuan', 'Hilang')
            ->with('aset.pic')
            ->get();

        foreach ($missingAssetFindings as $finding) {
            $asset = $finding->aset;

            if ($asset?->pic === null) {
                continue;
            }

            try {
                $title = 'Aset Hilang Terdeteksi';
                $message = "Aset {$asset->nama_aset} ({$asset->nomor_aset}) terdeteksi Hilang selama Stock Opname periode {$session->periode}. Harap lakukan pencarian fisik.";
                $url = route('aset.show', $asset->id);
                $asset->pic->notify(new SystemNotification($title, $message, $url, 'stock_opname'));
            } catch (Throwable $exception) {
                Log::warning('Missing asset stock opname notification failed.', [
                    'stock_opname_id' => $session->id,
                    'asset_id' => $asset->id,
                    'exception' => $exception,
                ]);
            }
        }
    }
}
