<?php

namespace App\Http\Controllers;

use App\Http\Services\consulta;
use Illuminate\Http\Request;

class ProductoDocInventarioAuditableController extends Controller
{

    public function showInventarioTerminado(Request $request)
    {
        $documento = isset($_REQUEST['Id']) ? $_REQUEST['Id'] : false;

        $query = "SELECT PDA.Lote, PDA.Cantidad_Auditada,
        DA.Fecha_Fin AS  Fecha_Realizado,
        PDA.Fecha_Vencimiento, PDA.Cantidad_Inventario, PDA.Primer_Conteo As Cantidad_Encontrada, PDA.Id_Producto,
        PDA.Id_Producto_Doc_Inventario_Auditable,
        P.Nombre_Comercial, E.Nombre As Estiba, E.Id_Estiba, E.Nombre As Nombre_Estiba,
        GE.Nombre  As Nombre_Grupo,

        PDA.Primer_Conteo,
        PDA.Segundo_Conteo,
        PDA.Fecha_Primer_Conteo,
        PDA.Fecha_Segundo_Conteo,

        (CASE WHEN (PDA.Segundo_Conteo) < (PDA.Cantidad_Inventario)
        THEN CONCAT('', PDA.Segundo_Conteo - PDA.Cantidad_Inventario)
        WHEN (PDA.Segundo_Conteo) >= (PDA.Cantidad_Inventario)
        THEN CONCAT('+', PDA.Segundo_Conteo - PDA.Cantidad_Inventario)
        END )
        AS Cantidad_Diferencial,

        CONCAT(
        IFNULL(P.Principio_Activo, '  '), ' ',
        P.Presentacion,' ',
        IFNULL(P.Concentracion, '  '), ' ',
        P.Cantidad,' ',
        P.Unidad_Medida,  ' LAB: ',
        P.Laboratorio_Comercial
         ) AS Nombre_Producto

        FROM Producto_Doc_Inventario_Auditable  As  PDA
        INNER JOIN Producto As P ON  P.Id_Producto = PDA.Id_Producto
        INNER JOIN Estiba As E ON  E.Id_Estiba = PDA.Id_Estiba
        INNER JOIN Grupo_Estiba As GE ON  E.Id_Grupo_Estiba = GE.Id_Grupo_Estiba
        INNER JOIN Doc_Inventario_Auditable As DA ON  DA.Id_Doc_Inventario_Auditable =   PDA.Id_Doc_Inventario_Auditable
        WHERE DA.Id_Doc_Inventario_Auditable = $documento
        AND DA.Estado='Terminado'
        ORDER BY P.Nombre_Comercial ASC
        ";

        //return response()->json($query);

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $Inventario = $oCon->getData();

        unset($oCon);

        if ($Inventario) {

            $producto["Mensaje"] = 'Bodegas Encontradas con éxito';
            $resultado["Tipo"] = "success";
            $resultado["Inventario"] = $Inventario;
            $resultado["documento"] = $documento;

        } else {

            $resultado["Tipo"] = "error";
            $resultado["Titulo"] = "Error al intentar buscar las bodegas";
            $resultado["Texto"] = "Ha ocurrido un error inesperado.";
        }

        return response()->json($resultado);
    }
}
