<?php

namespace App\Exports;

use App\Models\PayrollPayment;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class NovedadesExport implements FromView, WithEvents, ShouldAutoSize, WithColumnFormatting
{

    private $novedades;

    public function __construct($novedades)
    {
        $this->novedades = $novedades;
    }

    public function view(): View
    {
        return view('exports.NovedadesReport', ['novedades' => $this->novedades]);
    }

    public function registerEvents(): array
    {
        return [];
    }

    public function columnFormats(): array
    {
        return [
            'E1' => 'dd-mmmm-yyyy',
            'C1' => 'dd-mmmm-yyyy',
            'C' => '#,##0',
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            //'F'=>'dd-mm-yyyy',
            'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            //'G'=>'dd-mm-yyyy',
            'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'H' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
