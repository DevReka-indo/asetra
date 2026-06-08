<?php

namespace App\Imports;

use App\Models\KategoriAset;
use App\Models\JenisKategori;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class KategoriAsetImport implements ToCollection
{
    protected ?int $jenisKategoriId;
    protected ?string $kodeAwalan;
    protected $allJenisKategori;

    public function __construct(?int $jenisKategoriId = null)
    {
        $this->jenisKategoriId = $jenisKategoriId;
        if ($jenisKategoriId) {
            $this->kodeAwalan  = JenisKategori::findOrFail($jenisKategoriId)->kode_awalan;
        } else {
            $this->kodeAwalan  = null;
            $this->allJenisKategori = JenisKategori::all();
        }
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // Lewati baris pertama (Header)
            if ($index === 0) {
                continue;
            }

            $cells = $row->toArray();

            // Kolom A (0) = Nama Kategori, Kolom B (1) = Kode
            $nama = isset($cells[0]) ? trim((string)$cells[0]) : '';
            $kode = isset($cells[1]) ? trim((string)$cells[1]) : '';

            // Lewati jika seluruh baris kosong
            if (empty($nama) && empty($kode)) {
                continue;
            }

            // Validasi kolom wajib
            if (empty($kode)) {
                throw new \Exception("Kolom Kode tidak boleh kosong pada baris " . ($index + 1) . ".");
            }
            if (empty($nama)) {
                throw new \Exception("Kolom Nama tidak boleh kosong pada baris " . ($index + 1) . ".");
            }

            // Cek keunikan kode di database
            if (KategoriAset::where('kode', $kode)->exists()) {
                throw new \Exception("Kode Kategori '{$kode}' pada baris " . ($index + 1) . " sudah terdaftar.");
            }

            // Validasi awalan kode kategori
            $jenisId = $this->jenisKategoriId;
            if ($jenisId) {
                if (!str_starts_with($kode, $this->kodeAwalan)) {
                    throw new \Exception("Kode '{$kode}' pada baris " . ($index + 1) . " harus diawali dengan angka {$this->kodeAwalan}.");
                }
            } else {
                // Deteksi otomatis berdasarkan awalan kode
                $matched = $this->allJenisKategori->first(function ($jk) use ($kode) {
                    return str_starts_with($kode, $jk->kode_awalan);
                });
                if (!$matched) {
                    throw new \Exception("Kode '{$kode}' pada baris " . ($index + 1) . " tidak memiliki awalan yang cocok dengan jenis kategori manapun.");
                }
                $jenisId = $matched->id;
            }

            KategoriAset::create([
                'kode'              => $kode,
                'nama'              => $nama,
                'jenis_kategori_id' => $jenisId,
            ]);
        }
    }
}
