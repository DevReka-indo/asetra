<?php

namespace App\Exports;

use App\Models\DataAset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;

class DataAsetExport implements FromCollection, WithHeadings, WithMapping, WithEvents, ShouldAutoSize
{
    protected $search;
    protected $kondisi;
    protected $status;
    protected $lokasiId;
    protected $isTemplate;
    protected $departmentId;
    protected $divisiId;
    protected $jenisKategoriId;
    private $rowNum = 0;

    public function __construct($search = null, $kondisi = null, $status = null, $lokasiId = null, $isTemplate = false, $departmentId = null, $divisiId = null, $jenisKategoriId = null)
    {
        $this->search = $search;
        $this->kondisi = $kondisi;
        $this->status = $status;
        $this->lokasiId = $lokasiId;
        $this->isTemplate = $isTemplate;
        $this->departmentId = $departmentId;
        $this->divisiId = $divisiId;
        $this->jenisKategoriId = $jenisKategoriId;
    }

    public function collection()
    {
        if ($this->isTemplate) {
            return collect([]);
        }

        $query = DataAset::with([
            'kategoriAset',
            'lokasi',
            'pic',
            'penanggungJawab',
            'foto'
        ]);

        $user = auth()->user();
        $isAdmin = $user->role_id_role == 1 || $user->isBagianUmum();

        if (!$isAdmin) {
            $query->forUser($user);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('nomor_aset', 'LIKE', "%{$this->search}%")
                  ->orWhere('nama_aset', 'LIKE', "%{$this->search}%")
                  ->orWhereHas('kategoriAset', function($qj) {
                      $qj->where('nama', 'LIKE', "%{$this->search}%")
                         ->orWhere('kode', 'LIKE', "%{$this->search}%");
                  });
            });
        }

        if ($this->kondisi) {
            $query->where('status_kondisi', $this->kondisi);
        }

        if ($this->status) {
            $query->where('status_aset', $this->status);
        }

        if ($this->jenisKategoriId) {
            $query->whereHas('kategoriAset', function($q) {
                $q->where('jenis_kategori_id', $this->jenisKategoriId);
            });
        }

        if ($this->lokasiId) {
            $query->where('lokasi_id', $this->lokasiId);
        }

        if ($this->departmentId) {
            $query->where('id_department', $this->departmentId);
        }

        if ($this->divisiId) {
            $divisiId = $this->divisiId;
            $query->where(function($q) use ($divisiId) {
                $q->where('id_divisi', $divisiId)
                  ->orWhereHas('department', function($qd) use ($divisiId) {
                      $qd->where('divisi_id_divisi', $divisiId);
                  })
                  ->orWhereHas('section.department', function($qsd) use ($divisiId) {
                      $qsd->where('divisi_id_divisi', $divisiId);
                  })
                  ->orWhereHas('unit.department', function($qud) use ($divisiId) {
                      $qud->where('divisi_id_divisi', $divisiId);
                  })
                  ->orWhereHas('unit.section.department', function($qusd) use ($divisiId) {
                      $qusd->where('divisi_id_divisi', $divisiId);
                  });
            });
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            [
                'No',
                'Nama Aset',
                'Kode Aset',
                '',
                'Deskripsi Aset',
                'Merk Aset',
                'Tanggal Kapitalisasi',
                'Kondisi Aset',
                '',
                '',
                '',
                '',
                '',
                'Lokasi Aset',
                '',
                '',
                'Divisi/Dept',
                'Tanggal Cek Terakhir',
                'BAST',
                'PIC Aset',
                'Penanggung Jawab Aset',
                'Dokumentasi Aset'
            ],
            [
                '',
                '',
                'Kategori',
                'Nomor',
                '',
                '',
                '',
                'Baik',
                'Rusak',
                'Bongkar',
                'Tidak Terpakai',
                'Hilang',
                'Tidak Teridentifikasi',
                'Nama Lokasi',
                'Kode Lokasi',
                'Detail Lokasi',
                '',
                '',
                '',
                '',
                '',
                ''
            ]
        ];
    }

    public function map($row): array
    {
        $this->rowNum++;

        // Gabungkan semua link foto Google Drive menjadi satu string dipisahkan koma
        $fotoLinks = $row->foto->pluck('path_foto')->map(function($path) {
            return filter_var($path, FILTER_VALIDATE_URL) ? $path : asset('storage/' . $path);
        })->implode(', ');

        return [
            $this->rowNum,
            $row->nama_aset,
            $row->kategoriAset->kode ?? '',
            $row->nomor_aset,
            $row->deskripsi,
            $row->merek,
            $row->tanggal_kapitalisasi ? $row->tanggal_kapitalisasi->format('Y-m-d') : '',
            $row->status_kondisi === 'Baik' ? 'v' : '',
            $row->status_kondisi === 'Rusak' ? 'v' : '',
            $row->status_kondisi === 'Bongkar' ? 'v' : '',
            $row->status_kondisi === 'Tidak Terpakai' ? 'v' : '',
            $row->status_kondisi === 'Hilang' ? 'v' : '',
            $row->status_kondisi === 'Tidak Teridentifikasi' ? 'v' : '',
            $row->lokasi->nama_lokasi ?? '',
            $row->lokasi->kode_lokasi ?? '',
            $row->lokasi->detail_lokasi ?? '',
            $row->organisasi_terikat,           // Divisi/Dept (Q)
            $row->tanggal_cek_terakhir ? date('Y-m-d', strtotime($row->tanggal_cek_terakhir)) : '',
            $row->bast,
            $row->pic->fullname ?? '',
            $row->penanggungJawab->fullname ?? '',
            $fotoLinks
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // 1. Merging cells vertically (Row 1 to Row 2)
                $sheet->mergeCells('A1:A2'); // No
                $sheet->mergeCells('B1:B2'); // Nama Aset
                $sheet->mergeCells('E1:E2'); // Deskripsi Aset
                $sheet->mergeCells('F1:F2'); // Merk Aset
                $sheet->mergeCells('G1:G2'); // Tanggal Kapitalisasi
                $sheet->mergeCells('Q1:Q2'); // Divisi/Dept
                $sheet->mergeCells('R1:R2'); // Tanggal Cek Terakhir
                $sheet->mergeCells('S1:S2'); // BAST
                $sheet->mergeCells('T1:T2'); // PIC Aset
                $sheet->mergeCells('U1:U2'); // Penanggung Jawab Aset
                $sheet->mergeCells('V1:V2'); // Dokumentasi Aset

                // 2. Merging cells horizontally (Row 1)
                $sheet->mergeCells('C1:D1'); // Kode Aset
                $sheet->mergeCells('H1:M1'); // Kondisi Aset
                $sheet->mergeCells('N1:P1'); // Lokasi Aset

                // 3. Set Header Styles (Row 1 to Row 2)
                $headerStyle = [
                    'font' => [
                        'bold' => true,
                        'name' => 'Arial',
                        'size' => 10,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FFEFEFEF',
                        ],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color'       => ['argb' => 'FF000000'],
                        ],
                    ],
                ];
                $sheet->getStyle('A1:V2')->applyFromArray($headerStyle);

                // 4. Set Row Heights
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(25);

                // 5. Apply styling to data rows (Starts on Row 3)
                $highestRow = $sheet->getHighestRow();
                if ($highestRow >= 3) {
                    $sheet->getStyle('A3:V' . $highestRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color'       => ['argb' => 'FF000000'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // Center-align specific data columns
                    $sheet->getStyle('A3:A' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('C3:D' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('G3:G' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('H3:M' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('R3:R' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                    // Set Data Row Heights
                    for ($row = 3; $row <= $highestRow; $row++) {
                        $sheet->getRowDimension($row)->setRowHeight(20);
                    }
                }
            },
        ];
    }
}
