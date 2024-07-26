<?php

namespace App\Http\Controllers;

use App\Exports\AtencionTurneroExport;
use App\Http\Services\complex;
use App\Http\Services\consulta;
use App\Http\Services\HttpResponse;
use App\Http\Services\PaginacionData;
use App\Http\Services\QueryBaseDatos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TurnerosController extends Controller
{
    public function getTurneros()
    {

        $http_response = new HttpResponse();
        $queryObj = new QueryBaseDatos();

        $query = '
        SELECT 
            Id_Turneros AS value,
            Nombre AS label
        FROM Turneros';


        $queryObj->SetQuery($query);
        $turneros = $queryObj->Consultar('Multiple');

        unset($http_response);
        unset($queryObj);

        return response()->json($turneros);

    }

    public function listaTurnero()
    {
        $condicion = $this->SetCondiciones($_REQUEST);
        $pageSize = request()->get('pageSize', 10);
        $page = request()->get('page', 1);

        $query = DB::table('Turneros as T')
            ->select('T.*')
            ->whereRaw($condicion);

        $turneros = $query->paginate($pageSize, ['*'], 'page', $page);

        return response()->json($turneros);
    }

    function SetCondiciones($req)
    {
        $condicion = '1=1';
        if (isset($req['nom']) && $req['nom']) {
            $condicion .= " AND T.Nombre LIKE '%" . addslashes($req['nom']) . "%'";
        }
        return $condicion;
    }

    public function puntosFuncionario()
    {
        $query = 'SELECT PD.Nombre as label, PD.Id_Punto_Dispensacion as value
          FROM Punto_Dispensacion PD WHERE Estado != "Inactivo" ';

        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $puntos = $oCon->getData();
        unset($oCon);


        return response()->json($puntos);
    }

    public function reporteAtencionTurnero(Request $request)
    {
        $condiciones = $this->SetConditions($request);
        return Excel::download(new AtencionTurneroExport($condiciones), 'reporte_atencion_turnero.xlsx');
    }

    function SetConditions(Request $request)
    {
        $query = DB::table('Turneros as T')
            ->selectRaw('
            IFNULL(CONCAT_WS(" ", P.firstname, P.middlename, P.surname, P.secondsurname),"No Aplica") AS patients,
            T.Persona as Reclamante,
            IF(T.Hora_Turno = "23:59:59", "Auditoria", T.Hora_Turno) AS Hora_Turno,
            IF(T.Hora_Inicio_Atencion = "00:00:00", "No Atendido", T.Hora_Inicio_Atencion) Hora_Inicio_Atencion,
            T.Fecha,
            TS.Nombre,
            T.Tipo
        ')
            ->join('Turneros as TS', 'TS.Id_Turneros', '=', 'T.Id_Turneros')
            ->join('Punto_Turnero as PT', 'TS.Id_Turneros', '=', 'PT.Id_Turneros')
            ->leftJoin('Auditoria as A', 'T.Id_Auditoria', '=', 'A.Id_Auditoria')
            ->leftJoin('patients as P', 'A.Id_Paciente', '=', 'P.id')
            ->orderBy('T.Hora_Turno', 'DESC');

        if ($request->has('id_turneros') && $request->input('id_turneros') != "") {
            $query->where('TS.Id_Turneros', $request->input('id_turneros'));
        }

        if ($request->has('fechas') && $request->input('fechas')) {
            $fechas = $this->SepararFechas($request->input('fechas'));
            $query->whereBetween(DB::raw('DATE(T.Fecha)'), [$fechas[0], $fechas[1]]);
        }

        return $query;
    }

    function SepararFechas($fechas)
    {
        $fechas_separadas = explode(" - ", $fechas);
        return $fechas_separadas;
    }

    public function detalleTurnero()
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $query = 'SELECT * FROM Turneros WHERE Id_Turneros=' . $id;


        $oCon = new consulta();
        $oCon->setQuery($query);
        $turneros = $oCon->getData();
        unset($oCon);

        $query = 'SELECT Id_Punto_Dispensacion FROM Punto_Turnero WHERE Id_Turneros=' . $id;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $puntos_turnero = $oCon->getData();
        unset($oCon);

        $puntos = [];

        foreach ($puntos_turnero as $punto) {
            $puntos[] = $punto->Id_Punto_Dispensacion;
        }

        $servicios_turnero = $this->GetSeviciosTurnero($id);

        $resultado['turneros'] = $turneros;
        $resultado['puntos'] = $puntos;
        $resultado['servicios'] = $servicios_turnero;

        return response()->json($resultado);
    }

    function GetSeviciosTurnero($idTurneros)
    {
        $query = 'SELECT Id_Servicio FROM Servicio_Turnero WHERE Id_Turnero=' . $idTurneros;

        $serv = array();

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('multiple');
        $servicios_turnero = $oCon->getData();
        unset($oCon);

        foreach ($servicios_turnero as $key => $value) {
            foreach ($value as $id_servicio) {
                array_push($serv, $id_servicio);
            }
        }

        return $serv;
    }

    public function guardarTurnero()
    {
        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');
        $puntos = (isset($_REQUEST['puntos']) ? $_REQUEST['puntos'] : '');
        $servicios = (isset($_REQUEST['servicios']) ? $_REQUEST['servicios'] : '');

        $datos = (array) json_decode($datos);
        $puntos = (array) json_decode($puntos, true);
        $servicios = (array) json_decode($servicios);

        $oItem = new complex("Turneros", "Id_Turneros");
        $oItem->Nombre = strtoupper($datos["Nombre"]);
        $oItem->Capita = $datos["Capita"];
        $oItem->No_Pos = $datos["No_Pos"];
        $oItem->save();
        $id_turnero = $oItem->getId();
        unset($oItem);

        foreach ($puntos as $value) {
            $oItem = new complex("Punto_Turnero", "Id_Punto_Turnero");
            $oItem->Id_Punto_Dispensacion = $value;
            $oItem->Id_Turneros = $id_turnero;
            $oItem->Capita = $datos["Capita"];
            $oItem->No_Pos = $datos["No_Pos"];
            $oItem->save();
            unset($oItem);

        }

        $this->GuardarServiciosTunero($servicios, $id_turnero);

        $resultado['mensaje'] = "Tunero creado Correctamente";
        $resultado['tipo'] = "success";

        return response()->json($resultado);
    }

    function GuardarServiciosTunero($servicios, $idTunero)
    {

        foreach ($servicios as $service) {
            $oItem = new complex("Servicio_Turnero", "Id_Servicio_Turnero");
            $oItem->Id_Turnero = $idTunero;
            $oItem->Id_Servicio = $service;
            $oItem->save();
            unset($oItem);
        }
    }

    public function editarTurnero()
    {
        $queryObj = new QueryBaseDatos();

        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');
        $puntos = (isset($_REQUEST['puntos']) ? $_REQUEST['puntos'] : '');
        $servicios = (isset($_REQUEST['servicios']) ? $_REQUEST['servicios'] : '');

        $datos = (array) json_decode($datos);
        $puntos = (array) json_decode($puntos, true);
        $servicios = (array) json_decode($servicios);

        $oItem = new complex("Turneros", "Id_Turneros", $datos['Id_Turneros']);
        $oItem->Nombre = strtoupper($datos["Nombre"]);
        $oItem->Direccion = strtoupper($datos["Direccion"]);
        $oItem->Capita = $datos["Capita"];
        $oItem->No_Pos = $datos["No_Pos"];
        $oItem->Autorizacion_Servicios = $datos["Autorizacion_Servicios"];
        $oItem->Maximo_Turnos = $datos["Maximo_Turnos"];
        $oItem->save();
        $id_turnero = $oItem->getId();
        unset($oItem);

        $oCon = new consulta();
        $oCon->setQuery("DELETE FROM Punto_Turnero WHERE Id_Turneros = $datos[Id_Turneros]");
        $oCon->deleteData();
        unset($oCon);

        foreach ($puntos as $value) {
            $oItem = new complex("Punto_Turnero", "Id_Punto_Turnero");
            $oItem->Id_Punto_Dispensacion = $value;
            $oItem->Id_Turneros = $id_turnero;
            $oItem->Capita = $datos["Capita"];
            $oItem->No_Pos = $datos["No_Pos"];
            $oItem->save();
            unset($oItem);
        }

        $this->GuardarServiciosTurnero($servicios, $datos['Id_Turneros'], $queryObj);

        $resultado['mensaje'] = "Turnero editado Correctamente";
        $resultado['tipo'] = "success";

        return response()->json($resultado);
    }

    function GuardarServiciosTurnero($servicios, $idTurnero, $queryObj)
    {
        $query_delete = 'DELETE FROM Servicio_Turnero WHERE Id_Turnero = ' . $idTurnero;
        $queryObj->SetQuery($query_delete);
        $queryObj->QueryUpdate();

        foreach ($servicios as $service) {
            $oItem = new complex("Servicio_Turnero", "Id_Servicio_Turnero");
            $oItem->Id_Turnero = $idTurnero;
            $oItem->Id_Servicio = $service;
            $oItem->save();
            unset($oItem);
        }
    }

    public function eliminarTurnero()
    {
        $id_turnero = (isset($_REQUEST['id_turneros']) ? $_REQUEST['id_turneros'] : '');


        $oItem = new complex("Turneros", "Id_Turneros", (INT) $id_turnero);
        $oItem->delete();
        unset($oItem);

        $query = "DELETE 
FROM Punto_Turnero 
WHERE Id_Turneros= " . $id_turnero;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $bod = $oCon->deleteData();
        unset($oCon);

        $resultado["mensaje"] = "Se ha eliminado el Tipo de Soporte Correctamente";
        $resultado["tipo"] = "success";
        return response()->json($resultado);
    }
}
