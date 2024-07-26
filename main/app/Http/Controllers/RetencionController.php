<?php

namespace App\Http\Controllers;

use App\Models\Retencion;
use Illuminate\Http\Request;
use App\Http\Services\HttpResponse;
use App\Http\Services\QueryBaseDatos;
use App\Http\Services\consulta;


class RetencionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $http_response = new HttpResponse();

        $query = 'SELECT R.*
			FROM Retencion R
			ORDER BY R.Nombre ASC';


        $queryObj = new QueryBaseDatos($query);
        $retenciones = $queryObj->ExecuteQuery('Multiple');

        return response()->json($retenciones);
    }

    public function getRetencionesModalidad()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $modalidad = (isset($_REQUEST['modalidad']) ? $_REQUEST['modalidad'] : '');

        $query = '
        SELECT
            *
        FROM Retencion
        WHERE
            LOWER(Modalidad_Retencion) = "' . strtolower($modalidad) . '"';

        $q = new consulta();
        $q->setQuery($query);
        $q->setTipo('multiple');
        $retenciones = $q->getData();
        unset($q);

        return response()->json($retenciones);
    }

    public function lista()
    {
        $res = Retencion::where('Estado', 'Activo')->get();

        return response()->json($res);
    }

}
