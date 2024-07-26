<?php

namespace App\Http\Controllers;

use App\Http\Services\complex;
use App\Http\Services\consulta;
use App\Http\Services\HttpResponse;
use App\Http\Services\PaginacionData;
use App\Http\Services\QueryBaseDatos;
use App\Models\PuntoDispensacion;
use App\Models\Servicio;
use App\Models\ServicioPuntoDispensacion;
use App\Models\TipoServicioPuntoDispensacion;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PuntoDispensacionController extends Controller
{
    use ApiResponser;
    public function getPuntos(Request $request)
    {
        $id = $request->input('func');
        $puntos = PuntoDispensacion::join('people', 'Punto_Dispensacion.Id_Punto_Dispensacion', '=', 'people.dispensing_point_id')
            ->where('people.identifier', $id)
            ->select('Punto_Dispensacion.Id_Punto_Dispensacion as value', 'Punto_Dispensacion.Nombre as label')
            ->get();

        return $this->success($puntos);
    }

    public function serviciosNgSelect()
    {
        $services = Servicio::select('Id_Servicio as value', 'Nombre as label')
            ->orderBy('Nombre', 'desc')
            ->get();
        return response()->json($services);
    }

    public function detallePuntoDispensacion(Request $request)
    {
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);

        $query = PuntoDispensacion::with('departamento', 'tipoServicio', 'servicioPuntoDispensacion');

        if ($request->has('nombre_punto_dispensacion')) {
            $query->where('Nombre', 'like', '%' . $request->nombre_punto_dispensacion . '%');
        }

        if ($request->has('id_departamento')) {
            $query->where('Departamento', $request->id_departamento);
        }

        if ($request->has('tipo_dispensacion')) {
            $query->where('Tipo', 'like', '%' . $request->tipo_dispensacion . '%');
        }

        if ($request->has('direccion')) {
            $query->where('Direccion', 'like', '%' . $request->direccion . '%');
        }

        if ($request->has('telefono')) {
            $query->where('Telefono', 'like', '%' . $request->telefono . '%');
        }

        if ($request->has('tipo_entrega')) {
            $query->where('Tipo_Entrega', 'like', '%' . $request->tipo_entrega . '%');
        }

        if ($request->has('no_pos')) {
            $query->where('No_Pos', $request->no_pos);
        }

        if ($request->has('turnero')) {
            $query->where('Turnero', $request->turnero);
        }

        if ($request->has('wacom')) {
            $query->where('Wacom', $request->wacom);
        }

        if ($request->has('entrega')) {
            $query->where('Entrega_Doble', $request->entrega);
        }

        $query->orderBy('Id_Punto_Dispensacion', 'desc');

        $result = $query->paginate($pageSize, '*', 'page', $page);

        return $this->success($result);
    }

    public function getBodegas()
    {
        $currentPage = request('current_page', false);
        $params = request('filtros', false);

        if ($params) {
            $params = json_decode($params, true);
        }

        $data = DB::table('Bodega_Nuevo')
            ->select('Nombre as label', 'Id_Bodega_Nuevo as value')
            ->where('Tipo', 'Despacho')
            ->get();

        return response()->json($data);
    }

    public function savePuntoDispensacion(Request $request)
    {
        $modelo = json_decode($request->modelo, true);
        $servicios = json_decode($request->servicios, true);
        $tipo_servicio = json_decode($request->tipos_servicio, true);
        $punto = PuntoDispensacion::create($modelo);
        foreach ($servicios as $index => $value) {
            ServicioPuntoDispensacion::create([
                'Id_Punto_Dispensacion' => $punto->Id_Punto_Dispensacion,
                'Id_Servicio' => $value
            ]);
        }
        foreach ($tipo_servicio as $index => $value) {
            TipoServicioPuntoDispensacion::create([
                'Id_Punto_Dispensacion' => $punto->Id_Punto_Dispensacion,
                'Id_Tipo_Servicio' => $value
            ]);
        }
        return $this->success($punto);
    }

    public function guardarGenerico()
    {
        $mod = (isset($_REQUEST['modulo']) ? $_REQUEST['modulo'] : '');
        $datos = (isset($_REQUEST['modelo']) ? $_REQUEST['modelo'] : '');
        $datos = (array) json_decode($datos);
        try {
            if (isset($datos["id"]) && $datos["id"] != "") {
                $oItem = new complex($mod, "Id_" . $mod, $datos["id"]);
            } else {
                $oItem = new complex($mod, "Id_" . $mod);
            }
            foreach ($datos as $index => $value) {
                $oItem->$index = $value;
            }
            $oItem->save();
            $id = $oItem->getId();
            unset($oItem);
            if ($id) {
                $resultado['mensaje'] = "Se ha creado correctamente el punto de dispensacion";
                $resultado['type'] = "success";
                $resultado['title'] = "Operación Exitosa";
            } else {
                $resultado['mensaje'] = "Ha ocurrido un error inesperado. Por favor intentelo de nuevo";
                $resultado['type'] = "error";
                $resultado['title'] = "Error";
            }
            return response()->json($resultado);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), $th->getLine());
        }
    }

    public function getDetalleDispensacion()
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $query = 'SELECT
			*, (CASE
		WHEN LOCATE ("1", S.Id_Servicio) THEN "Si"
		ELSE "No"
			END ) as Pos,
			(CASE
		WHEN LOCATE ("2", S.Id_Servicio) THEN "Si"
		ELSE "No"
			END ) as NoPos
		FROM Punto_Dispensacion PD
		LEFT JOIN (SELECT Id_Punto_Dispensacion, GROUP_CONCAT(DISTINCT Id_Servicio ) as Id_Servicio FROM Servicio_Punto_Dispensacion GROUP BY Id_Punto_Dispensacion) S  ON PD.Id_Punto_Dispensacion=S.Id_Punto_Dispensacion
		WHERE PD.Id_Punto_Dispensacion = ' . $id;
        $queryObj = new QueryBaseDatos($query);
        $detalle = $queryObj->Consultar('simple');
        $detalle['servicios'] = $this->GetServiciosPunto($id, $queryObj);
        $detalle['tipos_servicio'] = $this->GetTiposServicioPunto($id, $queryObj);
        unset($queryObj);
        return response()->json($detalle);
    }

    private function GetServiciosPunto($idPunto, $queryObj)
    {
        $serv = array();
        $query = '
			SELECT
				Id_Servicio
			FROM Servicio_Punto_Dispensacion
			WHERE
				Id_Punto_Dispensacion = ' . $idPunto;
        $queryObj->SetQuery($query);
        $servicios = $queryObj->ExecuteQuery('multiple');
        foreach ($servicios as $key => $value) {
            foreach ($value as $id_servicio) {
                array_push($serv, $id_servicio);
            }
        }
        return $serv;
    }

    private function GetTiposServicioPunto($idPunto, $queryObj)
    {
        $tipos_serv = array();
        $query = '
			SELECT
				Id_Tipo_Servicio
			FROM Tipo_Servicio_Punto_Dispensacion
			WHERE
				Id_Punto_Dispensacion = ' . $idPunto;
        $queryObj->SetQuery($query);
        $tipos_servicio = $queryObj->ExecuteQuery('multiple');
        foreach ($tipos_servicio as $key => $value) {
            foreach ($value as $id_servicio) {
                array_push($tipos_serv, $id_servicio);
            }
        }
        return $tipos_serv;
    }

    public function getTiposServicioNgSelect()
    {
        $id_servicio = (isset($_REQUEST['id_servicio']) ? $_REQUEST['id_servicio'] : '');
        $id_servicio = str_replace("[", "(", $id_servicio);
        $id_servicio = str_replace("]", ")", $id_servicio);
        $query = 'SELECT TS.Id_Tipo_Servicio AS value,CONCAT_WS(" - ", S.Nombre, TS.Nombre) AS label
				FROM Tipo_Servicio TS
				INNER JOIN Servicio S ON TS.Id_Servicio = S.Id_Servicio
				WHERE TS.Id_Servicio IN ' . $id_servicio . ' ORDER BY TS.Nombre ASC';
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $tipos = $oCon->getData();
        unset($oCon);
        return response()->json($tipos);
    }

    public function updatePuntoDispensacion()
    {
        $http_response = new HttpResponse();
        $queryObj = new QueryBaseDatos();
        $response = array();
        $modelo = (isset($_REQUEST['modelo']) ? $_REQUEST['modelo'] : '');
        $servicios = (isset($_REQUEST['servicios']) ? $_REQUEST['servicios'] : '');
        $tipos_servicio = (isset($_REQUEST['tipos_servicio']) ? $_REQUEST['tipos_servicio'] : '');
        $modelo = json_decode($modelo, true);
        $servicios = json_decode($servicios, true);
        $tipos_servicio = json_decode($tipos_servicio, true);
        $this->GuardarPuntoDispensacion($modelo);
        $this->GuardarServiciosPuntoDispensacion($servicios, $modelo['id'], $queryObj);
        $tipos_servicio = $this->ValidarTiposServicios($servicios, $tipos_servicio, $queryObj);
        $this->GuardarTipoServiciosPuntoDispensacion($tipos_servicio, $modelo['id'], $queryObj);

        $http_response->SetRespuesta(0, 'Registro Exitoso', 'Se ha registrado el punto de dispensacion exitosamente!');
        $response = $http_response->GetRespuesta();

        return response()->json($response);
    }

    function GuardarPuntoDispensacion($modelo)
    {

        $oItem = new complex("Punto_Dispensacion", "Id_Punto_Dispensacion", $modelo['id']);

        foreach ($modelo as $index => $value) {
            $oItem->$index = $value;
        }

        $oItem->save();
        unset($oItem);
    }

    function GuardarServiciosPuntoDispensacion($servicios, $idPunto, $queryObj)
    {
        $query_delete = 'DELETE FROM Servicio_Punto_Dispensacion WHERE Id_Punto_Dispensacion = ' . $idPunto;
        $queryObj->SetQuery($query_delete);
        $queryObj->QueryUpdate();

        foreach ($servicios as $service) {
            $oItem = new complex("Servicio_Punto_Dispensacion", "Id_Servicio_Punto_Dispensacion");
            $oItem->Id_Punto_Dispensacion = $idPunto;
            $oItem->Id_Servicio = $service;
            $oItem->save();
            unset($oItem);
        }
    }

    function GuardarTipoServiciosPuntoDispensacion($tipoServicios, $idPunto, $queryObj)
    {
        $query_delete = 'DELETE FROM Tipo_Servicio_Punto_Dispensacion WHERE Id_Punto_Dispensacion = ' . $idPunto;
        $queryObj->SetQuery($query_delete);
        $queryObj->QueryUpdate();

        foreach ($tipoServicios as $ts) {
            $oItem = new complex("Tipo_Servicio_Punto_Dispensacion", "Id_Tipo_Servicio_Punto_Dispensacion");
            $oItem->Id_Punto_Dispensacion = $idPunto;
            $oItem->Id_Tipo_Servicio = $ts;
            $oItem->save();
            unset($oItem);
        }
    }

    function ValidarTiposServicios($servicios, $tiposServicio, $queryObj)
    {
        $condicion_servicios = $this->MakeInCondition($servicios);
        $tipoServicioFinal = $tiposServicio;


        foreach ($tiposServicio as $key => $id) {

            $query = '
				SELECT
					Id_Tipo_Servicio
				FROM Servicio S
				INNER JOIN Tipo_Servicio TS ON S.Id_Servicio = TS.Id_Servicio
				WHERE
					TS.Id_Servicio IN (' . $condicion_servicios . ')
					AND TS.Id_Tipo_Servicio = ' . $id;
            $queryObj->SetQuery($query);
            $exist = $queryObj->ExecuteQuery('simple');

            if ($exist === false) {
                unset($tipoServicioFinal[$key]);
            }
        }

        return $tipoServicioFinal;
    }

    function MakeInCondition($servicios)
    {
        $condicion = implode(", ", $servicios);
        return $condicion;
    }

    public function listaPuntosDispensacion()
{
    $tipo = request()->input('Tipo', null);

    $tipoClause = $tipo ? " AND Tipo LIKE '%$tipo%'" : "";

    $query = "SELECT Id_Punto_Dispensacion AS value, Nombre AS text FROM Punto_Dispensacion WHERE Estado='Activo' $tipoClause";

    $oCon = new consulta();
    $oCon->setQuery($query);
    $oCon->setTipo('Multiple');
    $res = $oCon->getData();
    unset($oCon);

    return $this->success($res);
}
}
