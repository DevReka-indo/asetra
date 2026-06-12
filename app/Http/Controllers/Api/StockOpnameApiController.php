<?php

namespace App\Http\Controllers\Api;

use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\DataAset;
use App\Models\AsetFoto;
use App\Models\LokasiAset;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockOpnameApiController extends BaseApiController
{
    use \App\Traits\HandlesImageUploads;
    /**
     * List all Stock Opname sessions
     */
    public function index()
    {
        $sessions = StockOpname::with('createdBy')->latest()->get();
        return $this->success($sessions, 'Stock opname sessions retrieved successfully.');
    }

    /**
     * Show detail of a Stock Opname
     */
    public function show($id)
    {
        $session = StockOpname::findOrFail($id);
        $user = auth()->user();
        $isAdmin = $user->role_id_role == 1 || $user->isBagianUmum();

        $queryAset = DataAset::with(['lokasi', 'kategoriAset']);

        if (!$isAdmin) {
            $queryAset->forUser($user);
        }

        $allAsetIds = $queryAset->pluck('id')->toArray();
        $totalAset = count($allAsetIds);

        // cek aset yang sudah dicek
        $telahDicek = StockOpnameDetail::with(['aset.lokasi', 'dicekOleh'])
            ->where('stock_opname_id', $id)
            ->whereIn('aset_id', $allAsetIds)
            ->get();
        $totalChecked = $telahDicek->count();
        $checkedAsetIds = $telahDicek->pluck('aset_id')->toArray();

        // cek aset yang belum dicek
        $belumDicek = DataAset::with(['lokasi', 'kategoriAset'])
            ->whereIn('id', $allAsetIds)
            ->whereNotIn('id', $checkedAsetIds)
            ->get();

        $response = [
            'session' => $session,
            'is_admin' => $isAdmin,
            'total_assets' => $totalAset,
            'total_checked' => $totalChecked,
        ];

        if ($isAdmin) {
            // General Affairs superdmin Stats
            $anomaliLokasi = [];
            $anomaliKondisi = [];

            foreach ($telahDicek as $finding) {
                if (!$finding->aset) continue;

                // Location anomaly
                if (is_numeric($finding->lokasi_temuan) && $finding->lokasi_temuan != $finding->aset->lokasi_id) {
                    $anomaliLokasi[] = $finding;
                }

                // kondisi
                $kondisiBuruk = ['Rusak', 'Hilang', 'Bongkar', 'Tidak Teridentifikasi'];
                if (in_array($finding->kondisi_temuan, $kondisiBuruk) && $finding->aset->status_kondisi == 'Baik') {
                    $anomaliKondisi[] = $finding;
                }
            }

            $response['anomali_lokasi'] = $anomaliLokasi;
            $response['anomali_kondisi'] = $anomaliKondisi;
            $response['telah_dicek'] = $telahDicek;
            $response['belum_dicek'] = $belumDicek;
        } else {
            // Staff Checklist
            $response['telah_dicek'] = $telahDicek;
            $response['belum_dicek'] = $belumDicek;
        }

        return $this->success($response, 'Stock opname session details retrieved successfully.');
    }

    /**
     * Create a new Stock Opname session
     */
    public function store(Request $request)
    {
        // Check if there is an active session
        $activeOpname = StockOpname::where('status', 'aktif')->first();
        if ($activeOpname) {
            return $this->error('Gagal membuat jadwal baru. Masih ada jadwal Stock Opname (' . $activeOpname->periode . ') yang sedang berjalan.', 422);
        }

        $request->validate([
            'periode'          => 'required|string|max:20',
            'tanggal_mulai'    => 'required|date',
            'tanggal_berakhir' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'       => 'nullable|string',
        ]);

        $session = StockOpname::create([
            'periode'          => $request->periode,
            'tanggal_mulai'    => $request->tanggal_mulai,
            'tanggal_berakhir' => $request->tanggal_berakhir,
            'keterangan'       => $request->keterangan,
            'created_by'       => auth()->id(),
            'status'           => 'aktif'
        ]);

        // Notify only General Affairs and Superadmins
        try {
            $users = User::where('role_id_role', 1)->get()->merge(
                User::all()->filter(fn($u) => $u->isGeneralAffairs())
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
        } catch (\Exception $e) {
            Log::warning("Notifications failed for new stock opname: " . $e->getMessage());
        }

        return $this->success($session, 'Jadwal Stock Opname berhasil dibuat.', 210);
    }

    /**
     * Update Stock Opname Status
     */
    public function updateStatus($id, Request $request)
    {
        $session = StockOpname::findOrFail($id);

        $request->validate([
            'status' => 'required|in:aktif,selesai'
        ]);

        $session->update(['status' => $request->status]);

        return $this->success($session, 'Status Stock Opname berhasil diperbarui.');
    }

    /**
     * Record a scan check on an asset
     */
    public function scan(Request $request)
    {
        $request->validate([
            'stock_opname_id' => 'required|exists:stock_opname,id',
            'aset_id'         => 'required|string', // Can be ID or Nomor Aset
            'kondisi_temuan'  => 'required|string',
            'lokasi_temuan'   => 'required_unless:kondisi_temuan,Tidak Teridentifikasi,Hilang|nullable|string',
            'foto_temuan'     => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            'keterangan'      => 'nullable|string'
        ]);

        $asetIdOrNo = $request->aset_id;
        $aset = DataAset::find($asetIdOrNo);

        if (!$aset) {
            // Find by asset number
            $aset = DataAset::where('nomor_aset', $asetIdOrNo)->first();
            if (!$aset) {
                return $this->error('Aset tidak ditemukan di database.', 404);
            }
        }

        $user = auth()->user();
        $isAdmin = $user->role_id_role == 1 || $user->isBagianUmum();

        if (!$isAdmin) {
            $isAuthorized = DataAset::where('id', $aset->id)->forUser($user)->exists() || $aset->pic_id == $user->id;
            if (!$isAuthorized) {
                return $this->error('Anda tidak memiliki akses untuk melakukan stock opname pada aset dari divisi/departemen lain.', 403);
            }
        }

        // Check if already scanned in this session
        $existing = StockOpnameDetail::where('stock_opname_id', $request->stock_opname_id)
            ->where('aset_id', $aset->id)
            ->first();

        if ($existing) {
            return $this->error('Aset ini sudah di-scan pada sesi Stock Opname ini.', 422);
        }

        $fotoPath = null;
        if ($request->hasFile('foto_temuan')) {
            $fotoPath = $this->compressAndStore($request->file('foto_temuan'), 'stock_opname_foto');
        }

        $detail = StockOpnameDetail::create([
            'stock_opname_id' => $request->stock_opname_id,
            'aset_id'         => $aset->id,
            'dicek_oleh'      => auth()->id(),
            'tanggal_cek'     => now(),
            'kondisi_temuan'  => $request->kondisi_temuan,
            'lokasi_temuan'   => $request->lokasi_temuan,
            'foto_temuan'     => $fotoPath,
            'keterangan'      => $request->keterangan
        ]);

        $detail->load(['aset', 'dicekOleh']);

        return $this->success($detail, 'Temuan aset berhasil disimpan.', 210);
    }

    /**
     * Sync findings to master asset data
     */
    public function sync($id)
    {
        $session = StockOpname::findOrFail($id);
        $details = StockOpnameDetail::where('stock_opname_id', $id)->get();

        DB::beginTransaction();
        try {
            foreach ($details as $detail) {
                $aset = DataAset::find($detail->aset_id);
                if ($aset) {
                    $aset->status_kondisi = $detail->kondisi_temuan;
                    // If location is a numeric ID, update it
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
            return $this->success(null, 'Master data berhasil disinkronisasi dengan temuan Stock Opname.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Gagal menyinkronkan data: ' . $e->getMessage(), 500);
        }
    }
}
