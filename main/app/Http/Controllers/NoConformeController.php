<?php

namespace App\Http\Controllers;

use App\Http\Services\complex;
use App\Http\Services\Configuracion;
use App\Http\Services\consulta;
use App\Http\Services\Contabilizar;
use App\Http\Services\lista;
use App\Http\Services\PaginacionData;
use App\Http\Services\QueryBaseDatos;
use App\Models\DevolucionCompra;
use App\Models\NoConforme;
use App\Models\Person;
use App\Traits\ApiResponser;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoConformeController extends Controller
{
    use ApiResponser;
    public function cargarFacturasActasRecepcion()
    {
        $id_acta = isset($_REQUEST['id_acta']) ? $_REQUEST['id_acta'] : false;
        $facturas = [];

        if ($id_acta) {
            $query = "SELECT Factura FROM Factura_Acta_Recepcion WHERE Id_Acta_Recepcion = $id_acta";

            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $facturas = $oCon->getData();
            unset($oCon);
        }

        return response()->json($facturas);
    }

    public function listaProductos()
    {
        $id_bodega = (isset($_REQUEST['id_bodega_nuevo']) ? $_REQUEST['id_bodega_nuevo'] : '');
        $id_acta_recepcion = (isset($_REQUEST['id_acta']) ? $_REQUEST['id_acta'] : '');
        $condicion = '';
        if (isset($_REQUEST['nom']) && $_REQUEST['nom'] != '') {
            $condicion .= ' AND (P.Principio_Activo LIKE "%' . $_REQUEST['nom'] . '%" OR P.Presentacion LIKE "%' . $_REQUEST['nom'] . '%" OR P.Concentracion LIKE "%' . $_REQUEST['nom'] . '%" OR P.Nombre_Comercial LIKE "%' . $_REQUEST['nom'] . '%" OR P.Cantidad LIKE "%' . $_REQUEST['nom'] . '%" OR P.Unidad_Medida LIKE "%' . $_REQUEST['nom'] . '%")';
        }
        if (isset($_REQUEST['lab_com']) && $_REQUEST['lab_com']) {
            $condicion .= " AND P.Laboratorio_Comercial LIKE '%$_REQUEST[lab_com]%'";
        }
        if (isset($_REQUEST['lab_gen']) && $_REQUEST['lab_gen']) {
            $condicion .= " AND P.Laboratorio_Generico LIKE '%$_REQUEST[lab_gen]%'";
        }
        if (isset($_REQUEST['cum']) && $_REQUEST['cum']) {
            $condicion .= " AND P.Codigo_Cum LIKE '%$_REQUEST[cum]%'";
        }
        $query =
            'SELECT P.Nombre_Comercial,
        IF(CONCAT( P.Nombre_Comercial," ",P.Cantidad, " ",P.Unidad_Medida, " (",P.Principio_Activo, " ",
                P.Presentacion, " ",
                P.Concentracion, ") " )="" OR CONCAT( P.Nombre_Comercial," ", P.Cantidad," ",
                P.Unidad_Medida ," (",P.Principio_Activo, " ",
                P.Presentacion, " ",
                P.Concentracion, ") "
            ) IS NULL, CONCAT(P.Nombre_Comercial), CONCAT( P.Nombre_Comercial," ", P.Cantidad," ",
                P.Unidad_Medida, " (",P.Principio_Activo, " ",
                P.Presentacion, " ",
                P.Concentracion,") " )) as Nombre,
                P.Nombre_Comercial, P.Laboratorio_Comercial, P.Id_Producto,
                P.Embalaje, P.Cantidad_Presentacion,
                IFNULL(P.Laboratorio_Generico, "No aplica") AS Laboratorio_Generico,
                P.Gravado, P.Codigo_Cum, P.Imagen, I.Lote, I.Fecha_Vencimiento, I.Id_Inventario_Nuevo,
                (I.Cantidad-I.Cantidad_Seleccionada-I.Cantidad_Apartada) as Cantidad_Inventario,
                IFNULL(PR.Precio,0) AS Costo,
                E.Nombre AS Nombre_Estiba,
                (SELECT G.Nombre From Grupo_Estiba G WHERE G.Id_Grupo_Estiba =  E.Id_Grupo_Estiba) AS Grupo_Estiba
                FROM Inventario_Nuevo I
                INNER JOIN Producto P ON P.Id_Producto=I.Id_Producto
                INNER JOIN Estiba E ON E.Id_Estiba = I.Id_Estiba
                INNER JOIN Producto_Acta_Recepcion PR ON P.Id_Producto=PR.Id_Producto and I.Lote = PR.Lote
                WHERE P.Codigo_Barras IS NOT NULL AND P.Codigo_Barras !=""';
        if (!empty($id_bodega)) {
            $query .= ' AND E.Id_Bodega_Nuevo=' . $id_bodega;
        }
        $query .= $condicion;
        if (!empty($id_acta_recepcion)) {
            $query .= ' AND PR.Id_Acta_Recepcion=' . $id_acta_recepcion;
        }
        $query .= ' AND (I.Cantidad - (I.Cantidad_Seleccionada + I.Cantidad_Apartada)) > 0 ORDER BY I.Id_Producto, I.Lote';
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $resultados = $oCon->getData();
        unset($oCon);
        foreach ($resultados as &$resultado) {
            $resultado->Producto = clone $resultado;
        }
        unset($resultado);
        return response()->json($resultados);
    }

    public function devoluciones(Request $request)
    {
        $condicion = [];

        if ($request->has('cod') && $request->input('cod') != "") {
            $condicion[] = ["Codigo", "like", '%' . $request->input('cod') . '%'];
        }
        if ($request->has('prov') && $request->input('prov') != "") {
            $condicion[] = ["social_reason", "like", '%' . $request->input('prov') . '%'];
        }
        if ($request->has('fecha') && $request->input('fecha') != "") {
            $fechas = explode(' - ', $request->input('fecha'));
            $fecha_inicio = trim($fechas[0]);
            $fecha_fin = trim($fechas[1]);
            $condicion[] = ["Fecha", "between", [$fecha_inicio, $fecha_fin]];
        }
        if ($request->has('estado') && $request->input('estado') != "") {
            $condicion[] = ["Devolucion_Compra.Estado", "=", $request->input('estado')];
        }

        $datos = DevolucionCompra::query()
            ->join('people', 'Devolucion_Compra.Identificacion_Funcionario', '=', 'people.identifier')
            ->join('third_parties', 'Devolucion_Compra.Id_Proveedor', '=', 'third_parties.id')
            ->where($condicion)
            ->select('Devolucion_Compra.*', 'people.image', 'third_parties.social_reason', 'third_parties.first_name', 'third_parties.first_surname')
            ->orderByDesc('Devolucion_Compra.Codigo')
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));

        $datos->getCollection()->transform(function ($item) {
            $item->Nombre = $item->social_reason ?: $item->first_name . ' ' . $item->first_surname;
            return $item;
        });

        return $this->success($datos);
    }

    public function vistaPrincpal()
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $oLista = new lista('No_Conforme');
        $oLista->setRestrict("Id_No_Conforme", "=", $id);
        $lista = $oLista->getlist();
        unset($oLista);
        $resultado['respuesta'] = $lista[0]['Estado'];
        return response()->json($resultado);
    }

    public function verNoConforme()
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $query = 'SELECT   B.Id_Bodega_Nuevo, B.Nombre AS Nombre_Bodega,AR.Id_Proveedor,NC.Id_No_Conforme, IFNULL(P.social_reason, CONCAT_WS(" ", P.first_name, P.first_surname)) as Proveedor, NC.Id_Acta_Recepcion_Compra, OCN.Codigo as Compra, AR.Codigo AS Acta
            FROM No_Conforme NC
            INNER JOIN Acta_Recepcion AR
            ON NC.Id_Acta_Recepcion_Compra=AR.Id_Acta_Recepcion
            LEFT JOIN third_parties P
            ON AR.Id_Proveedor = P.id AND P.is_supplier = 1
            INNER JOIN Orden_Compra_Nacional OCN ON AR.Id_Orden_Compra_Nacional=OCN.Id_Orden_Compra_Nacional
            INNER JOIN Bodega_Nuevo B ON AR.Id_Bodega_Nuevo=B.Id_Bodega_Nuevo
            WHERE NC.Id_No_Conforme= ' . $id;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $informacion = $oCon->getData();
        unset($oCon);
        $query = "SELECT F.Factura, F.Fecha_Factura FROM Factura_Acta_Recepcion F WHERE F.Id_Acta_Recepcion=" . $informacion['Id_Acta_Recepcion_Compra'];
        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $facturas = $oCon->getData();
        unset($oCon);
        $query = 'SELECT PRD.Nombre_Comercial, PRD.Nombre_General as Nombre_Producto, PNC.Observaciones AS Motivo, PRD.Id_Producto
            FROM Producto_No_Conforme PNC
            INNER JOIN Producto PRD  ON PNC.Id_Producto=PRD.Id_Producto
            WHERE PNC.Id_No_Conforme=' . $id;
        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $productos = $oCon->getData();
        unset($oCon);
        $resultado['encabezado'] = $informacion;
        $resultado['Productos'] = $productos;
        $resultado['Facturas'] = $facturas;
        return response()->json($resultado);
    }

    public function devolucionProductoNoConforme()
    {
        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');
        $productos = (isset($_REQUEST['productos']) ? $_REQUEST['productos'] : '');
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $Id_No_Conforme = (isset($_REQUEST['Id_No_Conforme']) ? $_REQUEST['Id_No_Conforme'] : '');
        $Identificacion_Funcionario = (isset($_REQUEST['Identificacion_Funcionario']) ? $_REQUEST['Identificacion_Funcionario'] : '');
        $Id_Proveedor = (isset($_REQUEST['Id_Proveedor']) ? $_REQUEST['Id_Proveedor'] : '');
        $Id_Bodega_Nuevo = (isset($_REQUEST['Id_Bodega_Nuevo']) ? $_REQUEST['Id_Bodega_Nuevo'] : '');


        $datos = (array) json_decode($datos);

        $datos['Id_No_Conforme'] = $Id_No_Conforme;
        $datos['Identificacion_Funcionario'] = $Identificacion_Funcionario;
        $datos['Id_Proveedor'] = $Id_Proveedor;
        $datos['Id_Bodega_Nuevo'] = $Id_Bodega_Nuevo;
        $productos = (array) json_decode($productos, true);
        // $cod = $configuracion->getConsecutivo('Devolucion_Compra', 'Devolucion_Compras');
        $cod = generateConsecutive('Devolucion_Compra');
        $datos['Codigo'] = $cod;
        $oItem = new complex('Devolucion_Compra', 'Id_Devolucion_Compra');
        foreach ($datos as $index => $value) {
            $oItem->$index = $value;
        }
        $oItem->save();
        sumConsecutive('Devolucion_Compra');
        $idDevolucionCompra = $oItem->getId();
        unset($oItem);
        $qr = generarqr('devolucioncompra', $idDevolucionCompra, '/IMAGENES/QR/');
        $oItem = new complex("Devolucion_Compra", "Id_Devolucion_Compra", $idDevolucionCompra);
        $oItem->Codigo_Qr = $qr;
        $oItem->save();
        unset($oItem);
        foreach ($productos as $producto) {
            $oItem = new complex('Producto_Devolucion_Compra', "Id_Producto_Devolucion_Compra");
            $oItem->Id_Devolucion_Compra = $idDevolucionCompra;
            $oItem->Id_Producto = $producto['Id_Producto'];
            $oItem->Cantidad = $producto['Cantidad'] ?? 1;
            $oItem->Motivo = $producto['Motivo'];
            $oItem->save();
            unset($oItem);
        }
        if ($id != '') {
            $oItem = new complex('No_Conforme', 'Id_No_Conforme', $id);
            $oItem->Estado = 'Cerrado';
            $oItem->save();
            unset($oItem);
        }
        $resultado['mensaje'] = "Se ha generado la devolucion de compra con codigo: " . $datos['Codigo'];
        $resultado['tipo'] = "success";
        return response()->json($resultado);
    }

    public function actividadesDevolucion()
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $query = 'SELECT AR.*, F.image,CONCAT_WS(" ",F.first_name, F.first_surname) as Funcionario,
            (CASE
                WHEN AR.Estado="Creacion" THEN CONCAT("1 ",AR.Estado)
                WHEN AR.Estado="Fase 1" THEN CONCAT("2 ",AR.Estado)
                WHEN AR.Estado="Fase 2" THEN CONCAT("3 ",AR.Estado)
                WHEN AR.Estado="Enviada" THEN CONCAT("4 ",AR.Estado)
                WHEN AR.Estado="Anulada" THEN CONCAT("5 ",AR.Estado)
            END) as Estado2,
            (CASE
                WHEN AR.Estado="Anulada" THEN CONCAT("Anulada - 5" , AR.Detalles)
                WHEN AR.Estado!="Anulda" THEN AR.Detalles
            END ) as Detalles
            FROM Actividad_Devolucion_Compra AR
            INNER JOIN people F
            On AR.Identificacion_Funcionario=F.identifier
            INNER JOIN Devolucion_Compra R ON AR.Id_Devolucion_Compra=R.Id_Devolucion_Compra
            WHERE AR.Id_Devolucion_Compra=' . $id . '
            Order BY Estado2 ASC, Fecha ASC';
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $actividades = $oCon->getData();
        unset($oCon);
        return response()->json($actividades);
    }

    public function detalleDevolucion()
    {
        $id_devolucion = (isset($_REQUEST['id_devolucion']) ? $_REQUEST['id_devolucion'] : '');
        $queryObj = new QueryBaseDatos();
        $query_no_conforme = 'SELECT
        PRD.Nombre_General as Nombre_Producto,
        POCN.* , PRD.Nombre_Comercial   ,
        (SELECT CONCAT(G.Nombre, " - ", E.Nombre)
             FROM Estiba E
             INNER JOIN Grupo_Estiba G ON G.Id_Grupo_Estiba = E.Id_Grupo_Estiba
             WHERE E.Id_Estiba = I.Id_Estiba
        ) AS Ubicacion
        FROM Producto_Devolucion_Compra POCN
        INNER JOIN Producto PRD
        ON PRD.Id_Producto = POCN.Id_Producto
        LEFT JOIN Inventario_Nuevo I
        ON I.Id_Inventario_Nuevo = POCN.Id_Inventario_Nuevo
        WHERE POCN.Id_Devolucion_Compra =' . $id_devolucion;
        $query_no_conforme_encabezado = 'SELECT D.*, CONCAT_WS(" ", f.first_name, f.first_surname) AS Nombre_Funcionario,
        IFNULL(p.social_reason, CONCAT_WS(" ", p.first_name, p.first_surname)) as Proveedor, p.id,
        bn.Nombre AS Bodega
        FROM Devolucion_Compra D
        LEFT JOIN Bodega_Nuevo bn ON bn.Id_Bodega_Nuevo = D.Id_Bodega_Nuevo
        INNER JOIN third_parties p ON D.Id_Proveedor=p.id
        INNER JOIN people f ON D.Identificacion_Funcionario=f.identifier
        WHERE D.Id_Devolucion_Compra=' . $id_devolucion;
        $queryObj->setQuery($query_no_conforme);
        $productos_no_conforme = $queryObj->Consultar('Multiple', false);
        $queryObj->setQuery($query_no_conforme_encabezado);
        $productos_no_conforme_encabezado = $queryObj->ExecuteQuery('simple');
        $result['encabezado'] = $productos_no_conforme_encabezado;
        $result['no_conformes'] = $productos_no_conforme['query_result'];
        unset($queryObj);
        return response()->json($result, JSON_UNESCAPED_UNICODE);
    }


    public function listaNoConformeCompra()
    {
        $query = 'SELECT COUNT(*) AS Total FROM No_Conforme WHERE Tipo = "Compra" AND Estado="Pendiente"';

        $oCon = new consulta();

        $oCon->setQuery($query);
        $total = $oCon->getData();
        unset($oCon);

        $tamPag = 10;
        $numReg = $total["Total"];
        $paginas = ceil($numReg / $tamPag);
        $limit = "";
        $paginaAct = "";


        if (!isset($_REQUEST['pag']) || $_REQUEST['pag'] == '') {
            $paginaAct = 1;
            $limit = 0;
        } else {
            $paginaAct = $_REQUEST['pag'];
            $limit = ($paginaAct - 1) * $tamPag;
        }

        $query = "SELECT NC.*, F.image, AR.Codigo as Codigo_Compra, P.Nombre, AR.Tipo, OCN.Codigo AS Codigo_Orden
            FROM No_Conforme NC
            INNER JOIN people F
            ON NC.Persona_Reporta=F.identifier
            INNER JOIN Acta_Recepcion AR
            ON NC.Id_Acta_Recepcion_Compra=AR.Id_Acta_Recepcion
            INNER JOIN Orden_Compra_Nacional OCN
            ON AR.Id_Orden_Compra_Nacional=OCN.Id_Orden_Compra_Nacional
            INNER JOIN Proveedor P
            ON AR.Id_Proveedor=P.Id_Proveedor
            WHERE NC.Tipo = 'Compra' AND NC.Estado='Pendiente' ORDER BY NC.Codigo DESC LIMIT " . $limit . ',' . $tamPag;

        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $datos = $oCon->getData();
        unset($oCon);

        $resultado['numReg'] = $numReg;
        $resultado['devoluciones'] = $datos;

        return response()->json($resultado);
    }


    public function cargarActasRecepcion()
    {
        $id_proveedor = isset($_REQUEST['id_proveedor']) ? $_REQUEST['id_proveedor'] : false;
        $actas = [];

        if ($id_proveedor) {
            $query = "SELECT Id_Acta_Recepcion AS ID, Codigo AS Acta FROM Acta_Recepcion WHERE Id_Proveedor = $id_proveedor AND ( Estado = 'Acomodada' OR (Fecha_Creacion < '2020-07-22' AND Estado = 'Aprobada' ) ) ORDER BY 1 DESC LIMIT 500";

            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $actas = $oCon->getData();
            unset($oCon);
        }

        return response()->json($actas);
    }

    public function listaNoConformeCompraD(Request $request)
    {
        $datos = NoConforme::select('No_Conforme.*', 'F.image', 'AR.Codigo as Codigo_Compra', 'P.social_reason', 'P.first_name', 'P.first_surname', 'AR.Tipo', 'OCN.Codigo AS Codigo_Orden')
            ->join('people as F', 'No_Conforme.Persona_Reporta', '=', 'F.identifier')
            ->join('Acta_Recepcion as AR', 'No_Conforme.Id_Acta_Recepcion_Compra', '=', 'AR.Id_Acta_Recepcion')
            ->join('Orden_Compra_Nacional as OCN', 'AR.Id_Orden_Compra_Nacional', '=', 'OCN.Id_Orden_Compra_Nacional')
            ->join('third_parties as P', function ($join) {
                $join->on('AR.Id_Proveedor', '=', 'P.id')
                    ->where('P.is_supplier', true);
            })
            ->where('No_Conforme.Tipo', 'Compra')
            ->where('No_Conforme.Estado', 'Pendiente')
            ->orderBy('No_Conforme.Codigo', 'DESC');

        $datos->when($request->input('codigo'), function ($query, $codigo) {
            return $query->where('AR.Codigo', 'like', '%' . $codigo . '%');
        });

        $datos->when($request->input('orden'), function ($query, $orden) {
            return $query->where('OCN.Codigo', 'like', '%' . $orden . '%');
        });

        $datos = $datos->paginate(Request()->get('pageSize', 10), ['*'], 'page', Request()->get('page', 1));

        $datos->getCollection()->transform(function ($item) {
            $item->Nombre = $item->social_reason ?: $item->first_name . ' ' . $item->first_surname;
            return $item;
        });

        return $this->success($datos);
    }

    public function cerrarNoConforme()
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        if ($id != '') {
            $oItem = new complex('No_Conforme', 'Id_No_Conforme', $id);
            $oItem->Estado = 'Cerrado';
            $oItem->save();
            unset($oItem);
        }
        $resultado['success'] = true;
        return response()->json($resultado);
    }

    public function anularDevolucion()
    {
        $contabilizacion = new Contabilizar();
        $modulo = $this->GetModulo();
        $id_devolucion = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $funcionario = (isset($_REQUEST['funcionario']) ? $_REQUEST['funcionario'] : '');
        $oItem1 = new complex('Devolucion_Compra', 'Id_Devolucion_Compra', $id_devolucion);
        $id_bodega = $oItem1->Id_Bodega;
        $devolucion = $oItem1->getData();
        if ($devolucion['Guia'] || $devolucion['Guia'] != '') {
            $resultado['mensaje'] = "No se pueden anular Devoluciones enviadas!";
            $resultado['tipo'] = "error";
        } else {
            $productos = $this->GetProductos($id_devolucion);
            if ($devolucion['Id_Bodega_Nuevo'] && $devolucion['Id_Bodega_Nuevo'] != '' && $devolucion['Fase_2'] && $devolucion['Fase_2'] != '' && $devolucion['Fase_2'] != NULL) {
                if (!$this->validarBodegaInventario($devolucion['Id_Bodega_Nuevo'])) {
                    foreach ($productos as $producto) {
                        $oItem = new complex('Inventario_Nuevo', 'Id_Inventario_Nuevo', $producto['Id_Inventario_Nuevo']);
                        $cantidad_final = $oItem->Cantidad + number_format($producto['Cantidad'], 0, "", "");
                        $oItem->Cantidad = number_format($cantidad_final, 0, "", "");
                        $oItem->save();
                        unset($oItem);
                    }
                    $resultado['mensaje'] = "¡Se ha anulado la devolución correctamente y ha retornado a inventario!";
                    $resultado['tipo'] = "success";
                } else {
                    $resultado['mensaje'] = "En este momento la bodega que seleccionó se encuentra realizando un inventario.";
                    $resultado['tipo'] = "error";
                    echo json_encode($resultado);
                    exit;
                }
            } else {
                $resultado['mensaje'] = "¡Se ha anulado la devolución correctamente pero no hubo devolución a inventario! - Bodega Antigua";
                $resultado['tipo'] = "success";
            }
            $oItem1->Estado = "Anulada";
            $oItem1->save();
            unset($oItem1);
            $oItem = new complex('Actividad_Devolucion_Compra', "Id_Actividad_Devolucion_Compra" . $id_devolucion);
            $oItem->Id_Devolucion_Compra = $id_devolucion;
            $oItem->Identificacion_Funcionario = $funcionario;
            $oItem->Detalles = "Se Anuló la devolución de compra " . $devolucion["Codigo"];
            $oItem->Estado = "Anulada";
            $oItem->Fecha = date("Y-m-d H:i:s");
            $oItem->save();
            unset($oItem);
            $contabilizacion->AnularMovimientoContable($id_devolucion, $modulo);
        }

        return response()->json($resultado);
    }

    private function setCondiciones($request)
    {
        $condiciones = [];

        if (isset($request['codigo']) && $request['codigo']) {
            $condiciones[] = "AR.Codigo LIKE '%" . $request['codigo'] . "%'";
        }
        if (isset($request['orden']) && $request['orden']) {
            $condiciones[] = "OCN.Codigo LIKE '%" . $request['orden'] . "%'";
        }

        return empty($condiciones) ? null : $condiciones;
    }



    function GetProductos($id)
    {
        $query = "SELECT * FROM Producto_Devolucion_Compra WHERE Id_Devolucion_Compra=$id ";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();
        unset($oCon);

        return $productos;
    }

    function GetModulo()
    {
        $query = "SELECT * FROM Modulo WHERE Nombre='Devolucion Acta' ";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $modelo = $oCon->getData();
        unset($oCon);

        return $modelo['Id_Modulo'] ?? 0;
    }

    function validarBodegaInventario($id_bodega)
    {

        $query = 'SELECT DOC.Id_Doc_Inventario_Fisico
        FROM Doc_Inventario_Fisico DOC
        INNER JOIN Estiba E ON E.Id_Estiba =  DOC.Id_Estiba
        WHERE  DOC.Estado != "Terminado" AND E.Id_Bodega_Nuevo =  ' . $id_bodega;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $documentos = $oCon->getData();
        return $documentos;
    }

    public function descargarPdf()
    {
        $tipo = (isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : '');
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');


        /* DATOS DEL ARCHIVO A MOSTRAR */
        $oItem = new complex($tipo, "Id_" . $tipo, $id);
        $data = $oItem->getData();
        unset($oItem);


        $query = 'SELECT
        IFNULL(CONCAT(PRD.Principio_Activo," ",PRD.Presentacion," ",PRD.Concentracion," (",PRD.Nombre_Comercial, ") ", PRD.Cantidad," ", PRD.Unidad_Medida, " "), CONCAT(PRD.Nombre_Comercial, " LAB-", PRD.Laboratorio_Comercial)) as Nombre_Producto, POCN.* , PRD.Embalaje, PRD.Nombre_Comercial
        FROM Producto_Devolucion_Compra POCN
        INNER JOIN Producto PRD
        ON PRD.Id_Producto = POCN.Id_Producto
        WHERE POCN.Id_Devolucion_Compra ='.$id ;

        $oCon= new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();
        unset($oCon);

        /* echo "<pre>";
        var_dump($productos);
        echo "</pre>"; */

        $query = 'SELECT D.*, IFNULL(p.social_reason, CONCAT_WS(" ", p.first_name, p.first_surname)) as Proveedor, b.Nombre as Bodega, p.id
        FROM Devolucion_Compra D
        Inner JOIN third_parties p ON D.Id_Proveedor=p.id
        LEFT JOIN Bodega_Nuevo b ON D.Id_Bodega=b.Id_Bodega_Nuevo
        WHERE D.Id_Devolucion_Compra='.$id ;

        $oCon= new consulta();
        $oCon->setQuery($query);
        $compra = $oCon->getData();
        unset($oCon);

        $elabora = Person::where('id', $data["Identificacion_Funcionario"])
            ->value(DB::raw("IFNULL(CONCAT_WS(' ', first_name, first_surname), 'Nombre no disponible')"));

        $header = (object) [
            'Titulo' => 'Devolución',
            'Codigo' => $data['Codigo'] ?? '',
            'Fecha' => $data['Fecha'],
            'CodigoFormato' => $data['Codigo'] ?? '',
        ];

        $pdf = Pdf::loadView('pdf.noConforme', [
            'data' => $data,
            'compra' => $compra,
            'productos' => $productos,
            'datosCabecera' => $header,
            'elabora' => $elabora,
            'tipo' => $tipo

        ]);

        return $pdf->stream("noConforme");
    }

    function fecha($str)
    {
        $parts = explode(" ", $str);
        $date = explode("-", $parts[0]);
        return $date[2] . "/" . $date[1] . "/" . $date[0];
    }
}
