<?php

namespace App\Http\Controllers;

use App\Http\Services\complex;
use App\Http\Services\consulta;
use App\Http\Services\HttpResponse;
use App\Http\Services\QueryBaseDatos;
use App\Models\CompanyConfiguration;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;

class RemisionSaveController extends Controller
{
    use ApiResponser;

    public function saveRemision()
    {
        $queryObj = new QueryBaseDatos();
        $response = array();
        $http_response = new HttpResponse();

        $codigos_rem = '';

        $datos = (isset($_REQUEST['datos']) ? $_REQUEST['datos'] : '');
        $productos = (isset($_REQUEST['productos']) ? $_REQUEST['productos'] : '');
        $codigo = (isset($_REQUEST['codigo']) ? $_REQUEST['codigo'] : '');
        $rotativo = (isset($_REQUEST['rotativo']) ? $_REQUEST['rotativo'] : '');

        $grupo = (isset($_REQUEST['grupo']) ? $_REQUEST['grupo'] : '');

        $grupo = (array) json_decode($grupo, true);

        $datos = (array) json_decode($datos);

        $tipocontrato = $datos["Tipo"];

        $productos = (array) json_decode($productos, true);
        $rotativo = (array) json_decode($rotativo, true);


        if (isset($datos['Id_Orden_Pedido'])) {
            $productos_remision = $this->separarProductosSubcategoria($productos);
        } else {
            $productos_remision['P'] = $productos;
        }


        foreach ($productos_remision as $productos) {

            if ($datos['Tipo'] == "Cliente") {
                $mora = $this->getMoraCliente($datos['Id_Destino']);
                if ($mora > 0) {
                    $datos['Estado'] = 'Cartera';
                }
            }
            if ($datos["Tipo"] == "Interna" && $datos["Modelo"] == "Bodega-Punto" && isset($rotativo["Fecha_Inicio"]) && isset($rotativo["Fecha_Fin"])) {
                $datos["FIni_Rotativo"] = $rotativo["Fecha_Inicio"];
                $datos["FFin_Rotativo"] = $rotativo["Fecha_Fin"];
                $datos["Eps_Rotativo"] = $rotativo["Id_Eps"];
            }

            $refrigerados = [];

            $productos_remision = [
                "Separables" => [],
                "No_Separables" => [],
            ];

            $productos_pendientes = [];
            $this->EliminarBorrador($codigo);
            // $item_remision = $this->GetLongitudRemision($queryObj);
            $item_remision = CompanyConfiguration::where('company_id', getCompanyWorkedId())->first()->max_item_remision;
            $remisiones = array_chunk($productos, $item_remision);

            foreach ($remisiones as $value) {

                $id_remision = $this->SaveEncabezado($datos, 'Productos', $grupo);
                $this->SaveProductoRemision($id_remision, $value, $tipocontrato, $queryObj, $datos, $codigos_rem);
            }
        }
        if ($datos['Estado'] == "Cartera") {
            $http_response->SetRespuesta(0, 'Guardado Correctamente', 'Las siguientes remisiones se guardaron pendientes de Cartera <br> ' . trim($codigos_rem, ','));
        } else {
            $http_response->SetRespuesta(0, 'Guardado Correctamente', 'Se ha guardado correctamente todas las remisiones! <br> ' . trim($codigos_rem, ','));
        }

        $response = $http_response->GetRespuesta();

        return $this->success($response);
    }

    private function separarProductosSubcategoria($productos)
    {
        $producto_separado = [];
        foreach ($productos as $producto) {
            if (count($producto['Lotes_Seleccionados']) > 0) {
                if ($producto['Id_Subcategoria'] == '8' || $producto['Id_Subcategoria'] == '10') {
                    $producto_separado['C'][] = $producto;
                } else if ($producto['Id_Subcategoria'] == '3') {
                    $producto_separado['R'][] = $producto;
                } else if ($producto['Id_Subcategoria'] == '2' || $producto['Id_Subcategoria'] == '6' || $producto['Id_Subcategoria'] == '13') {
                    $producto_separado['M'][] = $producto;
                } else {
                    $producto_separado['P'][] = $producto;
                }
            }
        }
        return $producto_separado;
    }

    private function getMoraCliente($id_cliente)
    {
        $query =
            "SELECT *, MAX(R.Dias_Mora) as Mora FROM (
                SELECT
                MC.Id_PLan_Cuenta,
                    C.id,
                    IFNULL(C.social_reason, CONCAT_WS(' ', C.first_name, C.first_surname)) as Nombre,
                    MC.Fecha_Movimiento,
                    IF(C.condition_payment > 1, IF(DATEDIFF(CURDATE(), DATE(MC.Fecha_Movimiento)) > C.condition_payment, DATEDIFF(CURDATE(), DATE(MC.Fecha_Movimiento)) - C.condition_payment, 0), 0) AS Dias_Mora,
                    (CASE PC.Naturaleza
                        WHEN 'C' THEN (SUM(MC.Haber) - SUM(MC.Debe))
                        ELSE (SUM(MC.Debe) - SUM(MC.Haber))
                    END) AS TOTAL
            FROM
                Movimiento_Contable MC
            INNER JOIN Plan_Cuentas PC ON MC.Id_Plan_Cuenta = PC.Id_Plan_Cuentas
            INNER JOIN third_parties C ON C.id = MC.Nit
            WHERE
                MC.Estado != 'Anulado'
                    AND C.id = $id_cliente
                    AND Id_Plan_Cuenta = 57
            GROUP BY MC.Documento , C.id , MC.Id_Plan_Cuenta
            HAVING TOTAL != 0) R ";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $mora = $oCon->getData()['Mora'];
        return $mora ? $mora : false;
    }

    private function EliminarBorrador($codigo)
    {

        $query = 'UPDATE Borrador
               Set Estado = "Eliminado"
               WHERE Codigo="' . $codigo . '"';
        $oCon = new consulta();
        $oCon->setQuery($query);
        $dato = $oCon->createData();
        unset($oCon);
    }

    private function GetLongitudRemision($queryObj)
    {

        $query = "SELECT Max_Item_Remision FROM Configuracion WHERE Id_Configuracion=1";
        $queryObj->SetQuery($query);
        $rem = $queryObj->ExecuteQuery('simple');
        return $rem['Max_Item_Remision'];
    }

    private function SaveEncabezado($modelo, $tipo, $grupo)
    {

        $modelo['Fecha'] = date("Y-m-d H:i:s");
        $modelo['Meses'] = (int) $modelo['Meses'];
        $modelo['Id_Contrato'] = (int) $modelo['Id_Contrato'];

        $oItem = new complex("Remision", "Id_Remision");

        foreach ($modelo as $index => $value) {

            if ($value != '') {
                if ($index == 'Subtotal_Remision' || $index == 'Impuesto_Remision' || $index == 'Descuento_Remision' || $index == 'Costo_Remision') {
                    $oItem->$index = number_format($value, 2, ".", "");
                } else {
                    $oItem->$index = $value;
                }
            }
        }

        $oItem->Id_Grupo_Estiba = $grupo['Id_Grupo'];
        // $oItem->Codigo = $configuracion->getConsecutivo('Remision', 'Remision');
        $oItem->Codigo = generateConsecutive('Remision');
        sumConsecutive('Remision');
        $oItem->save();
        $id_remision = $oItem->getId();
        unset($oItem);

        $qr = generarqr('remision', $id_remision, '/IMAGENES/QR/');
        $oItem = new complex("Remision", "Id_Remision", $id_remision);
        $oItem->Codigo_Qr = $qr;
        $oItem->save();
        unset($oItem);
        return $id_remision;
    }

    private function SaveProductoRemision($id_remision, $productos, $tipocontrato, $queryObj, $datos, $codigos_rem)
    {

        foreach ($productos as $producto) {

            foreach ($producto['Lotes_Seleccionados'] as $lote) {
                if ($tipocontrato == 'Contrato') {

                    $cantidad_disponible = $this->GetCantidadDisponible($lote['Id_Inventario_Contrato'], $lote['Cantidad_Seleccionada'], $queryObj, $tipocontrato);
                } else {

                    $cantidad_disponible = $this->GetCantidadDisponible($lote['Id_Inventario_Nuevo'], $lote['Cantidad_Seleccionada'], $queryObj, $tipocontrato);
                }

                $p = $lote;

                if ($cantidad_disponible >= $lote['Cantidad_Seleccionada']) {
                    //quitar Cantidad Seleccionada
                    if ($tipocontrato == 'Contrato') {

                        $descuento = $this->QuitarCantidadSeleccionada($lote['Id_Inventario_Contrato'], $lote['Cantidad_Seleccionada'], $p['Cantidad_Seleccionada'], $queryObj, $datos, $tipocontrato);
                    } else {

                        $descuento = $this->QuitarCantidadSeleccionada($lote['Id_Inventario_Nuevo'], $lote['Cantidad_Seleccionada'], $p['Cantidad_Seleccionada'], $queryObj, $datos, $tipocontrato);
                    }

                    if ($descuento) {

                        if ($cantidad_disponible < $lote['Cantidad_Seleccionada'] && $cantidad_disponible > 0) {
                            $p['Cantidad'] = $cantidad_disponible;
                            $p['Cantidad_Seleccionada'] = $cantidad_disponible;
                        }

                        $subtotal = (($p['Cantidad_Seleccionada'] * $producto['Precio']) * (1 - ($producto['Descuento'] ? $producto['Descuento'] : 0) / 100));
                        $p['Subtotal'] = number_format($subtotal, 2, ".", "");

                        $subtotal = ($p['Cantidad_Seleccionada'] * $producto['Precio']) * ($producto['Descuento'] / 100);
                        $p['Total_Descuento'] = number_format($subtotal, 2, ".", "");

                        $subtotal = ($p['Cantidad_Seleccionada'] * $producto['Precio']) * ($producto['Impuesto'] / 100);
                        $p['Total_Impuesto'] = number_format($subtotal, 2, ".", "");

                        $p['Impuesto'] = $producto['Impuesto'];
                        $p['Descuento'] = $producto['Descuento'];
                        $p['Cantidad_Total'] = $producto['Cantidad'];

                        $oItem = new complex('Producto_Remision', "Id_Producto_Remision");
                        $p['Id_Remision'] = $id_remision;
                        unset($p['Cantidad']);

                        foreach ($p as $index => $value) {

                            $oItem->$index = $value;
                        }

                        $ItemCosto = new complex('Costo_Promedio', "Id_Producto", $p['Id_Producto']);
                        $ItemCosto = $ItemCosto->getData();
                        $oItem->Cantidad = $p['Cantidad_Seleccionada'];
                        $oItem->Precio = number_format($producto['Precio'], 2, ".", "");
                        $oItem->Costo = number_format($producto['Costo'] ? $producto['Costo'] : $ItemCosto['Costo_Promedio'], 2, ".", "");
                        $oItem->save();

                        unset($oItem);
                    }
                } else {
                    if ($tipocontrato == 'Contrato') {

                        $this->QuitarCantidadSeleccionada($lote['Id_Inventario_Contrato'], $lote['Cantidad_Seleccionada'], 0, $queryObj, $datos, $tipocontrato);
                    } else {

                        $this->QuitarCantidadSeleccionada($lote['Id_Inventario_Nuevo'], $lote['Cantidad_Seleccionada'], 0, $queryObj, $datos, $tipocontrato);
                    }
                }
            }
            $this->GuardarPendientes($producto, $id_remision, $queryObj, $datos);
        }
        $this->GuardarActividadRemision($id_remision, $datos, $queryObj, $codigos_rem);
    }

    function GetCantidadDisponible($id_Inventario, $cantidad, $queryObj, $tipocontrato)
    {


        if ($tipocontrato == 'Contrato') {
            $query = "SELECT (Cantidad-(Cantidad_Apartada)) as Cantidad, Cantidad as Cantidad_Inventario, Cantidad_Seleccionada
                         FROM Inventario_Contrato WHERE Id_Inventario_Contrato = $id_Inventario";
        } else {
            $query = "SELECT (Cantidad-(Cantidad_Apartada)) as Cantidad, Cantidad as Cantidad_Inventario, Cantidad_Seleccionada
                         FROM Inventario_Nuevo WHERE Id_Inventario_Nuevo = $id_Inventario";
        }

        $queryObj->SetQuery($query);
        $inv = $queryObj->ExecuteQuery('simple');

        if ($inv['Cantidad_Seleccionada'] > 0) {
            $cantidad = $inv['Cantidad_Seleccionada'] - $cantidad;
        } else {
            $cantidad = 0;
        }
        return ($inv['Cantidad'] - $cantidad);
    }

    function QuitarCantidadSeleccionada($id_inventario, $cantidad, $apartada, $queryObj, $datos, $tipocontrato)
    {


        if ($tipocontrato == 'Contrato') {

            $oItem = new complex('Inventario_Contrato', "Id_Inventario_Contrato", $id_inventario);
        } else {
            $oItem = new complex('Inventario_Nuevo', "Id_Inventario_Nuevo", $id_inventario);
        }

        $inv = $oItem->getData();

        $cantidad_final = $inv['Cantidad_Seleccionada'] - $cantidad;
        $cantidad_apartada = $inv['Cantidad_Apartada'] + $apartada;
        $cantidad_inv = $inv['Cantidad'] - $apartada;

        $Nuevo_disponible = $inv['Cantidad'] - $inv['Cantidad_Apartada'] - $cantidad;
        if ($Nuevo_disponible >= 0) {
            if ($cantidad_final < 0) {
                $cantidad_final = 0;
            }
            if ($cantidad_apartada < 0) {
                $cantidad_apartada = 0;
            }
            if ($cantidad_inv < 0) {
                return false;
                $cantidad_inv = 0;
            }

            $oItem->Cantidad_Seleccionada = number_format($cantidad_final, 0, "", "");

            if ($datos['Tipo_Origen'] == 'Punto_Dispensacion') {
                $oItem->Cantidad = number_format($cantidad_inv, 0, "", "");
            } else {
                $oItem->Cantidad_Apartada = number_format($cantidad_apartada, 0, "", "");
            }

            $oItem->save();
            unset($oItem);
            return true;
        }
        $oItem->Cantidad_Seleccionada = number_format($cantidad_final, 0, "", "");
        $oItem->save();
        unset($oItem);
        return false;
    }

    function GuardarPendientes($p, $id, $queryObj, $datos)
    {


        if ($datos['Tipo_Destino'] == 'Punto_Dispensacion' && $datos['Tipo_Origen'] == 'Bodega') {

            $query = "SELECT Id_Producto_Pendientes_Remision, Cantidad FROM Producto_Pendientes_Remision
           WHERE Id_Punto_Dispensacion=$datos[Id_Destino] AND Id_Producto=$p[Id_Producto] ";

            $queryObj->SetQuery($query);
            $prod = $queryObj->ExecuteQuery('simple');

            if ($prod['Cantidad']) {

                $oItem = new complex("Producto_Pendientes_Remision", "Id_Producto_Pendientes_Remision", $prod['Id_Producto_Pendientes_Remision']);

                $cantidad_diferencial = $prod['Cantidad'] - $p['Cantidad'] + $p['Cantidad_Pendiente'];

                if ($cantidad_diferencial <= 0) {

                    $oItem->delete();
                } else {

                    $oItem->Cantidad = number_format($cantidad_diferencial, 0, "", "");
                    $oItem->save();
                }

                unset($oItem);
            } else {

                if ($p['Cantidad_Pendiente'] > 0) {

                    $oItem = new complex("Producto_Pendientes_Remision", "Id_Producto_Pendientes_Remision");
                    $oItem->Id_Remision = $id == '' ? '0' : $id;
                    $oItem->Id_Producto = $p['Id_Producto'];
                    $oItem->Cantidad = $p['Cantidad_Pendiente'];
                    $oItem->Id_Punto_Dispensacion = $datos['Id_Destino'];
                    $oItem->save();
                    unset($oItem);
                }
            }
        }
    }

    function GuardarActividadRemision($id_remision, $datos, $queryObj, $codigos_rem)
    {

        $cod = $this->GetCodigoRem($id_remision, $queryObj, $codigos_rem);

        $oItem = new complex('Actividad_Remision', "Id_Actividad_Remision");
        $oItem->Id_Remision = $id_remision;
        $oItem->Identificacion_Funcionario = $datos["Identificacion_Funcionario"];
        $oItem->Detalles = "Se creo la remision con codigo $cod";
        $oItem->Fecha = date("Y-m-d H:i:s");
        $oItem->save();
        unset($oItem);

        $this->guardarActividadPedido($datos['Id_Orden_Pedido'] ?? 0, $cod, $datos["Identificacion_Funcionario"]);
    }

    function GetCodigoRem($id_remision, $queryObj, $codigos_rem)
    {

        $query = "SELECT Codigo FROM Remision WHERE Id_Remision=$id_remision";

        $queryObj->SetQuery($query);
        $rem = $queryObj->ExecuteQuery('simple');

        $codigos_rem .= $rem['Codigo'] . ',';
        return $rem['Codigo'];
    }

    function guardarActividadPedido($id_orden_pedido, $rem, $func){
    
        if($id_orden_pedido !='0'){
            
            $ActividadDis['Fecha'] = date("Y-m-d H:i:s");
            $ActividadDis["Id_Orden_Pedido"] = $id_orden_pedido;
            $ActividadDis["Identificacion_Funcionario"] = $func;
            $ActividadDis["Detalle"] = "Se crea la Remision $rem";
            $ActividadDis["Estado"] = "Creacion";
        
            $oItem = new complex("Actividad_Orden_Pedido", "Id_Actividad_Orden_Pedido");
            foreach ($ActividadDis as $index => $value) {
                $oItem->$index = $value;
            }
            $oItem->save();
            unset($oItem);
        }
    
    }

}
