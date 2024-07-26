<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PlanCuentasExport implements FromView, WithColumnFormatting, ShouldAutoSize, WithEvents
{
    private $planes;
    public function __construct($planes)
    {
        $this->planes = $planes;
    }
    public function view(): View
    {
        return view('exports.PlanCuentas', ['planes' => $this->planes]);
    }
    /**
     * @return \Illuminate\Support\Collection
     */

    public function registerEvents(): array
    {
        return [];
    }

    public function columnFormats(): array
    {
        return [
        ];
    }

}
