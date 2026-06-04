<?php

namespace App\Exports;

use App\Models\JenisKategori;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JenisKategoriExport implements FromCollection, WithHeadings, WithMapping
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = JenisKategori::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('kode_awalan', 'LIKE', "%{$this->search}%")
                  ->orWhere('nama_jenis', 'LIKE', "%{$this->search}%");
            });
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Nama Jenis',
            'Kode Awalan',
            'Warna Label',
        ];
    }

    public function map($row): array
    {
        return [
            $row->nama_jenis,
            $row->kode_awalan,
            $row->warna_label ?? '#FF5E9B',
        ];
    }
}
