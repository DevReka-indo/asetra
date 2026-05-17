<?php

namespace App\Exports;

use App\Models\LokasiAset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LokasiAsetExport implements FromCollection, WithHeadings, WithMapping
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = LokasiAset::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('kode_lokasi', 'LIKE', "%{$this->search}%")
                  ->orWhere('nama_lokasi', 'LIKE', "%{$this->search}%")
                  ->orWhere('detail_lokasi', 'LIKE', "%{$this->search}%");
            });
        }

        return $query->oldest()->get();
    }

    public function headings(): array
    {
        return [
            'Kode Lokasi',
            'Nama Lokasi',
            'Detail Lokasi',
        ];
    }

    public function map($row): array
    {
        return [
            $row->kode_lokasi,
            $row->nama_lokasi,
            $row->detail_lokasi,
        ];
    }
}
