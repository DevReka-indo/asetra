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
        'id_director',
        'id_divisi',
        'id_department',
        'id_section',
        'id_unit',
        'sumber_kepemilikan_id',
        'lokasi_id',
        'kategori_id',
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

            //Kode kategori aset
            $katAset   = \App\Models\KategoriAset::find($aset->kategori_id);
            $kodeKategori = $katAset ? ($katAset->kode ?? 'XX') : 'XX';

            //Kode jenis aset
            $kodeJenis = $aset->kode_aset;
            if (!$kodeJenis) {
                $jenis     = \App\Models\JenisAsetKhusus::find($aset->jenis_aset_khusus_id);
                $kodeJenis = $jenis ? $jenis->full_kode : 'XXXX';
            }

            $aset->nomor_aset = "{$idFormatted}/{$kodeKepemilikan}/{$kodeJenis}/{$kodeLokasi}/{$kodeKategori}/{$tahun}";
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

    public function getOrganisasiTerikatAttribute(): string
    {
        if ($this->id_unit && $this->unit) return "Unit: " . $this->unit->name_unit;
        if ($this->id_section && $this->section) return "Bagian: " . $this->section->name_section;
        if ($this->id_department && $this->department) return "Departemen: " . $this->department->name_department;
        if ($this->id_divisi && $this->divisi) return "Divisi: " . $this->divisi->nm_divisi;
        if ($this->id_director && $this->director) return "Direktur: " . $this->director->name_director;
        return 'Tanpa Organisasi';
    }

    public function getKodeOrganisasiAttribute(): ?string
    {
        if ($this->id_unit) return "unit_" . $this->id_unit;
        if ($this->id_section) return "section_" . $this->id_section;
        if ($this->id_department) return "department_" . $this->id_department;
        if ($this->id_divisi) return "divisi_" . $this->id_divisi;
        if ($this->id_director) return "director_" . $this->id_director;
        return null;
    }

    public function jenisAsetKhusus()
    {
        return $this->belongsTo(JenisAsetKhusus::class, 'jenis_aset_khusus_id', 'id');
    }

    public function director()
    {
        return $this->belongsTo(Director::class, 'id_director', 'id_director');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi', 'id_divisi');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'id_department', 'id_department');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'id_section', 'id_section');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit', 'id_unit');
    }

    public function lokasi()
    {
        return $this->belongsTo(LokasiAset::class, 'lokasi_id', 'lokasi_id');
    }

    public function kategoriAset()
    {
        return $this->belongsTo(KategoriAset::class, 'kategori_id', 'kategori_id');
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

    /**
     * Semua pengajuan perbaikan untuk aset ini
     */
    public function pengajuanPerbaikan()
    {
        return $this->hasMany(PengajuanPerbaikan::class, 'aset_id');
    }

    /**
     * Pengajuan perbaikan yang masih aktif (menunggu atau disetujui)
     */
    public function pengajuanPerbaikanAktif()
    {
        return $this->hasMany(PengajuanPerbaikan::class, 'aset_id')
                    ->whereIn('status', ['menunggu', 'disetujui']);
    }
}