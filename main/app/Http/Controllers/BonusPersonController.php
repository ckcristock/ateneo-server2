<?php

namespace App\Http\Controllers;

use App\Models\BonusPerson;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class BonusPersonController extends Controller
{

    public function pdfGenerate($id, $period)
    {
        $data = BonusPerson::with('bonus', 'person')
            ->where('person_id', $id)
            ->where('lapse', $period)
            ->first();

        //nota, se le agrega a cada uno para que sirva desde el coponente individual
        $anio = explode('-', $data->lapse)[0];
        $period = explode('-', $data->lapse)[1];
        $data->fecha_inicio = ($period == 1) ? '01 Enero ' . $anio : '01 Julio ' . $anio;

        $pdf = PDF::loadView('pdf.bonus_person', ['bonus' => $data])
            ->setPaper([0, 0, 614.295, 397.485]);

        return $pdf->stream('colilla_prima.pdf');
    }
}
