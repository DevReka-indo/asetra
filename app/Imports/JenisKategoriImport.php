<?php

namespace App\Imports;

use App\Models\JenisKategori;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class JenisKategoriImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // Lewati baris pertama (Header)
            if ($index === 0) {
                continue;
            }

            $cells = $row->toArray();

            // Kolom A (0) = Nama Jenis, Kolom B (1) = Kode Awalan, Kolom C (2) = Warna Label (Opsional)
            $namaJenis  = isset($cells[0]) ? trim((string)$cells[0]) : '';
            $kodeAwalan = isset($cells[1]) ? trim((string)$cells[1]) : '';
            $warnaLabel = isset($cells[2]) ? trim((string)$cells[2]) : '';

            // Lewati jika seluruh baris kosong
            if (empty($namaJenis) && empty($kodeAwalan)) {
                continue;
            }

            // Validasi kolom wajib
            if (empty($namaJenis)) {
                throw new \Exception("Kolom Nama Jenis tidak boleh kosong pada baris " . ($index + 1) . ".");
            }
            if (empty($kodeAwalan)) {
                throw new \Exception("Kolom Kode Awalan tidak boleh kosong pada baris " . ($index + 1) . ".");
            }

            // Cek keunikan kode awalan
            if (JenisKategori::where('kode_awalan', $kodeAwalan)->exists()) {
                throw new \Exception("Kode Awalan '{$kodeAwalan}' pada baris " . ($index + 1) . " sudah terdaftar.");
            }

            $warna = !empty($warnaLabel) ? $warnaLabel : '#ea6565';

            JenisKategori::create([
                'nama_jenis'  => $namaJenis,
                'kode_awalan' => $kodeAwalan,
                'warna_label' => $warna,
            ]);
        }
    }
}
