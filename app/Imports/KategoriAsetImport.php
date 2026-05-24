<?php

namespace App\Imports;

use App\Models\KategoriAset;
use App\Models\JenisKategori;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class KategoriAsetImport implements ToModel, WithHeadingRow, WithValidation
{
    protected ?int $jenisKategoriId;
    protected ?string $kodeAwalan;
    protected $allJenisKategori;

    public function __construct(?int $jenisKategoriId = null)
    {
        $this->jenisKategoriId = $jenisKategoriId;
        if ($jenisKategoriId) {
            $this->kodeAwalan  = \App\Models\JenisKategori::findOrFail($jenisKategoriId)->kode_awalan;
        } else {
            $this->kodeAwalan  = null;
            // Muat semua jenis kategori untuk pencocokan awalan
            $this->allJenisKategori = \App\Models\JenisKategori::all();
        }
    }

    public function model(array $row): KategoriAset
    {
        $jenisId = $this->jenisKategoriId;

        if (!$jenisId) {
            // Deteksi otomatis berdasarkan awalan kode
            $matched = $this->allJenisKategori->first(function ($jk) use ($row) {
                return str_starts_with((string) $row['kode'], $jk->kode_awalan);
            });
            $jenisId = $matched ? $matched->id : null;
        }

        return new KategoriAset([
            'kode'              => $row['kode'],
            'nama'              => $row['nama'],
            'jenis_kategori_id' => $jenisId,
        ]);
    }

    public function rules(): array
    {
        return [
            'kode' => [
                'required',
                'unique:kategori_aset,kode',
                function ($attribute, $value, $fail) {
                    if ($this->jenisKategoriId) {
                        if (!str_starts_with((string) $value, $this->kodeAwalan)) {
                            $fail("Kode \"{$value}\" harus diawali dengan angka {$this->kodeAwalan}.");
                        }
                    } else {
                        // Cek apakah ada jenis kategori yang cocok dengan awalan kode
                        $matched = $this->allJenisKategori->first(function ($jk) use ($value) {
                            return str_starts_with((string) $value, $jk->kode_awalan);
                        });
                        if (!$matched) {
                            $fail("Kode \"{$value}\" tidak memiliki awalan kode yang cocok dengan jenis kategori manapun.");
                        }
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
