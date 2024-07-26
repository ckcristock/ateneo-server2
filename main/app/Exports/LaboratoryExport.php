<?php

namespace App\Exports;

use App\Models\Laboratories;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class LaboratoryExport implements FromView, WithEvents, WithDrawings
{

    public function __construct($data)
    {
        $this->fecha = $data['fecha'];
        $this->company_id = $data['company_id'];
        $this->company = DB::table('companies')->where('id', $this->company_id)->first();
        $this->person_id = $data['person_id'];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path('/app/public/people/' . explode("people/", $this->company->logo)[1]));
        $drawing->setHeight(50);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

    public function view(): View
    {
        return view('exports.LaboratoryReport', [
            'laboratories' => DB::table('laboratory_tube')
                ->select(
                    'laboratories.id',
                    'type_documents.code',
                    'patients.identifier',
                    DB::raw('CONCAT_WS(" ",patients.surname,patients.secondsurname,patients.firstname,patients.middlename) as full_name'),
                    'patients.date_of_birth',
                    'patients.gener',
                    DB::raw('(SELECT GROUP_CONCAT(DISTINCT(cups.code) SEPARATOR " - ")
                    FROM cup_laboratories
                    INNER JOIN cups ON cup_laboratories.id_cup=cups.id
                    WHERE cup_laboratories.id_laboratory =laboratories.id) AS cups'),
                    DB::raw('SUM(laboratory_tube.amount) AS examenes'),
                    'eps.name',
                )
                ->join('laboratories', 'laboratory_tube.laboratory_id', '=', 'laboratories.id')
                ->join('patients', 'patients.id', '=', 'laboratories.patient')
                ->join('type_documents', 'type_documents.id', '=', 'patients.type_document_id')
                ->join('eps', 'eps.id', '=', 'patients.eps_id')
                ->where([['laboratories.status', 'Tomado']])
                ->when($this->fecha, function ($q, $fill) {
                    $fechas = explode('a', $this->fecha);
                    $f1 = new Carbon($fechas[0]);
                    $f2 = new Carbon($fechas[1]);
                    $q->whereBetween('date', [$f1, $f2]);
                })
                ->groupBy('laboratory_tube.laboratory_id')
                ->get(),
            'company' => DB::table('companies')->where('id', $this->company_id)->first(),
            'person' => DB::table('people')->where('id', $this->person_id)->first()
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $event->sheet->getDelegate()->freezePane('A9');


            },
        ];
    }

}
