<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\DataAset;
use App\Models\AsetFoto;
use App\Models\LokasiAset;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Exports\StockOpnameExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class StockOpnameController extends Controller
{
    use \App\Traits\HandlesImageUploads;
    /**
     * Menampilkan daftar periode Stock Opname
     */
    public function index()
    {
        $sessions = StockOpname::with('createdBy')->latest()->get();
        return view('stock-opname.index', compact('sessions'));
    }

    /**
     * Membuat jadwal Stock Opname baru
     */
    public function store(Request $request)
    {
        // Cek apakah ada jadwal opname yang masih aktif
        $activeOpname = StockOpname::where('status', 'aktif')->first();
        if ($activeOpname) {
            $msg = 'Gagal membuat jadwal baru. Masih ada jadwal Stock Opname (' . $activeOpname->periode . ') yang sedang berjalan.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg
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
            'created_by' => auth()->id(),
            'status' => 'aktif'
        ]);

        // Notify all active users
        $users = User::all();
        $title = 'Jadwal Stock Opname Baru';
        $formattedMulai = \Carbon\Carbon::parse($request->tanggal_mulai)->format('d M');
        $formattedAkhir = \Carbon\Carbon::parse($request->tanggal_berakhir)->format('d M Y');
        $message = "Periode Stock Opname {$request->periode} telah dibuat. Pelaksanaan mulai {$formattedMulai} s/d {$formattedAkhir}.";
        $url = route('stock-opname.user-index');
        $type = 'stock_opname';

        foreach ($users as $u) {
            $u->notify(new SystemNotification($title, $message, $url, $type));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal Stock Opname berhasil dibuat.'
            ]);
        }

        return redirect()->route('stock-opname.index')->with('success', 'Jadwal Stock Opname berhasil dibuat.');
    }

    /**
     * Menampilkan Dashboard Detail Stock Opname (Sisi GA/Admin)
     */
    public function show($id)
    {
        $session = StockOpname::findOrFail($id);

        // 1. Ambil SEMUA Aset Aktif yang seharusnya dicek
        $allAsets = DataAset::with([
            'lokasi',
            'department',
            'section.department',
            'unit.section.department',
            'unit.department',
            'divisi',
            'director'
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
            if (!$finding->aset) continue;

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
        $asetsByGroup = $allAsets->groupBy(function($a) {
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
                'findings' => $allFindings->whereIn('aset_id', $groupAsetIds)
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
        $session = StockOpname::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:aktif,selesai'
        ]);

        $session->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status Stock Opname berhasil diperbarui.');
    }

    /**
     * Memperbarui jadwal Stock Opname
     */
    public function update(Request $request, $id)
    {
        $session = StockOpname::findOrFail($id);

        $request->validate([
            'periode' => 'required|string|max:20',
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:aktif,selesai',
        ]);

        // Jika status diubah menjadi aktif, pastikan tidak ada jadwal aktif lain (kecuali dirinya sendiri)
        if ($request->status === 'aktif' && $session->status !== 'aktif') {
            $activeOpname = StockOpname::where('status', 'aktif')->where('id', '!=', $id)->first();
            if ($activeOpname) {
                $msg = 'Gagal mengaktifkan jadwal. Masih ada jadwal Stock Opname (' . $activeOpname->periode . ') yang sedang aktif.';
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $msg
                    ], 422);
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', $msg);
            }
        }

        $session->update([
            'periode' => $request->periode,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_berakhir' => $request->tanggal_berakhir,
            'keterangan' => $request->keterangan,
            'status' => $request->status,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal Stock Opname berhasil diperbarui.'
            ]);
        }

        return redirect()->route('stock-opname.index')->with('success', 'Jadwal Stock Opname berhasil diperbarui.');
    }

    /**
     * Menghapus jadwal Stock Opname beserta temuan-temuannya (jika ada)
     */
    public function destroy($id)
    {
        $session = StockOpname::findOrFail($id);

        DB::beginTransaction();
        try {
            // Hapus file gambar temuan dari storage public jika ada
            $details = StockOpnameDetail::where('stock_opname_id', $id)->get();
            foreach ($details as $detail) {
                if ($detail->foto_temuan) {
                    Storage::disk('public')->delete($detail->foto_temuan);
                }
            }

            // Hapus detail stock opname terkait
            StockOpnameDetail::where('stock_opname_id', $id)->delete();

            // Hapus data stock opname utama
            $session->delete();

            DB::commit();
            return redirect()->route('stock-opname.index')->with('success', 'Jadwal Stock Opname berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Sinkronisasi data temuan ke master data aset
     */
    public function syncData($id)
    {
        $session = StockOpname::findOrFail($id);
        $details = StockOpnameDetail::where('stock_opname_id', $id)->get();

        DB::beginTransaction();
        try {
            foreach ($details as $detail) {
                // Update master data
                $aset = DataAset::find($detail->aset_id);
                if ($aset) {
                    $aset->status_kondisi = $detail->kondisi_temuan;
                    if ($detail->kondisi_temuan === 'Hilang') {
                        $aset->status_aset = 'Hilang';
                        if ($aset->pic) {
                            $title = 'Aset Hilang Terdeteksi';
                            $message = "Aset {$aset->nama_aset} ({$aset->nomor_aset}) terdeteksi Hilang selama Stock Opname periode {$session->periode}. Harap lakukan pencarian fisik.";
                            $url = route('aset.show', $aset->id);
                            $aset->pic->notify(new SystemNotification($title, $message, $url, 'stock_opname'));
                        }
                    }
                    // Note: lokasi_temuan dari input staff bisa berbentuk string bebas atau ID lokasi
                    // Meminta ID lokasi saat scan agar mudah disinkronkan
                    if (is_numeric($detail->lokasi_temuan)) {
                        $aset->lokasi_id = $detail->lokasi_temuan;
                    }
                    $aset->save();

                    // Sinkronisasi foto temuan jika ada
                    if ($detail->foto_temuan) {
                        $exists = AsetFoto::where('aset_id', $aset->id)
                            ->where('path_foto', $detail->foto_temuan)
                            ->exists();
                        
                        if (!$exists) {
                            $urutanTerakhir = $aset->foto()->max('urutan') ?? 0;
                            AsetFoto::create([
                                'aset_id'   => $aset->id,
                                'path_foto' => $detail->foto_temuan,
                                'urutan'    => $urutanTerakhir + 1,
                                'keterangan' => 'Sinkronisasi dari Stock Opname: ' . ($session->periode ?? 'Temuan'),
                            ]);
                        }
                    }
                }
            }
            DB::commit();

            return redirect()->back()->with('success', 'Master data berhasil disinkronisasi dengan temuan Stock Opname.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyinkronkan data: ' . $e->getMessage());
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

        $user = auth()->user();
        $isAdmin = $user->role_id_role == 1 || $user->isBagianUmum();

        $queryAset = DataAset::with(['lokasi', 'kategoriAset']);

        if (!$isAdmin) {
            $queryAset->forUser($user);
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
            'dicekOleh'
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
            'director'
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
            'foto_temuan' => 'nullable|image|mimes:jpeg,png,jpg|max:4096'
        ]);

        $asetId = $request->aset_id;
        $aset = DataAset::find($asetId);

        if (!$aset) {
            // cari berdasarkan nomor aset
            $aset = DataAset::where('nomor_aset', $asetId)->first();
            if (!$aset) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aset tidak ditemukan di database.'
                ], 404);
            }
            $asetId = $aset->id;
        }

        $user = auth()->user();
        $isAdmin = $user->role_id_role == 1 || $user->isBagianUmum();

        if (!$isAdmin) {
            $isAuthorized = DataAset::where('id', $aset->id)->forUser($user)->exists() || $aset->pic_id == $user->id;
            if (!$isAuthorized) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk melakukan stock opname pada aset dari divisi/departemen lain.'
                ], 403);
            }
        }

        // Cek apakah aset ini sudah di-scan di sesi ini
        $existing = StockOpnameDetail::where('stock_opname_id', $request->stock_opname_id)
            ->where('aset_id', $asetId)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Aset ini sudah di-scan pada sesi Stock Opname ini.'
            ], 422);
        }

        $fotoPath = null;
        if ($request->hasFile('foto_temuan')) {
            $fotoPath = $this->compressAndStore($request->file('foto_temuan'), 'stock_opname_foto');
        }

        StockOpnameDetail::create([
            'stock_opname_id' => $request->stock_opname_id,
            'aset_id' => $asetId,
            'dicek_oleh' => auth()->id(),
            'tanggal_cek' => now(),
            'kondisi_temuan' => $request->kondisi_temuan,
            'lokasi_temuan' => $request->lokasi_temuan,
            'foto_temuan' => $fotoPath,
            'keterangan' => $request->keterangan
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Temuan aset berhasil disimpan.'
        ]);
    }

    /**
     * Export laporan stock opname ke Excel
     */
    public function export($id)
    {
        $session = StockOpname::findOrFail($id);
        $fileName = 'Laporan_StockOpname_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $session->periode) . '.xlsx';
        return Excel::download(new StockOpnameExport($id), $fileName);
    }
}
