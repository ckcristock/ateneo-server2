<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CentroCostosExport implements FromView, WithColumnFormatting, ShouldAutoSize, WithEvents
{
    private $centro;
    public function __construct($centro)
    {
        $this->centro = $centro;
    }
    public function view(): View
    {
        //dd ($this->centro);
        return view('exports.CentroCostos', ['centro' => $this->centro]);
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
