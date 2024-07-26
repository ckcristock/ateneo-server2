<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class LoanExport implements FromView, ShouldAutoSize
{
    private $loan;
    private $proyecciones;
    private $getTotalA;
    private $getTotalI;
    private $getTotalV;
    private $company;

    public function __construct($loan, $proyecciones, $getTotalA, $getTotalI, $getTotalV, $company)
    {
        $this->loan = $loan;
        $this->proyecciones = $proyecciones;
        $this->getTotalA = $getTotalA;
        $this->getTotalI = $getTotalI;
        $this->getTotalV = $getTotalV;
        $this->company = $company;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        return view(
            'exports.loan',
            [
                'loan' => $this->loan,
                'proyecciones' => $this->proyecciones,
                'getTotalA' => $this->getTotalA,
                'getTotalI' => $this->getTotalI,
                'getTotalV' => $this->getTotalV,
                'company' => $this->company,
            ]
        );
    }

    /* public function drawings()
    {
        $parts = explode('/', $this->company->logo);
        $string = $parts[5];
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path('/app/public/company/' . $string));
        $drawing->setHeight(60);
        $drawing->setCoordinates('A1');

        return $drawing;
    } */
}
