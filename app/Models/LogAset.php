<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAset extends Model
{
    protected $table = 'log_aset';

    protected $fillable = [
        'aset_id',
        'tanggal_cek',
        'kondisi',
        'status_aset',
        'keterangan',
        'flag_perubahan',
        'lokasi_id',
        'id_director',
        'id_divisi',
        'id_department',
        'id_section',
        'id_unit',
        'foto_bukti',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal_cek' => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function getOrganisasiTerikatAttribute(): string
    {
        if ($this->id_unit && $this->unit) return "Unit: " . $this->unit->name_unit;
        if ($this->id_section && $this->section) return "Bagian: " . $this->section->name_section;
        if ($this->id_department && $this->department) return "Departemen: " . $this->department->name_department;
        if ($this->id_divisi && $this->divisi) return "Divisi: " . $this->divisi->nm_divisi;
        if ($this->id_director && $this->director) return "Direktur: " . $this->director->name_director;
        return 'Tanpa Organisasi';
    }

    /**
     * Lokasi aset saat dicatat
     */
    public function lokasi()
    {
        return $this->belongsTo(LokasiAset::class, 'lokasi_id', 'lokasi_id');
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

    /**
     * Aset yang dicatat dalam log ini
     */
    public function aset()
    {
        return $this->belongsTo(DataAset::class, 'aset_id');
    }

    /**
     * User yang mencatat log ini
     */
    public function dicatatOleh()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
