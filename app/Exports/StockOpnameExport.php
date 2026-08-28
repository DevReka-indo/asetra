<?php

namespace App\Exports;

use App\Models\LokasiAset;
use App\Models\StockOpnameDetail;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class StockOpnameExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithMapping
{
    protected $stockOpnameId;

    private $rowNum = 0;

    public function __construct($stockOpnameId)
    {
        $this->stockOpnameId = $stockOpnameId;
    }

    public function collection(): Enumerable
    {
        return StockOpnameDetail::with([
            'aset.kategoriAset',
            'aset.lokasi',
            'aset.department',
            'aset.section.department',
            'aset.unit.section.department',
            'aset.unit.department',
            'aset.divisi',
            'aset.director',
            'aset.foto',
            'aset.pic',
            'aset.penanggungJawab',
            'dicekOleh',
        ])
            ->where('stock_opname_id', $this->stockOpnameId)
            ->get();
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
                'Dokumentasi Aset',
                'Hasil Stock Opname',
                '',
                '',
                'Keterangan Temuan',
                'Dicek Oleh',
                'Tanggal Cek Opname',
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
                '',
                'Kondisi Temuan',
                'Lokasi Temuan',
                'Foto Temuan',
                '',
                '',
                '',
            ],
        ];
    }

    public function map($row): array
    {
        $this->rowNum++;
        $aset = $row->aset;

        // Foto aset master
        $fotoLinks = '';
        if ($aset && $aset->foto) {
            $fotoLinks = $aset->foto->pluck('path_foto')->map(function ($path) {
                return filter_var($path, FILTER_VALIDATE_URL) ? $path : asset('storage/'.$path);
            })->implode(', ');
        }

        // Foto temuan stock opname
        $fotoTemuan = '';
        if ($row->foto_temuan) {
            $fotoTemuan = filter_var($row->foto_temuan, FILTER_VALIDATE_URL)
                ? $row->foto_temuan
                : asset('storage/'.$row->foto_temuan);
        }

        // Lokasi temuan
        $lokasiTemuan = $row->lokasi_temuan;
        if (is_numeric($lokasiTemuan)) {
            $lokasiObj = LokasiAset::find($lokasiTemuan);
            $lokasiTemuan = $lokasiObj ? $lokasiObj->nama_lokasi : $lokasiTemuan;
        }

        return [
            $this->rowNum,
            $aset->nama_aset ?? '',
            $aset->kategoriAset->kode ?? '',
            $aset->nomor_aset ?? '',
            $aset->deskripsi ?? '',
            $aset->merek ?? '',
            $aset && $aset->tahun_kapitalisasi ? $aset->tahun_kapitalisasi : '',
            ($aset->status_kondisi ?? '') === 'Baik' ? 'v' : '',
            ($aset->status_kondisi ?? '') === 'Rusak' ? 'v' : '',
            ($aset->status_kondisi ?? '') === 'Bongkar' ? 'v' : '',
            ($aset->status_kondisi ?? '') === 'Tidak Terpakai' ? 'v' : '',
            ($aset->status_kondisi ?? '') === 'Hilang' ? 'v' : '',
            ($aset->status_kondisi ?? '') === 'Tidak Teridentifikasi' ? 'v' : '',
            $aset->lokasi->nama_lokasi ?? '',
            $aset->lokasi->kode_lokasi ?? '',
            $aset->lokasi->detail_lokasi ?? '',
            $aset->organisasi_terikat ?? '',
            $aset && $aset->tanggal_cek_terakhir ? date('Y-m-d', strtotime($aset->tanggal_cek_terakhir)) : '',
            $aset->bast ?? '',
            $aset->pic->fullname ?? '',
            $aset->penanggungJawab->fullname ?? '',
            $fotoLinks,
            $row->kondisi_temuan ?? '',
            $lokasiTemuan,
            $fotoTemuan,
            $row->keterangan ?? '',
            $row->dicekOleh ? ($row->dicekOleh->firstname.' '.$row->dicekOleh->lastname) : '',
            $row->tanggal_cek ? $row->tanggal_cek->format('Y-m-d') : '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

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
                $sheet->mergeCells('Z1:Z2'); // Keterangan Temuan
                $sheet->mergeCells('AA1:AA2'); // Dicek Oleh
                $sheet->mergeCells('AB1:AB2'); // Tanggal Cek Opname

                $sheet->mergeCells('C1:D1'); // Kode Aset
                $sheet->mergeCells('H1:M1'); // Kondisi Aset
                $sheet->mergeCells('N1:P1'); // Lokasi Aset
                $sheet->mergeCells('W1:Y1'); // Hasil Stock Opname

                // 3. Style header
                $headerStyle = [
                    'font' => [
                        'bold' => true,
                        'name' => 'Arial',
                        'size' => 10,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFEFEFEF'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ];
                $sheet->getStyle('A1:AB2')->applyFromArray($headerStyle);

                // Highlight kolom Hasil Stock Opname
                $sheet->getStyle('W1:Y2')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFD6E4F7');

                // Tinggi baris header
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(25);

                // Style data rows
                $highestRow = $sheet->getHighestRow();
                if ($highestRow >= 3) {
                    $sheet->getStyle('A3:AB'.$highestRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => 'FF000000'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // Center-align kolom tertentu
                    $sheet->getStyle('A3:A'.$highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('C3:D'.$highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('G3:G'.$highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('H3:M'.$highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('R3:R'.$highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('W3:W'.$highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('AB3:AB'.$highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                    // Tinggi baris data
                    for ($row = 3; $row <= $highestRow; $row++) {
                        $sheet->getRowDimension($row)->setRowHeight(20);
                    }
                }
            },
        ];
    }
}
