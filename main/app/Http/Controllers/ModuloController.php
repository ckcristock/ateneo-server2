<?php

namespace App\Http\Controllers;

use App\Http\Services\consulta;

class ModuloController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tipo = isset($_REQUEST['Tipo']) && $_REQUEST['Tipo'] == 'Normal' ? null : 'ng-select';

        if ($tipo === null) {
            $query = "SELECT * FROM Modulo WHERE Estado = 'Activo' AND Prefijo IS NOT NULL";

            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $resultados = $oCon->getData();
            unset($oCon);
        } else {
            $query = "SELECT Id_Modulo AS value, CONCAT(Prefijo,' - ',Documento) AS label FROM Modulo WHERE Estado = 'Activo' AND Prefijo IS NOT NULL";

            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $resultados = $oCon->getData();
            unset($oCon);
        }

        return response()->json($resultados);
    }
}
