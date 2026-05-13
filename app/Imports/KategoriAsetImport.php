<?php

namespace App\Imports;

use App\Models\KategoriAset;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class KategoriAsetImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new KategoriAset([
            'kode' => $row['kode'],
            'nama' => $row['nama'],
        ]);
    }

    public function rules(): array
    {
        return [
            'kode' => 'required|unique:kategori_aset,kode',
            'nama' => 'required|string|max:100',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'kode.required' => 'Kolom kode tidak boleh kosong.',
            'kode.unique'   => 'Kode :input sudah terdaftar.',
            'nama.required' => 'Kolom nama tidak boleh kosong.',
        ];
    }
}
