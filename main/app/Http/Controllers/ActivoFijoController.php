<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijo;
use Illuminate\Http\Request;
use App\Http\Services\PaginacionData;
use App\Http\Services\HttpResponse;
use App\Http\Services\consulta;
use App\Http\Services\complex;
use App\Http\Services\Contabilizar;
use App\Http\Services\QueryBaseDatos;
use App\Http\Services\comprobantes\ObtenerProximoConsecutivo;
use App\Http\Services\resumenretenciones\funciones;

class ActivoFijoController extends Controller
{

    public function paginate()
    {
        $http_response = new HttpResponse();

        $pag = (isset($_REQUEST['pag']) ? $_REQUEST['pag'] : '');
        $tam = (isset($_REQUEST['tam']) ? $_REQUEST['tam'] : '');
        $company = (isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : '');

        $condicion = $this->SetCondiciones($_REQUEST);

        $fecha = date('Y-m-d');

        $query = '
		SELECT
			AF.*,
            TAF.Nombre_Tipo_Activo AS Tipo_Activo,
            (SELECT Nombre FROM Centro_Costo WHERE ID_Centro_Costo=AF.Id_Centro_Costo) as Centro_Costo,
            (SELECT name FROM companies WHERE id = AF.Id_Empresa) as Empresa
		FROM Activo_Fijo AF
        INNER JOIN Tipo_Activo_Fijo TAF ON AF.Id_Tipo_Activo_Fijo = TAF.Id_Tipo_Activo_Fijo
		' . $condicion . '
		ORDER BY Id_Activo_Fijo DESC';

        $query_count = '
		SELECT
            COUNT(AF.Id_Activo_Fijo) AS Total
        FROM Activo_Fijo AF
        ' . $condicion;

        $paginationData = new PaginacionData($tam, $query_count, $pag);

        //Se crea la instancia que contiene la consulta a realizar
        $queryObj = new QueryBaseDatos($query);

        //Ejecuta la consulta de la instancia queryobj y retorna el resultado de la misma segun los parametros
        $tipo_activos = $queryObj->Consultar('Multiple', true, $paginationData);

        return response()->json($tipo_activos);
    }

    function SetCondiciones($req)
    {
        $condicion = '';

        if (isset($req['company_id']) && $req['company_id']) {
            $condicion .= " WHERE company_id=" . $req['company_id'];
        }

        if (isset($req['codigo']) && $req['codigo']) {
            if ($condicion != "") {
                $condicion .= " AND Codigo LIKE '%" . $req['codigo'] . "%'";
            } else {
                $condicion .= " WHERE Codigo LIKE '%" . $req['codigo'] . "%'";
            }
        }

        if (isset($req['nombre']) && $req['nombre']) {
            if ($condicion != "") {
                $condicion .= " AND Nombre LIKE '%" . $req['nombre'] . "%'";
            } else {
                $condicion .= " WHERE Nombre LIKE '%" . $req['nombre'] . "%'";
            }
        }

        if (isset($req['tipo']) && $req['tipo']) {
            if ($condicion != "") {
                $condicion .= " AND AF.Id_Tipo_Activo_Fijo = " . $req['tipo'];
            } else {
                $condicion .= " WHERE AF.Id_Tipo_Activo_Fijo = " . $req['tipo'];
            }
        }

        if (isset($req['costo_niif']) && $req['costo_niif']) {
            if ($condicion != "") {
                $condicion .= " AND Costo_NIIF = " . $req['costo_niif'];
            } else {
                $condicion .= " WHERE Costo_NIIF = " . $req['costo_niif'];
            }
        }

        if (isset($req['costo_pcga']) && $req['costo_pcga']) {
            if ($condicion != "") {
                $condicion .= " AND Costo_PCGA = " . $req['costo_pcga'];
            } else {
                $condicion .= " WHERE Costo_PCGA = " . $req['costo_pcga'];
            }
        }

        return $condicion;
    }

    public function datosReporte()
    {
        $query = "SELECT Id_Tipo_Activo_Fijo AS value, Nombre_Tipo_Activo AS label FROM Tipo_Activo_Fijo WHERE Mantis = 'No' AND Estado = 'Activo' ORDER BY Nombre_Tipo_Activo";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $resultados['Tipos_Activos'] = $oCon->getData();
        unset($oCon);

        $query = "SELECT Id_Centro_Costo AS value, CONCAT(Codigo,' - ',Nombre) AS label FROM Centro_Costo WHERE Estado = 'Activo' AND Movimiento = 'Si' ORDER BY Codigo";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $resultados['Centro_Costos'] = $oCon->getData();
        unset($oCon);

        return response()->json($resultados);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $contabilizar = new Contabilizar();
        $http_response = new HttpResponse();
        $response = array();
        $modelo = (isset($_REQUEST['modelo']) ? $_REQUEST['modelo'] : '');
        $ctas_anticipo = (isset($_REQUEST['ctas_anticipo']) ? $_REQUEST['ctas_anticipo'] : '');
        $modelo = json_decode($modelo, true);
        $ctas_anticipo = json_decode($ctas_anticipo, true);
        $otros = ($modelo['Base'] + $modelo['Iva']) - ($modelo['Costo_Rete_Fuente'] /* + $modelo['Costo_Rete_Iva'] */);
        $modelo['Costo_NIIF'] = number_format($modelo['Costo_NIIF'], 2, ".", "");
        $modelo['Costo_PCGA'] = number_format($modelo['Costo_PCGA'], 2, ".", "");
        $modelo['Iva'] = number_format($modelo['Iva'], 2, ".", "");
        $modelo['Base'] = number_format($modelo['Base'], 2, ".", "");
        $modelo['Iva_Niif'] = number_format($modelo['Iva_NIIF'], 2, ".", "");
        $modelo['Base_Niif'] = number_format($modelo['Base_NIIF'], 2, ".", "");
        $modelo['Otros'] = number_format($otros, 2, '.', '');
        $fecha = date('Y-m-d H:i:s');
        $modelo['Tipo_Depreciacion'] = $this->ObtenerTipoDepreciacion($modelo['Costo_PCGA']);
        $modelo['Ctas_Anticipo'] = $ctas_anticipo;
        $modelo['Codigo_Activo_Fijo'] = funciones::generarConsecutivoTipoActivo($modelo['Id_Tipo_Activo_Fijo']);
        $bandera = false;
        if ($modelo['Id_Activo_Fijo'] == '') {
            $oItem = new complex("Activo_Fijo", "Id_Activo_Fijo");
            $mes = isset($modelo['Fecha']) ? date('m', strtotime($modelo['Fecha'])) : date('m');
            $anio = isset($modelo['Fecha']) ? date('Y', strtotime($modelo['Fecha'])) : date('Y');
            $cod = ObtenerProximoConsecutivo::generarConsecutivo('Activo', $mes, $anio);
            $modelo['Codigo'] = $cod;
        } else {
            $this->EliminarMovimientosContable($modelo['Id_Activo_Fijo']);
            $oItem = new complex("Activo_Fijo", "Id_Activo_Fijo", $modelo['Id_Activo_Fijo']);
            unset($modelo['Id_Activo_Fijo']);
        }
        foreach ($modelo as $index => $value) {
            if ($value != '' && $value != null) {
                $oItem->$index = $value;
            }
        }
        $oItem->save();
        $id_activo = $oItem->getId();
        unset($oItem);
        $movimiento['Datos'] = $this->ArmarConceptoContabilizzacion($modelo);
        $movimiento['Datos_Anticipos'] = $this->crearMovimientosCtasAnticipo($modelo, $id_activo);
        $movimiento['Id_Registro'] = $id_activo;
        $movimiento['Nit'] = $modelo['Nit'];
        $movimiento['Fecha'] = $modelo['Fecha'];
        // var_dump($movimiento);
        $contabilizar->CrearMovimientoContable('Activo Fijo', $movimiento);


        $http_response->SetRespuesta(0, 'Actualizacion Exitosa', 'Se ha actualizado el activo fijo exitosamente!');
        $response = $http_response->GetRespuesta();
        $response['Id'] = $id_activo;
        return response()->json($response);
    }

    function crearMovimientosCtasAnticipo($modelo, $id_activo)
    {
        global $fecha;
        $mod = [];
        unset($modelo['Ctas_Anticipo'][count($modelo['Ctas_Anticipo']) - 1]);
        if (count($modelo['Ctas_Anticipo']) > 0) {
            foreach ($modelo['Ctas_Anticipo'] as $i => $value) {
                $datos['Id_Plan_Cuenta'] = $value['Id_Plan_Cuenta'];
                $datos['Debe'] = '0';
                $datos['Haber'] = $value['Valor'];
                $datos['Debe_Niif'] = '0';
                $datos['Haber_Niif'] = $value['Valor'];
                $datos['Documento'] = $value['Documento'];
                $datos['Detalles'] = $value['Detalles'];
                $datos['Nit'] = $value['Nit'];
                $datos['Fecha_Movimiento'] = $modelo['Fecha'];
                $datos['Id_Modulo'] = 27;
                $datos['Id_Registro_Modulo'] = $id_activo;
                $datos['Id_Centro_Costo'] = $modelo['Id_Centro_Costo'];
                $datos['Numero_Comprobante'] = $modelo['Codigo'];
                $datos['Estado'] = 'Activo';
                $datos['Fecha_Registro'] = date('Y-m-d H:i:s');
                $mod[] = $datos;
            }
        }
        return $mod;
    }

    function ArmarConceptoContabilizzacion($modelo)
    {

        $modelo['Id_Plan_Cuenta'] = $this->GetTipoActivoFijo($modelo['Id_Tipo_Activo_Fijo']);
        $contabilizacion['Base'] = $this->CrearMovimiento($modelo, 'Base');
        if ($modelo['Iva'] != 0) {
            $contabilizacion['Iva'] = $this->CrearMovimiento($modelo, 'Iva');
        }
        if ($modelo['Id_Cuenta_Rete_Ica'] != 0 && $modelo['Id_Cuenta_Rete_Ica'] != '' && $modelo['Costo_Rete_Ica'] != 0) {
            $contabilizacion['Rete_Ica'] = $this->CrearMovimiento($modelo, 'Rete_Ica');
        }
        if ($modelo['Id_Cuenta_Rete_Fuente'] != 0 && $modelo['Id_Cuenta_Rete_Fuente'] != '' && $modelo['Costo_Rete_Fuente'] != 0) {
            $contabilizacion['Rete_Fuente'] = $this->CrearMovimiento($modelo, 'Rete_Fuente');
        }
        if ($modelo['Id_Cuenta_Cuenta_Por_Pagar'] != 0 && $modelo['Id_Cuenta_Cuenta_Por_Pagar'] != '') {
            $contabilizacion['CtaPorPagar'] = $this->CrearMovimiento($modelo, 'CtaPorPagar');
        }

        return $contabilizacion;
    }

    function CrearMovimiento($modelo, $tipo)
    {
        global $id_activo, $fecha;
        $mod = [];

        $mod['Fecha_Movimiento'] = $modelo['Fecha'];
        $mod['Id_Modulo'] = 27;
        $mod['Id_Registro_Modulo'] = $id_activo;
        $mod['Nit'] = $modelo['Nit'];
        $mod['Tipo_Nit'] = $modelo['Tipo'];
        $mod['Documento'] = $modelo['Documento'];
        $mod['Detalles'] = $modelo['Concepto'];
        $mod['Id_Centro_Costo'] = $modelo['Id_Centro_Costo'];
        $mod['Numero_Comprobante'] = $modelo['Codigo'];
        $mod['Estado'] = 'Activo';
        $mod['Fecha_Registro'] = date('Y-m-d H:i:s');

        if ($tipo == 'Base') {
            $mod['Id_Plan_Cuenta'] = $modelo['Id_Plan_Cuenta'];
            $mod['Debe'] = $modelo['Base'];
            $mod['Haber'] = '0';
            $mod['Debe_Niif'] = $modelo['Base_NIIF'];
            $mod['Haber_Niif'] = '0';
        } elseif ($tipo == 'Iva') {
            $mod['Id_Plan_Cuenta'] = $modelo['Id_Plan_Cuenta'];
            $mod['Debe'] = $modelo['Iva'];
            $mod['Haber'] = '0';
            $mod['Debe_Niif'] = $modelo['Iva_NIIF'];
            $mod['Haber_Niif'] = '0';
        } elseif ($tipo == 'Rete_Ica') {
            $mod['Id_Plan_Cuenta'] = $modelo['Id_Cuenta_Rete_Ica'];
            $mod['Debe'] = '0';
            $mod['Haber'] = $modelo['Costo_Rete_Ica'];
            $mod['Debe_Niif'] = '0';
            $mod['Haber_Niif'] = $modelo['Costo_Rete_Ica_NIIF'];
        } elseif ($tipo == 'Rete_Fuente') {
            $mod['Id_Plan_Cuenta'] = $modelo['Id_Cuenta_Rete_Fuente'];
            $mod['Debe'] = '0';
            $mod['Haber'] = $modelo['Costo_Rete_Fuente'];
            $mod['Debe_Niif'] = '0';
            $mod['Haber_Niif'] = $modelo['Costo_Rete_Fuente_NIIF'];
        } elseif ($tipo == 'CtaPorPagar') {
            $mod['Id_Plan_Cuenta'] = $modelo['Id_Cuenta_Cuenta_Por_Pagar'];
            $mod['Debe'] = '0';
            $mod['Haber'] = $modelo['Valor_CtaPorPagar'];
            $mod['Debe_Niif'] = '0';
            $mod['Haber_Niif'] = $modelo['Valor_CtaPorPagar'];
            $mod['Nit'] = $modelo['Nit_CtaPorPagar'];
            $mod['Documento'] = $modelo['Documento_CtaPorPagar'];
            $mod['Detalles'] = $modelo['Detalles'];
        }

        return $mod;
    }

    function GetTipoActivoFijo($id)
    {
        $query = "SELECT Id_Plan_Cuenta_PCGA FROM Tipo_Activo_Fijo WHERE Id_Tipo_Activo_Fijo=$id";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $id_tipo = $oCon->getData();
        unset($oCon);

        return $id_tipo['Id_Plan_Cuenta_PCGA'];
    }

    function ObtenerTipoDepreciacion($base)
    {
        $tipo = '0';
        $query = "SELECT 	Valor_Unidad_Tributaria FROM Configuracion WHERE Id_Configuracion=1";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $uvt = $oCon->getData();
        unset($oCon);

        $valor = $uvt['Valor_Unidad_Tributaria'] * 50;

        if ($base < $valor) {
            $tipo = '1';
        }
        return $tipo;
    }

    function EliminarMovimientosContable($id)
    {
        $query = "DELETE FROM Movimiento_Contable WHERE Id_Modulo=27 AND Id_Registro_Modulo=$id AND Detalles NOT LIKE 'Adicion%'";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->deleteData();
        unset($oCon);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ActivoFijo  $activoFijo
     * @return \Illuminate\Http\Response
     */
    public function show(ActivoFijo $activoFijo)
    {
        $http_response = new HttpResponse();

        $id_activo = (isset($_REQUEST['id_activo']) ? $_REQUEST['id_activo'] : '');

        $query = 'SELECT
		AF.*,
		(SELECT	T.Nombre_Tercero 	FROM
		(SELECT id AS Nit, 	CONCAT_WS(" ", first_name, first_surname) AS Nombre_Tercero,
		"Funcionario" as Tipo
		FROM people
		UNION
		SELECT
		Id_Cliente AS Nit,
		Nombre AS Nombre_Tercero,"Cliente" as Tipo
		FROM Cliente
		UNION
		SELECT
		Id_Proveedor AS Nit,
		IF((Primer_Nombre IS NULL OR Primer_Nombre = ""), Nombre, CONCAT_WS(" ", Primer_Nombre, Segundo_Nombre, Primer_Apellido, Segundo_Apellido)) AS Nombre_Tercero, "Proveedor" as Tipo
		FROM Proveedor) T
		WHERE NIT=AF.Nit LIMIT 1) as Proveedor,
		-- (SELECT Nombre FROM Plan_Cuentas WHERE Id_Plan_Cuentas=AF.Id_Cuenta_Contrapartida) as Contrapartida,
		(SELECT Nombre FROM Centro_Costo WHERE Id_Centro_Costo=AF.Id_Centro_Costo) as Centro_Costo,
		(SELECT Nombre_Tipo_Activo FROM Tipo_Activo_Fijo WHERE Id_Tipo_Activo_Fijo=AF.Id_Tipo_Activo_Fijo ) as Tipo_Activo,
		 IFNULL((SELECT Nombre FROM   Plan_Cuentas WHERE Id_Plan_Cuentas=AF.Id_Cuenta_Rete_Ica),"No Tiene Cuenta Asociada") as Cuenta_Rete_Iva,
		 IFNULL((SELECT Nombre FROM Plan_Cuentas WHERE Id_Plan_Cuentas=AF.Id_Cuenta_Rete_Fuente),"No Tiene Cuenta Asociada") as Cuenta_Rete_Ica,(SELECT CONCAT(first_name," ",first_surname ) FROM people WHERE id=AF.Identificacion_Funcionario) as Funcionario
        FROM Activo_Fijo AF
        WHERE
        AF.Id_Activo_Fijo =' . $id_activo;

        //Se crea la instancia que contiene la consulta a realizar
        $queryObj = new QueryBaseDatos($query);

        //Ejecuta la consulta de la instancia queryobj y retorna el resultado de la misma segun los parametros
        $activo = $queryObj->ExecuteQuery('simple');


        return response()->json($activo);
    }

    public function adiciones()
    {
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : false;
        $adiciones = [];

        if ($id) {
            $query = "SELECT Id_Adicion_Activo_Fijo, Id_Activo_Fijo, Fecha, Nombre, Concepto, Base, Iva, Costo_NIIF AS Costo FROM Adicion_Activo_Fijo WHERE Id_Activo_Fijo = $id";

            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $adiciones = $oCon->getData();
            unset($oCon);
        }

        return response()->json($adiciones);
    }

    public function getCodigo()
    {

        $mes = isset($_REQUEST['Fecha']) && $_REQUEST['Fecha'] != '' ? date('m', strtotime($_REQUEST['Fecha'])) : date('m');
        $anio = isset($_REQUEST['Fecha']) && $_REQUEST['Fecha'] != '' ? date('Y', strtotime($_REQUEST['Fecha'])) : date('Y');
        $dia = isset($_REQUEST['Fecha']) && $_REQUEST['Fecha'] != '' ? date('Y', strtotime($_REQUEST['Fecha'])) : date('d');
        $company = (isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : '1');

        $consecutivo = obtenerProximoConsecutivo::obtener('Activo', $company, $mes, $anio, $dia);

        return response()->json([
            "consecutivo" => $consecutivo
        ]);
    }

    public function cuentasRetenciones()
    {
        $match = (isset($_REQUEST['coincidencia']) ? $_REQUEST['coincidencia'] : '');

        $http_response = new HttpResponse();

        $query = '
		SELECT
			T.*
		FROM (SELECT
                P.Id_Plan_Cuentas AS Id,
                Codigo,
				CONCAT_WS(" ", P.Codigo," - ",P.Nombre) AS Nombre
			FROM Retencion R
			INNER JOIN  Plan_Cuentas P ON R.Id_Plan_Cuenta=P.Id_Plan_Cuentas
				) T';
        /* WHERE
            T.Codigo LIKE "%'.$match.'%" OR T.Nombre LIKE "%'.$match.'%"';  */


        $queryObj = new QueryBaseDatos($query);
        $matches = $queryObj->ExecuteQuery('Multiple');

        return response()->json($matches);
    }

    public function cuentas()
    {
        $match = (isset($_REQUEST['coincidencia']) ? $_REQUEST['coincidencia'] : '');

        $http_response = new HttpResponse();

        $query = '
		SELECT
			T.*
		FROM (SELECT
                Id_Plan_Cuentas AS Id,
                Codigo,
				CONCAT_WS(" ", Codigo," - ",Nombre) AS Nombre,
				Movimiento
			FROM Plan_Cuentas
				) T'
            /* WHERE
            (T.Codigo LIKE "%'.$match.'%" OR T.Nombre LIKE "%'.$match.'%") AND T.Movimiento = "S"' */;


        $queryObj = new QueryBaseDatos($query);
        $matches = $queryObj->ExecuteQuery('Multiple');

        return response()->json($matches);
    }

    public function filtrar()
    {
        $match = (isset($_REQUEST['coincidencia']) ? $_REQUEST['coincidencia'] : '');

        $http_response = new HttpResponse();

        $query = '
		SELECT
			T.*
		FROM (SELECT
                Id_Centro_Costo AS Id,
                Codigo,
				CONCAT(Codigo," - ",Nombre) AS Nombre,
				Movimiento,
				Estado
			FROM Centro_Costo
				) T
		WHERE
			(T.Codigo LIKE "%' . $match . '%" OR T.Nombre LIKE "%' . $match . '%") AND T.Movimiento = "Si" AND T.Estado = "Activo"';


        $queryObj = new QueryBaseDatos($query);
        $matches = $queryObj->ExecuteQuery('Multiple');

        return response()->json($matches);
    }

    public function listaFacturas()
    {
        $id = isset($_REQUEST['nit']) ? $_REQUEST['nit'] : false;
        $id_plan_cuenta = isset($_REQUEST['id_plan_cuenta']) ? $_REQUEST['id_plan_cuenta'] : false;
        $resultado['Facturas'] = funciones::listaCartera($id, $id_plan_cuenta); 
        return response()->json($resultado);
    }

    public function adicion()
    {
        $http_response = new HttpResponse();

        $id_activo = (isset($_REQUEST['id_activo']) ? $_REQUEST['id_activo'] : '');

        $query = '
		SELECT
			AF.Codigo, (SELECT CONCAT_WS(" ",Codigo," - ", Nombre)FROM Centro_Costo WHERE Id_Centro_Costo=AF.Id_Centro_Costo) as Centro_Costo, (SELECT Nombre_Tipo_Activo FROM Tipo_Activo_Fijo WHERE Id_Tipo_Activo_Fijo=AF.Id_Tipo_Activo_Fijo) as Tipo_Activo, AF.Id_Activo_Fijo,Id_Centro_Costo,Id_Tipo_Activo_Fijo
		FROM Activo_Fijo AF
		WHERE
			AF.Id_Activo_Fijo =' . $id_activo;

        //Se crea la instancia que contiene la consulta a realizar
        $queryObj = new QueryBaseDatos($query);

        //Ejecuta la consulta de la instancia queryobj y retorna el resultado de la misma segun los parametros
        $activo = $queryObj->ExecuteQuery('simple');



        return response()->json($activo);
    }

    public function pdf()
    {
        $id_registro = (isset($_REQUEST['id_registro']) ? $_REQUEST['id_registro'] : '');
        $id_funcionario_imprime = (isset($_REQUEST['id_funcionario_elabora']) ? $_REQUEST['id_funcionario_elabora'] : '');
        $tipo_valor = (isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : '');
        $company_id = (isset($_REQUEST['company']) ? $_REQUEST['company'] : '1');
        $id_modulo = 27;
        $adicion = isset($_REQUEST['activo']) && $_REQUEST['activo'] == 'Adicion' ? true : false;
        $id_adicion = isset($_REQUEST['id_adicion']) ? $_REQUEST['id_adicion'] : false;
        $titulo = $tipo_valor != '' ? "CONTABILIZACIÓN NIIF" : "CONTABILIZACIÓN PCGA";
        $queryObj = new QueryBaseDatos();
        /* DATOS GENERALES DE CABECERAS Y CONFIGURACION */
        $oItem = new complex('Configuracion', "Id_Configuracion", 1);
        $config = $oItem->getData();
        unset($oItem);
        /* FIN DATOS GENERALES DE CABECERAS Y CONFIGURACION */


        /* COMPANY */
        $oItem = new complex('companies', "id", $company_id);
        $company = $oItem->getData();
        unset($oItem);


        $query = "SELECT A.*, (
    CASE A.Tipo
    WHEN 'Cliente' THEN (SELECT IF(Nombre IS NULL OR Nombre = '', CONCAT_WS(' ', Primer_Nombre, Segundo_Nombre, Primer_Apellido, Segundo_Apellido), Nombre) FROM Cliente WHERE Id_Cliente = A.Nit)
    WHEN 'Proveedor' THEN (SELECT IF(Nombre IS NULL OR Nombre = '', CONCAT_WS(' ', Primer_Nombre, Segundo_Nombre, Primer_Apellido, Segundo_Apellido), Nombre) FROM Proveedor WHERE Id_Proveedor = A.Nit)
    WHEN 'Funcionario' THEN (SELECT CONCAT_WS(' ', first_name, first_surname) FROM people WHERE id = A.Nit)
    END) AS Tercero FROM Activo_Fijo A WHERE A.Id_Activo_Fijo = $id_registro";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $datos = $oCon->getData();
        unset($oCon);

        ob_start(); // Se Inicializa el gestor de PDF

        /* HOJA DE ESTILO PARA PDF*/
        $style = '<style>
.page-content{
width:750px;
}
.row{
display:inlinie-block;
width:750px;
}
.td-header{
    font-size:15px;
    line-height: 20px;
}
.titular{
    font-size: 11px;
    text-transform: uppercase;
    margin-bottom: 0;
  }
</style>';
        /* FIN HOJA DE ESTILO PARA PDF*/

        /* HAGO UN SWITCH PARA TODOS LOS MODULOS QUE PUEDEN GENERAR PDF */

        $condicionAdicion = '';

        if ($adicion) {
            $oItem = new complex('Adicion_Activo_Fijo', 'Id_Adicion_Activo_Fijo', $id_adicion);
            $dataAdicion = $oItem->getData();
            unset($oItem);
            $condicionAdicion = " AND MC.Detalles LIKE 'Adicion%' AND DATE(MC.Fecha_Movimiento) = DATE('$dataAdicion[Fecha]')";
        }

        $query = '
        SELECT
            PC.Codigo,
            PC.Nombre,
            PC.Codigo_Niif,
            PC.Nombre_Niif,
            MC.Nit,
            MC.Fecha_Movimiento AS Fecha,
            MC.Tipo_Nit,
            MC.Id_Registro_Modulo,
            MC.Documento,
            MC.Numero_Comprobante,
            MC.Debe,
            MC.Detalles,
            MC.Haber,
            MC.Debe_Niif,
            MC.Haber_Niif,
            (CASE
                WHEN MC.Tipo_Nit = "Cliente" THEN (SELECT Nombre FROM Cliente WHERE Id_Cliente = MC.Nit)
                WHEN MC.Tipo_Nit = "Proveedor" THEN (SELECT Nombre FROM Proveedor WHERE Id_Proveedor = MC.Nit)
                WHEN MC.Tipo_Nit = "Funcionario" THEN (SELECT CONCAT_WS(" ", first_name, first_surname) FROM people WHERE id = MC.Nit)
            END) AS Nombre_Cliente,
            "Factura Venta" AS Registro
        FROM Movimiento_Contable MC
        INNER JOIN Plan_Cuentas PC ON MC.Id_Plan_Cuenta = PC.Id_Plan_Cuentas
        WHERE
            MC.Estado = "Activo" AND Id_Modulo = ' . $id_modulo . ' AND Id_registro_Modulo =' . $id_registro . ' ' . $condicionAdicion . ' ORDER BY Debe DESC';

        $queryObj->SetQuery($query);
        $movimientos = $queryObj->ExecuteQuery('multiple');


        $query = '
        SELECT
            SUM(MC.Debe) AS Debe,
            SUM(MC.Haber) AS Haber,
            SUM(MC.Debe_Niif) AS Debe_Niif,
            SUM(MC.Haber_Niif) AS Haber_Niif
        FROM Movimiento_Contable MC
        WHERE
          MC.Estado = "Activo" AND Id_Modulo = ' . $id_modulo . ' AND Id_registro_Modulo =' . $id_registro . ' ' . $condicionAdicion;

        $queryObj->SetQuery($query);
        $movimientos_suma = $queryObj->ExecuteQuery('simple');

        $query = '
        SELECT
            CONCAT_WS(" ", first_name, first_surname) AS Nombre_Funcionario
        FROM people
        WHERE
            id =' . $id_funcionario_imprime;

        $queryObj->SetQuery($query);
        $imprime = $queryObj->ExecuteQuery('simple');

        $query = '
        SELECT
            CONCAT_WS(" ", first_name, first_surname) AS Nombre_Funcionario
        FROM people
        WHERE
            id =' . $datos['Identificacion_Funcionario'];

        $queryObj->SetQuery($query);
        $elabora = $queryObj->ExecuteQuery('simple');

        unset($queryObj);
        $codigos = '
            <h4 style="margin:5px 0 0 0;font-size:19px;line-height:22px;">' . $titulo . '</h4>
            <h4 style="margin:5px 0 0 0;font-size:19px;line-height:22px;">Activo Fijo</h4>
            <h4 style="margin:5px 0 0 0;font-size:19px;line-height:22px;">' . count($movimientos) > 0 ? $movimientos[0]['Numero_Comprobante'] : '' . '</h4>
            <h5 style="margin:5px 0 0 0;font-size:16px;line-height:16px;">Fecha ' . /* $this->fecha($movimientos[0]['Fecha']) . */'</h5>
        ';

        $contenido = '<table style="background: #e6e6e6;margin-top:20px">
            <tr style=" min-height: 100px;
            background: #e6e6e6;
            padding: 15px;
            border-radius: 10px;
            margin: 0;">

                <td style="font-size:14px;font-weight:bold;width:100px;padding:5px">
                Activo Fijo:
                </td>

                <td style="width:610px;padding:5px">
                ' . $datos['Nombre'] . '
                </td>

            </tr>
            <tr style=" min-height: 100px;
            background: #e6e6e6;
            padding: 15px;
            border-radius: 10px;
            margin: 0;">

                <td style="font-size:14px;font-weight:bold;width:100px;padding:5px">
                Beneficiario:
                </td>

                <td style="width:610px;padding:5px">
                ' . $datos['Tercero'] . '
                </td>

            </tr>

            <tr style=" min-height: 100px;
            background: #e6e6e6;
            padding: 15px;
            border-radius: 10px;
            margin: 0;">

                <td style="font-size:14px;font-weight:bold;width:100px;padding:5px">
                Documento:
                </td>

                <td style="width:610px;padding:5px">
                ' . $datos['Nit'] . '
                </td>

            </tr>

            <tr style=" min-height: 100px;
            background: #e6e6e6;
            padding: 15px;
            border-radius: 10px;
            margin: 0;">

                <td style="font-size:14px;font-weight:bold;width:100px;padding:5px">
                Concepto:
                </td>

                <td style="width:610px;padding:5px">
                ' . $datos['Concepto'] . '
                </td>

            </tr>
        </table>
        ';


        $contenido .= '<table style="font-size:10px;margin-top:10px;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:78px;max-width:78pxfont-weight:bold;text-align:center;background:#cecece;;border:1px solid #cccccc;">
                Cuenta ' . $tipo_valor . '
            </td>
            <td style="width:170px;max-width:170pxfont-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
               Nombre Cuenta ' . $tipo_valor . '
            </td>
            <td style="width:115px;max-width:115pxfont-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
               Documento
            </td>
            <td style="width:115px;max-width:115pxfont-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                Nit
            </td>
            <td style="width:115px;max-width:115pxfont-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                Debitos ' . $tipo_valor . '
            </td>
            <td style="width:115px;max-width:115pxfont-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                Crédito ' . $tipo_valor . '
            </td>
        </tr>';

        if (count($movimientos) > 0) {

            foreach ($movimientos as $value) {

                if ($tipo_valor != '') {
                    $codigo = $value['Codigo_Niif'];
                    $nombre_cuenta = $value['Nombre_Niif'];
                    $debe = $value['Debe_Niif'];
                    $haber = $value['Haber_Niif'];
                    $total_debe = $movimientos_suma["Debe_Niif"];
                    $total_haber = $movimientos_suma["Haber_Niif"];
                } else {
                    $codigo = $value['Codigo'];
                    $nombre_cuenta = $value['Nombre'];
                    $debe = $value['Debe'];
                    $haber = $value['Haber'];
                    $total_debe = $movimientos_suma["Debe"];
                    $total_haber = $movimientos_suma["Haber"];
                }

                $contenido .= '
                <tr>
                    <td style="width:78px;padding:4px;text-align:left;border:1px solid #cccccc;">
                        ' . $codigo . '
                    </td>
                    <td style="width:150px;padding:4px;text-align:left;border:1px solid #cccccc;">
                        ' . $nombre_cuenta . '
                    </td>
                    <td style="width:100px;padding:4px;text-align:right;border:1px solid #cccccc;">
                        ' . $value["Documento"] . '
                    </td>
                    <td style="width:100px;padding:4px;text-align:right;border:1px solid #cccccc;">
                       ' . $value['Nombre_Cliente'] . ' - ' . $value["Nit"] . '
                    </td>
                    <td style="width:100px;padding:4px;text-align:right;border:1px solid #cccccc;">
                        $ ' . number_format($debe, 2, ".", ",") . '
                    </td>
                    <td style="width:100px;padding:4px;text-align:right;border:1px solid #cccccc;">
                        $ ' . number_format($haber, 2, ".", ",") . '
                    </td>
                </tr>
            ';
            }

            $contenido .= '
            <tr>
                <td colspan="4" style="padding:4px;text-align:center;border:1px solid #cccccc;">
                    TOTAL
                </td>
                <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                    $ ' . number_format($total_debe, 2, ".", ",") . '
                </td>
                <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                    $ ' . number_format($total_haber, 2, ".", ",") . '
                </td>
            </tr>';
        }

        $contenido .= '</table>

    <table style="margin-top:10px;" cellpadding="0" cellspacing="0">

        <tr>
            <td style="font-weight:bold;width:170px;border:1px solid #cccccc;padding:4px">
                Elaboró:
            </td>
            <td style="font-weight:bold;width:168px;border:1px solid #cccccc;padding:4px">
                Imprimió:
            </td>
            <td style="font-weight:bold;width:168px;border:1px solid #cccccc;padding:4px">
                Revisó:
            </td>
            <td style="font-weight:bold;width:168px;border:1px solid #cccccc;padding:4px">
                Aprobó:
            </td>
        </tr>

        <tr>
            <td style="font-size:10px;width:170px;border:1px solid #cccccc;padding:4px">
            ' . $elabora['Nombre_Funcionario'] . '
            </td>
            <td style="font-size:10px;width:168px;border:1px solid #cccccc;padding:4px">
            ' . $imprime['Nombre_Funcionario'] . '

            </td>
            <td style="font-size:10px;width:168px;border:1px solid #cccccc;padding:4px">

            </td>
            <td style="font-size:10px;width:168px;border:1px solid #cccccc;padding:4px">

            </td>
        </tr>

    </table>

    ';



        /* CABECERA GENERAL DE TODOS LOS ARCHIVOS PDF*/
        //<img src="'.$_SERVER["DOCUMENT_ROOT"].substr($company["logo"], strpos($company["logo"], "image?path=")+11).'" style="width:60px;" alt="" />
        $cabecera = '<table style="" >
              <tbody>
                <tr>
                  <td style="width:70px;">
                    LOGO EMPRESA
                  </td>
                  <td class="td-header" style="width:410px;font-weight:thin;font-size:14px;line-height:20px;">
                    ' . $company["name"] . '<br>
                    N.I.T.: ' . number_format($company["document_number"], 0, ",", ".") . "-" . $company["verification_digit"] . '<br>
                  </td>
                  <td style="width:250px;text-align:right">
                        ' . $codigos . '
                  </td>
                </tr>
              </tbody>
            </table><hr style="border:1px dotted #ccc;width:730px;">';


        /* FIN CABECERA GENERAL DE TODOS LOS ARCHIVOS PDF*/

        $marca_agua = '';

        if (count($movimientos) > 0 && $movimientos[0]['Estado'] == 'Anulado') {
            $marca_agua = 'backimg="' . $_SERVER["DOCUMENT_ROOT"] . 'assets/images/anulada.png"';
        }

        /* CONTENIDO GENERAL DEL ARCHIVO MEZCLANDO TODA LA INFORMACION*/
        $content = '<page backtop="0mm" backbottom="0mm" ' . $marca_agua . '>
                <div class="page-content" >' .
            $cabecera .
            $contenido .
            '
                </div>
            </page>';
        /* FIN CONTENIDO GENERAL DEL ARCHIVO MEZCLANDO TODA LA INFORMACION*/

        // var_dump($content);
        // exit;
        return $content;
    }

    function fecha($str)
    {
        $parts = explode(" ", $str);
        $date = explode("-", $parts[0]);
        return $date[2] . "/" . $date[1] . "/" . $date[0];
    }

    public function guardarAdicion()
    {
        $contabilizar = new Contabilizar();

        $http_response = new HttpResponse();
        $response = array();
        $modelo = (isset($_REQUEST['modelo']) ? $_REQUEST['modelo'] : '');
        $modelo = json_decode($modelo, true);
        $ctas_anticipo = (isset($_REQUEST['ctas_anticipo']) ? $_REQUEST['ctas_anticipo'] : '');
        $ctas_anticipo = json_decode($ctas_anticipo, true);

        $otros = ($modelo['Base'] + $modelo['Iva']) - ($modelo['Costo_Rete_Fuente'] + $modelo['Costo_Rete_Ica']);
        $modelo['Costo_NIIF'] = number_format($modelo['Costo_NIIF'], 2, ".", "");
        $modelo['Costo_PCGA'] = number_format($modelo['Costo_PCGA'], 2, ".", "");
        $modelo['Iva'] = number_format($modelo['Iva'], 2, ".", "");
        $modelo['Base'] = number_format($modelo['Base'], 2, ".", "");
        $modelo['Otros'] = number_format($otros, 2, '.', '');

        $modelo['Ctas_Anticipo'] = $ctas_anticipo;
        $modelo['Tipo_Depreciacion'] = $this->ObtenerTipoDepreciacion($modelo['Costo_Rete_Fuente']);

        $oItem = new complex("Adicion_Activo_Fijo", "Id_Adicion_Activo_Fijo");
        foreach ($modelo as $index => $value) {
            if ($value != '' && $value != null) {
                $oItem->$index = $value;
            }
        }
        $oItem->save();
        $id_adicion = $oItem->getId();
        $id_activo = $modelo['Id_Activo_Fijo'];
        unset($oItem);

        $modelo['Codigo'] = $this->getCodigoActivo($modelo['Id_Activo_Fijo']);

        $movimiento['Datos'] = $this->ArmarConceptoContabilizzacion($modelo);
        $movimiento['Datos_Anticipos'] = $this->crearMovimientosCtasAnticipo($modelo, $id_adicion);
        $movimiento['Id_Registro'] = $id_adicion;
        $movimiento['Nit'] = $modelo['Nit'];
        $movimiento['Fecha'] = $modelo['Fecha'];
        $contabilizar->CrearMovimientoContable('Activo Fijo', $movimiento);




        $http_response->SetRespuesta(0, 'Actualizacion Exitosa', 'Se ha realizado una adición al activo fijo exitosamente!');
        $response = $http_response->GetRespuesta();
        $response['Id'] = $id_activo;
        $response['Id_Adicion'] = $id_adicion;
        return response()->json($response);
    }


    function getCodigoActivo($id)
    {
        $oItem = new complex('Activo_Fijo', 'Id_Activo_Fijo', $id);
        $codigo = $oItem->getData()['Codigo'];
        unset($oItem);

        return $codigo;
    }


    public function anularDocumento()
    {
        $datos = isset($_REQUEST['datos']) ? $_REQUEST['datos'] : false;

        if ($datos) {
            $datos = json_decode($datos, true);

            $resultado = funciones::anularDocumento($datos); 
        }

        return response()->json($resultado);
    }

    public function reportes()
    {
        $tipo_reporte = $_REQUEST['Tipo_Reporte'];
        $resultados = $this->getResultReporte($tipo_reporte);
        $contenido = 'SIN RESULTADOS PARA MOSTRAR';
        if ($resultados) {
            $encabezado = $resultados[0];
            $contenido = '<table border="1" style="border-collapse:collapse">';
            $contenido .= '<tr>';
            foreach ($encabezado as $columna => $value) {
                $contenido .= '<th>' . $columna . '</th>';
            }
            $contenido .= '</tr>';
            foreach ($resultados as $i => $value) {
                $contenido .= '<tr>';
                foreach ($value as $columna => $valor) {
                    if ($this->isFieldNumber($columna)) {
                        $contenido .= '<td>' . number_format($valor, 2, ",", "") . '</td>';
                    } else {
                        if ($columna = 'Codigo') {
                            /* $valor =  utf8_encode($valor); */
                            /*  $valor .='/'; */
                        }
                        $contenido .= '<td>' . utf8_encode($valor) . '</td>';
                    }
                }
                $contenido .= '</tr>';
            }
            $contenido .= '</table>';
        }
        return $contenido;
    }

    function getResultReporte($tipo_reporte)
    {
        $resultado = [];
        $query = '';
        $condiciones = $this->strCondiciones();
        switch ($tipo_reporte) {
            case 'Compras':
                $query = "SELECT AF.Codigo_Activo_Fijo AS Codigo, AF.Nombre AS Activo, Referencia, C.Codigo AS Cod_Centro_Costo, C.Nombre AS Nom_Centro_Costo, DATE(AF.Fecha) AS Fecha_Compra, Base AS Costo, Iva, Costo_PCGA AS Total  FROM Activo_Fijo AF LEFT JOIN Centro_Costo C ON C.Id_Centro_Costo = AF.Id_Centro_Costo WHERE AF.Estado != 'Anulada' $condiciones ORDER BY AF.Fecha";
                break;
            case 'Movimientos':
                list($fecha_inicio, $fecha_fin) = (isset($_REQUEST['Fechas'])) && $_REQUEST['Fechas'] != '' ? explode(' - ', $_REQUEST['Fechas']) : [];
                $query = "SELECT AF.Codigo_Activo_Fijo AS Codigo, AF.Nombre AS Activo, Referencia, C.Codigo AS Cod_Centro_Costo, C.Nombre AS Nom_Centro_Costo, '$fecha_inicio' AS Mov_Desde, '$fecha_fin' AS Mov_Hasta, Base AS Costo, Iva, IFNULL(AAF.Adicion,0) AS Adicion, IFNULL(D.Depreciado, 0) AS Depreciacion, 0 AS Baja, (Costo_PCGA+IFNULL(AAF.Adicion,0)-IFNULL(D.Depreciado, 0)) AS Neto_Movimiento  FROM Activo_Fijo AF LEFT JOIN Centro_Costo C ON C.Id_Centro_Costo = AF.Id_Centro_Costo LEFT JOIN (SELECT AFD.Id_Activo_Fijo, SUM(AFD.Valor_PCGA) AS Depreciado FROM Activo_Fijo_Depreciacion AFD INNER JOIN Depreciacion DP ON AFD.Id_Depreciacion = DP.Id_Depreciacion WHERE DP.Estado = 'Activo' GROUP BY AFD.Id_Activo_Fijo) D ON D.Id_Activo_Fijo = AF.Id_Activo_Fijo LEFT JOIN (SELECT Id_Activo_Fijo, Costo_PCGA AS Adicion FROM Adicion_Activo_Fijo) AAF ON AAF.Id_Activo_Fijo = AF.Id_Activo_Fijo WHERE AF.Estado != 'Anulada' $condiciones ORDER BY AF.Fecha";
                break;
            case 'Relacion':
                $query = "SELECT AF.Codigo_Activo_Fijo AS Codigo, AF.Nombre AS Activo, Referencia, C.Codigo AS Cod_Centro_Costo,
                C.Nombre AS Nom_Centro_Costo, '$_REQUEST[Fechas]' AS Fecha_Corte, Base AS Costo, Iva, Costo_PCGA AS Total,
                IFNULL(D.Depreciado, 0) AS Depreciacion, (Costo_PCGA-IFNULL(D.Depreciado, 0)) AS Neto_Activo  FROM Activo_Fijo AF
                LEFT JOIN Centro_Costo C ON C.Id_Centro_Costo = AF.Id_Centro_Costo LEFT JOIN (SELECT AFD.Id_Activo_Fijo, SUM(AFD.Valor_PCGA) AS Depreciado FROM Activo_Fijo_Depreciacion AFD INNER JOIN Depreciacion DP ON AFD.Id_Depreciacion = DP.Id_Depreciacion WHERE DP.Estado = 'Activo' GROUP BY AFD.Id_Activo_Fijo) D ON D.Id_Activo_Fijo = AF.Id_Activo_Fijo WHERE AF.Estado != 'Anulada' $condiciones ORDER BY AF.Fecha";
                break;
            case 'Adiciones':
                $query = "SELECT AF.Codigo_Activo_Fijo AS Codigo, AF.Nombre AS Activo,
                         C.Codigo AS Cod_Centro_Costo, C.Nombre AS Nom_Centro_Costo,
                        AD.Codigo AS Cod_Adicion, AD.Fecha, AD.Nombre AS Nombre_Adicion, AD.Concepto, AD.Base, AD.Iva,
                        AD.Costo_NIIF AS Costo
                    FROM Activo_Fijo AF
                    LEFT JOIN Centro_Costo C ON C.Id_Centro_Costo = AF.Id_Centro_Costo
                    INNER JOIN Adicion_Activo_Fijo AD ON AD.Id_Activo_Fijo = AF.Id_Activo_Fijo
                WHERE AF.Estado != 'Anulada'  $condiciones ORDER BY AF.Fecha";
                break;
        }
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $resultado = $oCon->getData();
        unset($oCon);
        return $resultado;
    }

    function strCondiciones()
    {
        $condicion = '';
        global $tipo_reporte;
        if (isset($_REQUEST['Fechas']) && $_REQUEST['Fechas'] != '') {
            list($fecha_inicio, $fecha_fin) = explode(' - ', $_REQUEST['Fechas']);
            if ($tipo_reporte == 'Adiciones') {
                $condicion .= ' AND  DATE(AD.Fecha)  BETWEEN "' . $fecha_inicio . '" AND "' . $fecha_fin . '" ';
            } else {
                $condicion .= $tipo_reporte == 'Relacion' ? " AND DATE(AF.Fecha) <= '$_REQUEST[Fechas]'" : " AND DATE(AF.Fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
            }
        }
        if (isset($_REQUEST['Tipo_Activo']) && $_REQUEST['Tipo_Activo'] != '') {
            $condicion .= " AND AF.Id_Tipo_Activo_Fijo = $_REQUEST[Tipo_Activo]";
        }
        if (isset($_REQUEST['Centro_Costo']) && $_REQUEST['Centro_Costo'] != '') {
            $condicion .= " AND AF.Id_Centro_Costo = $_REQUEST[Centro_Costo]";
        }
        return $condicion;
    }

    function isFieldNumber($field)
    {
        $fields = ["Total", "Iva", "Costo", "Depreciacion", "Neto_Activo", "Adicion", "Baja", "Neto_Movimiento"];
        return in_array($field, $fields);
    }
}
