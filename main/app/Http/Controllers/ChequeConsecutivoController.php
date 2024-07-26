<?php

namespace App\Http\Controllers;

use App\Http\Services\consulta;

class ChequeConsecutivoController extends Controller
{
    public function lista()
    {
        $query = "SELECT * FROM Cheque_Consecutivo WHERE Estado = 'Activo'";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $resultado = $oCon->getData();

        foreach ($resultado as $i => $valor) {
            $resultado[$i]->value = $valor->Prefijo . str_pad($valor->Consecutivo, 4, '0', STR_PAD_LEFT);
            $resultado[$i]->label = $valor->Prefijo . str_pad($valor->Consecutivo, 4, '0', STR_PAD_LEFT);
        }

        return response()->json($resultado);
    }
}
