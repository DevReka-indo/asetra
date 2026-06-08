<?php

namespace App\Imports;

use App\Models\DataAset;
use App\Models\KategoriAset;
use App\Models\LokasiAset;
use App\Models\User;
use App\Models\AsetFoto;
use App\Models\Department;
use App\Models\Divisi;
use App\Models\Section;
use App\Models\Unit;
use App\Models\Director;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DataAsetImport implements ToCollection
{
    use \App\Traits\HandlesImageUploads;
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
            // PIC Aset: Indeks 19
            $picNameOrEmail = isset($cells[19]) ? trim((string) $cells[19]) : '';
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

            // Penanggung Jawab Aset: Indeks 20
            $pjNameOrEmail = isset($cells[20]) ? trim((string) $cells[20]) : '';
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

            // 6. Cari aset existing berdasarkan Nomor Aset di Excel (Indeks 3)
            $nomorAset = isset($cells[3]) ? trim((string) $cells[3]) : '';
            $aset = null;
            $nomorUrut = null;
            if (!empty($nomorAset)) {
                $aset = DataAset::where('nomor_aset', $nomorAset)->first();
                $parts = explode('/', $nomorAset);
                if (count($parts) >= 2) {
                    $nomorUrut = str_pad($parts[1], 5, '0', STR_PAD_LEFT);
                }
            }

            // 6b. Resolve Divisi/Dept dari kolom Indeks 16
            // Cukup isi nama organisasi di Excel, misal: "Departemen Teknologi", "Divisi Keuangan"
            // Sistem akan mencari secara berurutan: Department → Divisi → Section → Unit → Director
            // Jika kolom kosong → pertahankan org dari aset existing (update) atau null (create baru)
            $orgRaw       = isset($cells[16]) ? trim((string) $cells[16]) : '';
            $idDirector   = $aset ? $aset->id_director   : null;
            $idDivisi     = $aset ? $aset->id_divisi     : null;
            $idDepartment = $aset ? $aset->id_department : null;
            $idSection    = $aset ? $aset->id_section    : null;
            $idUnit       = $aset ? $aset->id_unit       : null;

            if (!empty($orgRaw)) {
                // Reset semua ID organisasi, akan di-set ulang sesuai hasil pencarian
                $idDirector = $idDivisi = $idDepartment = $idSection = $idUnit = null;

                $searchName = $orgRaw;
                $prefixType = '';

                if (str_contains($orgRaw, ':')) {
                    $parts = explode(':', $orgRaw, 2);
                    $prefixType = strtolower(trim($parts[0]));
                    $searchName = trim($parts[1]);
                }

                // Jika ada prefix type, kita bisa langsung targetkan model yang sesuai
                if ($prefixType === 'departemen' || $prefixType === 'department') {
                    $dept = Department::where('name_department', 'LIKE', "%{$searchName}%")->first();
                    if ($dept) $idDepartment = $dept->id_department;
                } elseif ($prefixType === 'divisi') {
                    $divisiFound = Divisi::where('nm_divisi', 'LIKE', "%{$searchName}%")->first();
                    if ($divisiFound) $idDivisi = $divisiFound->id_divisi;
                } elseif ($prefixType === 'bagian' || $prefixType === 'section') {
                    $sectionFound = Section::where('name_section', 'LIKE', "%{$searchName}%")->first();
                    if ($sectionFound) $idSection = $sectionFound->id_section;
                } elseif ($prefixType === 'unit') {
                    $unitFound = Unit::where('name_unit', 'LIKE', "%{$searchName}%")->first();
                    if ($unitFound) $idUnit = $unitFound->id_unit;
                } elseif ($prefixType === 'direktur' || $prefixType === 'director') {
                    $directorFound = Director::where('name_director', 'LIKE', "%{$searchName}%")->first();
                    if ($directorFound) $idDirector = $directorFound->id_director;
                } else {
                    // Fallback jika tidak ada colon atau prefix tidak cocok, cari ke semua secara berurutan
                    $dept = Department::where('name_department', 'LIKE', "%{$searchName}%")->first();
                    if ($dept) {
                        $idDepartment = $dept->id_department;
                    } elseif ($divisiFound = Divisi::where('nm_divisi', 'LIKE', "%{$searchName}%")->first()) {
                        $idDivisi = $divisiFound->id_divisi;
                    } elseif ($sectionFound = Section::where('name_section', 'LIKE', "%{$searchName}%")->first()) {
                        $idSection = $sectionFound->id_section;
                    } elseif ($unitFound = Unit::where('name_unit', 'LIKE', "%{$searchName}%")->first()) {
                        $idUnit = $unitFound->id_unit;
                    } elseif ($directorFound = Director::where('name_director', 'LIKE', "%{$searchName}%")->first()) {
                        $idDirector = $directorFound->id_director;
                    }
                }
            }

            $asetData = [
                'nama_aset'            => $namaAset,
                'kategori_id'          => $kategori->id,
                'nomor_urut'           => $nomorUrut,
                'deskripsi'            => isset($cells[4]) ? trim((string) $cells[4]) : '',
                'merek'                => isset($cells[5]) ? trim((string) $cells[5]) : '',
                'tanggal_kapitalisasi' => $tanggalKapitalisasi,
                'id_director'          => $idDirector,
                'id_divisi'            => $idDivisi,
                'id_department'        => $idDepartment,
                'id_section'           => $idSection,
                'id_unit'              => $idUnit,
                'lokasi_id'            => $lokasiId,
                'pic_id'               => $picId,
                'penanggung_jawab_id'  => $pjId,
                'bast'                 => isset($cells[17]) ? trim((string) $cells[17]) : '',
                'status_kondisi'       => $statusKondisi,
                'status_aset'          => $aset ? $aset->status_aset : 'Aktif',
                'keterangan'           => $aset ? $aset->keterangan : '',
            ];

            if ($aset) {
                // Update aset yang sudah ada
                $aset->update($asetData);
            } else {
                // Buat aset baru (nomor_aset di-generate otomatis oleh model booted)
                $aset = DataAset::create($asetData);
            }

            // Jika kolom nomor aset di Excel terisi → gunakan langsung sebagai nomor_aset, dan simpan nomor_urut
            if (!empty($nomorAset)) {
                $aset->nomor_aset = $nomorAset;
                if (!empty($nomorUrut)) {
                    $aset->nomor_urut = $nomorUrut;
                }
                $aset->saveQuietly();
            }


            // 7. Simpan/Update Link Foto Google Drive: Indeks 21 (Dokumentasi Aset)
            $photosRaw = isset($cells[21]) ? trim((string) $cells[21]) : '';
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
                                
                                // Kompresi dan simpan ke Storage lokal
                                $filename = 'import_' . time() . '_' . uniqid() . '.jpg';
                                $localFolder = 'dokumentasi_aset';
                                $savedPath = $this->compressAndStore($tempFile, $localFolder, $filename);
                                
                                @unlink($tempFile);
                                
                                if ($savedPath) {
                                    $finalUrl = $savedPath;
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
