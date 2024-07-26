<?php

namespace App\Http\Controllers;

use App\Http\Services\complex;
use App\Http\Services\HttpResponse;
use App\Http\Services\QueryBaseDatos;
use App\Http\Services\Utility;
use App\Models\CompanyConfiguration;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;

class RemisionNoRotativoController extends Controller
{
    use ApiResponser;

    public function getNoRotativos(Request $request)
    {
        $http_response = new HttpResponse();
        $queryObj = new QueryBaseDatos();
        $util = new Utility();

        $punto_dispensacion = $request->input('id_destino', ''); 
        $fechaMes = $request->input('fmes', '');
        $fecha_inicio = $fechaMes . '-01';
        $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));
        $bodega = $request->input('id_origen', '');
        $eps = $request->input('eps', ''); 

        $id_categoria_nueva = $request->input('id_categoria_nueva', ''); 

        $mes = $request->input('mes', ''); 

        $grupo = $request->input('grupo', ''); 
        $grupo = (array) json_decode($grupo, true);

        $hoy = date("Y-m-t", strtotime(date('Y-m-d')));

        $nueva_fecha = strtotime('+ 1 months', strtotime($hoy));
        $nuevafecha = date('Y-m-t', $nueva_fecha);
        /*
        var_dump($nueva_fecha);
        var_dump($nuevafecha);
         */
        $condicion = $this->SetCondiciones($bodega, $eps, $fecha_inicio, $fecha_fin, $id_categoria_nueva);

        $condicion_lotes = $this->SetCondicionLotes($bodega, $nuevafecha, $mes, $id_categoria_nueva, $grupo);
        $condicion_lotes_Punto = $this->SetCondicionLotesSimilaresPunto($punto_dispensacion, $nuevafecha, $mes, $id_categoria_nueva, $grupo);

        $query = $this->CrearQuery($condicion, $punto_dispensacion, $bodega, $condicion_lotes, $queryObj);
        // return $query;

        $queryObj->SetQuery($query);
        $productosCrudos = $queryObj->ExecuteQuery('Multiple');
        $productos = [];
        foreach ($productosCrudos as $productoCrudo) {
            $producto = new Product();
            foreach ((array) $productoCrudo as $key => $value) {
                $producto->$key = $value;
            }
            $producto->exists = true; 
            $productos[] = $producto;
        }

        foreach ($productos as $producto) {
            $producto->load('variableProductsSinRecepcion');
        }

        [$productos, $variablesLabels] = getVariablesProductos($productos);
        // return $productos;

        $j = -1;
        $elimLotes = 0;
        foreach ($productos as $producto) {
            $j++;
            if ($producto->Id_Subcategoria != '') {
                // Busco los lotes de inventario de los productos
                $producto->Cantidad_Requerida = $this->ValidarRotacion($producto);
                $Cantidad_Requerida = $producto->Cantidad_Requerida;
        
                if ($Cantidad_Requerida > 0) {
                    $productossimilares = $this->GetSimilares($producto, $queryObj);
        
                    $producto->Similares_Con_Cantidad = isset($productossimilares) && count($productossimilares) > 0 ? $this->GetLotesProductosimilaresPunto($productossimilares, $queryObj, $condicion_lotes_Punto) : [];
        
                    if (count($producto->Similares_Con_Cantidad) > 0) {
                        unset($productos[$j]);
                    } else {
                        $lotes = $this->GetLotes($producto, $queryObj, $condicion_lotes);
        
                        // echo $Cantidad_Requerida; exit;
                        if (count($lotes) > 0) {
        
                            $cantidad = $producto->Cantidad_Requerida;

        
                            $cantidad_inicial = $producto->Cantidad_Requerida;
                            $producto->Lotes = $lotes;
        
                            $multiplo = 0;
                            $cantidad_presentacion_producto = false;
        
 
                            $lotes_seleccionados = [];
                            $lotes_visuales = [];
        
                            if ($multiplo == 0 && $cantidad > 0) {
        
                                $flag = true;
        
                                for ($i = 0; $i < count($lotes); $i++) {
        
                                    if ($flag && $cantidad <= $lotes[$i]->Cantidad) {
                                        $lote = $lotes[$i];
                                        $lote->Cantidad_Seleccionada = $cantidad;
        
                                        // método de seleccionar los lotes
                                        // $this->SelecionarLotes($lote, $queryObj);
        
                                        $lotes[$i]->Cantidad_Seleccionda = $cantidad;
                                        $labellote = "Lote: " . $lotes[$i]->Lote . " - Vencimiento: " . $lotes[$i]->Fecha_Vencimiento . " - Cantidad: " . $cantidad;
        
                                        $producto->Cantidad = $cantidad_inicial;
        
                                        array_push($lotes_visuales, $labellote);
                                        array_push($lotes_seleccionados, $lote);
                                        $flag = false;
                                    } elseif ($flag && $cantidad > $lotes[$i]->Cantidad) {
                                        $lote = $lotes[$i];
                                        $lote->Cantidad_Seleccionada = $lotes[$i]->Cantidad;
        
                                        // método de seleccionar los lotes
                                        // $this->SelecionarLotes($lote, $queryObj);
        
                                        $labellote = "Lote: " . $lotes[$i]->Lote . " - Vencimiento: " . $lotes[$i]->Fecha_Vencimiento . " - Cantidad: " . $lotes[$i]->Cantidad;
                                        array_push($lotes_seleccionados, $lote);
                                        array_push($lotes_visuales, $labellote);
        
                                        $producto->Cantidad = $producto->Cantidad + $lotes[$i]->Cantidad;
        
                                        $cantidad = $cantidad - (int) $lotes[$i]->Cantidad;
        
                                    }
                                }
        
                                $producto->Lotes_Visuales = $lotes_visuales;
                                $producto->Lotes_Seleccionados = $lotes_seleccionados;
        
                            } else {
                                unset($productos[$j]);
                            }
        
                        } else {
                            $similares = $this->GetSimilares($producto, $queryObj);
                            if (!$similares) {
                                unset($productos[$j]);
                            } else {
                                $productossimilares = $this->GetLotesProductosimilares($similares, $bodega, $nuevafecha, $queryObj, $condicion_lotes);
                                if (count($productossimilares) == 0 || !$productossimilares) {
                                    unset($productos[$j]);
                                } else {
                                    $producto->Similares = $productossimilares;
                                    $producto->Cantidad_Disponible = 0;
                                }
                            }
                        }
                    }
                } else {
                    unset($productos[$j]);
                }
        
            } else {
                unset($productos[$j]);
            }
        }
        

        $productos = array_values($productos);
        return $this->success([
            'productos' => $productos,
            'variables' => $variablesLabels,
        ]);
    }

    function SetCondiciones($bodega, $eps, $fecha_inicio, $fecha_fin, $id_categoria_nueva)
    {

        $condicion = "WHERE D.Estado_Dispensacion!='Anulada' AND DATE(D.Fecha_Actual) BETWEEN  '$fecha_inicio' AND '$fecha_fin' ";

        return $condicion;
    }

    function SetCondicionLotes($bodega, $nuevafecha, $mes, $id_categoria_nueva, $grupo)
    {

    //     $condicion_grupo = $grupo['Id_Grupo'] != '-1' ? " AND  G.Id_Grupo_Estiba = $grupo[Id_Grupo]" : '';
    //     $condicion_principal =
    //         " INNER JOIN Producto PRD
    // On I.Id_Producto=PRD.Id_Producto
    // INNER JOIN Subcategoria SubC ON PRD.Id_Subcategoria = SubC.Id_Subcategoria

    // INNER JOIN Estiba E ON I.Id_Estiba=E.Id_Estiba
    // INNER JOIN Bodega_Nuevo B ON E.Id_Bodega_Nuevo = B.Id_Bodega_Nuevo
    // INNER JOIN Grupo_Estiba G ON G.Id_Grupo_Estiba = E.Id_Grupo_Estiba
    // INNER JOIN Categoria_Nueva C ON C.Id_Categoria_Nueva = SubC.Id_Categoria_Nueva

    // WHERE E.Estado = 'Disponible' AND B.Id_Bodega_Nuevo =  $bodega  $condicion_grupo";

        // if ($grupo['Fecha_Vencimiento'] === "Si" && $mes != '-1') {

        //     $condicion_principal .= "  AND I.Fecha_Vencimiento>='$nuevafecha' ";
        // }
        // echo $condicion_principal; exit;



        return "INNER JOIN Producto PRD
        On I.Id_Producto=PRD.Id_Producto
        INNER JOIN Subcategoria SubC ON PRD.Id_Subcategoria = SubC.Id_Subcategoria
        INNER JOIN Categoria_Nueva C ON C.Id_Categoria_Nueva = SubC.Id_Categoria_Nueva WHERE C.Id_Categoria_Nueva = $id_categoria_nueva";

        // return 'where 1=1';


    }

    function SetCondicionLotesSimilaresPunto($punto_dispensacion, $nuevafecha, $mes, $id_categoria_nueva, $grupo)
    {

    //     $condicion_principal =
    //         'INNER JOIN Producto PRD
    // On I.Id_Producto=PRD.Id_Producto
    // INNER JOIN Subcategoria SubC ON PRD.Id_Subcategoria = SubC.Id_Subcategoria

    // INNER JOIN Estiba E ON I.Id_Estiba=E.Id_Estiba
    // INNER JOIN Punto_Dispensacion B ON E.Id_Punto_Dispensacion = B.Id_Punto_Dispensacion

    // WHERE E.Estado = "Disponible" AND B.Id_Punto_Dispensacion = ' . $punto_dispensacion;

    //     if ($grupo['Fecha_Vencimiento'] === "Si" && $mes != '-1') {

    //         $condicion_principal .= "  AND I.Fecha_Vencimiento>='$nuevafecha' ";
    //     }
        // echo $condicion_principal; exit;
        return '';

    }

    function CrearQuery($condicion, $punto_dispensacion, $bodega, $condicion_lotes, $queryObj)
{
    $punto_dispensacion = preg_replace('/^[A-Za-z]-/', '', $punto_dispensacion);
    $bodega = preg_replace('/^[A-Za-z]-/', '', $bodega);
    // $max_costo = $this->GetMaxCosto($queryObj);
    $max_costo = CompanyConfiguration::where('company_id', getCompanyWorkedId())->first()->max_costo_nopos;

    if ($max_costo == '' || $max_costo == null) {
        $max_costo = 500000;
    }

    $queryPendientesPropharmacy = "SELECT
                            SubC.Nombre AS Subcategoria,
                            SubC.Separable AS Categoria_Separable,
                            PTO.Nombre AS Punto,
                            D.Codigo AS Dispensacion,
                            PRD.Id_Producto,
                            PRD.Id_Subcategoria,
                            PRD.Nombre_General AS Nombre,
                            PRD.Nombre_Comercial,
                            PRD.Referencia as Codigo_Cum,
                            (CASE
                                WHEN PRD.Gravado = 'Si' THEN (SELECT Valor FROM Impuesto WHERE  Valor > 0 ORDER BY Id_Impuesto DESC LIMIT 1)
                                WHEN PRD.Gravado = 'No' THEN 0
                            END) AS Impuesto,
                            SUM(PR.Cantidad_Formulada - PR.Cantidad_Entregada) AS Requerida,
                            D.Id_Punto_Dispensacion,
                            0 AS Cantidad,
                            IFNULL(I.Precio, 0) AS Precio,
                            IFNULL(I.Cantidad_Disponible, 0) AS Cantidad_Inventario
                        FROM
                            Producto_Dispensacion PR
                        INNER JOIN Auditoria A ON  (A.Estado = 'Aceptar'  OR A.Estado = 'Auditado')  AND PR.Id_Dispensacion = A.Id_Dispensacion
                        INNER JOIN Dispensacion D ON PR.Id_Dispensacion = D.Id_Dispensacion  AND D.Estado_Dispensacion != 'Anulada'
                        LEFT JOIN  Punto_Dispensacion PTO ON PTO.Id_Punto_Dispensacion = D.Id_Punto_Dispensacion
                        INNER JOIN Producto PRD ON PR.Id_Producto = PRD.Id_Producto
                        Left JOIN (SELECT Id_Paciente, EPS, Nit FROM  Paciente) PA ON D.Numero_Documento = PA.Id_Paciente
                        LEFT JOIN (SELECT  SUM(I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada) AS Cantidad_Disponible,
                                    I.Id_Producto,
                                    PDI.Id_Punto_Dispensacion AS Id_Punto,
                                    ROUND(AVG(I.Costo)) AS Precio
                                    FROM
                                    Punto_Dispensacion PDI
                                    INNER JOIN Estiba E ON E.Id_Punto_Dispensacion = PDI.Id_Punto_Dispensacion
                                    INNER JOIN Inventario_Nuevo I ON E.Id_Estiba = I.Id_Estiba
                                    GROUP BY I.Id_Producto , PDI.Id_Punto_Dispensacion
                            ) I ON I.Id_Producto = PR.Id_Producto  AND I.Id_Punto = D.Id_Punto_Dispensacion
                        INNER JOIN Subcategoria SubC ON PRD.Id_Subcategoria = SubC.Id_Subcategoria
                        $condicion
                        AND D.Id_Punto_Dispensacion IN ($punto_dispensacion)
                        GROUP BY PR.Id_Producto
                        HAVING Requerida > 0
                        ORDER BY Nombre_Comercial";

    $queryPendientesPuntos = "SELECT
                        SubC.Nombre AS Subcategoria,
                        SubC.Separable AS Categoria_Separable,
                        PTO.Nombre AS Punto,
                        D.Codigo AS Dispensacion,
                        PRD.Id_Producto,
                        PRD.Id_Subcategoria,
                        PRD.Nombre_General AS Nombre,
                        PRD.Nombre_Comercial,
                        PRD.Referencia as Codigo_Cum,
                        (CASE
                            WHEN PRD.Gravado = 'Si' THEN (SELECT Valor FROM Impuesto WHERE  Valor > 0 ORDER BY Id_Impuesto DESC LIMIT 1)
                            WHEN PRD.Gravado = 'No' THEN 0
                        END) AS Impuesto,
                        SUM(PR.Cantidad_Formulada - PR.Cantidad_Entregada) AS Requerida,
                        D.Id_Punto_Dispensacion,
                        0 AS Cantidad,
                        IFNULL(I.Precio, 0) AS Precio,
                        IFNULL(I.Cantidad_Disponible, 0) AS Cantidad_Inventario
                    FROM
                        Producto_Dispensacion PR
                    INNER JOIN Auditoria A ON  (A.Estado = 'Aceptar'  OR A.Estado = 'Auditado')  AND PR.Id_Dispensacion = A.Id_Dispensacion
                    INNER JOIN Dispensacion D ON PR.Id_Dispensacion = D.Id_Dispensacion  AND D.Estado_Dispensacion != 'Anulada'
                    LEFT JOIN  Punto_Dispensacion PTO ON PTO.Id_Punto_Dispensacion = D.Id_Punto_Dispensacion
                    INNER JOIN Producto PRD ON PR.Id_Producto = PRD.Id_Producto
                    Left JOIN (SELECT Id_Paciente, EPS, Nit FROM  Paciente) PA ON D.Numero_Documento = PA.Id_Paciente
                    LEFT JOIN (SELECT  SUM(I.Cantidad - I.Cantidad_Apartada - I.Cantidad_Seleccionada) AS Cantidad_Disponible,
                                I.Id_Producto,
                                PDI.Id_Punto_Dispensacion AS Id_Punto,
                                ROUND(AVG(I.Costo)) AS Precio
                                FROM
                                Punto_Dispensacion PDI
                                INNER JOIN Estiba E ON E.Id_Punto_Dispensacion = PDI.Id_Punto_Dispensacion
                                INNER JOIN Inventario_Nuevo I ON E.Id_Estiba = I.Id_Estiba
                                GROUP BY I.Id_Producto , PDI.Id_Punto_Dispensacion
                        ) I ON I.Id_Producto = PR.Id_Producto
                    INNER JOIN Subcategoria SubC ON PRD.Id_Subcategoria = SubC.Id_Subcategoria
                    $condicion
                    GROUP BY PR.Id_Producto
                    HAVING Requerida > 0
                    ORDER BY Nombre_Comercial";

    $query =
        "   SELECT  D.*, SUM(D.Requerida)AS Cantidad_Requerida, GROUP_CONCAT(D.Punto) AS Puntos,
            IFNULL(I.Disponible, 0) AS Cantidad_Disponible
        FROM(   (  $queryPendientesPropharmacy
             ) UNION ALL (  $queryPendientesPuntos  )   )
        D
        LEFT JOIN (
                    SELECT SUM(I.Cantidad-I.Cantidad_Apartada-I.Cantidad_Seleccionada) AS Disponible, I.Id_Producto
                    FROM
                        Inventario_Nuevo I
                    $condicion_lotes
                    GROUP BY I.Id_Producto
                    HAVING Disponible>0
        )I ON I.Id_Producto=D.Id_Producto
        GROUP BY D.Id_Producto
        HAVING Cantidad_Requerida > Cantidad_Inventario";
    return $query;

}

function GetMaxCosto($queryObj)
{
    $query = "SELECT Max_Costo_Nopos FROM Configuracion WHERE Id_Configuracion=1";
    $queryObj->SetQuery($query);
    $data = $queryObj->ExecuteQuery('simple');

    return $data['Max_Costo_Nopos'];
}

function ValidarRotacion($producto)
{

    $cantidad = $producto->Cantidad_Requerida - $producto->Cantidad_Inventario;

    // echo$producto['Cantidad_Requerida']-$producto['Cantidad_Disponible'];exit;
    return $cantidad > 0 ? $cantidad : 0;

}

function GetSimilares($producto, $queryObj)
{


    $query = "SELECT PA.Producto_Asociado From(
            SELECT CONCAT('-',REPLACE(REPLACE(PA.Producto_Asociado, ',', '-,-'), ' ', ''), '-') AS Asociado, Producto_Asociado
            FROM Producto_Asociado PA
        )PA Where PA.Asociado LIKE '%-$producto->Id_Producto-%' ";

    $queryObj->SetQuery($query);
    $productos = $queryObj->ExecuteQuery('simple');

    // echo json_encode($productos);exit;]
    return $productos;

}

function GetLotesProductosimilaresPunto($productos, $queryObj, $condicion_lotes_Punto)
{


    $query = 'SELECT SUM(I.Cantidad-(I.Cantidad_Apartada+I.Cantidad_Seleccionada)) as Cantidad_Disponible,PRD.Nombre_Comercial,
    CONCAT(PRD.Principio_Activo," ",PRD.Presentacion," ",PRD.Concentracion," ", PRD.Cantidad," ", PRD.Unidad_Medida) as Nombre,
    PRD.Codigo_Cum,
     0 as Seleccionado,
     PRD.Id_Producto
    FROM  Inventario_Nuevo I
    ' . $condicion_lotes_Punto . ' AND  I.Id_Producto
     IN (' . $productos['Producto_Asociado'] . ')
    GROUP BY I.Id_Producto
    HAVING Cantidad_Disponible > 0 ';

    // echo $query; exit;
    $queryObj->SetQuery($query);
    $productos = $queryObj->ExecuteQuery('Multiple');

    return $productos;
}

function GetLotes($producto, $queryObj, $condicion_lotes)
{
    $having = " HAVING Cantidad>0
    ORDER BY I.Fecha_Vencimiento ASC";

    $query1 = "SELECT I.Id_Inventario_Nuevo, I.Id_Producto,I.Lote,(I.Cantidad-(I.Cantidad_Apartada+I.Cantidad_Seleccionada)) as Cantidad,
    I.Fecha_Vencimiento, -- $producto->Precio as Precio,
    0 as Cantidad_Seleccionada
    FROM  Inventario_Nuevo I
    " . $condicion_lotes . " AND I.Id_Producto= $producto->Id_Producto
    " . $having;

    $queryObj->SetQuery($query1);
    // echo $query1; exit;
    $lotes = $queryObj->ExecuteQuery('Multiple');

    return $lotes;
}

function SelecionarLotes($lote, $queryObj)
{

    $query = "SELECT Cantidad_Seleccionada FROM Inventario_Nuevo WHERE Id_Inventario_Nuevo = $lote->Id_Inventario_Nuevo";

    $queryObj->SetQuery($query);

    $cantidad_seleccionada_inventario = $queryObj->ExecuteQuery('simple');

    $cantidad_total = $lote->Cantidad_Seleccionada + $cantidad_seleccionada_inventario['Cantidad_Seleccionada'];

    $oItem = new complex("Inventario_Nuevo", "Id_Inventario_Nuevo", $lote->Id_Inventario_Nuevo);

    $oItem->Cantidad_Seleccionada = number_format($cantidad_total, 0, "", "");

    $oItem->save();
    unset($oItem);
}

function GetLotesProductosimilares($productos, $bodega, $nuevafecha, $queryObj, $condicion_lotes)
{


    $query = 'SELECT SUM(I.Cantidad-(I.Cantidad_Apartada+I.Cantidad_Seleccionada)) as Cantidad_Disponible,PRD.Nombre_Comercial,
    CONCAT(PRD.Principio_Activo," ",PRD.Presentacion," ",PRD.Concentracion," ", PRD.Cantidad," ", PRD.Unidad_Medida) as Nombre, PRD.Id_Producto,
     0 as Seleccionado
    FROM  Inventario_Nuevo I
    ' . $condicion_lotes . ' AND  I.Id_Producto
     IN (' . $productos['Producto_Asociado'] . ')
    GROUP BY I.Id_Producto
    HAVING Cantidad_Disponible > 0 ';

    $queryObj->SetQuery($query);
    $productos = $queryObj->ExecuteQuery('Multiple');

    return $productos;
}

}
