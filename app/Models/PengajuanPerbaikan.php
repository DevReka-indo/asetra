<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanPerbaikan extends Model
{
    protected $table = 'pengajuan_perbaikan';

    protected $fillable = [
        'aset_id',
        'diajukan_oleh',
        'tanggal_pengajuan',
        'deskripsi_kerusakan',
        'foto_kerusakan',
        'tingkat_urgensi',
        'status',
        'catatan',
        'diproses_oleh',
        'tanggal_diproses',
        'kondisi_setelah',
        'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_diproses'  => 'date',
        'tanggal_selesai'   => 'date',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    public function isPending(): bool
    {
        return $this->status === 'menunggu';
    }

    public function isApproved(): bool
    {
        return $this->status === 'disetujui';
    }

    public function isRejected(): bool
    {
        return $this->status === 'ditolak';
    }

    public function isDone(): bool
    {
        return $this->status === 'selesai';
    }

    /** Label badge warna per status */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'menunggu'  => 'warning',
            'disetujui' => 'primary',
            'ditolak'   => 'danger',
            'selesai'   => 'success',
            default     => 'secondary',
        };
    }

    /** Label teks per status */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'menunggu'  => 'Menunggu Review',
            'disetujui' => 'Disetujui',
            'ditolak'   => 'Ditolak',
            'selesai'   => 'Selesai',
            default     => ucfirst($this->status),
        };
    }

    /** Label urgensi */
    public function getUrgensiLabelAttribute(): string
    {
        return match($this->tingkat_urgensi) {
            'rendah'  => '🟢 Rendah',
            'sedang'  => '🟡 Sedang',
            'tinggi'  => '🔴 Tinggi',
            default   => ucfirst($this->tingkat_urgensi),
        };
    }

    /** Badge warna urgensi */
    public function getUrgensiBadgeAttribute(): string
    {
        return match($this->tingkat_urgensi) {
            'rendah'  => 'success',
            'sedang'  => 'warning',
            'tinggi'  => 'danger',
            default   => 'secondary',
        };
    }

    /** Aset yang dilaporkan */
    public function aset()
    {
        return $this->belongsTo(DataAset::class, 'aset_id');
    }

    /** User yang mengajukan */
    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    /** Admin Bagian Umum yang memproses */
    public function pemroses()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }
}
