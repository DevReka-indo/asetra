<?php

namespace App\Imports;

use App\Models\KategoriAset;
use App\Models\JenisKategori;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class KategoriAsetImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $jenisKategoriId;
    protected string $kodeAwalan;

    public function __construct(int $jenisKategoriId)
    {
        $this->jenisKategoriId = $jenisKategoriId;
        $this->kodeAwalan      = JenisKategori::findOrFail($jenisKategoriId)->kode_awalan;
    }

    public function model(array $row): KategoriAset
    {
        return new KategoriAset([
            'kode'              => $row['kode'],
            'nama'              => $row['nama'],
            'jenis_kategori_id' => $this->jenisKategoriId,
        ]);
    }

    public function rules(): array
    {
        return [
            'kode' => [
                'required',
                'unique:kategori_aset,kode',
                function ($attribute, $value, $fail) {
                    if (!str_starts_with((string) $value, $this->kodeAwalan)) {
                        $fail("Kode \"{$value}\" harus diawali dengan angka {$this->kodeAwalan}.");
                    }
                },
            ],
            'nama' => 'required|string|max:100',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'kode.required' => 'Kolom kode tidak boleh kosong.',
            'kode.unique'   => 'Kode :input sudah terdaftar.',
            'nama.required' => 'Kolom nama tidak boleh kosong.',
        ];
    }
}
