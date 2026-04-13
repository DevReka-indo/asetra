<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataAset extends Model
{
    use HasFactory;

    protected $table = 'data_aset';

    protected $fillable = [
        'nama_aset',
        'nomor_aset',
        'kode_aset',
        'jenis_aset_khusus_id',
        'deskripsi',
        'merek',
        'tahun_kapitalisasi',
        'id_divisi',
        'sumber_kepemilikan_id',
        'lokasi_id',
        'pic_id',
        'status_kondisi',
        'status_aset',
        'keterangan_kondisi',
        'keterangan',
    ];

    protected $casts = [
        'tahun_kapitalisasi' => 'integer',
    ];

    /**
     * Generate nomor_aset.
     */
    protected static function booted()
    {
        static::created(function ($aset) {
            $idFormatted = str_pad($aset->id, 3, '0', STR_PAD_LEFT);
            $tahun       = $aset->tahun_kapitalisasi ?? date('Y');

            //Kode sumber kepemilikan
            $sumber          = \App\Models\SumberKepemilikan::find($aset->sumber_kepemilikan_id);
            $kodeKepemilikan = $sumber ? ($sumber->kode ?? 'REKA') : 'REKA';

            //Kode lokasi aset
            $lokAset   = \App\Models\LokasiAset::find($aset->lokasi_id);
            $kodeLokasi = $lokAset ? ($lokAset->kode_lokasi ?? 'LOK') : 'LOK';

            //Kode jenis aset
            $kodeJenis = $aset->kode_aset;
            if (!$kodeJenis) {
                $jenis     = \App\Models\JenisAsetKhusus::find($aset->jenis_aset_khusus_id);
                $kodeJenis = $jenis ? $jenis->full_kode : 'XXXX';
            }

            $aset->nomor_aset = "{$idFormatted}/{$kodeKepemilikan}/{$kodeJenis}/{$kodeLokasi}/{$tahun}";
            $aset->saveQuietly();
        });
    }

    /**
     * Tanggal cek terakhir
     */
    public function getTanggalCekTerakhirAttribute(): ?string
    {
        return $this->logAset()->latest('tanggal_cek')->value('tanggal_cek');
    }


    public function jenisAsetKhusus()
    {
        return $this->belongsTo(JenisAsetKhusus::class, 'jenis_aset_khusus_id', 'id');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi', 'id_divisi');
    }

    public function lokasi()
    {
        return $this->belongsTo(LokasiAset::class, 'lokasi_id', 'lokasi_id');
    }

    /**
     * Sumber kepemilikan aset
     */
    public function sumberKepemilikan()
    {
        return $this->belongsTo(SumberKepemilikan::class, 'sumber_kepemilikan_id', 'id');
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id', 'id');
    }

    /**
     * Semua foto aset 
     */
    public function foto()
    {
        return $this->hasMany(AsetFoto::class, 'aset_id')->orderBy('urutan');
    }

    /**
     * Foto pertama aset 
     */
    public function fotoPertama()
    {
        return $this->hasOne(AsetFoto::class, 'aset_id')->orderBy('urutan');
    }

    /**
     * Riwayat log/pengecekan aset
     */
    public function logAset()
    {
        return $this->hasMany(LogAset::class, 'aset_id');
    }

    /**
     * Detail stock opname 
     */
    public function stockOpnameDetail()
    {
        return $this->hasMany(StockOpnameDetail::class, 'aset_id');
    }
}