<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataAset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'data_aset';

    protected $fillable = [
        'nama_aset',
        'nomor_aset',
        'nomor_urut',
        'kategori_id',
        'deskripsi',
        'merek',
        'tanggal_kapitalisasi',
        'id_director',
        'id_divisi',
        'id_department',
        'id_section',
        'id_unit',
        'lokasi_id',
        'pic_id',
        'penanggung_jawab_id',
        'bast',
        'status_kondisi',
        'status_aset',
        'keterangan',
        'dokumen_penghapusan',
    ];

    protected $casts = [
        'tanggal_kapitalisasi' => 'date',
    ];

    public static function getNextNomorUrut(): string
    {
        $max = self::max(\DB::raw('CAST(nomor_urut AS UNSIGNED)')) ?? 0;
        return str_pad($max + 1, 5, '0', STR_PAD_LEFT);
    }

    public function generateNomorAset(?string $noUrut = null): string
    {
        $noUrut = $noUrut ?? $this->nomor_urut;

        // Fallback jika kosong
        if (empty($noUrut)) {
            $noUrut = self::getNextNomorUrut();
        } else {
            $noUrut = str_pad($noUrut, 5, '0', STR_PAD_LEFT);
        }

        // Tahun kapitalisasi
        $tahun = $this->tanggal_kapitalisasi ? date('Y', strtotime($this->tanggal_kapitalisasi)) : date('Y');

        // Kode kategori aset (101, 102, 201, ...)
        $kategori = \App\Models\KategoriAset::find($this->kategori_id);
        $kodeKategori = $kategori ? $kategori->kode : 'XXX';

        // Kode lokasi aset
        $lokAset    = \App\Models\LokasiAset::find($this->lokasi_id);
        $kodeLokasi = $lokAset ? ($lokAset->kode_lokasi ?? 'LOK') : 'LOK';

        // Susun nomor aset
        return "{$kodeKategori}/{$noUrut}/{$kodeLokasi}/{$tahun}";
    }

    /**
     * Generate nomor_aset format baru: [KODE_KLASIFIKASI]/[NOMOR_URUT]/[KODE_LOKASI]/[TAHUN]
     */
    protected static function booted()
    {
        static::creating(function ($aset) {
            if (empty($aset->nomor_urut)) {
                $aset->nomor_urut = self::getNextNomorUrut();
            } else {
                $aset->nomor_urut = str_pad($aset->nomor_urut, 5, '0', STR_PAD_LEFT);
            }
        });

        static::created(function ($aset) {
            $aset->nomor_aset = $aset->generateNomorAset();
            $aset->saveQuietly();
        });

        static::updating(function ($aset) {
            if ($aset->isDirty('nomor_urut') && !empty($aset->nomor_urut)) {
                $aset->nomor_urut = str_pad($aset->nomor_urut, 5, '0', STR_PAD_LEFT);
            }
            if ($aset->isDirty('lokasi_id') || $aset->isDirty('kategori_id') || $aset->isDirty('tanggal_kapitalisasi') || $aset->isDirty('nomor_urut')) {
                $aset->nomor_aset = $aset->generateNomorAset();
            }
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
        if ($this->id_unit && $this->unit) return $this->unit->name_unit;
        if ($this->id_section && $this->section) return $this->section->name_section;
        if ($this->id_department && $this->department) return $this->department->name_department;
        if ($this->id_divisi && $this->divisi) return $this->divisi->nm_divisi;
        if ($this->id_director && $this->director) return $this->director->name_director;
        return 'Tanpa Organisasi';
    }

    public function getResolvedDepartmentNameAttribute(): string
    {
        if ($this->id_unit && $this->unit) {
            if ($this->unit->section && $this->unit->section->department) {
                return $this->unit->section->department->name_department;
            }
            if ($this->unit->department) {
                return $this->unit->department->name_department;
            }
        }
        if ($this->id_section && $this->section && $this->section->department) {
            return $this->section->department->name_department;
        }
        if ($this->id_department && $this->department) {
            return $this->department->name_department;
        }
        if ($this->id_divisi && $this->divisi) {
            return $this->divisi->nm_divisi;
        }
        if ($this->id_director && $this->director) {
            return $this->director->name_director;
        }
        return 'Tanpa Departemen';
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

    /**
     * Kategori aset (menggantikan jenis aset umum/khusus dan kategori lama)
     */
    public function kategoriAset()
    {
        return $this->belongsTo(KategoriAset::class, 'kategori_id');
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

    /**
     * Helper: cek jenis kategori
     */
    public function getIsAsetTetapAttribute(): bool
    {
        return $this->kategoriAset && $this->kategoriAset->tipe === 'aset_tetap';
    }

    /**
     * Helper: cek jenis kategori
     */
    public function getIsInventarisAttribute(): bool
    {
        return $this->kategoriAset && $this->kategoriAset->tipe === 'inventaris';
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id', 'id');
    }

    public function penanggungJawab()
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id', 'id');
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