<?php

namespace App\Http\Controllers;

use App\Exports\CentroCostosExport;
use App\Models\CentroCosto;
use Illuminate\Http\Request;
use App\Http\Services\HttpResponse;
use App\Http\Services\consulta;
use App\Http\Services\complex;
use App\Http\Services\SystemConstant;
use Maatwebsite\Excel\Facades\Excel;

class CentroCostoController extends Controller
{

    public function listaCentro()
    {
        $query = 'SELECT Id_Centro_Costo AS value, CONCAT(Codigo, " - ", Nombre) AS label FROM Centro_Costo WHERE Estado = "Activo" AND Movimiento = "Si"';
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $res = $oCon->getData();
        unset($oCon);
        return response()->json($res);
    }

    public function paginate()
    {
        $id_centro = (isset($_REQUEST['id_centro']) && $_REQUEST['id_centro'] != '') ? $_REQUEST['id_centro'] : '';
        $centros_padre = array();
        $centros_hijos = array();
        $centros = '';
        $companyId = getCompanyWorkedId();

        if ($id_centro != '') {
            $this->CicloInverso($id_centro);
            $this->ConsultarHijos($centros_padre);
            //var_dump($centros_padre);
            $centros = $this->ConvertirEnCadena($centros_hijos);
        }
        //NUEVO CODIGO
        $query = '';

        $condicion = $this->SetCondiciones($_REQUEST); // Obtener filtros.

        if ($id_centro == '') {
            $query = 'SELECT
                CC.*,
                (IF(CC.Id_Centro_Padre != 0, (SELECT Nombre FROM Centro_Costo WHERE Id_Centro_Costo = CC.Id_Centro_Padre), "Sin Padre")) AS PadreCentro,
                IF(CC.Id_Tipo_Centro != 0, (SELECT Nombre FROM Tipo_Centro WHERE Id_Tipo_Centro = CC.Id_Tipo_Centro), "") AS Tipo_Centro,
                (CASE CC.Id_Tipo_Centro
                    WHEN 1 THEN (SELECT CONCAT(first_name, " ", first_surname) AS Nombre FROM third_parties WHERE id = CC.Valor_Tipo_Centro AND is_client = 1)
                    WHEN 2 THEN (SELECT name FROM departments WHERE id = CC.Valor_Tipo_Centro)
                    WHEN 4 THEN (SELECT name FROM municipalities WHERE id = CC.Valor_Tipo_Centro)
                    ELSE ""
                END) AS ValorTipoCentro
            FROM
                Centro_Costo CC ' . $condicion . ' ORDER BY Codigo';
        } else {

            if ($centros == '') {
                $centros = 0;
            }

            $query = 'SELECT
                CC.*,
                (IF(CC.Id_Centro_Padre != 0, (SELECT Nombre FROM Centro_Costo WHERE Id_Centro_Costo = CC.Id_Centro_Padre), "Sin Padre")) AS PadreCentro,
                IF(CC.Id_Tipo_Centro != 0, (SELECT Nombre FROM Tipo_Centro WHERE Id_Tipo_Centro = CC.Id_Tipo_Centro), "") AS Tipo_Centro,
                (CASE CC.Id_Tipo_Centro
                    WHEN 1 THEN (SELECT CONCAT(first_name, " ",first_surname) AS Nombre FROM third_parties WHERE id = CC.Valor_Tipo_Centro)
                    WHEN 2 THEN (SELECT Nombre FROM departments WHERE id = CC.Valor_Tipo_Centro)
                    WHEN 4 THEN (SELECT Nombre FROM municipalities WHERE id = CC.Valor_Tipo_Centro)
                    ELSE ""
                END) AS ValorTipoCentro
            FROM
                Centro_Costo CC
            WHERE
                CC.Id_Centro_Costo IN(' . $centros . ') ' . $condicion;
        }

        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $res = $oCon->getData();
        $numReg = count($res);
        unset($oCon);

        if ($id_centro == '') {

            $resultado['Centros'] = $this->getPagination($query, $_REQUEST, $res);
        } else {
            $resultado['Centros'] = $res;
        }

        $query = "SELECT Id_Centro_Costo AS value,
            CONCAT(Codigo,' - ',Nombre) AS label
            FROM Centro_Costo
            WHERE Estado = 'Activo'
            AND company_id = " . $companyId; // Lista para que escojan el padre del centro de costo a crear.

        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $resultado['CentrosCostosPadre'] = $oCon->getData();
        unset($oCon);

        $resultado['numReg'] = $numReg;

        return response()->json($resultado);
    }

    public function buscar()
    {
        $centrocosto = CentroCosto::selectRaw('CONCAT(Codigo, " - ", Nombre) AS Nombre, Id_Centro_Costo')
            ->where('Movimiento', 'Si')
            ->where('Estado', 'Activo')
            ->get();

        return response()->json($centrocosto);
    }

    function getPagination($query, $req, $resulset)
    {
        ####### PAGINACIÓN ########
        $tamPag = 100;
        $numReg = count($resulset);
        $paginas = ceil($numReg / $tamPag);
        $limit = "";
        $paginaAct = "";

        if (!isset($req['pag']) || $req['pag'] == '') {
            $paginaAct = 1;
            $limit = 0;
        } else {
            $paginaAct = $req['pag'];
            $limit = ($paginaAct - 1) * $tamPag;
        }

        $query = $query . " LIMIT $limit,$tamPag";


        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $resultado = $oCon->getData();
        unset($oCon);

        return $resultado;
    }

    function CicloInverso($idCentro)
    {
        $centros_padre = array();

        $query = '
            SELECT
                Id_Centro_Padre
            FROM
                Centro_Costo
            WHERE
                Id_Centro_Costo = ' . $idCentro;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $centro_padre = $oCon->getData();
        $centro_padre = $centro_padre != false ? $centro_padre['Id_Centro_Padre'] : '';
        unset($oCon);
        //dd($centros_padre);
        if ($centro_padre != '') {

            array_push($centros_padre, $centro_padre);
            $this->CicloInverso($centro_padre);
        } else {
            return;
        }
    }

    public function consultarCentro()
    {
        $id_centro = (isset($_REQUEST['id_centro']) && $_REQUEST['id_centro'] != "") ? $_REQUEST['id_centro'] : '';
        $opcion = (isset($_REQUEST['opcion']) && $_REQUEST['opcion'] != "") ? $_REQUEST['opcion'] : '';

        $centro_costo = array();

        if ($opcion == 'editar') {

            $query = '
        SELECT
            *
        FROM Centro_Costo
        WHERE
            Id_Centro_Costo = ' . $id_centro;

            $oCon = new consulta();
            $oCon->setQuery($query);
            $centro_costo = $oCon->getData();
            unset($oCon);
        } else {

            $query = '
        SELECT
            Nombre AS NombreCentro,
            Codigo AS CodigoCentro,
            (IF(CC.Id_Centro_Padre != 0, (SELECT Nombre FROM Centro_Costo WHERE Id_Centro_Costo = CC.Id_Centro_Padre), "Sin Padre")) AS PadreCentro,
            (SELECT Nombre FROM Tipo_Centro WHERE Id_Tipo_Centro = CC.Id_Tipo_Centro) AS TipoCentro
        FROM Centro_Costo CC
        WHERE
            Id_Centro_Costo = ' . $id_centro;

            $oCon = new consulta();
            $oCon->setQuery($query);
            $centro_costo = $oCon->getData();
            unset($oCon);

            if ($centro_costo != false) {

                switch (isset($value['TipoCentro'])) {
                    case 'Tercero':

                        $query = '
                        SELECT
                            Nombre
                        FROM Cliente
                        WHERE
                            Id_Cliente = ' . isset($value['Id_Tipo_Centro']);

                        $oCon = new consulta();
                        $oCon->setQuery($query);
                        $nombre_tercero = $oCon->getData();
                        unset($oCon);

                        $centro_costo['ValorTipoCentro'] = $nombre_tercero['Nombre'];
                        break;

                    case 'Departamento':

                        $query = '
                        SELECT
                            Nombre
                        FROM Departamento
                        WHERE
                            Id_Departamento = ' . isset($value['Id_Tipo_Centro']);

                        $oCon = new consulta();
                        $oCon->setQuery($query);
                        $nombre_departamento = $oCon->getData();
                        unset($oCon);

                        $centro_costo['ValorTipoCentro'] = $nombre_departamento['Nombre'];
                        break;

                    case 'Punto de Dispensacion':

                        $query = '
                        SELECT
                            Nombre
                        FROM Punto_Dispensacion
                        WHERE
                            Id_Punto_Dispensacion = ' . isset($value['Id_Tipo_Centro']);

                        $oCon = new consulta();
                        $oCon->setQuery($query);
                        $nombre_punto = $oCon->getData();
                        unset($oCon);

                        $centro_costo['ValorTipoCentro'] = $nombre_punto['Nombre'];
                        break;

                    default:
                        $centro_costo['ValorTipoCentro'] = '';
                        break;
                }
            }
        }


        return response()->json($centro_costo);
    }

    public function cambiarCentro()
    {
        $id_centro = (isset($_REQUEST['id_centro']) ? $_REQUEST['id_centro'] : '');
        $id_centro = json_decode($id_centro);

        if (empty($id_centro)) {
            $respuesta = [
                'codigo' => 1,
                'titulo' => 'Alerta',
                'mensaje' => 'No se envío un centro de costo para proceder, contacte con el administrador!'
            ];
            return response()->json($respuesta);
        }

        $centroCosto = CentroCosto::find($id_centro);

        if (!$centroCosto) {
            $respuesta = [
                'codigo' => 1,
                'titulo' => 'Alerta',
                'mensaje' => 'No se encontró el centro de costo especificado'
            ];
            return response()->json($respuesta);
        }

        $centroCosto->Estado = $centroCosto->Estado == 'Activo' ? 'Inactivo' : 'Activo';
        $centroCosto->save();

        $respuesta = [
            'codigo' => 0,
            'titulo' => 'Operación exitosa!',
            'mensaje' => 'Se ha cambiado el estado del centro de costo'
        ];

        return response()->json($respuesta);
    }

    function ConvertirEnCadena($centros)
    {
        $cadena = '';

        if (count($centros) == 0) {
            return $cadena;
        }

        for ($i = 0; $i <= (count($centros) - 1); $i++) {
            if (($i + 1) == count($centros)) {
                $cadena .= $centros[$i];
            } else if (($i + 1) < count($centros)) {
                $cadena .= $centros[$i] . ",";
            }
        }
        return $cadena;
    }

    function ConsultarHijos($idsPadre)
    {
        $centros_hijos = array();
        if (count($idsPadre) > 0) {
            $ind = 0;
            $q = '';
            $ids = '';

            foreach ($idsPadre as $v) {

                if ($idsPadre[$ind + 1] != '' || $idsPadre[$ind + 1] != null) {
                    $q = '
                        SELECT
                            GROUP_CONCAT(Id_Centro_Costo) AS Id_Centros
                        FROM
                            Centro_Costo
                        WHERE
                            Id_Centro_Padre = ' . $idsPadre[$ind + 1];

                    $oCon = new consulta();
                    $oCon->setTipo('Multiple');
                    $oCon->setQuery($q);
                    $ids = $oCon->getData();
                    unset($oCon);
                } else {
                    return;
                }
                if ($ids[0]['Id_Centros'] != '') {
                    array_push($centros_hijos, $ids[0]['Id_Centros']);
                }
                $ind++;
            }
        }
    }

    function SetCondiciones($req)
    {
        $condicion = '';
        $id_centro = (isset($_REQUEST['id_centro']) && $_REQUEST['id_centro'] != '') ? $_REQUEST['id_centro'] : '';
        if ($id_centro == '') {
            if (isset($req['cod']) && $req['cod'] != "") {
                $condicion .= " WHERE Codigo LIKE '%" . $req['cod'] . "%'";
            }

            if (isset($req['nom']) && $req['nom'] != "") {
                if ($condicion != "") {
                    $condicion .= " AND Nombre LIKE '%" . $req['nom'] . "%'";
                } else {
                    $condicion .= " WHERE Nombre LIKE '%" . $req['nom'] . "%'";
                }
            }
            $condicion .= " WHERE company_id =" . getCompanyWorkedId();
        } else {
            if (isset($req['cod']) && $req['cod'] != "") {
                $condicion .= " AND Codigo LIKE '%" . $req['cod'] . "%'";
            }

            if (isset($req['nom']) && $req['nom']) {
                $condicion .= " AND Nombre LIKE '%" . $req['nom'] . "%'";
            }
            $condicion .= " WHERE company_id =" . getCompanyWorkedId();
        }
        return $condicion;
    }

    public function listaTipo()
    {
        $query = '
        SELECT
            *
        FROM Tipo_Centro';

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $tipos = $oCon->getData();
        unset($oCon);

        return response()->json($tipos);
    }

    public function listaValores()
    {
        $company = (isset($_REQUEST['company']) && $_REQUEST['company'] != "") ? $_REQUEST['company'] : '1';
        $tipo = (isset($_REQUEST['tipo']) && $_REQUEST['tipo'] != "") ? $_REQUEST['tipo'] : '';

        $result = array();

        switch ($tipo) {
            case 'Tercero':

                $query = ' SELECT id AS Id, IFNULL(social_reason, CONCAT_WS(" ", first_name, first_surname, second_name, second_surname)) AS Nombre FROM third_parties /* WHERE company_id = */ ' /* . $company */ ;

                $oCon = new consulta();
                $oCon->setQuery($query);
                $oCon->setTipo('Multiple');
                $result = $oCon->getData();
                unset($oCon);
                break;

            case 'Departamento':

                $query = '
                SELECT
                    id as Id,
                    name AS Nombre
                FROM departments';

                $oCon = new consulta();
                $oCon->setQuery($query);
                $oCon->setTipo('Multiple');
                $result = $oCon->getData();
                unset($oCon);
                break;

            case 'Municipio':

                $query = '
                SELECT
                    id as Id,
                    name AS Nombre
                FROM municipalities ORDER BY name';

                $oCon = new consulta();
                $oCon->setQuery($query);
                $oCon->setTipo('Multiple');
                $result = $oCon->getData();
                unset($oCon);
                break;
            case 'Zonas':

                $query = '
                SELECT
                    id,
                    name AS Nombre
                FROM zones ORDER BY name';

                $oCon = new consulta();
                $oCon->setQuery($query);
                $oCon->setTipo('Multiple');
                $result = $oCon->getData();
                unset($oCon);
                break;

            default:
                break;
        }

        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $respuesta = array();
        $http_response = new HttpResponse();

        $datos = (isset($_REQUEST['Datos']) ? $_REQUEST['Datos'] : '');
        $accion = (isset($_REQUEST['accion']) ? $_REQUEST['accion'] : '');
        $datos = (array) json_decode($datos);

        if ($accion == 'guardar') {
            if ($this->ComprobarExistenciaCentro($datos['Codigo'])) {
                $http_response->SetRespuesta(2, "Respuesta", 'El centro de costo que intenta registrar ya existe!');
                $respuesta = $http_response->GetRespuesta();
                return response()->json($respuesta);
            }
        }


        if (isset($datos['Id_Centro_Costo']) && $datos['Id_Centro_Costo'] != '') {
            $oItem = new complex("Centro_Costo", "Id_Centro_Costo", $datos['Id_Centro_Costo']);
        } else {

            $oItem = new complex("Centro_Costo", "Id_Centro_Costo");
        }


        foreach ($datos as $index => $value) {
            if ($index == 'Id_Tipo_Centro' && $value == '') {
                $value = '0';
            }
            if ($index == 'Valor_Tipo_Centro' && $value == '') {
                $value = '0';
            }
            if ($index == 'Id_Centro_Padre' && $value == '') {
                $value = '0';
            }
            $oItem->$index = $value;
        }

        $oItem->save();
        unset($oItem);

        $http_response->SetRespuesta(0, "Respuesta", '¡Operación exitosa!');
        $respuesta = $http_response->GetRespuesta();
        return response()->json($respuesta);
    }

    function ComprobarExistenciaCentro($codigoCentro)
    {

        $query_centro_costo = '
			SELECT
				*
			FROM Centro_Costo
			WHERE
				Codigo = "' . $codigoCentro . '"';

        $oCon = new consulta();
        $oCon->setQuery($query_centro_costo);
        $total = $oCon->getData();
        unset($oCon);

        return $total != false;
    }

    function SetIdentificadorVisual($codigoCentro)
    {
        $longitud_codigo = strlen($codigoCentro);
        $nivel_visual = $longitud_codigo / SystemConstant::$Digitos_Codigo_Centro_Costo;
        $identificador_visual = '';

        for ($i = $nivel_visual; $i == 0; $i--) {
            $identificador_visual .= '-';
        }

        return $identificador_visual;
    }

    public function exportar()
    {
        $company = (isset($_REQUEST['company']) && $_REQUEST['company'] != "") ? $_REQUEST['company'] : '1';

        $query = "SELECT CAST(CC.Codigo AS CHAR(60)) as Codigo, CC.Nombre AS Centro_Costo,
            (CASE Id_Centro_Padre WHEN 0 THEN 'Sin Padre'
            ELSE (SELECT Nombre FROM Centro_Costo
            WHERE Id_Centro_Costo = CC.Id_Centro_Padre)
            END) AS Centro_Padre,
            IF(CC.Id_Tipo_Centro != 0, (SELECT Nombre FROM Tipo_Centro WHERE Id_Tipo_Centro = CC.Id_Tipo_Centro), '') AS Tipo_Centro,
            IF(CC.company_id != 0,(SELECT name FROM companies WHERE id = CC.company_id), '') AS Empresa,
            (CASE CC.Id_Tipo_Centro
            WHEN 1 THEN
            (SELECT CONCAT(first_name, ' ',first_surname) AS Nombre
            FROM third_parties WHERE id = CC.Valor_Tipo_Centro
            AND is_client = 1)
            WHEN 2 THEN (SELECT name FROM departments
            WHERE id = CC.Valor_Tipo_Centro)
            WHEN 3 THEN (SELECT name FROM municipalities
            WHERE id = CC.Valor_Tipo_Centro) ELSE '' END) AS Asignado_A, CC.Estado
            FROM Centro_Costo CC WHERE CC.company_id =" . $company . " ORDER BY CC.Codigo";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $centros = $oCon->getData();
        unset($oCon);
        //return $centros;
        return Excel::download(new CentroCostosExport($centros), 'Centro costos.xlsx');
    }
}
