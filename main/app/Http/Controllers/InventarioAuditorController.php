<?php

namespace App\Http\Controllers;

use App\Http\Services\complex;
use App\Http\Services\consulta;
use App\Http\Services\HttpResponse;
use App\Http\Services\Contabilizar;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Carbon\Carbon as Carbon;

class InventarioAuditorController extends Controller
{
    use ApiResponser;

    function inventario()
    {
        $idBodega = (isset($_REQUEST['bodega']) && ($_REQUEST['bodega'] != '0') ? $_REQUEST['bodega'] : '');

        $productos = $this->getData($idBodega);

        return $this->success($this->anexarInventario($productos, $idBodega));
    }

    function getData($idBodega)
    {
        $query = "SELECT P.Nombre_Comercial, P.Laboratorio_Comercial, P.Laboratorio_Generico, P.Codigo_Cum, PR.Id_Producto, SUM(PR.Cantidad) AS Cant, CONCAT(IFNULL(P.Principio_Activo, ' '), ' ', P.Presentacion, ' ', IFNULL(P.Concentracion, ' '), ' ', P.Cantidad, ' ', P.Unidad_Medida, ' LAB: ', P.Laboratorio_Comercial) AS Nombre_Producto FROM Remision AS R INNER JOIN Producto_Remision AS PR ON PR.Id_Remision = R.Id_Remision INNER JOIN Producto AS P ON P.Id_Producto = PR.Id_Producto WHERE R.Tipo_Origen = 'Bodega' AND R.Id_Origen = $idBodega AND CAST(R.Fecha AS Date) >= '" . Carbon::now()->subDays(150)->format('Y-m-d') . "' GROUP BY PR.Id_Producto ORDER BY Cant DESC LIMIT 50";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $data = $oCon->getData();
        unset($oCon);
        return $data;
    }

    function anexarInventario($productos, $idBodega)
    {
        foreach ($productos as $key => $producto) {
            $IdEstiba = (int) $this->getEstiba($producto->Id_Producto, $idBodega);
            $productos[$key]->Inventario = $this->getInventario($producto->Id_Producto, $producto->Codigo_Cum, $IdEstiba, $idBodega);

            if (empty((array) $productos[$key]->Inventario)) {
                unset($productos[$key]);
            }
        }

        return $this->transformData(array_values($productos));
    }

    function getEstiba($IdProducto, $idBodega)
    {
        $query = "SELECT SUM(PR.Cantidad) AS Cant, InN.Id_Estiba FROM Producto_Remision AS PR INNER JOIN Inventario_Nuevo AS InN ON PR.Id_Inventario_Nuevo = InN.Id_Inventario_Nuevo INNER JOIN Remision AS RE ON PR.Id_Remision = RE.Id_Remision INNER JOIN Estiba AS E ON E.Id_Estiba = InN.Id_Estiba INNER JOIN Bodega_Nuevo AS B ON B.Id_Bodega_Nuevo = E.Id_Bodega_Nuevo WHERE InN.Id_Producto = $IdProducto AND PR.Id_Producto = $IdProducto AND RE.Fecha <= '" . Carbon::now()->subDays(150)->format('Y-m-d') . "' AND B.Id_Bodega_Nuevo = $idBodega GROUP BY InN.Id_Estiba ORDER BY Cant DESC LIMIT 1";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('simple');
        $data = $oCon->getData();
        unset($oCon);
        return $data['Id_Estiba'];
    }

    function getInventario($IdProducto, $cum, $IdEstiba, $idBodega)
    {
        $query = "SELECT B.Nombre AS Bodega, E.Nombre AS Estiba, InN.Lote, InN.Fecha_Vencimiento, InN.Cantidad, InN.Codigo_Cum, InN.Id_Inventario_Nuevo, E.Id_Estiba, CONCAT(IFNULL(P.Principio_Activo, ' '), ' ', P.Presentacion, ' ', IFNULL(P.Concentracion, ' '), ' ', P.Cantidad, ' ', P.Unidad_Medida, ' LAB: ', P.Laboratorio_Comercial) AS Nombre_Producto FROM Inventario_Nuevo AS InN INNER JOIN Estiba AS E ON E.Id_Estiba = InN.Id_Estiba INNER JOIN Bodega_Nuevo AS B ON B.Id_Bodega_Nuevo = E.Id_Bodega_Nuevo INNER JOIN Producto AS P ON P.Id_Producto = InN.Id_Producto WHERE InN.Id_Producto = $IdProducto AND InN.Codigo_Cum = '$cum' AND B.Id_Bodega_Nuevo = $idBodega AND InN.Cantidad <> 0 AND E.Id_Estiba = $IdEstiba";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $data = $oCon->getData();
        unset($oCon);
        return $data;
    }

    function transformData($productos)
    {
        $temporal = [];
        if (count($productos) > 0) {
            // Obtener al menos un elemento y un máximo de 5 o la cantidad de productos disponibles
            $randomCount = min(5, count($productos));
            $randomKeys = array_rand($productos, $randomCount);

            // Si array_rand devuelve un solo valor, convertirlo en un arreglo
            if (!is_array($randomKeys)) {
                $randomKeys = [$randomKeys];
            }
            foreach ($randomKeys as $index) {
                array_push($temporal, $productos[$index]);
            }
        }
        return $temporal;
    }


    public function saveconteoCustom(Request $request)
    {
        $productos = $request->input('productos');
        $datos = json_decode($productos);
        $bodega = (isset($_REQUEST['bodega']) && ($_REQUEST['bodega'] != '') ? json_decode($_REQUEST['bodega']) : '');
        $documento = (isset($_REQUEST['documento']) && ($_REQUEST['documento'] != '') ? json_decode($_REQUEST['documento']) : '');

        try {
            foreach ($datos as $producto) {
                $oItem = new complex('Producto_Doc_Inventario_Auditable', 'Id_Producto_Doc_Inventario_Auditable');
                $oItem->Id_Producto = $producto->producto;
                $oItem->Id_Inventario_Nuevo = 0;
                $oItem->Primer_Conteo = ($producto->inventario->cantidad == '') ? 0 : $producto->inventario->cantidad;
                $oItem->Lote = $producto->inventario->Lote;
                $oItem->Id_Inventario_Nuevo = $producto->inventario->Id_Inventario_Nuevo;
                $oItem->Fecha_Vencimiento = $producto->inventario->Fecha_Vencimiento;
                $oItem->Fecha_Primer_Conteo = Carbon::now()->format('Y-m-d H:m');
                $oItem->Id_Doc_Inventario_Auditable = $documento;
                $oItem->Id_Estiba = $producto->inventario->Id_Estiba;
                $oItem->Cantidad_Inventario = $producto->inventario->Cantidad;
                $oItem->save();
                unset($oItem);
            }

            $this->updateDocument($documento);

        } catch (\Throwable $th) {
            $this->show($this->myerror($th->getMessage()));
        }
    }

    function myerror($message = '')
    {
        header("HTTP/1.0 400 ");
        $response = new HttpResponse();
        $response->SetRespuesta(1, 'Operacion Erronea', $message);
        $response->SetDatosRespuesta([]);
        return $response->GetRespuesta();
    }

    function show($data, $e = false)
    {

        echo json_encode($data);
        if ($e) {
            /*   $myfile = fopen("testing.txt", "w") or die("Unable");
            fwrite($myfile, json_encode($data));
            fclose($myfile); */
            exit;

        }
    }

    private function updateDocument($documento)
    {
        $oItem = new complex('Doc_Inventario_Auditable', 'Id_Doc_Inventario_Auditable', $documento);
        $oItem->getData();
        $oItem->Estado = 'Primer Conteo';
        $oItem->save();
        $response['tipo'] = 'success';
        $response['title'] = 'Cambio de estado exitoso';
        $response['mensaje'] = 'Documento actualizado con éxito';
        $this->show($response);
    }

    public function documentosParaAjustarAuditables()
    {
        $documento = isset($_REQUEST['Id_Bodega']) ? $_REQUEST['Id_Bodega'] : false;

        $query = "SELECT PDA.Lote,
    PDA.Fecha_Vencimiento, PDA.Cantidad_Inventario, PDA.Primer_Conteo AS Cantidad_Encontrada, PDA.Id_Producto,
    PDA.Id_Producto_Doc_Inventario_Auditable,
    P.Nombre_General, E.Nombre AS Estiba, E.Id_Estiba, E.Nombre AS Nombre_Estiba,
    GE.Nombre AS Nombre_Grupo,

    P.Nombre_General AS Nombre_Producto,

    PDA.Primer_Conteo,
    PDA.Segundo_Conteo,
    PDA.Fecha_Primer_Conteo,
    PDA.Fecha_Segundo_Conteo,

    (CASE WHEN (PDA.Segundo_Conteo) < (PDA.Cantidad_Inventario)
    THEN CONCAT('', PDA.Segundo_Conteo - PDA.Cantidad_Inventario)
    WHEN (PDA.Segundo_Conteo) >= (PDA.Cantidad_Inventario)
    THEN CONCAT('+', PDA.Segundo_Conteo - PDA.Cantidad_Inventario)
    END ) AS Cantidad_Diferencial

    FROM Producto_Doc_Inventario_Auditable AS PDA
    INNER JOIN Producto AS P ON P.Id_Producto = PDA.Id_Producto
    INNER JOIN Estiba AS E ON E.Id_Estiba = PDA.Id_Estiba
    INNER JOIN Grupo_Estiba AS GE ON E.Id_Grupo_Estiba = GE.Id_Grupo_Estiba
    INNER JOIN Doc_Inventario_Auditable AS DA ON DA.Id_Doc_Inventario_Auditable = PDA.Id_Doc_Inventario_Auditable
    WHERE DA.Id_Doc_Inventario_Auditable = $documento AND DA.Estado='Segundo Conteo'
    ORDER BY Estiba ASC, P.Nombre_General ASC";

        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $productos = $oCon->getData();
        unset($oCon);

        $resultado['productos'] = $productos;
        $resultado['documento'] = $documento;

        return $this->success($resultado);
    }


    public function guardarInventarioFinal()
    {
        $contabilizar = new Contabilizar();
        $response = array();
        $funcionario = isset($_REQUEST['id_funcionario']) ? $_REQUEST['id_funcionario'] : false;
        $inventarios = isset($_REQUEST['inventarios']) ? $_REQUEST['inventarios'] : false;
        $productos = isset($_REQUEST['productos']) ? $_REQUEST['productos'] : false;

        $listado_inventario = (array) json_decode($productos, true);

        $ids_estibas = [];

        foreach ($listado_inventario as $res) {
            if (isset($res['Id_Estiba']) && !in_array($res['Id_Estiba'], $ids_estibas)) {
                $ids_estibas[] = $res['Id_Estiba'];
            }

            $query = 'SELECT Id_Inventario_Nuevo FROM Inventario_Nuevo WHERE Id_Producto=' . $res["Id_Producto"] .
                ' AND Id_Estiba=' . $res['Id_Estiba'] . ' AND Lote="' . $res["Lote"] . '" LIMIT 1';
            $oCon = new consulta();
            $oCon->setQuery($query);
            $inven = $oCon->getData();

            $cantidad = number_format(isset($res['Cantidad_Auditada']) ? $res["Cantidad_Auditada"] : $res["Segundo_Conteo"], 0, "", "");

            $oItem = new complex('Inventario_Nuevo', 'Id_Inventario_Nuevo', $inven ? $inven['Id_Inventario_Nuevo'] : null);
            $oItem->Cantidad = $cantidad;
            $oItem->Id_Estiba = $res['Id_Estiba'];
            $oItem->Lote = strtoupper($res["Lote"]);
            $oItem->Fecha_Vencimiento = $res["Fecha_Vencimiento"];
            $oItem->Id_Producto = $res["Id_Producto"];
            $oItem->Identificacion_Funcionario = $funcionario;
            $oItem->Id_Punto_Dispensacion = 0;
            $oItem->Cantidad_Apartada = '0';
            $oItem->Cantidad_Seleccionada = '0';
            $oItem->save();
            unset($oItem);
        }

        if (!empty($ids_estibas)) {
            $ids_estibas_string = implode(',', $ids_estibas);
            $query2 = "UPDATE Estiba SET Estado = 'Disponible' WHERE Id_Estiba IN ($ids_estibas_string)";
            $oCon = new consulta();
            $oCon->setQuery($query2);
            $oCon->createData();
            unset($oCon);
        }

        $resultado = [
            'titulo' => "Registro Exitoso",
            'mensaje' => "Se ha guardado el inventario exitosamente!",
            'tipo' => "success"
        ];

        $this->ActualizarBodegaState($inventarios);
        $this->show($resultado);
    }

    function ActualizarProductoDocumento($id_producto_doc_inventario, $cantidad)
    {
        global $funcionario;
        $query = 'UPDATE Producto_Doc_Inventario_Auditable SET Cantidad_Auditada =' . $cantidad . ', Funcionario_Cantidad_Auditada = ' . $funcionario .
            ' WHERE Id_Producto_Doc_Inventario_Auditable = ' . $id_producto_doc_inventario;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->createData();
    }

    function ActualizarBodegaState($idBodega)
    {
        $query = "UPDATE Estiba SET Estado = 'Disponible' WHERE Id_Bodega_Nuevo  = $idBodega AND Estado = 'Inventario'";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->createData();
    }


    public function reconteo()
    {
        $documento = (isset($_REQUEST['inv']) && ($_REQUEST['inv'] != '') ? json_decode($_REQUEST['inv']) : '');

        try {

            $resultado['tipo'] = "success";
            $resultado['Productos'] = $this->getProductosDif($documento);
            $resultado['Productos_Sin_Diferencia'] = $this->getProductosIquals($documento);
            $resultado['Inventarios'] = $documento;
            $resultado['Estado'] = 'Primer Conteo';

            return $this->success($resultado);

        } catch (\Throwable $th) {
            $this->show($this->myerror($th->getMessage()));
        }

    }

    function getProductosIquals($documento)
    {
        try {
            $query = "SELECT PDA.Lote, PDA.Fecha_Vencimiento, PDA.Cantidad_Inventario, PDA.Primer_Conteo AS Cantidad_Encontrada, P.Nombre_General, E.Nombre AS Estiba,

        P.Nombre_General AS Nombre_Producto

        FROM Producto_Doc_Inventario_Auditable AS PDA
        INNER JOIN Producto AS P ON P.Id_Producto = PDA.Id_Producto
        INNER JOIN Estiba AS E ON E.Id_Estiba = PDA.Id_Estiba
        WHERE Id_Doc_Inventario_Auditable = $documento AND Primer_Conteo = Cantidad_Inventario
        ORDER BY Estiba ASC, P.Nombre_General ASC";

            $oCon = new consulta();
            $oCon->setTipo('Multiple');
            $oCon->setQuery($query);
            $productos_inventario = $oCon->getData();
            unset($oCon);

            return $productos_inventario;

        } catch (\Throwable $th) {
            $this->show($this->myerror($th->getMessage()));
        }
    }


    function getProductosDif($documento)
    {
        try {
            $query = "SELECT PDA.Lote,
        PDA.Fecha_Vencimiento, PDA.Cantidad_Inventario, PDA.Primer_Conteo AS Cantidad_Encontrada, PDA.Id_Producto,
        PDA.Id_Producto_Doc_Inventario_Auditable,
        P.Nombre_General, E.Nombre AS Estiba,

        P.Nombre_General AS Nombre_Producto,

        (CASE WHEN (PDA.Primer_Conteo) < (PDA.Cantidad_Inventario)
        THEN CONCAT('', PDA.Primer_Conteo - PDA.Cantidad_Inventario)
        WHEN (PDA.Primer_Conteo) > (PDA.Cantidad_Inventario)
        THEN CONCAT('+', PDA.Primer_Conteo - PDA.Cantidad_Inventario)
        END) AS Cantidad_Diferencial

        FROM Producto_Doc_Inventario_Auditable AS PDA
        INNER JOIN Producto AS P ON P.Id_Producto = PDA.Id_Producto
        INNER JOIN Estiba AS E ON E.Id_Estiba = PDA.Id_Estiba
        WHERE Id_Doc_Inventario_Auditable = $documento AND Primer_Conteo <> Cantidad_Inventario
        ORDER BY Estiba ASC, P.Nombre_General ASC";

            $oCon = new consulta();
            $oCon->setTipo('Multiple');
            $oCon->setQuery($query);
            $productos_inventario = $oCon->getData();
            unset($oCon);

            return $productos_inventario;

        } catch (\Throwable $th) {
            $this->show($this->myerror($th->getMessage()));
        }
    }


    public function saveReconteo()
    {

        $contabilizar = new Contabilizar();
        $response = array();
        $http_response = new HttpResponse();

        $listado_inventario = isset($_REQUEST['listado_inventario']) ? $_REQUEST['listado_inventario'] : false;
        $funcionario = isset($_REQUEST['id_funcionario']) ? $_REQUEST['id_funcionario'] : false;
        $inventarios = isset($_REQUEST['inventarios']) ? $_REQUEST['inventarios'] : false;

        $listado_inventario = (array) json_decode($listado_inventario, true);


        foreach ($listado_inventario as $value) {

            // Registrar (actualizar) el conteo final en el producto de inventario físico

            if ($value['Id_Producto_Doc_Inventario_Auditable'] != 0) {
                $id_inventario = explode(",", $value['Id_Producto_Doc_Inventario_Auditable']);
                for ($i = 0; $i < count($id_inventario); $i++) {
                    if ($i != 0) {
                        $oItem = new complex('Producto_Doc_Inventario_Auditable', 'Id_Producto_Doc_Inventario_Auditable', $id_inventario[$i]);
                        $oItem->delete();
                        unset($oItem);
                    } else {
                        $oItem = new complex('Producto_Doc_Inventario_Auditable', 'Id_Producto_Doc_Inventario_Auditable', $id_inventario[$i]);
                        $cantidad = number_format((INT) $value['Cantidad_Final'], 0, '', ''); // parseando
                        $conteo1 = number_format((INT) $value['Cantidad_Encontrada'], 0, '', ''); // parseando
                        $oItem->Segundo_Conteo = $cantidad;
                        $oItem->Primer_Conteo = $conteo1;
                        $oItem->Cantidad_Inventario = $value['Cantidad_Inventario'];
                        $oItem->Fecha_Segundo_Conteo = date('Y-m-d');
                        $oItem->save();
                        unset($oItem);
                    }
                }

            } else {

                // $oItem = new complex('Producto_Doc_Inventario_Fisico', 'Id_Producto_Doc_Inventario_Fisico');
                // $cantidad = number_format((INT)$value['Cantidad_Final'],0,'',''); // parseando
                // $oItem->Segundo_Conteo = $cantidad;
                // $oItem->Id_Producto =$value['Id_Producto'];
                // $oItem->Id_Inventario_Nuevo =$value['Id_Inventario_Nuevo'];
                // $oItem->Primer_Conteo ="0";
                // $oItem->Fecha_Primer_Conteo = date('Y-m-d');
                // $oItem->Fecha_Segundo_Conteo = date('Y-m-d');
                // $oItem->Cantidad_Inventario = number_format($value['Cantidad_Inventario'],0,"","");
                // $oItem->Id_Doc_Inventario_Fisico = AsignarIdInventarioFisico($inventarios);
                // $oItem->Lote = strtoupper($value['Lote']);
                // $oItem->Fecha_Vencimiento = $value['Fecha_Vencimiento'];
                // $oItem->save();
                // unset($oItem);
            }

        }

        $query2 = 'UPDATE Doc_Inventario_Auditable
SET Estado ="Segundo Conteo", Fecha_Fin="' . date('Y-m-d H:i:s') . '" , Funcionario_Autorizo=' . $funcionario . '
WHERE  Id_Doc_Inventario_Auditable IN (' . $inventarios . ')';
        $oCon = new consulta();
        $oCon->setQuery($query2);
        $oCon->createData();
        unset($oCon);


        //acutalizar los que no tienen diferencia
        $query2 = 'UPDATE Producto_Doc_Inventario_Auditable
SET Segundo_Conteo = Primer_Conteo
WHERE Segundo_Conteo IS NULL AND Id_Doc_Inventario_Auditable IN (' . $inventarios . ')';
        $oCon = new consulta();
        $oCon->setQuery($query2);
        $oCon->createData();
        unset($oCon);




        $resultado['titulo'] = "Operación exitosa";
        $resultado['mensaje'] = "Se ha guardado el segundo conteo exitosamente!";
        $resultado['tipo'] = "success";

        return $this->success($resultado);

    }

    function AsignarIdInventarioFisico($inventarios)
    {
        $inv = explode(',', $inventarios);

        return $inv[0];
    }


}

