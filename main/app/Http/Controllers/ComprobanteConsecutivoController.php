<?php

namespace App\Http\Controllers;

use App\Models\ComprobanteConsecutivo;
use App\Models\Person;
use App\Models\Quotation;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class ComprobanteConsecutivoController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function paginate(Request $request)
    {
        return $this->success(
            ComprobanteConsecutivo::when($request->type, function ($q, $fill) {
                $q->where('Tipo', 'like', "%$fill%");
            })
                ->where('company_id', $this->getCompany())
                ->paginate(Request()->get('pageSize', 10), ['*'], 'page', Request()->get('page', 1))
        );
    }

    public function getConsecutive($table)
    {
        return $this->success(getConsecutive($table));
    }

    public function update(Request $request, ComprobanteConsecutivo $comprobanteConsecutivo)
    {
        $comprobanteConsecutivo->update($request->all());
        return $this->success('Consecutivo actualizado correctamente.');
    }

}
