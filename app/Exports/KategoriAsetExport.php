<?php

namespace App\Exports;

use App\Models\KategoriAset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KategoriAsetExport implements FromCollection, WithHeadings, WithMapping
{
    protected $jenisKategoriId;
    protected $search;

    public function __construct($jenisKategoriId = null, $search = null)
    {
        $this->jenisKategoriId = $jenisKategoriId;
        $this->search          = $search;
    }

    public function collection()
    {
        $query = KategoriAset::with('jenisKategori');

        if ($this->jenisKategoriId) {
            $query->where('jenis_kategori_id', $this->jenisKategoriId);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('kode', 'LIKE', "%{$this->search}%")
                  ->orWhere('nama', 'LIKE', "%{$this->search}%");
            });
        }

        return $query->oldest()->get();
    }

    public function headings(): array
    {
        return [
            'Nama Kategori',
            'Kode',
            'Jenis Kategori',
        ];
    }

    public function map($row): array
    {
        return [
            $row->nama,
            $row->kode,
            $row->jenisKategori ? $row->jenisKategori->nama_jenis : '-',
        ];
    }
}
