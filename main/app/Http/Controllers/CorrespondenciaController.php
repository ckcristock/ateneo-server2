<?php

namespace App\Http\Controllers;

use App\Http\Services\consulta;
use App\Models\Correspondencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CorrespondenciaController extends Controller
{
    public function listaCorrespondencia()
    {
        $pageSize = request('pageSize', 20);

        $query = Correspondencia::select(
            'Correspondencia.*',
            DB::raw('CONCAT_WS(" ", F.first_name, F.first_surname) as Funcionario_Envio'),
            'F.image',
            'D.Codigo as Codigo_Dispensacion',
        )
            ->join('people as F', 'Correspondencia.Id_Funcionario_Envia', '=', 'F.identifier')
            ->join('Dispensacion as D', 'Correspondencia.Id_Correspondencia', '=', 'D.Id_Correspondencia')
            ->where(function ($correspondenciaQuery) {
                $this->applyFilters($correspondenciaQuery);
            });
        $correspondencia = $query->paginate($pageSize);

        return response()->json([
            'Correspondencia' => $correspondencia->items(),
            'numReg' => $correspondencia->total(),
        ]);
    }


    private function applyFilters($query)
    {
        if (request()->filled('cod')) {
            $query->where('Correspondencia.Id_Correspondencia', str_replace('CO000', '', request('cod')));
        }

        if (request()->filled('guia')) {
            $query->where(function ($q) {
                $q->where('Correspondencia.Numero_Guia', request('guia'))
                    ->orWhere('Correspondencia.Empresa_Envio', request('guia'));
            });
        }

        if (request()->filled('est')) {
            $query->where('Correspondencia.Estado', request('est'));
        }

        if (request()->filled('fecha')) {
            list($fecha_inicio, $fecha_fin) = array_map('trim', explode(' - ', request('fecha')));
            $query->whereBetween(DB::raw('DATE_FORMAT(Correspondencia.Fecha_Envio, "%Y-%m-%d")'), [$fecha_inicio, $fecha_fin]);
        }

        if (request()->filled('disp') && request('disp') != "undefined") {
            $query->where('D.Codigo', 'like', request('disp') . '%');
        }
    }
}
