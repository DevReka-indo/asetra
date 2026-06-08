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
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat jadwal baru. Masih ada jadwal Stock Opname (' . $activeOpname->periode . ') yang sedang berjalan.');
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

        // 4. Hitung Progres per Divisi
        $divisiStats = [];
        $asetsByDivisi = $allAsets->groupBy(fn($a) => $a->resolved_divisi_name);

        foreach ($asetsByDivisi as $divisiName => $divisiAsets) {
            $divisiAsetIds = $divisiAsets->pluck('id')->toArray();
            $divisiCheckedCount = $allFindings->whereIn('aset_id', $divisiAsetIds)->count();
            $totalInDivisi = $divisiAsets->count();
            
            $divisiStats[] = [
                'name' => $divisiName,
                'total' => $totalInDivisi,
                'checked' => $divisiCheckedCount,
                'progress' => $totalInDivisi > 0 ? round(($divisiCheckedCount / $totalInDivisi) * 100) : 0,
                'findings' => $allFindings->whereIn('aset_id', $divisiAsetIds)
            ];
        }

        // 5. Aset yang belum dicek sama sekali (Hilang/Belum discan)
        $belumDicek = $allAsets->whereNotIn('id', $checkedAsetIds);

        return view('stock-opname.show', compact(
            'session', 
            'totalAset', 
            'totalChecked', 
            'divisiStats', 
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

        // Daftar Divisi unik untuk filter GA/Admin
        $availableDivisis = [];
        if ($isAdmin) {
            $availableDivisis = DataAset::with([
                'department',
                'section.department',
                'unit.section.department',
                'unit.department',
                'divisi',
                'director'
            ])
            ->get()
            ->map(fn($a) => $a->resolved_divisi_name)
            ->unique()
            ->filter()
            ->values()
            ->toArray();
        }

        return view('stock-opname.user-show', compact('session', 'telahDicek', 'belumDicek', 'lokasis', 'availableDivisis', 'isAdmin'));
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
            'lokasi_temuan' => 'required_unless:kondisi_temuan,Tidak Teridentifikasi,Hilang|nullable|string',
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
