<?php

namespace App\Http\Controllers;

use App\Exports\ReporteSismedCompraExport;
use App\Exports\ReporteSismedExport;
use App\Http\Services\complex;
use App\Http\Services\consulta;
use App\Models\Product;
use App\Models\ProductoActaRecepcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReporteSismedController extends Controller
{
    public function reporteSismedCompra(Request $request)
    {
        $meses = $request->meses;
        $ano = request('ano', '');
        $meses = explode("-", $meses);
        
        $resultado = [];
    
        for ($i = 0; $i < count($meses); $i++) {
            $query = 'SELECT
            MONTH(FAR.Fecha_Factura) as Mes,
            PR.Precio as Precio_Regulacion,
            MAX(PAR.Precio) as Maximo,
            MIN(PAR.Precio) as Minimo,
            MAX(CONCAT(PAR.Precio,"|",FAR.Factura)) AS Maximo_Factura,
            MIN(CONCAT(PAR.Precio,"|",FAR.Factura)) AS Minimo_Factura,
            SUM(PAR.Precio*PAR.Cantidad) AS Precio,
            SUM(PAR.Cantidad) AS Cantidad,
            Nombre_General as Nombre_Producto,
            FAR.Cufe
            FROM
            Producto_Acta_Recepcion PAR
            INNER JOIN Factura_Acta_Recepcion FAR ON PAR.Id_Acta_Recepcion = FAR.Id_Acta_Recepcion AND FAR.Factura = PAR.Factura
            INNER JOIN Producto P ON P.Id_Producto = PAR.Id_Producto
            LEFT JOIN Precio_Regulado PR ON P.Id_Producto = PR.Id_Producto
            LEFT JOIN variable_products VP ON P.Id_Producto = VP.product_id AND VP.category_variables_id = 1
            WHERE MONTH(FAR.Fecha_Factura)=' . $meses[$i] . ' AND YEAR(FAR.Fecha_Factura)=' . $ano . ' AND P.Id_Categoria = 5
            GROUP BY VP.valor, P.Id_Producto
            ORDER BY VP.valor, P.Id_Producto;';
    
            $result = DB::select($query);
            $resultado = array_merge($resultado, $result);
        }
    
        foreach ($resultado as $key => $item) {
            $item['Numero'] = $key + 1;
            $item['Indice'] = $key + 1;
        }
    
        return Excel::download(new ReporteSismedCompraExport($resultado), 'reporte_sismed_compra.xlsx');
    }
    

    public function reporteSismed()
    {
        $meses = request('meses', '');
        $ano = request('ano', '');
        $tipo = request('tipo', 'Dispensacion');
        $meses = explode("-", $meses);
        for ($i = 0; $i < count($meses); $i++) {
            $condicion = " WHERE PF1.Id_Producto=PF.Id_Producto AND MONTH(F1.Fecha_Documento)=" . $meses[$i] . " AND YEAR(F1.Fecha_Documento)='" . $ano . "'";
            $maximo_Factura = '(SELECT F1.Codigo FROM Producto_Factura PF1 INNER JOIN Factura F1 ON PF1.Id_Factura=F1.Id_Factura ' . $condicion . ' AND F1.Tipo!="Homologo" AND F1.Estado_Factura != "Anulada" ORDER BY PF1.Precio DESC LIMIT 1 ) as Maximo_Factura,';
            $minimo_Factura = '( SELECT F1.Codigo FROM Producto_Factura PF1 INNER JOIN Factura F1 ON PF1.Id_Factura=F1.Id_Factura ' . $condicion . ' AND F1.Tipo!="Homologo" AND F1.Estado_Factura != "Anulada" ORDER BY PF1.Precio ASC LIMIT 1 ) as Minimo_Factura,';
            $subtotal_Factura = '(SELECT SUM(((PF1.Precio*PF1.Cantidad)+ ((PF1.Precio * PF1.Cantidad) * (PF1.Impuesto/100))) ) FROM Producto_Factura PF1 INNER JOIN Factura F1 ON PF1.Id_Factura=F1.Id_Factura ' . $condicion . ' AND F1.Tipo!="Homologo" AND F1.Estado_Factura != "Anulada" ) as Precio,';
            $cantidad_Factura = '(SELECT SUM(PF1.Cantidad) FROM Producto_Factura PF1 INNER JOIN Factura F1 ON PF1.Id_Factura=F1.Id_Factura ' . $condicion . ' AND F1.Tipo!="Homologo" AND F1.Estado_Factura != "Anulada") as Cantidad,';
            $costo_Factura = " IFNULL((SELECT PAR.Precio FROM Producto_Acta_Recepcion PAR INNER JOIN Acta_Recepcion AR ON PAR.Id_Acta_Recepcion=AR.Id_Acta_Recepcion WHERE PAR.Id_Producto=PF.Id_Producto ORDER BY AR.Fecha_Creacion DESC LIMIT 1),0) as Costo,";
            $maximo_factura_venta = '(SELECT F1.Codigo FROM Producto_Factura_Venta PF1 INNER JOIN Factura_Venta F1 ON PF1.Id_Factura_Venta=F1.Id_Factura_Venta ' . $condicion . ' AND F1.Estado != "Anulada" ORDER BY PF1.Precio_Venta DESC LIMIT 1 ) as Maximo_Factura,';
            $minimo_factura_venta = '(SELECT F1.Codigo FROM Producto_Factura_Venta PF1 INNER JOIN Factura_Venta F1 ON PF1.Id_Factura_Venta=F1.Id_Factura_Venta ' . $condicion . ' AND F1.Estado != "Anulada" ORDER BY PF1.Precio_Venta ASC LIMIT 1 ) as Minimo_Factura,';
            $subtotal_factura_venta = '(SELECT SUM((PF1.Precio_Venta*PF1.Cantidad)+((PF1.Precio_Venta*PF1.Cantidad)*(PF1.Impuesto/100))) FROM Producto_Factura_Venta PF1 INNER JOIN Factura_Venta F1 ON PF1.Id_Factura_Venta=F1.Id_Factura_Venta ' . $condicion . ' AND F1.Estado != "Anulada") as Precio,';
            $cantidad_factura_venta = '(SELECT SUM(PF1.Cantidad) FROM Producto_Factura_Venta PF1 INNER JOIN Factura_Venta F1 ON PF1.Id_Factura_Venta=F1.Id_Factura_Venta ' . $condicion . ' AND F1.Estado != "Anulada" ) as Cantidad,';
            $costo_factura_venta = "IFNULL((SELECT PAR.Precio FROM Producto_Acta_Recepcion PAR INNER JOIN Acta_Recepcion AR ON PAR.Id_Acta_Recepcion=AR.Id_Acta_Recepcion WHERE PAR.Id_Producto=PF.Id_Producto ORDER BY AR.Fecha_Creacion DESC LIMIT 1),0) as Costo,";
            $producto = 'Nombre_General as Nombre_Producto';
            if ($tipo == "Dispensacion") {
                $query2 = 'SELECT 2, MONTH(F.Fecha_Documento) as Mes, "INS" as tipo,
                  VP.valor as Codigo_Cum, P.Id_Producto,
                  PR.Precio as Precio_Regulacion,
                  MAX(PF.Precio) as Maximo,
                  MIN(PF.Precio) as Minimo,' . $maximo_Factura . $minimo_Factura . $subtotal_Factura . $cantidad_Factura . $costo_Factura . $producto . ', F.Cufe 
                  FROM Producto_Factura PF INNER JOIN Factura F ON PF.Id_Factura=F.Id_Factura  INNER JOIN Producto P ON PF.Id_Producto = P.Id_Producto
                  LEFT JOIN Precio_Regulado PR ON P.Id_Producto = PR.Id_Producto
                  LEFT JOIN variable_products VP ON P.Id_Producto = VP.product_id AND VP.category_variables_id = 1
                  WHERE MoNTH(F.Fecha_Documento)=' . $meses[$i] . ' AND F.Estado_Factura != "Anulada" AND YEAR(F.Fecha_Documento)="' . $ano . '" AND P.Id_Categoria = 5 AND F.Tipo!="Homologo" GROUP by VP.valor ORDER BY VP.valor, Id_Producto';
            } elseif ($tipo == "Cliente") {
                $query2 = 'SELECT 2, MONTH(F.Fecha_Documento) as Mes, "INS" as tipo,
                    VP.valor as Codigo_Cum, P.Id_Producto,
                    PR.Precio as Precio_Regulacion,
                    MAX(PF.Precio_Venta) as Maximo,
                    MIN(PF.Precio_Venta) as Minimo,' . $maximo_factura_venta . $minimo_factura_venta . $subtotal_factura_venta . $cantidad_factura_venta . $costo_factura_venta . $producto . ', F.Cufe  
                    FROM Producto_Factura_Venta PF INNER JOIN Factura_Venta F ON PF.Id_Factura_Venta=F.Id_Factura_Venta  INNER JOIN Producto P ON PF.Id_Producto = P.Id_Producto
                    LEFT JOIN Precio_Regulado PR ON P.Id_Producto = PR.Id_Producto
                    LEFT JOIN variable_products VP ON P.Id_Producto = VP.product_id AND VP.category_variables_id = 1
                    WHERE MoNTH(F.Fecha_Documento)=' . $meses[$i] . ' AND F.Estado != "Anulada" AND YEAR(F.Fecha_Documento)="' . $ano . '" AND P.Id_Categoria = 5 GROUP by P.Id_Producto  ORDER BY VP.valor, Id_Producto';
            }
            $oCon = new consulta();
            $oCon->setQuery($query2);
            $oCon->setTipo('Multiple');
            if ($i == 0) {
                $mes1 = $oCon->getData();
            } else if ($i == 1) {
                $mes2 = $oCon->getData();
            } else if ($i == 2) {
                $mes3 = $oCon->getData();
            }
            unset($oCon);
        }
        $resultado = [];
        $id = 0;
        $i = -1;
        $total = 0;
        foreach ($mes1 as $producto) {
            $i++;
            if ($id != (int) $producto->Id_Producto) {
                $id = (int) $producto->Id_Producto;
                $minimo = (int) $producto->Minimo;
                $maximo = (int) $producto->Maximo;
                $cantidad = $producto->Cantidad;
                $fac_max = $producto->Maximo_Factura;
                $fac_min = $producto->Minimo_Factura;
                $subtotal = $producto->Precio;
                $total = (int) $producto->Cantidad;
                $subtotal = $subtotal + $producto->Precio;
            } else {
                $total = $producto->Cantidad + $total;
                $subtotal = $producto->Precio;
                $minimo = (int) $producto->Minimo;
                $maximo = (int) $producto->Maximo;
                $subtotal = $producto->Precio + $subtotal;
                if ((int) $producto->Minimo < $minimo) {
                    $minimo = (int) $producto->Minimo;
                    $fac_min = $producto->Minimo_Factura;
                }
                if ((int) $producto->Maximo > $maximo) {
                    $maximo = (int) $producto->Maximo;
                    $fac_max = $producto->Maximo_Factura;
                }
                $id = (int) $producto->Id_Producto;
                $mes1[$i]->Maximo_Factura = $fac_max;
                $mes1[$i]->Minimo_Factura = $fac_min;
                $mes1[$i]->Minimo = $minimo;
                $mes1[$i]->Maximo = $maximo;
                $mes1[$i]->Cantidad = $total;
                $mes1[$i]->Precio = $subtotal;
                $total = 0;
                $subtotal = 0;
                unset($mes1[$i - 1]);
            }
        }
        $mes1 = array_values($mes1);
        $id = 0;
        $i = -1;
        $total = 0;
        foreach ($mes2 as $producto) {
            $i++;
            if ($id != (int) $producto->Id_Producto) {
                $id = (int) $producto->Id_Producto;
                $minimo = (int) $producto->Minimo;
                $maximo = (int) $producto->Maximo;
                $cantidad = (int) $producto->Cantidad;
                $fac_max = $producto->Maximo_Factura;
                $fac_min = $producto->Minimo_Factura;
                $subtotal = $producto->Precio;
                $total = (int) $producto->Cantidad;
                $subtotal = $subtotal + $producto->Precio;
            } else {
                $total = (int) $producto->Cantidad + $total;
                $subtotal = $producto->Precio + $subtotal;
                if ((int) $producto->Minimo < $minimo) {
                    $minimo = (int) $producto->Minimo;
                    $fac_min = $producto->Minimo_Factura;
                }
                if ((int) $producto->Maximo > $maximo) {
                    $maximo = (int) $producto->Maximo;
                    $fac_max = $producto->Maximo_Factura;
                }
                $id = (int) $producto->Id_Producto;
                $mes2[$i]->Maximo_Factura = $fac_max;
                $mes2[$i]->Minimo_Factura = $fac_min;
                $mes2[$i]->Minimo = $minimo;
                $mes2[$i]->Maximo = $maximo;
                $mes2[$i]->Cantidad = $total;
                $mes2[$i]->Precio = $subtotal;
                $total = 0;
                unset($mes2[$i - 1]);
            }
        }
        $mes2 = array_values($mes2);
        $id = 0;
        $i = -1;
        $total = 0;
        foreach ($mes3 as $producto) {
            $i++;
            if ($id != (int) $producto->Id_Producto) {
                $id = (int) $producto->Id_Producto;
                $minimo = (int) $producto->Minimo;
                $maximo = (int) $producto->Maximo;
                $cantidad = (int) $producto->Cantidad;
                $fac_max = $producto->Maximo_Factura;
                $fac_min = $producto->Minimo_Factura;
                $subtotal = $producto->Precio;
                $total = (int) $producto->Cantidad;
            } else {
                $total = (int) $producto->Cantidad + $total;
                $subtotal = $producto->Precio + $subtotal;
                if ((int) $producto->Minimo < $minimo) {
                    $minimo = (int) $producto->Minimo;
                    $fac_min = $producto->Minimo_Factura;
                }
                if ((int) $producto->Maximo > $maximo) {
                    $maximo = (int) $producto->Maximo;
                    $fac_max = $producto->Maximo_Factura;
                }
                $id = (int) $producto->Id_Producto;
                $mes3[$i]->Maximo_Factura = $fac_max;
                $mes3[$i]->Minimo_Factura = $fac_min;
                $mes3[$i]->Minimo = $minimo;
                $mes3[$i]->Maximo = $maximo;
                $mes3[$i]->Cantidad = $total;
                $mes3[$i]->Precio = $subtotal;
                $total = 0;
                unset($mes3[$i - 1]);
            }
        }
        $mes3 = array_values($mes3);
        $resultado = array_merge($mes1, $mes2, $mes3);

        // return $resultado;
        $ij=0;
        foreach($resultado as $item){
            $ij++;
            $item->iter = $ij; 
        }
        return Excel::download(new ReporteSismedExport($resultado), 'reporte_sismed.xlsx');
    }

    public function reporteSismedPlanoCompra(Request $request)
    {
        $meses = explode("-", $request->input('meses', ''));
        $ano = $request->input('ano', '');

        $resultados = [];

        foreach ($meses as $mes) {
            $query = ProductoActaRecepcion::query()
                ->selectRaw('
                    MONTH(FAR.Fecha_Factura) as Mes,
                    P.Nombre_Comercial,
                    P.Nombre_General,
                    PR.Precio as Precio_Regulacion,
                    MAX(Producto_Acta_Recepcion.Precio) as Maximo,
                    MIN(Producto_Acta_Recepcion.Precio) as Minimo,
                    MAX(CONCAT(Producto_Acta_Recepcion.Precio, "-", FAR.Factura)) AS Maximo_Factura,
                    MIN(CONCAT(Producto_Acta_Recepcion.Precio, "-", FAR.Factura)) AS Minimo_Factura,
                    SUM(Producto_Acta_Recepcion.Precio * Producto_Acta_Recepcion.Cantidad) AS Precio,
                    SUM(Producto_Acta_Recepcion.Cantidad) AS Cantidad,
                    IFNULL(CONCAT(P.Nombre_Comercial, " (", P.Nombre_General, ") ", P.Cantidad_Maxima, " ", P.Unidad_Medida), P.Nombre_Comercial) as Nombre_Producto
                ')
                ->join('Factura_Acta_Recepcion as FAR', 'Producto_Acta_Recepcion.Id_Acta_Recepcion', '=', 'FAR.Id_Acta_Recepcion')
                ->join('Producto as P', 'Producto_Acta_Recepcion.Id_Producto', '=', 'P.Id_Producto')
                ->leftJoin('Precio_Regulado as PR', 'P.Id_Producto', '=', 'PR.Id_Producto')
                ->whereMonth('FAR.Fecha_Factura', $mes)
                ->whereYear('FAR.Fecha_Factura', $ano)
                ->whereIn('P.Id_Categoria', [12, 8, 9, 3, 5, 10])
                ->groupBy('P.Id_Producto')
                ->get();

            $resultados = array_merge($resultados, $query->toArray());
        }

        return response()->json($resultados);
    }

    public function reporteSismedPlano(Request $request)
    {
        $meses = explode("-", $request->input('meses', ''));
        $ano = $request->input('ano', '');
        $tipo = $request->input('tipo', 'Dispensacion');

        $resultados = [];

        foreach ($meses as $mes) {
            $query = Product::query()
                ->selectRaw('
                    MONTH(F.Fecha_Documento) as Mes,
                    Producto.Id_Producto,
                    Producto.Nombre_Comercial,
                    Producto.Nombre_General,
                    MAX(PF.Precio) as Maximo,
                    MIN(PF.Precio) as Minimo,
                    (SELECT F1.Codigo FROM Producto_Factura PF1 INNER JOIN Factura F1 ON PF1.Id_Factura=F1.Id_Factura WHERE PF1.Id_Producto=Producto.Id_Producto AND MONTH(F1.Fecha_Documento)=? AND YEAR(F1.Fecha_Documento)=? AND F1.Tipo!="Homologo" ORDER BY PF1.Precio DESC LIMIT 1) as Maximo_Factura,
                    (SELECT F1.Codigo FROM Producto_Factura PF1 INNER JOIN Factura F1 ON PF1.Id_Factura=F1.Id_Factura WHERE PF1.Id_Producto=Producto.Id_Producto AND MONTH(F1.Fecha_Documento)=? AND YEAR(F1.Fecha_Documento)=? AND F1.Tipo!="Homologo" ORDER BY PF1.Precio ASC LIMIT 1) as Minimo_Factura,
                    SUM(PF.Precio * PF.Cantidad) as Precio,
                    SUM(PF.Cantidad) as Cantidad,
                    IFNULL((SELECT PAR.Precio FROM Producto_Acta_Recepcion PAR INNER JOIN Acta_Recepcion AR ON PAR.Id_Acta_Recepcion=AR.Id_Acta_Recepcion WHERE PAR.Id_Producto=Producto.Id_Producto ORDER BY AR.Fecha_Creacion DESC LIMIT 1), 0) as Costo,
                    IFNULL(CONCAT(Producto.Nombre_Comercial, " (", Producto.Nombre_General, ") ", Producto.Cantidad_Maxima, " ", Producto.Unidad_Medida, " "), Producto.Nombre_Comercial) as Nombre_Producto
                ', [$mes, $ano, $mes, $ano])
                ->join('Producto_Factura as PF', 'PF.Id_Producto', '=', 'Producto.Id_Producto')
                ->join('Factura as F', 'PF.Id_Factura', '=', 'F.Id_Factura')
                ->whereMonth('F.Fecha_Documento', $mes)
                ->whereYear('F.Fecha_Documento', $ano)
                ->where('F.Estado_Factura', '!=', 'Anulada')
                ->whereIn('Producto.Id_Categoria', [12, 8, 9, 3, 5, 10])
                ->where('F.Tipo', '!=', 'Homologo')
                ->groupBy('Producto.Id_Producto')
                ->orderBy('Producto.Id_Producto')
                ->get();

            $resultados = array_merge($resultados, $query->toArray());
        }

        return response()->json($resultados);
    }
    public function reporteVentas()
{
    $condicion = $this->SetCondiciones($_REQUEST);
    $query = $this->CrearQuery();
    $this->ArmarReporte($query);
}

function ArmarReporte($query)
{
    $encabezado = $this->GetEncabezado($query);
    $datos = $this->GetDatos($query);
    $contenido = '';
    if ($encabezado) {
        $contenido .= '<table border="1"><tr>';
        foreach ($encabezado as $key => $value) {
            $contenido .= '<td>' . $key . '</td>';
        }
        $contenido .= '</tr>';
    }
    if ($datos) {
        foreach ($datos as $i => $dato) {
            $contenido .= '<tr>';
            foreach ($dato as $key => $value) {
                if ($this->ValidarKey($key)) {
                    $valor = $dato->$key != '' ? $dato->$key : 0;
                    $contenido .= '<td>' . number_format($valor, 2, ",", "") . '</td>';
                } else {
                    $contenido .= '<td>' . $dato->$key . '</td>';
                }
            }
            $contenido .= '</tr>';
        }
        $contenido .= '</table>';
    }
    if ($contenido == '') {
        $contenido .= '
            <table>
                <tr>
                    <td>NO EXISTE INFORMACION PARA MOSTRAR</td>
                </tr>
            </table>
        ';
    }
    echo $contenido;
}

function GetEncabezado($query)
{
    $oCon = new consulta();
    $oCon->setQuery($query);
    $encabezado = $oCon->getData();
    unset($oCon);
    return $encabezado;
}

function GetDatos($query)
{
    $oCon = new consulta();
    $oCon->setQuery($query);
    $oCon->setTipo('Multiple');
    $datos = $oCon->getData();
    unset($oCon);
    return $datos;
}

function ValidarKey($key)
{
    $datos = ["Nada", "PrecioVenta", "Subtotal"];
    $pos = array_search($key, $datos);
    return strval($pos);
}

function CrearQuery()
{
    global $condicion;

    $query = 'SELECT P.Nombre_Comercial, SU.Nombre as Nombre_Subcategoria,CN.Nombre as Nombre_Categoria_Nueva,
        CONCAT_WS(" ", P.Referencia, P.Unidad_Medida) as Producto,     
        PFV.Cantidad as Cantidad,
        PFV.Precio_Venta as PrecioVenta,
        (PFV.Cantidad * PFV.Precio_Venta) as Subtotal,
        (CASE  
          WHEN P.Gravado = "Si"  THEN "19%" 
          ELSE "0%" 
        END) as Impuesto, 
        F.Codigo as Factura, "Factura" as Tipo_Factura,  "Venta" Tipo_Cliente ,IFNULL(CONCAT(FUN.identifier, " - ", FUN.first_name, " ", FUN.first_surname), CONCAT(FUN.identifier, " - ", FUN.full_name)) AS Funcionario, DATE(F.Fecha_Documento) as Fecha 
        FROM Producto_Factura_Venta PFV          
        INNER JOIN Producto P ON PFV.Id_Producto = P.Id_Producto
        INNER JOIN Factura_Venta F ON PFV.Id_Factura_Venta = F.Id_Factura_Venta 
        INNER JOIN Subcategoria SU ON P.Id_Subcategoria = SU.Id_Subcategoria
        INNER JOIN Categoria_Nueva CN ON SU.Id_Categoria_Nueva = CN.Id_Categoria_Nueva
        INNER JOIN people FUN ON F.Id_Funcionario = FUN.identifier 
        WHERE F.Estado != "Anulada" ' . $condicion . '
    
        UNION ALL (
        SELECT P.Nombre_Comercial, SU.Nombre as Nombre_Subcategoria,CN.Nombre as Nombre_Categoria_Nueva,
        CONCAT_WS(" ", P.Referencia, P.Unidad_Medida) as Producto,
        PF.Cantidad,
        PF.Precio as PrecioVenta,
        (PF.Cantidad * PF.Precio) as Subtotal,
        (CASE  
          WHEN P.Gravado = "Si"  THEN "19%" 
          ELSE "0%" 
        END) as Impuesto,
        F.Codigo as Factura, F.Tipo as Tipo_Factura,  "Dispensacion" Tipo_Cliente, IFNULL(CONCAT(FUN.identifier, " - ", FUN.first_name, " ", FUN.first_surname), CONCAT(FUN.identifier, " - ", FUN.full_name)) AS Funcionario, DATE(F.Fecha_Documento) as Fecha
        FROM Producto_Factura PF 
        INNER JOIN Factura F ON PF.Id_Factura = F.Id_Factura
        INNER JOIN Producto P ON PF.Id_Producto = P.Id_Producto
        INNER JOIN Subcategoria SU ON P.Id_Subcategoria = SU.Id_Subcategoria
        INNER JOIN Categoria_Nueva CN ON SU.Id_Categoria_Nueva = CN.Id_Categoria_Nueva
        INNER JOIN people FUN ON F.Id_Funcionario = FUN.identifier 
        WHERE F.Estado_Factura != "Anulada" ' . $condicion . '
        ) ORDER BY Nombre_Comercial ASC';

    return $query;
}

function SetCondiciones($req)
{
    $condicion = '';
    $fecha_inicio = '';
    $fecha_fin = '';
    if (isset($req['fini']) && $req['fini'] != "" && $req['fini'] != "undefined") {
        $fecha_inicio = $req['fini'];
    }
    if (isset($_REQUEST['ffin']) && $_REQUEST['ffin'] != "" && $_REQUEST['ffin'] != "undefined") {
        $fecha_fin = $_REQUEST['ffin'];
    }

    if ($fecha_fin != '' && $fecha_inicio != '') {
        $condicion = " AND (DATE(F.Fecha_Documento) BETWEEN '$fecha_inicio' AND '$fecha_fin') ";
    }

    return $condicion;
}

}
