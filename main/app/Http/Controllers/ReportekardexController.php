<?php

namespace App\Http\Controllers;

use App\Exports\KardexReportExport;
use App\Http\Services\complex;
use App\Http\Services\consulta;
use App\Http\Services\NumeroALetras;
use App\Http\Services\QueryBaseDatos;
use App\Models\ActaRecepcion;
use App\Models\BodegaNuevo;
use App\Models\DevolucionCompra;
use App\Models\Dispensacion;
use App\Models\FacturaVenta;
use App\Models\InventarioNuevo;
use App\Models\NotaCredito;
use App\Models\Person;
use App\Models\Product;
use App\Models\ProductoActaRecepcion;
use App\Models\ProductoFacturaVenta;
use App\Models\PuntoDispensacion;
use App\Models\Remision;
use App\Models\Resolucion;
use App\Models\ThirdParty;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class ReportekardexController extends Controller
{
    public function listaProductos(Request $request)
    {
        $nombre = $request->input('nom');
        $codigoBarras = $request->input('codigo_barras');

        $query = Product::query();

        if ($nombre) {
            $query->where(function ($q) use ($nombre) {
                $q->where('Nombre_Comercial', 'LIKE', "%$nombre%")
                    ->orWhere('Nombre_General', 'LIKE', "%$nombre%")
                    ->orWhere('Referencia', 'LIKE', "%$nombre%")
                    ->orWhere('Codigo_Barras', 'LIKE', "%$nombre%");
            });
        }

        if ($codigoBarras) {
            $query->where('Codigo_Barras', 'LIKE', "%$codigoBarras%");
        }

        $resultados = $query->with([
            'inventarioNuevo' => function ($q) {
                $q->select('Id_Producto', 'Lote', 'Fecha_Vencimiento', 'Cantidad AS Cantidad_Disponible');
            }
        ])
            ->select('Id_Producto', 'Nombre_Comercial', 'Codigo_Barras', 'Unidad_Medida', 'Id_Categoria', 'Referencia')
            ->orderBy('Nombre_Comercial', 'ASC')
            ->get();

        return response()->json($resultados);
    }

    public function bodegaPunto(Request $request)
    {
        $bod = $request->input('bod', false);
        $pto = $request->input('pto', false);

        $resultados = [];

        if ($bod) {
            $resultados = BodegaNuevo::select('Id_Bodega_Nuevo as value', 'Nombre as label')->get();
        } else {
            $resultados = PuntoDispensacion::select('Id_Punto_Dispensacion as value', 'Nombre as label')->get();
        }

        return response()->json($resultados);

    }
    public function consultaKardexd(Request $request)
    {
        $condicion = '';
        $condicion2 = '';
        $condicion3 = '';
        $condicion4 = '';
        $condicion5 = '';
        $condicion6 = '';
        $tipo = $request->input('tipo');
        $idTipo = $request->input('idtipo');
        $producto = $request->input('producto');
        $fecha_inicio = $request->input('fecha_inicio') ? $request->input('fecha_inicio') . '-01' : null;
        $fecha_fin = $request->input('fecha_fin') ? $request->input('fecha_fin') : null;

        $ruta = '';
        $tabla = '';
        $tablaDest = '';
        $attrFecha = '';
        $query_dispensaciones = '';
        $query_notas_creditos = '';
        $query_devoluciones_compras = '';
        $query_actas_internacionales = '';

        $documento = '';
        $group = '';

        if ($tipo == 'Bodega') {
            $condicion .= " AND R.Id_Origen=$idTipo AND R.Tipo_Origen='Bodega'";
            $condicion2 .= " AND AR.Id_Bodega_Nuevo=$idTipo";
            $condicion3 .= " AND AI.Id_Origen_Destino=$idTipo AND AI.Origen_Destino='Bodega'";
            $condicion4 .= " AND INF.Id_Bodega_Nuevo=$idTipo";
            $condicion5 .= " AND Id_Bodega=$idTipo";
            $condicion6 .= " AND Id_Origen=$idTipo";
            $ruta = 'actarecepcionver';
            $tabla = 'Acta_Recepcion';
            $attrFecha = 'Fecha_Creacion';
            $tablaDest = 'Bodega_Nuevo';

            $condicion2Acta = " AND AR.Id_Bodega_Nuevo=$idTipo";
            $condicion5Acta = " AND Id_Bodega_Nuevo=$idTipo";
            $tablaDestACT = 'Bodega_Nuevo';

            $documento .= ' (SELECT INF.Id_Inventario_Fisico_Nuevo AS ID,
                        "" AS Nombre_Origen,
                        (SELECT Nombre FROM Bodega_Nuevo WHERE Id_Bodega_Nuevo=INF.Id_Bodega_Nuevo) AS Destino,
                        "inventariofisico/inventario_final_pdf.php" AS Ruta,
                        "Inventario" AS Tipo,
                        CONCAT("INVF",INF.Id_Inventario_Fisico_Nuevo) AS Codigo,
                        INF.Fecha AS Fecha,
                        IFNULL(SUM(PIF.Segundo_Conteo), PIF.Cantidad_Auditada) AS Cantidad,
                        GROUP_CONCAT(PIF.Lote SEPARATOR " | ") AS Lote,
                        GROUP_CONCAT(PIF.Fecha_Vencimiento SEPARATOR " | ") AS Fecha_Vencimiento,
                        "" AS Id_Factura,
                        "" AS Codigo_Fact
                        FROM Producto_Doc_Inventario_Fisico PIF
                                        INNER JOIN Doc_Inventario_Fisico DIF ON PIF.Id_Doc_Inventario_Fisico = DIF.Id_Doc_Inventario_Fisico
                                        INNER JOIN Inventario_Fisico_Nuevo INF ON DIF.Id_Inventario_Fisico_Nuevo=INF.Id_Inventario_Fisico_Nuevo';

            $group .= 'GROUP BY PIF.Id_Doc_Inventario_Fisico';

            $origen_acta_recepcion_rem = $this->getOrigenActa('Acta_Recepcion_Remision');

            $sql_acta_recepcion_bodegas = "SELECT AR.Id_Acta_Recepcion_Remision as ID,
        $origen_acta_recepcion_rem as Nombre_Origen,
        (SELECT Nombre FROM $tablaDestACT WHERE Id_$tablaDestACT = $idTipo) as Destino,
        'actarecepcionbodegaver' as Ruta, 'Entrada' as Tipo, AR.Codigo, AR.Fecha as Fecha, PAR.Cantidad, PAR.Lote, PAR.Fecha_Vencimiento, '' as Id_Factura, '' as Codigo_Fact
        FROM Producto_Acta_Recepcion_Remision PAR
        INNER JOIN Acta_Recepcion_Remision AR
        ON PAR.Id_Acta_Recepcion_Remision = AR.Id_Acta_Recepcion_Remision
        WHERE PAR.Id_Producto = $producto $condicion2Acta AND (AR.Fecha BETWEEN  '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59')";

            $query_notas_creditos .= ' UNION ALL (SELECT NC.Id_Nota_Credito AS ID, R.Nombre_Destino AS Nombre_Origen, R.Nombre_Origen as Destino, "notascreditover" AS Ruta, "Entrada" AS Tipo, NC.Codigo, NC.Fecha, SUM(PNC.Cantidad), GROUP_CONCAT(PNC.Lote SEPARATOR " | ") as Lote, GROUP_CONCAT(PNC.Fecha_Vencimiento SEPARATOR " | ") as Fecha_Vencimiento, NC.Id_Factura, (SELECT Codigo FROM Factura_Venta WHERE Id_Factura_Venta = NC.Id_Factura) AS Codigo_Fact
        FROM Producto_Nota_Credito PNC
        INNER JOIN Nota_Credito NC ON NC.Id_Nota_Credito = PNC.Id_Nota_Credito
        INNER JOIN (SELECT RE.Nombre_Destino, RE.Nombre_Origen, RE.Id_Factura FROM Producto_Remision PR INNER JOIN Remision RE ON RE.Id_Remision = PR.Id_Remision WHERE PR.Id_Producto=' . $producto . ' AND RE.Id_Origen=' . $idTipo . ' GROUP BY RE.Id_Factura) R ON R.Id_Factura = NC.Id_Factura
        WHERE NC.Estado IN ("Acomodada","Anulada") AND PNC.Id_Producto = ' . $producto . ' AND (NC.Fecha BETWEEN  "' . $fecha_inicio . ' 00:00:00" AND "' . $fecha_fin . ' 23:59:59") GROUP BY ID)';

            $query_devoluciones_compras .= ' UNION ALL (
                                    SELECT D.Id_Devolucion_Compra AS ID, 
                                    (SELECT Nombre FROM Bodega_Nuevo WHERE Id_Bodega_Nuevo = D.Id_Bodega_Nuevo) AS Nombre_Origen, 
                                    (SELECT social_reason FROM third_parties WHERE Id_Proveedor = D.Id_Proveedor AND is_supplier = 1) as Destino, 
                                    "verdetalledevolucion" AS Ruta, "Salida" AS Tipo, D.Codigo, AD.Fecha, PDC.Cantidad, PDC.Lote, 
                                    PDC.Fecha_Vencimiento, "", "" AS Codigo_Fact 
                                    FROM Producto_Devolucion_Compra PDC 
                                    INNER JOIN Devolucion_Compra D ON PDC.Id_Devolucion_Compra = D.Id_Devolucion_Compra
                                    INNER JOIN Actividad_Devolucion_Compra AD ON PDC.Id_Devolucion_Compra = AD.Id_Devolucion_Compra AND AD.Estado ="Fase 2"
                                    
                                    WHERE PDC.Id_Producto = ' . $producto . $condicion5Acta . ' AND D.Estado = "Anulada" AND (D.Fecha BETWEEN  "' . $fecha_inicio . ' 00:00:00" AND "' . $fecha_fin . ' 23:59:59"))

                                UNION ALL (
                                    SELECT D.Id_Devolucion_Compra AS ID,
                                    (CASE D.Estado WHEN "Anulada" THEN (SELECT social_reason FROM third_parties WHERE Id_Proveedor = D.Id_Proveedor AND is_supplier = 1) 
                                        ELSE (SELECT Nombre FROM Bodega_Nuevo WHERE Id_Bodega_Nuevo = D.Id_Bodega_Nuevo) END) AS Nombre_Origen, 
                                    (CASE D.Estado WHEN "Anulada" THEN (SELECT Nombre FROM Bodega_Nuevo WHERE Id_Bodega_Nuevo = D.Id_Bodega_Nuevo) 
                                        ELSE (SELECT social_reason FROM third_parties WHERE Id_Proveedor = D.Id_Proveedor AND is_supplier = 1) END) as Destino, 
                                    "verdetalledevolucion" AS Ruta, (CASE D.Estado WHEN "Anulada" THEN "Entrada" ELSE "Salida" END) AS Tipo, CONCAT(D.Codigo,IF(D.Estado="Anulada"," (Anulada)","")) AS Codigo,
                                    AD.Fecha, PDC.Cantidad, PDC.Lote, PDC.Fecha_Vencimiento, "", "" AS Codigo_Fact 
                                    FROM Producto_Devolucion_Compra PDC 
                                    INNER JOIN Devolucion_Compra D ON PDC.Id_Devolucion_Compra = D.Id_Devolucion_Compra
                                    INNER JOIN Actividad_Devolucion_Compra AD ON PDC.Id_Devolucion_Compra = AD.Id_Devolucion_Compra AND AD.Estado ="Fase 2"
                                    
                                    WHERE PDC.Id_Producto = ' . $producto . $condicion5Acta . ' AND (D.Fecha BETWEEN  "' . $fecha_inicio . ' 00:00:00" AND "' . $fecha_fin . ' 23:59:59"))
        ';
            $query_actas_internacionales .= ' UNION ALL (SELECT NP.Id_Nacionalizacion_Parcial AS ID, (SELECT social_reason FROM third_parties WHERE Id_Proveedor = ARI.Id_Proveedor AND is_supplier = 1) AS Nombre_Origen, (SELECT Nombre FROM Bodega_Nuevo WHERE Id_Bodega_Nuevo = ARI.Id_Bodega) as Destino, "parcialactainternacionalver" AS Ruta, "Entrada" AS Tipo, NP.Codigo, NP.Fecha_Registro, PNP.Cantidad, (SELECT Lote FROM Producto_Acta_Recepcion_Internacional WHERE Id_Producto_Acta_Recepcion_Internacional = PNP.Id_Producto_Acta_Recepcion_Internacional) AS Lote, (SELECT Fecha_Vencimiento FROM Producto_Acta_Recepcion_Internacional WHERE Id_Producto_Acta_Recepcion_Internacional = PNP.Id_Producto_Acta_Recepcion_Internacional) AS Fecha_Vencimiento, "", "" AS Codigo_Fact FROM Producto_Nacionalizacion_Parcial PNP INNER JOIN Nacionalizacion_Parcial NP ON NP.Id_Nacionalizacion_Parcial = PNP.Id_Nacionalizacion_Parcial INNER JOIN Acta_Recepcion_Internacional ARI ON NP.Id_Acta_Recepcion_Internacional = ARI.Id_Acta_Recepcion_Internacional WHERE PNP.Id_Producto = ' . $producto . $condicion5Acta . ' AND (NP.Fecha_Registro BETWEEN  "' . $fecha_inicio . ' 00:00:00" AND "' . $fecha_fin . ' 23:59:59"))';
        } else {


            $query_comprobar = '
    SELECT
        IFPN.Id_Inventario_Fisico_Punto_Nuevo AS Id,
        IFPN.Fecha,
        INV.Lote
    FROM Producto_Doc_Inventario_Fisico_Punto PIF
    INNER JOIN Doc_Inventario_Fisico_Punto INF ON PIF.Id_Doc_Inventario_Fisico_Punto = INF.Id_Doc_Inventario_Fisico_Punto
    INNER JOIN Inventario_Fisico_Punto_Nuevo IFPN ON INF.Id_Inventario_Fisico_Punto_Nuevo = IFPN.Id_Inventario_Fisico_Punto_Nuevo
    INNER JOIN Inventario_Nuevo INV ON INV.Id_Inventario_Nuevo = PIF.Id_Inventario_Nuevo  -- Ajusta la relación aquí
    WHERE PIF.Id_Producto = ' . $producto . '
          AND PIF.Lote = INV.Lote
          AND IFPN.Id_Punto_Dispensacion = ' . $idTipo . '
          AND IFPN.Fecha BETWEEN "' . $fecha_inicio . ' 00:00:00" AND "' . $fecha_fin . ' 23:59:59"
    GROUP BY PIF.Id_Producto, IFPN.Fecha, INV.Lote
';

            // Ejecuta la consulta
            $oCon = new consulta();
            $oCon->setQuery($query_comprobar);
            $oCon->setTipo('multiple');
            $comprobacion = $oCon->getData();
            unset($oCon);



            if (count($comprobacion) > 0) {
                foreach ($comprobacion as $value) {

                    $query_invs = 'SELECT GROUP_CONCAT(INF.Id_Inventario_Fisico_Punto_Nuevo) AS Ids
                             FROM Inventario_Fisico_Punto_Nuevo INF
                             WHERE INF.Id_Punto_Dispensacion = ' . $idTipo
                        . ' AND INF.Fecha = "' . $value["Fecha_Fin"] . '"';

                    $oCon = new consulta();
                    $oCon->setQuery($query_invs);
                    $result = $oCon->getData();
                    $ids_inv .= $result['Ids'] . ",";
                    unset($oCon);
                }

                $ids_inv = trim($ids_inv, ",");
            } else {
                $ids_inv = '0';
            }

            $condicion .= " AND R.Id_Origen=$idTipo AND R.Tipo_Origen='Punto_Dispensacion'";
            $condicion3 .= " AND AI.Id_Origen_Destino=$idTipo AND AI.Origen_Destino='Punto'";
            $condicion2 .= " AND AR.Id_Punto_Dispensacion=$idTipo";
            // $condicion4 .= " AND INF.Bodega=''";
            $condicion4 .= " AND INF.Id_Punto_Dispensacion= ''";
            $condicion5 .= " AND Id_Punto_Dispensacion=$idTipo";
            $ruta = 'actarecepcionremisionver';
            $tabla = 'Acta_Recepcion_Remision';
            $tablaDest = 'Punto_Dispensacion';
            $attrFecha = 'Fecha';

            $documento = '';
            $group = '';

            $tablaDestACT = 'Punto_Dispensacion';
            $condicion2Acta = " AND AR.Id_Punto_Dispensacion=$idTipo";

            //10-08-2021 roberth morales
            $documento .= ' (SELECT INF.Id_Inventario_Fisico_Punto_Nuevo AS ID,
                        "" AS Nombre_Origen,
                        (SELECT Nombre FROM Punto_Dispensacion WHERE Id_Punto_Dispensacion=INF.Id_Punto_Dispensacion) AS Destino,
                        "inventariofisico/inventario_final_pdf.php" AS Ruta,
                        "Inventario" AS Tipo,
                        CONCAT("INVF",INF.Id_Inventario_Fisico_Punto_Nuevo) AS Codigo,
                        INF.Fecha AS Fecha,
                        IFNULL(SUM(PIF.Segundo_Conteo), PIF.Cantidad_Auditada) AS Cantidad,
                        GROUP_CONCAT(PIF.Lote SEPARATOR " | ") AS Lote,
                        GROUP_CONCAT(PIF.Fecha_Vencimiento SEPARATOR " | ") AS Fecha_Vencimiento,
                        "" AS Id_Factura,
                        "" AS Codigo_Fact FROM Producto_Doc_Inventario_Fisico_Punto PIF
                        INNER JOIN Doc_Inventario_Fisico_Punto DIF ON PIF.Id_Doc_Inventario_Fisico_Punto = DIF.Id_Doc_Inventario_Fisico_Punto
                        INNER JOIN Inventario_Fisico_Punto_Nuevo INF ON DIF.Id_Inventario_Fisico_Punto_Nuevo = INF.Id_Inventario_Fisico_Punto_Nuevo';

            $group .= 'GROUP BY PIF.Id_Doc_Inventario_Fisico_Punto';
            //10-08-2021 roberth morales

            //(SELECT Nombre FROM Bodega_Nuevo WHERE Id_Bodega_Nuevo=INF.Id_Bodega_Nuevo) AS Destino,
            // $condicion2Acta .= " AND AR.Id_Bodega_Nuevo=$idTipo";
            // $query_dispensaciones - $sql_acta_recepcion_bodegas

            $origen_acta_recepcion = $this->getOrigenActa('Acta_Recepcion');

            $sql_acta_recepcion_bodegas =
                "SELECT AR.Id_Acta_Recepcion as ID, $origen_acta_recepcion as Nombre_Origen, (SELECT Nombre FROM $tablaDest WHERE Id_$tablaDest = $idTipo) as Destino, 'actarecepcionvernuevo' as Ruta, 'Entrada' as Tipo, AR.Codigo, AC.Fecha as Fecha, PAR.Cantidad, PAR.Lote, PAR.Fecha_Vencimiento, '' as Id_Factura, '' as Codigo_Fact
        FROM Producto_Acta_Recepcion PAR
        INNER JOIN Acta_Recepcion AR ON PAR.Id_Acta_Recepcion = AR.Id_Acta_Recepcion
        INNER JOIN Actividad_Orden_Compra AC ON PAR.Id_Acta_Recepcion = AC.Id_Acta_Recepcion_Compra 
        WHERE PAR.Id_Producto = $producto$condicion2 AND AC.Estado = 'Acomodada' AND (AC.Fecha BETWEEN  '$fecha_inicio' AND '$fecha_fin 23:59:59')";


            $query_invents =
                "SELECT INF.Id_Inventario_Fisico_Punto_Nuevo AS ID, ' ' AS Nombre_Origen,
    (SELECT Nombre FROM Punto_Dispensacion
                WHERE Id_Punto_Dispensacion=INF.Id_Punto_Dispensacion) AS Destino,
                'inventario_fisico_puntos/descarga_pdf.php' AS Ruta,
                'Inventario' AS Tipo,
    CONCAT('INVF',INF.Id_Inventario_Fisico_Punto_Nuevo) AS Codigo,
    INF.Fecha AS Fecha,
    IFNULL(SUM(PIF.Segundo_Conteo), PIF.Cantidad_Auditada) AS Cantidad,
    GROUP_CONCAT(PIF.Lote SEPARATOR ' | ') AS Lote,
    GROUP_CONCAT(PIF.Fecha_Vencimiento SEPARATOR ' | ') AS Fecha_Vencimiento,
    '' AS Id_Factura,
    '' AS Codigo_Fact
    
    FROM Producto_Doc_Inventario_Fisico_Punto PIF
                    INNER JOIN Doc_Inventario_Fisico_Punto DIF ON PIF.Id_Doc_Inventario_Fisico_Punto = DIF.Id_Doc_Inventario_Fisico_Punto
                    INNER JOIN Inventario_Fisico_Punto_Nuevo INF ON DIF.Id_Inventario_Fisico_Punto_Nuevo = INF.Id_Inventario_Fisico_Punto_Nuevo
    
    WHERE PIF.Id_Producto =  $producto
        AND INF.Id_Punto_Dispensacion = $idTipo
        AND INF.Fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'
        GROUP BY PIF.Id_Producto, INF.Fecha";

            $query_invents2 =
                "SELECT
                INF.Id_Inventario_Fisico_Punto_Nuevo AS ID,
                '' AS Nombre_Origen,
                  (SELECT Nombre FROM Punto_Dispensacion WHERE Id_Punto_Dispensacion=INF.Id_Punto_Dispensacion) AS Destino,
                  'inventario_fisico_puntos/descarga_pdf.php' AS Ruta,
                   'Inventario' AS Tipo,
                   CONCAT('INVF',INF.Id_Inventario_Fisico_Punto_Nuevo) AS Codigo,
                  INF.Fecha AS Fecha,
                  0 AS Cantidad,
                  '' AS Lote,
                  '' AS Fecha_Vencimiento,
                  '' AS Id_Factura,
                  '' AS Codigo_Fact
                  FROM Inventario_Fisico_Punto_Nuevo INF
                  WHERE INF.Id_Inventario_Fisico_Punto_Nuevo NOT IN ($ids_inv)
                                  AND INF.Id_Punto_Dispensacion =  $idTipo
                     AND INF.Fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'
                     GROUP BY INF.Fecha";


            $query_dis =
                "SELECT   D.Id_Dispensacion AS ID,
                    (SELECT Nombre FROM Punto_Dispensacion WHERE Id_Punto_Dispensacion = E.Id_Punto_Dispensacion) AS Nombre_Origen,
                    (SELECT CONCAT(Primer_Nombre,' ', Primer_Apellido,' (',Id_Paciente,') ') FROM Paciente WHERE Id_Paciente = D.Numero_Documento) AS Destino,
                    'dispensacion' AS Ruta,
                    'Salida' AS Tipo,
                    IF(D.Estado_Dispensacion='Anulada', CONCAT(D.Codigo, ' (Anulada)'), D.Codigo )AS Codigo,
                    IFNULL(
                        (SELECT PDP.Timestamp FROM Producto_Dispensacion_Pendiente PDP WHERE PDP.Id_Producto_Dispensacion = PD.Id_Producto_Dispensacion LIMIT 1),
                            IFNULL((SELECT PD2.Fecha_Carga FROM Producto_Dispensacion PD2 WHERE PD2.Id_Producto_Dispensacion = PD.Id_Producto_Dispensacion LIMIT 1),
                        D.Fecha_Actual)) AS Fecha,
                    PD.Cantidad_Entregada AS Cantidad,
                    PD.Lote,
                    '' AS Fecha_Vencimiento,
                    '' AS Id_Factura,
                    '' AS Codigo_Fact
                    FROM
                    Producto_Dispensacion PD
                    INNER JOIN
                    Dispensacion D ON PD.Id_Dispensacion = D.Id_Dispensacion
                    INNER JOIN
                    Inventario_Nuevo I ON PD.Id_Inventario_Nuevo = I.Id_Inventario_Nuevo
                    LEFT JOIN Estiba E on E.Id_Estiba=I.Id_Estiba
                    WHERE
                        PD.Id_Producto =  $producto
                        AND  PD.Cantidad_Entregada!=0
                        AND E.Id_Punto_Dispensacion = $idTipo
                        HAVING Fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";

            $query_dis_anuladas = " SELECT D.Id_Dispensacion AS ID,
                ('') AS Nombre_Origen,
                (SELECT Nombre FROM Punto_Dispensacion WHERE Id_Punto_Dispensacion = E.Id_Punto_Dispensacion ) AS Destino,
                'dispensacion' AS Ruta,
                'Entrada' AS Tipo,
                CONCAT(D.Codigo, ' (Anulada)') as Codigo,
                AD.Fecha,
                PD.Cantidad_Entregada AS Cantidad,
                PD.Lote,
                '' AS Fecha_Vencimiento,
                '' AS Id_Factura,
                '' AS Codigo_Fact
                FROM
                Producto_Dispensacion PD
                INNER JOIN Dispensacion D ON PD.Id_Dispensacion = D.Id_Dispensacion
                INNER JOIN Inventario_Nuevo I ON PD.Id_Inventario_Nuevo = I.Id_Inventario_Nuevo
                INNER JOIN Actividades_Dispensacion AD ON AD.Id_Dispensacion = D.Id_Dispensacion and AD.Estado = 'Anulada'
                INNER JOIN Estiba E ON E.Id_Estiba = I.Id_Estiba
                WHERE PD.Id_Producto = $producto
                    AND PD.Cantidad_Entregada!=0
                    AND E.Id_Punto_Dispensacion = $idTipo
                    AND   D.Estado_Dispensacion='Anulada'
                    HAVING Fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
            //

            $query_dispensaciones .=
                "UNION ALL($query_invents)
            UNION($query_invents2)
            UNION ALL ($query_dis)
            UNION ALL ($query_dis_anuladas)";
        }

        $condicion .= " AND R.Fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
        $condicion2 .= " AND AR.$attrFecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";

        $tipoOrigen = " AND DI.Id_Origen = $idTipo";
        $ultimo_dia_mes = date("Y-m", (mktime(0, 0, 0, date("m", strtotime($fecha_inicio)), 1, date("Y", strtotime($fecha_inicio))) - 1));
        $fechaConsulta = explode("-", $ultimo_dia_mes);
        $ano = $fechaConsulta[0];
        $mes = $fechaConsulta[1];
        $fechaConsulta = " AND YEAR(IV.Fecha_Documento) = $ano AND MONTH(IV.Fecha_Documento) = $mes";

        $query_inicial = "SELECT SUM(Cantidad) as Total
                        FROM Inventario_Valorizado IV
                        INNER JOIN Descripcion_Inventario_Valorizado DI ON IV.Id_Inventario_Valorizado = DI.Id_Inventario_Valorizado
                        WHERE DI.Id_Producto =  $producto $fechaConsulta $tipoOrigen  GROUP BY DI.Id_Producto";

        $oCon = new consulta();
        $oCon->setQuery($query_inicial);
        $res = $oCon->getData();
        unset($oCon);

        if (isset($res["Total"])) {
            $acum = $total = (int) $res["Total"];
        } else {
            $acum = $total = 0;
        }

        $query_remisiones = "SELECT R.Id_Remision as ID,
            R.Nombre_Origen,
            (CASE
                WHEN R.Tipo='Cliente' THEN CONCAT(R.Id_Destino,' - ',R.Nombre_Destino)
                WHEN R.Tipo='Interna' THEN R.Nombre_Destino
            END) as Destino,
            'remision' as Ruta,
            'Salida' as Tipo,
            CONCAT(R.Codigo,' - (', R.Estado,')') AS Codigo,
            R.Fecha as Fecha,
            PR.Cantidad,
            PR.Lote,
            PR.Fecha_Vencimiento,
            F.Id_Factura_Venta as Id_Factura,
            F.Codigo as Codigo_Fact

            FROM Producto_Remision PR
            INNER JOIN Remision R ON R.Id_Remision = PR.Id_Remision
            LEFT JOIN Factura_Venta F ON F.Id_Factura_Venta = R.Id_Factura
            WHERE R.Estado = 'Anulada' AND PR.Id_Producto = $producto $condicion";

        $query = '(' . $query_remisiones . ')
    UNION ALL (
            SELECT R.Id_Remision as ID,
            (
                CASE R.Estado
                    WHEN "Anulada" THEN ""
                    ELSE
                        R.Nombre_Origen
                END
            ) AS Nombre_Origen,
            (
                CASE R.Estado
                    WHEN "Anulada" THEN R.Nombre_Origen
                    ELSE
                        (CASE
                            WHEN R.Tipo="Cliente" THEN CONCAT(R.Id_Destino," - ",R.Nombre_Destino)
                            WHEN R.Tipo="Interna" THEN R.Nombre_Destino
                        END)
                END
            ) as Destino,
            "remision" as Ruta,
            (
                CASE R.Estado
                    WHEN "Anulada" THEN "Entrada"
                    ELSE
                        "Salida"
                END
            ) as Tipo,
            CONCAT(R.Codigo," - (", R.Estado,")") AS Codigo,
            (
                CASE R.Estado
                    WHEN "Anulada" THEN (SELECT MAX(Fecha) FROM Actividad_Remision WHERE Id_Remision = R.Id_Remision)
                    ELSE
                        R.Fecha
                END
            ) as Fecha,
            PR.Cantidad,
            PR.Lote,
            PR.Fecha_Vencimiento,
            F.Id_Factura_Venta as Id_Factura,
            F.Codigo as Codigo_Fact
            FROM Producto_Remision PR
            INNER JOIN Remision R ON R.Id_Remision = PR.Id_Remision
            LEFT JOIN Factura_Venta F ON F.Id_Factura_Venta = R.Id_Factura
            WHERE PR.Id_Producto = ' . $producto . $condicion . ')

    UNION ALL (
        SELECT AI.Id_Ajuste_Individual as ID,
        IF(AI.Tipo="Entrada","",(SELECT Nombre FROM ' . $tablaDest . ' WHERE Id_' . $tablaDest . '=' . $idTipo . ')) AS Nombre_Origen,
        IF(AI.Tipo="Entrada",(SELECT Nombre FROM ' . $tablaDest . ' WHERE Id_' . $tablaDest . '=' . $idTipo . '),"") as Destino,
        "ajustesinventariover" as Ruta,
        AI.Tipo,
        CONCAT(AI.Codigo," (Anulada)") AS Codigo,
        IFNULL(AC.Fecha_Creacion, AI.Fecha) as Fecha,
        PAI.Cantidad,
        PAI.Lote,
        PAI.Fecha_Vencimiento,
        "" as Id_Factura,
        "" as Codigo_Fact
        FROM Producto_Ajuste_Individual PAI
        INNER JOIN Ajuste_Individual AI ON AI.Id_Ajuste_Individual = PAI.Id_Ajuste_Individual
        INNER JOIN Actividad_Ajuste_Individual AC on AC.Id_Ajuste_Individual = AI.Id_Ajuste_Individual and (AC.Estado ="Acomodada" or AC.Estado ="Aprobacion" )
        WHERE AI.Estado = "Anulada" AND PAI.Id_Producto = ' . $producto . $condicion3 . ' AND (AI.Fecha BETWEEN  "' . $fecha_inicio . ' 00:00:00" AND "' . $fecha_fin . ' 23:59:59")
        Group By PAI.Id_Producto_Ajuste_Individual
        )

    UNION ALL (
            SELECT AI.Id_Ajuste_Individual as ID,
            (
                CASE AI.Estado
                    WHEN "Anulada" THEN IF(AI.Tipo="Entrada",(SELECT Nombre FROM ' . $tablaDest . ' WHERE Id_' . $tablaDest . '=' . $idTipo . '),"")
                    ELSE
                        IF(AI.Tipo="Entrada","",(SELECT Nombre FROM ' . $tablaDest . ' WHERE Id_' . $tablaDest . '=' . $idTipo . '))
                END
            ) AS Nombre_Origen,
            (
                CASE AI.Estado
                    WHEN "Anulada" THEN IF(AI.Tipo="Entrada","",(SELECT Nombre FROM ' . $tablaDest . ' WHERE Id_' . $tablaDest . '=' . $idTipo . '))
                    ELSE
                        IF(AI.Tipo="Entrada",(SELECT Nombre FROM ' . $tablaDest . ' WHERE Id_' . $tablaDest . '=' . $idTipo . '),"")
                END
            ) as Destino,
            "ajustesinventariover" as Ruta,
            (
                CASE AI.Estado
                    WHEN "Anulada" THEN IF(AI.Tipo="Entrada","Salida","Entrada")
                    ELSE
                        AI.Tipo
                END
            ) AS Tipo, AI.Codigo,
            AI.Fecha as Fecha, PAI.Cantidad, PAI.Lote, PAI.Fecha_Vencimiento, "" as Id_Factura, "" as Codigo_Fact
            FROM Producto_Ajuste_Individual PAI
            INNER JOIN Ajuste_Individual AI
            ON AI.Id_Ajuste_Individual = PAI.Id_Ajuste_Individual
            
            INNER JOIN Actividad_Ajuste_Individual AC on AC.Id_Ajuste_Individual = AI.Id_Ajuste_Individual -- and (AC.Estado ="Acomodada" or AC.Estado ="Aprobacion" )
            WHERE PAI.Id_Producto = ' . $producto . $condicion3 . ' AND (AI.Fecha BETWEEN  "' . $fecha_inicio . ' 00:00:00" AND "' . $fecha_fin . ' 23:59:59")
            AND( (AI.Tipo="Entrada" and AC.Estado ="Acomodada" )
            OR (AI.Origen_Destino = "Bodega" AND AI.Tipo="Salida" and AC.Estado ="Aprobacion" )
            OR (AI.Origen_Destino = "Punto" AND AI.Tipo="Salida" and AC.Estado ="Creacion" )
            )
            Group By PAI.Id_Producto_Ajuste_Individual)


    UNION ALL (
            SELECT AR.Id_' . $tabla . ' as ID, ' . $this->getOrigenActa($tabla) . ' as Nombre_Origen,
            (SELECT Nombre FROM ' . $tablaDestACT . ' WHERE Id_' . $tablaDestACT . '=' . $idTipo . ') as Destino,
            "' . $ruta . '" as Ruta,
            "Entrada" as Tipo,
            AR.Codigo, AR.' . $attrFecha . ' as Fecha,
            PAR.Cantidad,
            PAR.Lote,
            PAR.Fecha_Vencimiento,
            "" as Id_Factura,
            "" as Codigo_Fact
            FROM Producto_' . $tabla . ' PAR
            INNER JOIN ' . $tabla . ' AR ON PAR.Id_' . $tabla . ' = AR.Id_' . $tabla . '
            WHERE PAR.Id_Producto = ' . $producto . $condicion2 . ' AND AR.Estado = "Acomodada")

    UNION ALL(
        ' . $sql_acta_recepcion_bodegas . ' )

    UNION ALL 
        ' . $documento . '
        WHERE
        PIF.Id_Producto = ' . $producto . $condicion4 . '
        AND (INF.Fecha BETWEEN  "' . $fecha_inicio . ' 00:00:00"
        AND "' . $fecha_fin . ' 23:59:59")
        ' . $group . ')

        ' . $query_dispensaciones . $query_notas_creditos . $query_devoluciones_compras . $query_actas_internacionales . '
        ORDER BY Fecha ASC';



        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $resultados = $oCon->getData();
        unset($oCon);

        $saldo_actual = $this->getSaldoActualProducto($tipo, $idTipo, $producto);

        // Obtener las variables dinámicas de los productos
        list($productosConVariables, $variablesLabels) = $this->obtenerProductosConVariables([$producto]);

        // Verificar si la categoría tiene has_lote o has_expiration_date en true
        $productoInfo = Product::with('category')->find($producto);
        $hasLote = $productoInfo->category->has_lote;
        $hasExpirationDate = $productoInfo->category->has_expiration_date;

        $i = 0;
        $acum = 0; // Ensure that $acum is initialized

        foreach ($resultados as $res) {
            if ($res->Tipo == 'Entrada') {
                $acum += $res->Cantidad;
            } elseif ($res->Tipo == 'Salida') {
                $acum -= $res->Cantidad;
            } elseif ($res->Tipo == 'Inventario') {
                if ($i > 0 && isset($resultados[$i - 1]->Tipo) && $resultados[$i - 1]->Tipo != "Inventario") {
                    $acum = $res->Cantidad;
                } else {
                    $acum += $res->Cantidad;
                }
            }

            $resultados[$i]->Saldo = $acum;

            // Añadir las variables dinámicas y campos adicionales a los productos en el resultado
            foreach ($productosConVariables as $productoVar) {
                if (isset($res->Id_Producto) && $res->Id_Producto == $productoVar->Id_Producto) {
                    $res->variables = $productoVar->variables;
                    if ($hasLote || $hasExpirationDate) {
                        if ($hasLote) {
                            $res->Lote = $res->Lote;
                        }
                        if ($hasExpirationDate) {
                            $res->Fecha_Vencimiento = $res->Fecha_Vencimiento;
                        }
                    }
                }
            }

            $i++;
        }


        // Crear la salida final, excluyendo los campos Lote y Fecha_Vencimiento si no aplican
        $final = [
            'Productos' => array_map(function ($producto) use ($hasLote, $hasExpirationDate) {
                $data = (array) $producto;
                if (!$hasLote) {
                    unset($data['Lote']);
                }
                if (!$hasExpirationDate) {
                    unset($data['Fecha_Vencimiento']);
                }
                return $data;
            }, $resultados),
            'Inicial' => $total,
            'Saldo_Actual' => $saldo_actual,
            'Variables_Labels' => $variablesLabels
        ];

        return response()->json($final);
    }

    function getOrigenActa($tabla)
    {
        $string = '""';

        if ($tabla == 'Acta_Recepcion') {
            $string = "(SELECT first_name FROM third_parties WHERE id = AR.Id_Proveedor AND is_supplier = 1)";
        } elseif ($tabla == 'Acta_Recepcion_Remision') {
            $string = "(SELECT Nombre_Origen FROM Remision WHERE Id_Remision = AR.Id_Remision)";
        } elseif ($tabla == 'Nota_Credito') {
            $string = "(SELECT Nombre_Origen FROM Remision WHERE Id_Factura = NC.Id_Factura)";
        }

        return $string;
    }




    private function obtenerProductosConVariables($productosIds)
    {
        // Obtener productos con sus variables y etiquetas asociadas
        $productos = Product::whereIn('Id_Producto', $productosIds)
            ->with(['variableProducts.categoryVariable'])
            ->get();

        $variablesLabels = [];
        foreach ($productos as $producto) {
            $variables = [];
            foreach ($producto->variableProducts as $variableProduct) {
                $variables[$variableProduct->categoryVariable->label] = $variableProduct->valor;
                $variablesLabels[] = $variableProduct->categoryVariable->label;
            }
            $producto->variables = $variables;
        }

        return [$productos, $variablesLabels];
    }

    private function getSaldoInicial($producto, $idTipo, $tipo, $fecha_inicio)
    {
        $condiciones = [];

        if ($tipo == 'Bodega') {
            $condiciones[] = "E.Id_Bodega_Nuevo = $idTipo";
        } else {
            $condiciones[] = "E.Id_Punto_Dispensacion = $idTipo";
        }

        $condiciones[] = "DI.Id_Producto = $producto";

        $ultimo_dia_mes = date("Y-m", strtotime("-1 month", strtotime($fecha_inicio)));
        $fechaConsulta = explode("-", $ultimo_dia_mes);
        $ano = $fechaConsulta[0];
        $mes = $fechaConsulta[1];
        $fechaConsulta = "YEAR(IV.Fecha_Documento) = $ano AND MONTH(IV.Fecha_Documento) = $mes";

        $query_inicial = "SELECT SUM(DI.Cantidad) as Total
                          FROM Descripcion_Inventario_Valorizado DI
                          INNER JOIN Inventario_Valorizado IV ON IV.Id_Inventario_Valorizado = DI.Id_Inventario_Valorizado
                          INNER JOIN Inventario_Nuevo INV ON DI.Id_Inventario_Nuevo = INV.Id_Inventario_Nuevo
                          INNER JOIN Estiba E ON INV.Id_Estiba = E.Id_Estiba
                          WHERE DI.Id_Producto = $producto AND $fechaConsulta AND " . implode(" AND ", $condiciones) . "
                          GROUP BY DI.Id_Producto";

        $saldo_inicial = DB::select($query_inicial);

        return $saldo_inicial ? $saldo_inicial[0]->Total : 0;
    }

    function getSaldoActualProducto($tipo, $idTipo, $producto)
    {

        $idTipo = intval($idTipo);
        $producto = intval($producto);

        $cond_saldo_actual = '';
        if ($tipo == 'Bodega') {
            $cond_saldo_actual = "WHERE E.Id_Bodega_Nuevo = $idTipo AND INV.Id_Producto = $producto";
        } else {
            $cond_saldo_actual = "WHERE E.Id_Punto_Dispensacion = $idTipo AND INV.Id_Producto = $producto";
        }

        $q = "SELECT SUM(INV.Cantidad) AS Cantidad, GROUP_CONCAT(INV.Id_Inventario_Nuevo) as inventarios
          FROM Inventario_Nuevo INV
          INNER JOIN Estiba E ON INV.Id_Estiba = E.Id_Estiba
          $cond_saldo_actual";

        $oCon = new consulta();
        $oCon->setQuery($q);

        $saldo_actual = $oCon->getData();
        unset($oCon);

        return $saldo_actual;
    }


    public function descargaKardexd(Request $request)
    {
        $consultaKardexdResponse = $this->consultaKardexd($request);
        $data = json_decode($consultaKardexdResponse->getContent(), true);

        $reportData = [
            'resultados' => $data['Productos'],
            'total' => $data['Saldo_Actual']['Cantidad'],
            'variables_labels' => $data['Variables_Labels']
        ];

        return Excel::download(new KardexReportExport($reportData), 'Reporte-Kardex.xlsx');
    }

    public function detalleFacturaVenta(Request $request)
    {
        $id = $request->input('id');

        $facturaVenta = FacturaVenta::with(['thirdParty', 'thirdParty.municipality'])
            ->where('Id_Factura_Venta', $id)
            ->first();

        if (!$facturaVenta) {
            return response()->json(['error' => 'Factura no encontrada'], 404);
        }

        $productos = ProductoFacturaVenta::with(['producto', 'inventarioNuevo'])
            ->where('Id_Factura_Venta', $id)
            ->get();

        if ($productos->isEmpty()) {
            $productos = ProductoFacturaVenta::with(['producto'])
                ->where('Id_Factura_Venta', $id)
                ->get();
        }

        // Obtener las variables dinámicas de los productos
        $productosIds = $productos->pluck('Id_Producto')->toArray();
        list($productosConVariables, $variablesLabels) = $this->obtenerProductosConVariables($productosIds);

        $productos = Product::whereIn('Id_Producto', $productosIds)
            ->with(['variableProducts.categoryVariable'])
            ->get();

        // Añadir las variables dinámicas y campos adicionales a los productos en el resultado
        foreach ($productos as $producto) {
            $producto->variables = []; // Inicializar la propiedad variables
            $productoInfo = Product::with('category')->find($producto->Id_Producto);
            $hasLote = $productoInfo->category->has_lote;
            $hasExpirationDate = $productoInfo->category->has_expiration_date;

            foreach ($productosConVariables as $productoVar) {
                if ($producto->Id_Producto == $productoVar->Id_Producto) {
                    $producto->variables = $productoVar->variables;
                    if ($hasLote) {
                        $producto->Lote = $producto->Lote ?? null; // Asegurarse de que Lote esté definido
                    }
                    if ($hasExpirationDate) {
                        $producto->Fecha_Vencimiento = $producto->Fecha_Vencimiento ?? null; // Asegurarse de que Fecha_Vencimiento esté definido
                    }
                    break;
                }
            }
        }

        $totalNotasCredito = ProductoFacturaVenta::where('Id_Factura_Venta', $id)
            ->sum(DB::raw('Descuento'));

        $totalFactura = ProductoFacturaVenta::where('Id_Factura_Venta', $id)
            ->sum(DB::raw('Cantidad * Precio_Venta * (1 - (Descuento / 100))'));

        $totalImpuesto = $productos->sum(function ($producto) {
            $subtotal = $producto->Cantidad * $producto->Precio_Venta * (1 - ($producto->Descuento / 100));
            $impuesto = floatval(str_replace("%", "", $producto->Impuesto)) / 100;
            return $subtotal * $impuesto;
        });

        $totalFacturaConImpuesto = $totalFactura + $totalImpuesto;
        $numero = number_format($totalFacturaConImpuesto, 2, '.', '');
        $letras = NumeroALetras::convertir($numero) . " PESOS MCTE.";

        $resolucion = Resolucion::find($facturaVenta->Id_Resolucion);

        $actividades = [];
        $actividadesNota = [];

        $remision = Remision::where('Id_Factura', $id)->first();
        if ($remision) {
            $actividades = $remision->actividades()
                ->whereIn('Estado', ['Facturada', 'Anulada'])
                ->orderByRaw('CASE
            WHEN Estado = "Creacion" THEN 1
            WHEN Estado = "Alistamiento" THEN 2
            WHEN Estado = "Edicion" THEN 2
            WHEN Estado = "Fase 1" THEN 2
            WHEN Estado = "Fase 2" THEN 3
            WHEN Estado = "Enviada" THEN 4
            WHEN Estado = "Facturada" THEN 5
            WHEN Estado = "Recibida" THEN 5
            WHEN Estado = "Anulada" THEN 2
        END ASC, Fecha ASC')
                ->get()
                ->map(function ($actividad) {
                    $funcionario = Person::find($actividad->Identificacion_Funcionario);
                    return [
                        'Id_Actividad_Remision' => $actividad->Id_Actividad_Remision,
                        'Id_Remision' => $actividad->Id_Remision,
                        'Identificacion_Funcionario' => $actividad->Identificacion_Funcionario,
                        'Fecha' => $actividad->Fecha,
                        'Detalles' => $actividad->Detalles,
                        'Estado' => $actividad->Estado,
                        'Imagen' => $actividad->funcionario->Imagen,
                        'Funcionario' => $actividad->funcionario->Nombres . ' ' . $actividad->funcionario->Apellidos,
                        'Estado2' => $actividad->Estado2,
                        'full_name' => $actividad->funcionario->Nombres . ' ' . $actividad->funcionario->Apellidos,
                        'image' => $actividad->funcionario->Imagen,
                        'description' => $actividad->Detalles,
                        'date' => $actividad->Fecha,
                        'title' => $funcionario->first_name . ' ' . $funcionario->first_surname, // Añadido el campo title con nombre y apellido del funcionario
                    ];
                });

            $actividadesNota = NotaCredito::with(['funcionario'])
                ->where('Id_Factura', $id)
                ->get()
                ->map(function ($nota) {
                    $funcionario = Person::find($nota->Identificacion_Funcionario);
                    return [
                        'Id_Actividad_Remision' => '',
                        'Id_Remision' => '',
                        'Identificacion_Funcionario' => $nota->Identificacion_Funcionario,
                        'Fecha' => $nota->Fecha,
                        'Detalles' => 'Se realizo un Nota credito de la factura, el codigo de esta nota es ' . $nota->Codigo,
                        'Estado' => 'Creacion',
                        'Imagen' => $nota->funcionario->Imagen,
                        'Funcionario' => $nota->funcionario->Nombres . ' ' . $nota->funcionario->Apellidos,
                        'Estado2' => '',
                        'full_name' => $nota->funcionario->Nombres . ' ' . $nota->funcionario->Apellidos,
                        'image' => $nota->funcionario->Imagen,
                        'description' => 'Se realizo un Nota credito de la factura, el codigo de esta nota es ' . $nota->Codigo,
                        'date' => $nota->Fecha,
                        'title' => $funcionario->first_name . ' ' . $funcionario->first_surname, // Añadido el campo title con nombre y apellido del funcionario
                    ];
                });
        }

        $actividades = array_merge($actividades->toArray(), $actividadesNota->toArray());

        // Crear la salida final, excluyendo los campos Lote y Fecha_Vencimiento si no aplican
        $productosFinal = $productos->map(function ($producto) {
            $data = $producto->toArray();
            $productoInfo = Product::with('category')->find($producto['Id_Producto']);
            $hasLote = $productoInfo->category->has_lote;
            $hasExpirationDate = $productoInfo->category->has_expiration_date;

            if (!$hasLote) {
                unset($data['Lote']);
            }
            if (!$hasExpirationDate) {
                unset($data['Fecha_Vencimiento']);
            }
            return $data;
        });

        return response()->json([
            'Datos' => [
                'Fecha' => $facturaVenta->Fecha_Documento,
                'Cufe' => $facturaVenta->Cufe,
                'Id_Resolucion' => $facturaVenta->Id_Resolucion,
                'Observacion' => $facturaVenta->Observacion_Factura_Venta,
                'Codigo' => $facturaVenta->Codigo,
                'Codigo_Qr' => $facturaVenta->Codigo_Qr,
                'Condicion_Pago' => $facturaVenta->Condicion_Pago == 1 ? 'CONTADO' : $facturaVenta->Condicion_Pago,
                'Fecha_Pago' => $facturaVenta->Fecha_Pago,
                'IdCliente' => $facturaVenta->thirdParty->id,
                'NombreCliente' => $facturaVenta->thirdParty->first_name,
                'DireccionCliente' => $facturaVenta->thirdParty->address_one,
                'CiudadCliente' => $facturaVenta->thirdParty->municipality ? $facturaVenta->thirdParty->municipality->name : null,
                'CreditoCliente' => $facturaVenta->thirdParty->Credito,
                'Telefono' => $facturaVenta->thirdParty->cell_phone,
                'Id_Factura_Venta' => $facturaVenta->Id_Factura_Venta,
                'Observaciones2' => Remision::where('Id_Factura', $facturaVenta->Id_Factura_Venta)->orderBy('Id_Remision', 'asc')->value('Observaciones')
            ],
            'activities' => $actividades,
            'Productos' => $productosFinal,
            'NotasCredito' => $actividadesNota,
            'TotalNc' => [
                'TotalNC' => $totalNotasCredito
            ],
            'TotalFc' => [
                'TotalFac' => $totalFactura,
                'Iva' => $totalImpuesto
            ],
            'letra' => $letras,
            'resolucion' => $resolucion,
        ]);
    }



    public function movimientosFacturaVentaPdf(Request $request)
    {

        $id_registro = $request->get('id_registro', '');
        $id_funcionario_imprime = Auth::id();
        $tipo_valor = $request->get('tipo_valor', '');
        $titulo = $tipo_valor != '' ? "CONTABILIZACIÓN NIIF" : "CONTABILIZACIÓN PCGA";

        $queryObj = new QueryBaseDatos();

        function fecha($str)
        {
            $parts = explode(" ", $str);
            $date = explode("-", $parts[0]);
            return $date[2] . "/" . $date[1] . "/" . $date[0];
        }

        $oItem = new complex('Factura_Venta', 'Id_Factura_Venta', $id_registro);
        $datos = $oItem->getData();
        unset($oItem);

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
        MC.Debe,
        MC.Haber,
        MC.Debe_Niif,
        MC.Haber_Niif,
        (CASE
            WHEN MC.Tipo_Nit = "Cliente" THEN (SELECT Nombre FROM third_parties WHERE is_client = 1 AND id = MC.Nit)
            WHEN MC.Tipo_Nit = "Proveedor" THEN (SELECT Nombre FROM third_parties WHERE is_supplier = 1 AND id = MC.Nit)
            WHEN MC.Tipo_Nit = "Funcionario" THEN (SELECT CONCAT_WS(" ", first_name, first_surname) FROM people WHERE identifier = MC.Nit)
        END) AS Nombre_Cliente,
        "Factura Venta" AS Registro
    FROM Movimiento_Contable MC
    INNER JOIN Plan_Cuentas PC ON MC.Id_Plan_Cuenta = PC.Id_Plan_Cuentas
    WHERE
        MC.Estado = "Activo" AND Id_Modulo = 2 AND Id_registro_Modulo =' . $id_registro . ' ORDER BY Debe DESC';

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
            MC.Estado = "Activo" AND Id_Modulo = 2 AND Id_registro_Modulo =' . $id_registro;

        $queryObj->SetQuery($query);
        $movimientos_suma = $queryObj->ExecuteQuery('simple');

        $query = '
        SELECT
            CONCAT_WS(" ", first_name, first_surname) AS Nombre_Funcionario
        FROM people
        WHERE
        identifier =' . $id_funcionario_imprime;

        $queryObj->SetQuery($query);
        $imprime = $queryObj->ExecuteQuery('simple');

        $query = '
        SELECT
            CONCAT_WS(" ", first_name, first_surname) AS Nombre_Funcionario
        FROM people
        WHERE
        id =' . $datos['Id_Funcionario'];

        $queryObj->SetQuery($query);
        $elabora = $queryObj->ExecuteQuery('simple');

        unset($queryObj);

        $codigos = '
    <h4 style="margin:5px 0 0 0;font-size:19px;line-height:22px;">' . $titulo . '</h4>
    <h4 style="margin:5px 0 0 0;font-size:19px;line-height:22px;">Factura Venta</h4>';

        if (!empty($movimientos) && isset($movimientos[0])) {
            $codigos .= '
    <h4 style="margin:5px 0 0 0;font-size:19px;line-height:22px;">' . $movimientos[0]['Documento'] . '</h4>
    <h5 style="margin:5px 0 0 0;font-size:16px;line-height:16px;">Fecha ' . fecha($movimientos[0]['Fecha']) . '</h5>';
        } else {
            $codigos .= '
    <h4 style="margin:5px 0 0 0;font-size:19px;line-height:22px;">No Document Found</h4>';
        }

        $header = (object) array(
            'Titulo' => $titulo,
            'Codigo' => $datos["code"] ?? '',
            'Fecha' => $datos["created_at"],
            'CodigoFormato' => $datos["format_code"] ?? ''
        );

        $data = [
            'datosCabecera' => $header,
            'movimientos' => $movimientos,
            'movimientos_suma' => $movimientos_suma,
            'imprime' => $imprime,
            'elabora' => $elabora,
            'codigos' => $codigos,
            'tipo_valor' => $tipo_valor,
            'titulo' => $titulo
        ];

        $pdf = Pdf::loadView('pdf.factura_venta', $data);

        return $pdf->stream($datos["Codigo"] . '.pdf');
    }

    public function getNotaCreditoPorFactura(Request $request)
    {
        $id_factura = $request->input('id_factura', '');
        $tipo_factura = $request->input('tipo_factura', '');

        if ($id_factura) {
            $query = 'SELECT * FROM Nota_Credito_Global WHERE Id_Factura = ' . intval($id_factura) . ' AND Tipo_Factura = "' . addslashes($tipo_factura) . '"';
            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $notas_credito = $oCon->getData();
            unset($oCon);

            foreach ($notas_credito as $key => $nota_credito) {
                $nota_credito->Observaciones = utf8_decode($nota_credito->Observaciones);

                // Agregar clave 'Total' inicializada en 0
                $nota_credito->Total = 0;

                if ($nota_credito) {
                    $query = 'SELECT P.*, C.Nombre AS Motivo FROM Producto_Nota_Credito_Global P
                      LEFT JOIN Causal_No_Conforme C ON C.Id_Causal_No_Conforme = P.Id_Causal_No_Conforme
                      WHERE P.Id_Nota_Credito_Global = ' . intval($nota_credito->Id_Nota_Credito_Global);
                    $oCon = new consulta();
                    $oCon->setTipo('Multiple');
                    $oCon->setQuery($query);
                    $nota_credito->Productos_Nota = $oCon->getData();
                    unset($oCon);

                    // Calcular el total si hay información relevante en Productos_Nota
                    if (!empty($nota_credito->Productos_Nota)) {
                        $nota_credito->Total = array_reduce($nota_credito->Productos_Nota, function ($carry, $item) {
                            return $carry + $item->Precio_Total;
                        }, 0);
                    }
                }
            }

            // Factura datos
            $tercero = 'Cliente';
            if ($nota_credito->Tipo_Factura == 'Documento_No_Obligados') {
                $tercero = 'Proveedor';
            }
            $query = 'SELECT Id_' . $nota_credito->Tipo_Factura . ' AS Id_Factura, Codigo, Fecha_Documento, Id_' . $tercero;

            if ($nota_credito->Tipo_Factura == 'Factura_Administrativa' || $nota_credito->Tipo_Factura == 'Documento_No_Obligados') {
                $query .= ', Tipo_' . $tercero;
            }

            $query .= ' FROM ' . $tipo_factura . ' WHERE Id_' . $tipo_factura . ' = ' . intval($id_factura);
            $oCon = new consulta();
            $oCon->setQuery($query);
            $factura = $oCon->getData();
            unset($oCon);

            // Dato cliente
            if ($tipo_factura == 'Factura_Administrativa') {
                $query = $this->queryClientesFacturaAdministrativa($factura["Tipo_Cliente"], $factura["Id_Cliente"]);
            } else if ($tipo_factura == 'Documento_No_Obligados') {
                $query = $this->queryClientesFacturaAdministrativa($factura["Tipo_Proveedor"], $factura["Id_Proveedor"]);
            } else {
                $query = $this->queryClientesFacturaAdministrativa('Cliente', $factura["Id_Cliente"]);
            }

            $oCon = new consulta();
            $oCon->setQuery($query);
            $cliente = $oCon->getData();
            unset($oCon);

            $response['Notas'] = $notas_credito;
            $response['Cliente'] = $cliente;
            $response['Factura'] = $factura;

            return response()->json($response);
        }
    }

    public function queryClientesFacturaAdministrativa($tipoCliente, $id_cliente)
    {
        $query = 'SELECT ';
        if ($tipoCliente == 'Funcionario') {
            $query .= 'IFNULL(CONCAT(C.first_name, " ", C.first_surname), "") AS Nombre_Cliente, 
               C.identifier AS Id_Cliente, 
               C.address_one AS Direccion_Cliente, 
               IFNULL(C.cell_phone, "") AS Telefono, 
               "" AS Ciudad_Cliente, 
               "1" AS Condicion_Pago 
               FROM people C 
               WHERE C.identifier = "' . addslashes($id_cliente) . '"';
        } else if ($tipoCliente == 'Cliente') {
            $query .= 'CONCAT(C.first_name, " ", C.first_surname) AS Nombre_Cliente, 
               C.id AS Id_Cliente, 
               C.address_one AS Direccion_Cliente, 
               IFNULL(C.cell_phone, "") AS Telefono, 
               M.name AS Ciudad_Cliente, 
               IFNULL(C.condition_payment, 1) AS Condicion_Pago 
               FROM third_parties C 
               INNER JOIN municipalities M ON M.id = C.municipality_id 
               WHERE C.id = ' . intval($id_cliente) . ' AND C.is_client = 1';
        } else if ($tipoCliente == 'Proveedor') {
            $query .= 'CONCAT(C.first_name, " ", C.first_surname) AS Nombre_Cliente, 
               C.id AS Id_Cliente, 
               C.address_one AS Direccion_Cliente, 
               IFNULL(C.cell_phone, "") AS Telefono, 
               M.name AS Ciudad_Cliente, 
               IFNULL(C.condition_payment, 1) AS Condicion_Pago 
               FROM third_parties C 
               INNER JOIN municipalities M ON M.id = C.municipality_id 
               WHERE C.id = ' . intval($id_cliente) . ' AND C.is_supplier = 1';
        }

        return $query;
    }


    public function descargaPdf(Request $request)
    {
        $id = $request->get('id', '');
        function fecha1($date)
        {
            return date('d-m-Y', strtotime($date));
        }


        /* DATOS DEL ARCHIVO A MOSTRAR */
        $oItem = new complex("Factura_Venta", "Id_Factura_Venta", $id);
        $data = $oItem->getData();
        unset($oItem);

        // Verificamos si $data es un objeto o un array
        $idResolucion = is_array($data) ? $data['Id_Resolucion'] : $data->Id_Resolucion;

        $query = "SELECT * FROM Resolucion WHERE Id_Resolucion=" . $idResolucion;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $fact = $oCon->getData();
        unset($oCon);

        $query = 'SELECT 
        FV.Fecha_Documento as Fecha, FV.Cufe, FV.Observacion_Factura_Venta as observacion, 
        FV.Codigo as Codigo, FV.Codigo_Qr, IF(FV.Condicion_Pago=1,"CONTADO",FV.Condicion_Pago) as Condicion_Pago,
        FV.Fecha_Pago as Fecha_Pago,
        C.id as IdCliente, C.first_name as NombreCliente, C.address_one as DireccionCliente,
        M.name as CiudadCliente, C.cell_phone as Telefono, FV.Id_Factura_Venta,
        (SELECT R.Observaciones FROM Remision R WHERE Id_Factura = FV.Id_Factura_Venta Order By R.Id_Remision ASC LIMIT 1) as Observaciones2
        FROM Factura_Venta FV
        INNER JOIN third_parties C ON FV.Id_Cliente = C.id AND C.is_client = 1
        INNER JOIN municipalities M ON C.municipality_id = M.id
        WHERE FV.Id_Factura_Venta =' . $id;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $cliente = $oCon->getData();
        unset($oCon);

        $query = 'SELECT 
        P.Id_Producto,
        P.Nombre_Comercial as producto,
        P.Nombre_General as Laboratorio,
        PFV.Fecha_Vencimiento as Vencimiento,
        PFV.Lote as Lote,
        IFNULL(PFV.Id_Inventario, PFV.Id_Inventario_Nuevo) as Id_Inventario,
        PFV.Precio_Venta as Costo_unitario,
        PFV.Cantidad as Cantidad,
        PFV.Precio_Venta as PrecioVenta,
        PFV.Subtotal as Subtotal,
        C.regime as Regimen,
        PFV.Id_Producto_Factura_Venta as idPFV,
        (CASE
            WHEN P.Gravado = "Si" AND C.apply_iva = "Si" THEN "19%"
            ELSE "0%"
        END) as Impuesto,
        CONCAT(PFV.Impuesto, "%") as Impuesto
        FROM Producto_Factura_Venta PFV
        INNER JOIN Factura_Venta F ON PFV.Id_Factura_Venta = F.Id_Factura_Venta
        INNER JOIN third_parties C ON F.Id_Cliente = C.id AND C.is_client = 1
        LEFT JOIN Producto P ON PFV.Id_Producto = P.Id_Producto
        WHERE PFV.Id_Factura_Venta =' . $id;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo("Multiple");
        $productos = $oCon->getData();
        unset($oCon);

        $regimen = '';
        if (count($productos) > 0) {
            $regimen = $productos[0]->Regimen == 'Comun' ? 'Impuesto Sobre las Ventas-IVA' : 'No Responsable IVA';
        }

        if (count($productos) == 0) {
            $query22 = 'SELECT 
            P.Id_Producto,
            P.Nombre_Comercial as producto,
            P.Nombre_General as Laboratorio,
            PFV.Fecha_Vencimiento as Vencimiento,
            PFV.Lote as Lote,
            IFNULL(PFV.Id_Inventario, PFV.Id_Inventario_Nuevo) as Id_Inventario,
            PFV.Precio_Venta as Costo_unitario,
            PFV.Cantidad as Cantidad,
            PFV.Precio_Venta as PrecioVenta,
            PFV.Subtotal as Subtotal,
            PFV.Descuento as Descuento,
            PFV.Id_Producto_Factura_Venta as idPFV,
            (CASE
                WHEN P.Gravado = "Si" THEN "19%"
                ELSE "0%"
            END) as Impuesto,
            CONCAT(PFV.Impuesto, "%") as Impuesto
            FROM Producto_Factura_Venta PFV
            LEFT JOIN Producto P ON PFV.Id_Producto = P.Id_Producto
            WHERE PFV.Id_Factura_Venta =' . $id;

            $oCon = new consulta();
            $oCon->setQuery($query22);
            $oCon->setTipo('Multiple');
            $productos = $oCon->getData();
            unset($oCon);
        }

        // Verificamos si $data es un objeto o un array para obtener el Id_Funcionario
        $idFuncionario = is_array($data) ? $data['Id_Funcionario'] : $data->Id_Funcionario;

        $oItem = new complex("people", "identifier", $idFuncionario);
        $func = $oItem->getData();
        unset($oItem);

        $query5 = 'SELECT SUM(Subtotal) as TotalFac FROM Producto_Factura_Venta WHERE Id_Factura_Venta = ' . $id;
        $oCon = new consulta();
        $oCon->setQuery($query5);
        $totalFactura = $oCon->getData();
        unset($oCon);

        // Verificamos si $fact es un objeto o un array para obtener el Tipo_Resolucion
        $tipoResolucion = is_array($fact) ? $fact['Tipo_Resolucion'] : $fact->Tipo_Resolucion;

        if ($tipoResolucion == "Resolucion_Electronica") {
            $titulo = "Factura Electrónica de Venta";
        } else {
            $titulo = "Factura de Venta";
        }

        // Verificamos si $data es un objeto o un array para obtener las propiedades
        $codigo = is_array($data) ? $data['Codigo'] : $data->Codigo;
        $fechaDocumento = is_array($data) ? $data['Fecha_Documento'] : $data->Fecha_Documento;
        $fechaPago = is_array($data) ? $data['Fecha_Pago'] : $data->Fecha_Pago;

        $codigos = '
        <span style="margin:-5px 0 0 0;font-size:16px;line-height:16px;">' . $titulo . '</span>
        <h3 style="margin:0 0 0 0;font-size:22px;line-height:22px;">' . $codigo . '</h3>
        <h5 style="margin:5px 0 0 0;font-size:11px;line-height:11px;">F. Expe.:' . fecha1($fechaDocumento) . '</h5>
        <h5 style="margin:5px 0 0 0;font-size:11px;line-height:11px;">H. Expe.:&nbsp;&nbsp;&nbsp;' . date('H:i:s', strtotime($fechaDocumento)) . '</h5>
        <h4 style="margin:5px 0 0 0;font-size:11px;line-height:11px;">F. Venc.:' . fecha1($fechaPago) . '</h4>
        ';

        $conditionPayment = isset($cliente['condition_payment']) ? $cliente['condition_payment'] : '';
        $condicion_pago = $conditionPayment == "CONTADO" ? $conditionPayment : "Credito a $conditionPayment Días";
        $header = (object) array(
            'Titulo' => $titulo,
            'Codigo' => $query["code"] ?? '',
            'Fecha' => $query["created_at"] ?? '',
            'CodigoFormato' => $query["format_code"] ?? ''
        );

        $data = [
            'data' => $data,
            'datosCabecera' => $header,
            'fact' => $fact,
            'cliente' => $cliente,
            'productos' => $productos,
            'func' => $func,
            'totalFactura' => $totalFactura,
            'titulo' => $titulo,
            'regimen' => $regimen,
            'codigos' => $codigos,
            'condicion_pago' => $condicion_pago,
        ];

        $pdf = Pdf::loadView('pdf.factura_venta_pdf', $data);

        return $pdf->stream($codigo . '.pdf');
    }



    public function remision(Request $request)
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $query = 'SELECT R.*
    FROM Remision R
    WHERE R.Id_Remision=' . $id;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $remision = $oCon->getData();
        unset($oCon);
        $bodega = '';

        if ($remision['Tipo_Origen'] == 'Bodega') {
            $remision['Tipo_Origen'] .= '_Nuevo';
        }
        if ($remision['Tipo_Destino'] == 'Bodega') {
            $remision['Tipo_Destino'] .= '_Nuevo';
        }

        // Check if Tipo_Origen or Tipo_Destino needs to be converted to third_parties
        if ($remision['Tipo_Origen'] == 'Cliente') {
            $remision['Tipo_Origen'] = 'third_parties';
        }
        if ($remision['Tipo_Destino'] == 'Cliente') {
            $remision['Tipo_Destino'] = 'third_parties';
        }

        // Determine the correct table and column for the origin
        if ($remision['Tipo_Origen'] == 'third_parties' && isset($remision['is_client']) && $remision['is_client']) {
            $query = 'SELECT * 
            FROM third_parties 
            WHERE is_client = 1 AND id = ' . $remision['Id_Origen'];
        } elseif ($remision['Tipo_Origen'] == 'Bodega_Nuevo') {
            $query = 'SELECT *
            FROM Bodega_Nuevo
            WHERE Id_Bodega_Nuevo = ' . $remision['Id_Origen'];
        } else {
            $query = 'SELECT *
            FROM ' . $remision['Tipo_Origen'] . $bodega . '
            WHERE id = ' . $remision['Id_Origen'];
        }

        $oCon = new consulta();
        $oCon->setQuery($query);
        $origen = $oCon->getData();
        unset($oCon);

        // Determine the correct table and column for the destination
        if ($remision['Tipo_Destino'] == 'Contrato') {
            $query = 'SELECT * , Nombre_Contrato AS Nombre 
            FROM ' . $remision['Tipo_Destino'] . '
            WHERE Id_Contrato = ' . $remision['Id_Destino'];
        } elseif ($remision['Tipo_Destino'] == 'third_parties' && isset($remision['is_supplier']) && $remision['is_supplier']) {
            $query = 'SELECT *
            FROM third_parties 
            WHERE is_supplier = 1 AND id = ' . $remision['Id_Destino'];
        } else {
            $query = 'SELECT *
            FROM ' . $remision['Tipo_Destino'] . '
            WHERE id = ' . $remision['Id_Destino'];
        }

        $oCon = new consulta();
        $oCon->setQuery($query);
        $destino = $oCon->getData();
        unset($oCon);

        if ($remision['Tipo_Lista'] == "Contrato") {
            $oItem = new complex('Contrato', 'Id_Contrato', $remision['Id_Lista']);
            $contrato = $oItem->getData();
            $resultado['Contrato'] = $contrato;
            unset($oItem);
        } elseif ($remision['Tipo_Lista'] == "Lista_Ganancia") {
            $oItem = new complex('Lista_Ganancia', 'Id_Lista_Ganancia', $remision['Id_Lista']);
            $contrato = $oItem->getData();
            $resultado['Lista'] = $contrato;
            unset($oItem);
        }

        $resultado['Remision'] = $remision;
        $resultado['Origen'] = $origen;
        $resultado['Destino'] = $destino;

        return response()->json($resultado);
    }

    public function inventarioFiscoDescargaPdf(Request $request)
    {
        $id_inventario_fisico = $request->get('id', false);
        $id_pto = $request->get('id_pto', false);
        $condicion = '';
    
        if ($id_inventario_fisico) {
            $condicion = "WHERE INF.Id_Inventario_Fisico_Punto_Nuevo=$id_inventario_fisico";
        } else {
            $condicion = "WHERE INF.Id_Punto_Dispensacion=$id_pto";
        }
    
        function fecha($str)
        {
            $parts = explode(" ", $str);
            $date = explode("-", $parts[0]);
            return $date[2] . "/" . $date[1] . "/" . $date[0];
        }
    
        $query = 'SELECT INF.*, 
                  DATE_FORMAT(Fecha, "%d/%m/%Y %r") AS f_inicio, 
                  DATE_FORMAT(Fecha, "%d/%m/%Y %r") AS f_fin, 
                  (SELECT Nombre FROM Punto_Dispensacion WHERE Id_Punto_Dispensacion=INF.Id_Punto_Dispensacion) AS Nom_Bodega,  
                  (SELECT CONCAT(first_name, " ", second_name, " ", first_surname, " ", second_surname) FROM people WHERE identifier=INF.Funcionario_Autoriza) AS Funcionario_Digitador, 
                  (SELECT CONCAT(first_name, " ", second_name, " ", first_surname, " ", second_surname) FROM people WHERE identifier=INF.Funcionario_Autoriza) AS Funcionario_Cuenta, 
                  (SELECT CONCAT(first_name, " ", second_name, " ", first_surname, " ", second_surname) FROM people WHERE identifier=INF.Funcionario_Autoriza) AS Funcionario_Autorizo 
                  FROM Inventario_Fisico_Punto_Nuevo INF ' . $condicion;
    
        $oCon = new consulta();
        $oCon->setQuery($query);
        $datos = $oCon->getData();
        unset($oCon);
    
        $query = 'SELECT PIF.Id_Producto_Inventario_Fisico, PIF.Id_Inventario_Fisico_Punto_Nuevo,  
                  P.Nombre_Comercial, LEFT(P.Nombre_Comercial, 256) AS Nombre_Producto, 
                  PIF.Lote, PIF.Fecha_Vencimiento, PIF.Primer_Conteo AS Cantidad_Encontrada, IFNULL(PIF.Segundo_Conteo, PIF.Primer_Conteo) AS Segundo_Conteo, 
                  (PIF.Segundo_Conteo - PIF.Primer_Conteo) AS Cantidad_Diferencial, IF(PIF.Cantidad_Final = 0 OR PIF.Cantidad_Final IS NULL, PIF.Segundo_Conteo, PIF.Cantidad_Final) 
                  AS Cantidad_Final 
                  FROM Producto_Inventario_Fisico_Punto PIF 
                  INNER JOIN Producto P ON PIF.Id_Producto = P.Id_Producto 
                  INNER JOIN Inventario_Fisico_Punto_Nuevo INF ON PIF.Id_Inventario_Fisico_Punto_Nuevo = INF.Id_Inventario_Fisico_Punto_Nuevo ' . $condicion . 
                  ' ORDER BY P.Nombre_Comercial';
    
        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $productos = $oCon->getData();
        unset($oCon);
    
        $total = count($productos);
    //dd($datos);
        $detalles = '';
        if ($id_inventario_fisico) {
            $detalles = '<h4 style="margin:5px 0 0 0;font-size:22px;line-height:22px;">INVFP' . $datos['Id_Inventario_Fisico_Punto_Nuevo'] . '</h4>
            <h6 style="margin:5px 0 0 0;font-weight: normal;font-size:12px;line-height:16px;">Inicio: ' . $datos['f_inicio']. '</h6>
            <h6 style="margin:5px 0 0 0;font-weight: normal;font-size:12px;line-height:16px;">Fin: ' . $datos['f_fin'] . '</h6>';
        }
    
        $codigos = '
            <h3 style="margin:5px 0 0 0;font-size:22px;line-height:22px;">Inventario Físico</h3>
            ' . $detalles . '
            <h6 style="margin:5px 0 0 0;font-weight: normal;font-size:12px;line-height:16px;">Punto: ' . $datos['Nom_Bodega'] . '</h6>
        ';

        $header = (object) array(
            'Titulo' => 'Reporte de Inventario Físico',
            'Codigo' => $query["code"] ?? '',
            'Fecha' => $query["created_at"] ?? '',
            'CodigoFormato' => $query["format_code"] ?? ''
        );
    
        $data = [
            'codigos' => $codigos,
            'productos' => $productos,
            'datos' => $datos,
            'datosCabecera' => $header,
        ];
    
        $pdf = Pdf::loadView('pdf.inventario_fisico', $data);
    
        return $pdf->stream('listado.pdf');
    }
    





}