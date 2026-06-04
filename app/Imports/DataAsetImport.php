<?php

namespace App\Imports;

use App\Models\DataAset;
use App\Models\KategoriAset;
use App\Models\LokasiAset;
use App\Models\User;
use App\Models\AsetFoto;
use App\Models\Department;
use App\Models\Divisi;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DataAsetImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Skip 2 baris pertama karena merupakan header bertingkat (Row 1 dan Row 2)
        $dataRows = $rows->slice(2);

        foreach ($dataRows as $row) {
            // Ubah row menjadi array biasa
            $cells = $row->toArray();

            // Skip jika baris kosong (Kategori & Nama Aset kosong)
            // Nama Aset adalah indeks 1, Kategori adalah indeks 2
            $namaAset = isset($cells[1]) ? trim((string) $cells[1]) : '';
            $kategoriKode = isset($cells[2]) ? trim((string) $cells[2]) : '';

            if (empty($kategoriKode) && empty($namaAset)) {
                continue;
            }

            // 1. Cari Kategori
            $kategori = KategoriAset::where('kode', $kategoriKode)->first();
            if (!$kategori) {
                // Lewati baris jika kategori tidak valid
                continue;
            }

            // 2. Format Tanggal Kapitalisasi (Indeks 6)
            $tanggalRaw = isset($cells[6]) ? trim((string) $cells[6]) : '';
            $tanggalKapitalisasi = null;
            if (!empty($tanggalRaw)) {
                // Cek jika numeric excel date
                if (is_numeric($tanggalRaw)) {
                    $tanggalKapitalisasi = date('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($tanggalRaw));
                } else {
                    $timestamp = strtotime($tanggalRaw);
                    $tanggalKapitalisasi = $timestamp ? date('Y-m-d', $timestamp) : date('Y-m-d');
                }
            } else {
                $tanggalKapitalisasi = date('Y-m-d');
            }

            // 3. Tentukan Status Kondisi (Indeks 7 s.d 12)
            $statusKondisi = 'Baik';
            if (!empty(trim((string) ($cells[7] ?? '')))) $statusKondisi = 'Baik';
            elseif (!empty(trim((string) ($cells[8] ?? '')))) $statusKondisi = 'Rusak';
            elseif (!empty(trim((string) ($cells[9] ?? '')))) $statusKondisi = 'Bongkar';
            elseif (!empty(trim((string) ($cells[10] ?? '')))) $statusKondisi = 'Tidak Terpakai';
            elseif (!empty(trim((string) ($cells[11] ?? '')))) $statusKondisi = 'Hilang';
            elseif (!empty(trim((string) ($cells[12] ?? '')))) $statusKondisi = 'Tidak Teridentifikasi';

            // 4. Cari PIC & Penanggung Jawab berdasarkan Nama (atau Email)
            // PIC Aset: Indeks 18
            $picNameOrEmail = isset($cells[18]) ? trim((string) $cells[18]) : '';
            $pic = null;
            if (!empty($picNameOrEmail)) {
                if (filter_var($picNameOrEmail, FILTER_VALIDATE_EMAIL) !== false || str_contains($picNameOrEmail, '@')) {
                    $pic = User::where('email', $picNameOrEmail)->first();
                }
                if (!$pic) {
                    // Cari berdasarkan fullname (firstname + ' ' + lastname) atau firstname
                    $pic = User::where(\DB::raw("CONCAT(firstname, ' ', lastname)"), 'LIKE', "%{$picNameOrEmail}%")
                               ->orWhere('firstname', 'LIKE', "%{$picNameOrEmail}%")
                               ->first();
                }
            }
            $picId = $pic ? $pic->id : auth()->id();

            // Penanggung Jawab Aset: Indeks 19
            $pjNameOrEmail = isset($cells[19]) ? trim((string) $cells[19]) : '';
            $pj = null;
            if (!empty($pjNameOrEmail)) {
                if (filter_var($pjNameOrEmail, FILTER_VALIDATE_EMAIL) !== false || str_contains($pjNameOrEmail, '@')) {
                    $pj = User::where('email', $pjNameOrEmail)->first();
                }
                if (!$pj) {
                    $pj = User::where(\DB::raw("CONCAT(firstname, ' ', lastname)"), 'LIKE', "%{$pjNameOrEmail}%")
                               ->orWhere('firstname', 'LIKE', "%{$pjNameOrEmail}%")
                               ->first();
                }
            }
            $pjId = $pj ? $pj->id : $picId;

            // 5. Cari / Buat Lokasi Aset otomatis berdasarkan data Excel
            // Nama Lokasi: Indeks 13, Kode Lokasi: Indeks 14, Detail Lokasi: Indeks 15
            $namaLokasi = isset($cells[13]) ? trim((string) $cells[13]) : '';
            $kodeLokasi = isset($cells[14]) ? trim((string) $cells[14]) : '';
            $detailLokasi = isset($cells[15]) ? trim((string) $cells[15]) : '';
            $lokasiId = null;

            if (!empty($kodeLokasi)) {
                $lokasi = LokasiAset::updateOrCreate(
                    ['kode_lokasi' => $kodeLokasi],
                    [
                        'nama_lokasi' => $namaLokasi ?: $kodeLokasi,
                        'detail_lokasi' => $detailLokasi
                    ]
                );
                $lokasiId = $lokasi->lokasi_id;
            } else {
                $lokasiId = LokasiAset::first()->lokasi_id ?? null;
            }

            // 6. Simpan atau Update Data Aset
            // Nomor Aset: Indeks 3 (Kode Aset -> Nomor)
            $nomorAset = isset($cells[3]) ? trim((string) $cells[3]) : '';
            $aset = null;
            if (!empty($nomorAset)) {
                $aset = DataAset::where('nomor_aset', $nomorAset)->first();
            }

            $asetData = [
                'nama_aset'            => $namaAset,
                'kategori_id'          => $kategori->id,
                'deskripsi'            => isset($cells[4]) ? trim((string) $cells[4]) : '',
                'merek'                => isset($cells[5]) ? trim((string) $cells[5]) : '',
                'tanggal_kapitalisasi' => $tanggalKapitalisasi,
                'id_director'          => $aset ? $aset->id_director : null,
                'id_divisi'            => $aset ? $aset->id_divisi : null,
                'id_department'        => $aset ? $aset->id_department : null,
                'id_section'           => $aset ? $aset->id_section : null,
                'id_unit'              => $aset ? $aset->id_unit : null,
                'lokasi_id'            => $lokasiId,
                'pic_id'               => $picId,
                'penanggung_jawab_id'  => $pjId,
                'bast'                 => isset($cells[17]) ? trim((string) $cells[17]) : '',
                'status_kondisi'       => $statusKondisi,
                'status_aset'          => $aset ? $aset->status_aset : 'Aktif',
                'keterangan'           => $aset ? $aset->keterangan : '',
            ];

            if ($aset) {
                // Update
                $aset->update($asetData);
            } else {
                // Create
                $aset = DataAset::create($asetData);
            }

            // 7. Simpan/Update Link Foto Google Drive: Indeks 20 (Dokumentasi Aset)
            $photosRaw = isset($cells[20]) ? trim((string) $cells[20]) : '';
            if (!empty($photosRaw)) {
                // Pisahkan string koma menjadi array URL
                $photoUrls = array_map('trim', explode(',', $photosRaw));
                
                // Hapus foto lama, lalu daftarkan ulang link barunya
                $aset->foto()->delete();

                foreach ($photoUrls as $i => $url) {
                    if (!empty($url)) {
                        $finalUrl = $url;
                        $driveId = $this->extractDriveId($url);
                        
                        if ($driveId) {
                            // Coba unduh file dari Drive publik dan unggah ke Folder Sistem
                            $downloadUrl = "https://drive.google.com/uc?export=download&id=" . $driveId;
                            
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $downloadUrl);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                            $content = curl_exec($ch);
                            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);
                            
                            if ($httpCode === 200 && !empty($content)) {
                                // Simpan ke file temp lokal
                                $tempDir = sys_get_temp_dir();
                                $tempFile = tempnam($tempDir, 'aset_img_');
                                file_put_contents($tempFile, $content);
                                
                                // Unggah ke Google Drive Sistem
                                $filename = time() . '_' . uniqid() . '.jpg';
                                $driveService = app(\App\Services\GoogleDriveService::class);
                                $systemDriveUrl = $driveService->uploadFile($tempFile, $filename);
                                
                                @unlink($tempFile);
                                
                                if ($systemDriveUrl) {
                                    $finalUrl = $systemDriveUrl;
                                } else {
                                    $finalUrl = "https://lh3.googleusercontent.com/d/" . $driveId;
                                }
                            } else {
                                $finalUrl = "https://lh3.googleusercontent.com/d/" . $driveId;
                            }
                        }

                        AsetFoto::create([
                            'aset_id'   => $aset->id,
                            'path_foto' => $finalUrl,
                            'urutan'    => $i + 1,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Mengekstrak file ID dari link Google Drive.
     */
    private function extractDriveId(string $url): ?string
    {
        if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
