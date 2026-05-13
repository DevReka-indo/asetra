<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplateExport implements FromCollection, WithHeadings
{
    protected $headings;

    public function __construct(array $headings)
    {
        $this->headings = $headings;
    }

    public function collection()
    {
        return collect([]);
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
