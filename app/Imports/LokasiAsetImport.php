<?php

namespace App\Imports;

use App\Models\LokasiAset;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class LokasiAsetImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // Lewati baris pertama (Header)
            if ($index === 0) {
                continue;
            }

            $cells = $row->toArray();
            
            // Menggunakan indeks kolom numerik agar kebal terhadap perubahan nama/spasi/BOM pada header Excel
            // Kolom A (0) = Nama Lokasi, Kolom B (1) = Kode Lokasi, Kolom C (2) = Detail Lokasi
            $namaLokasi   = isset($cells[0]) ? trim((string)$cells[0]) : '';
            $kodeLokasi   = isset($cells[1]) ? trim((string)$cells[1]) : '';
            $detailLokasi = isset($cells[2]) ? trim((string)$cells[2]) : '';

            // Lewati jika seluruh baris kosong
            if (empty($namaLokasi) && empty($kodeLokasi)) {
                continue;
            }

            // Validasi kolom wajib
            if (empty($kodeLokasi)) {
                throw new \Exception("Kolom Kode Lokasi tidak boleh kosong pada baris " . ($index + 1) . ".");
            }
            if (empty($namaLokasi)) {
                throw new \Exception("Kolom Nama Lokasi tidak boleh kosong pada baris " . ($index + 1) . ".");
            }

            // Cek keunikan di database
            if (LokasiAset::where('kode_lokasi', $kodeLokasi)->exists()) {
                throw new \Exception("Kode Lokasi '{$kodeLokasi}' pada baris " . ($index + 1) . " sudah terdaftar.");
            }
            if (LokasiAset::where('nama_lokasi', $namaLokasi)->exists()) {
                throw new \Exception("Nama Lokasi '{$namaLokasi}' pada baris " . ($index + 1) . " sudah terdaftar.");
            }

            LokasiAset::create([
                'kode_lokasi'   => $kodeLokasi,
                'nama_lokasi'   => $namaLokasi,
                'detail_lokasi' => $detailLokasi ?: null,
            ]);
        }
    }
}
