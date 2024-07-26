<?php

namespace App\Http\Controllers;

use App\Models\TipoActivoFijo;
use Illuminate\Http\Request;
use App\Http\Services\HttpResponse;
use App\Http\Services\QueryBaseDatos;
use App\Http\Services\PaginacionData;
use App\Http\Services\complex;

class TipoActivoFijoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $http_response = new HttpResponse();

        $query = '
		SELECT
			*
		FROM Tipo_Activo_Fijo
		ORDER BY Nombre_Tipo_Activo ASC';

        //Se crea la instancia que contiene la consulta a realizar
        $queryObj = new QueryBaseDatos($query);

        //Ejecuta la consulta de la instancia queryobj y retorna el resultado de la misma segun los parametros
        $tipo_activos = $queryObj->Consultar('Multiple');

        return response()->json($tipo_activos);
    }

    public function paginate()
    {
        $http_response = new HttpResponse();

        $pag = (isset($_REQUEST['pag']) ? $_REQUEST['pag'] : '');
        $tam = (isset($_REQUEST['tam']) ? $_REQUEST['tam'] : '');

        $condicion = $this->SetCondiciones($_REQUEST);

        $fecha = date('Y-m-d');

        $query = '
		SELECT
			*,
            (SELECT Nombre FROM Plan_Cuentas WHERE Id_Plan_Cuentas = Id_Plan_Cuenta_Depreciacion_NIIF) AS Cuenta_Depreciacion_NIIF,
            (SELECT Nombre FROM Plan_Cuentas WHERE Id_Plan_Cuentas = Id_Plan_Cuenta_Depreciacion_PCGA) AS Cuenta_Depreciacion_PCGA,
            (SELECT Nombre FROM Plan_Cuentas WHERE Id_Plan_Cuentas = Id_Plan_Cuenta_NIIF) AS Cuenta_Niif,
            (SELECT Nombre FROM Plan_Cuentas WHERE Id_Plan_Cuentas = Id_Plan_Cuenta_PCGA) AS Cuenta_Pcga,
            (SELECT Nombre FROM Plan_Cuentas WHERE Id_Plan_Cuentas = Id_Plan_Cuenta_Credito_Depreciacion_PCGA) AS Cuenta_Credito_Pcga, (SELECT Nombre FROM Plan_Cuentas WHERE Id_Plan_Cuentas = Id_Plan_Cuenta_Credito_Depreciacion_NIIF) as Cuenta_Credito_Niif
		FROM Tipo_Activo_Fijo
		' . $condicion . '
		ORDER BY Nombre_Tipo_Activo ASC';

        $query_count = '
		SELECT
			COUNT(Id_Tipo_Activo_Fijo) AS Total
		FROM Tipo_Activo_Fijo
		' . $condicion;

        $paginationData = new PaginacionData($tam, $query_count, $pag);

        //Se crea la instancia que contiene la consulta a realizar
        $queryObj = new QueryBaseDatos($query);

        //Ejecuta la consulta de la instancia queryobj y retorna el resultado de la misma segun los parametros
        $tipos_contrato = $queryObj->Consultar('Multiple', true, $paginationData);

        return response()->json($tipos_contrato);
    }

    function SetCondiciones($req)
    {
        $condicion = '';

        if (isset($req['nombre']) && $req['nombre']) {
            $condicion .= " WHERE Nombre_Tipo_Activo LIKE '%" . $req['nombre'] . "%'";
        }

        if (isset($req['categoria']) && $req['categoria']) {
            if ($condicion != "") {
                $condicion .= " AND Categoria LIKE '%" . $req['categoria'] . "%'";
            } else {
                $condicion .= " WHERE Categoria LIKE '%" . $req['categoria'] . "%'";
            }
        }

        if (isset($req['vida_util']) && $req['vida_util']) {
            if ($condicion != "") {
                $condicion .= " AND Vida_Util = " . $req['vida_util'];
            } else {
                $condicion .= " WHERE Vida_Util = " . $req['vida_util'];
            }
        }

        if (isset($req['depreciacion']) && $req['depreciacion']) {
            if ($condicion != "") {
                $condicion .= " AND Porcentaje_Depreciacion_Anual = " . $req['depreciacion'];
            } else {
                $condicion .= " WHERE Porcentaje_Depreciacion_Anual = " . $req['depreciacion'];
            }
        }

        return $condicion;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $http_response = new HttpResponse();
        $repsonse = array();

        $modelo = (isset($_REQUEST['modelo']) ? $_REQUEST['modelo'] : '');
        $modelo = json_decode($modelo, true);

        $fecha = date('Y-m-d H:i:s');

        if ($modelo['Id_Tipo_Activo_Fijo'] == '') {
            $oItem = new complex("Tipo_Activo_Fijo", "Id_Tipo_Activo_Fijo");
            $oItem->Nombre_Tipo_Activo = $modelo['Nombre_Tipo_Activo'];
            $oItem->Categoria = $modelo['Categoria'];
            $oItem->Vida_Util = $modelo['Vida_Util'];
            $oItem->Vida_Util_PCGA = $modelo['Vida_Util_PCGA'];
            $oItem->Porcentaje_Depreciacion_Anual = number_format($modelo['Porcentaje_Depreciacion_Anual'], 2);
            $oItem->Porcentaje_Depreciacion_Anual_PCGA = number_format($modelo['Porcentaje_Depreciacion_Anual_PCGA'], 2);
            $oItem->Id_Plan_Cuenta_Depreciacion_NIIF = $modelo['Id_Plan_Cuenta_Depreciacion_NIIF'];
            $oItem->Id_Plan_Cuenta_Depreciacion_PCGA = $modelo['Id_Plan_Cuenta_Depreciacion_PCGA'];
            $oItem->Id_Plan_Cuenta_NIIF = $modelo['Id_Plan_Cuenta_NIIF'];
            $oItem->Id_Plan_Cuenta_PCGA = $modelo['Id_Plan_Cuenta_PCGA'];
            $oItem->Id_Plan_Cuenta_Credito_Depreciacion_PCGA = $modelo['Id_Plan_Cuenta_Credito_Depreciacion_PCGA'];
            $oItem->Id_Plan_Cuenta_Credito_Depreciacion_NIIF = $modelo['Id_Plan_Cuenta_Credito_Depreciacion_NIIF'];
            $oItem->save();
            unset($oItem);

            $http_response->SetRespuesta(0, 'Registro Exitoso', 'Se ha registrado el tipo de activo fijo exitosamente!');
            $repsonse = $http_response->GetRespuesta();
        } else if (isset($modelo['Estado'])) {

            $oItem = new complex("Tipo_Activo_Fijo", "Id_Tipo_Activo_Fijo", $modelo['Id_Tipo_Activo_Fijo']);
            $oItem->Estado = $modelo['Estado'];
            $oItem->save();
            unset($oItem);

            $http_response->SetRespuesta(0, 'Actualizacion Exitosa', 'Se ha actualizado el tipo de activo fijo exitosamente!');
            $repsonse = $http_response->GetRespuesta();
        } else if (!isset($modelo['Estado'])) {
            $oItem = new complex("Tipo_Activo_Fijo", "Id_Tipo_Activo_Fijo", $modelo['Id_Tipo_Activo_Fijo']);
            $oItem->Nombre_Tipo_Activo = $modelo['Nombre_Tipo_Activo'];
            $oItem->Categoria = $modelo['Categoria'];
            $oItem->Vida_Util = $modelo['Vida_Util'];
            $oItem->Vida_Util_PCGA = $modelo['Vida_Util_PCGA'];
            $oItem->Porcentaje_Depreciacion_Anual = number_format($modelo['Porcentaje_Depreciacion_Anual'], 2);
            $oItem->Porcentaje_Depreciacion_Anual_PCGA = number_format($modelo['Porcentaje_Depreciacion_Anual_PCGA'], 2);
            $oItem->Id_Plan_Cuenta_Depreciacion_NIIF = $modelo['Id_Plan_Cuenta_Depreciacion_NIIF'];
            $oItem->Id_Plan_Cuenta_Depreciacion_PCGA = $modelo['Id_Plan_Cuenta_Depreciacion_PCGA'];
            $oItem->Id_Plan_Cuenta_NIIF = $modelo['Id_Plan_Cuenta_NIIF'];
            $oItem->Id_Plan_Cuenta_PCGA = $modelo['Id_Plan_Cuenta_PCGA'];
            $oItem->Id_Plan_Cuenta_Credito_Depreciacion_PCGA = $modelo['Id_Plan_Cuenta_Credito_Depreciacion_PCGA'];
            $oItem->Id_Plan_Cuenta_Credito_Depreciacion_NIIF = $modelo['Id_Plan_Cuenta_Credito_Depreciacion_NIIF'];
            $oItem->save();
            unset($oItem);

            $http_response->SetRespuesta(0, 'Actualizacion Exitosa', 'Se ha actualizado el tipo de activo fijo exitosamente!');
            $repsonse = $http_response->GetRespuesta();
        }

        return response()->json($repsonse);
    }
}
