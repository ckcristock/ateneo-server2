<?php

namespace App\Exports;

use App\Models\Bonus;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BonusExport implements FromView, WithEvents, ShouldAutoSize, WithColumnFormatting
{

    private $bonuses;

    public function __construct($bonuses)
    {
        $this->bonuses = $bonuses;
    }

    public function view(): View
    {
        //return view('exports.BonusReport', ['bonuses'=>Bonus::where('period', $this->anio.'-'.$this->period)->with('bonusPerson', 'personPayer')->first()]);
        return view('exports.BonusReport', ['bonuses' => $this->bonuses]);
    }

    public function registerEvents(): array
    {
        return [];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
        ];
    }
}
