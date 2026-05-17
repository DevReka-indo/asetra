<?php

namespace App\Imports;

use App\Models\JenisKategori;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class JenisKategoriImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row): JenisKategori
    {
        $warna = $row['warna_label'] ?? '#ea6565';
        if (empty($warna)) {
            $warna = '#ea6565';
        }
        return new JenisKategori([
            'nama_jenis'  => $row['nama_jenis'],
            'kode_awalan' => $row['kode_awalan'],
            'warna_label' => $warna,
        ]);
    }

    public function rules(): array
    {
        return [
            'nama_jenis'  => 'required|string|max:100',
            'kode_awalan' => 'required|string|max:10|unique:jenis_kategori,kode_awalan',
            'warna_label' => 'nullable|string|max:7',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama_jenis.required'  => 'Kolom nama_jenis tidak boleh kosong.',
            'kode_awalan.required' => 'Kolom kode_awalan tidak boleh kosong.',
            'kode_awalan.unique'   => 'Kode awalan :input sudah terdaftar.',
        ];
    }
}
