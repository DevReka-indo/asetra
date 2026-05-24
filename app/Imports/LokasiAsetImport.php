<?php

namespace App\Imports;

use App\Models\LokasiAset;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class LokasiAsetImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row): LokasiAset
    {
        return new LokasiAset([
            'kode_lokasi'   => $row['kode_lokasi'],
            'nama_lokasi'   => $row['nama_lokasi'],
            'detail_lokasi' => $row['detail_lokasi'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'kode_lokasi'   => 'required|string|max:45|unique:lokasi_aset,kode_lokasi',
            'nama_lokasi'   => 'required|string|max:100|unique:lokasi_aset,nama_lokasi',
            'detail_lokasi' => 'nullable|string|max:255',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'kode_lokasi.required' => 'Kolom kode_lokasi tidak boleh kosong.',
            'kode_lokasi.unique'   => 'Kode Lokasi :input sudah terdaftar.',
            'nama_lokasi.required' => 'Kolom nama_lokasi tidak boleh kosong.',
            'nama_lokasi.unique'   => 'Nama Lokasi :input sudah terdaftar.',
        ];
    }
}
