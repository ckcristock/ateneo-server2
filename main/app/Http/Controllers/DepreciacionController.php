<?php

namespace App\Http\Controllers;

use App\Models\Depreciacion;
use Illuminate\Http\Request;
use App\Http\Services\PaginacionData;
use App\Http\Services\consulta;
use App\Http\Services\complex;
use App\Http\Services\Utility;
use App\Http\Services\Contabilizar;
use App\Http\Services\QueryBaseDatos;
use Barryvdh\DomPDF\Facade\Pdf;

class DepreciacionController extends Controller
{
    public function paginate()
    {
        $pag = (isset($_REQUEST['pag']) ? $_REQUEST['pag'] : '');
        $tam = (isset($_REQUEST['tam']) ? $_REQUEST['tam'] : '');
        $condicion = $this->setCondiciones($_REQUEST);
        $years = ["2019", "2020"];
        $query = '
        SELECT
            D.*,Concat(F.first_name," ",F.first_surname) as Funcionario, F.image
        FROM Depreciacion D
        INNER JOIN people F ON D.Identificacion_Funcionario = F.id
        ' . $condicion . ' ORDER BY Anio DESC, Mes DESC';
        $query_count = '
        SELECT
            COUNT(D.Id_Depreciacion) AS Total
            FROM Depreciacion D
            INNER JOIN people F ON D.Identificacion_Funcionario = F.id
        ' . $condicion;
        $paginationData = new PaginacionData($tam, $query_count, $pag);
        $queryObj = new QueryBaseDatos($query);
        $actas_realizadas = $queryObj->Consultar('Multiple', true, $paginationData);

        foreach ($actas_realizadas['query_result'] as $key => $value) {
            //$actas_realizadas['query_result'][$key]['Nombre_Mes'] = $this->mesString($value['Mes']);
            $value['Nombre_Mes'] = $this->mesString($value['Mes']);
        }
        return response()->json($actas_realizadas);
    }

    public function setCondiciones($req)
    {
        $util = new Utility();
        $condicion = '';
        if (isset($req['codigo_orden']) && $req['codigo_orden']) {
            $condicion .= " WHERE D.Codigo LIKE '%" . $req['codigo_orden'] . "%'";
        }
        if (isset($req['func']) && $req['func']) {
            if ($condicion != "") {
                $condicion .= " AND CONCAT(F.Nombres,' ', F.Apellidos) LIKE '%" . $req['func'] . "%'";
            } else {
                $condicion .= " WHERE CONCAT(F.Nombres,' ', F.Apellidos) LIKE '%" . $req['func'] . "%'";
            }
        }
        if (isset($req['estado']) && $req['estado']) {
            if ($condicion != "") {
                $condicion .= " AND D.Estado LIKE '%" . $req['estado'] . "%'";
            } else {
                $condicion .= " WHERE D.Estado LIKE '%" . $req['estado'] . "%'";
            }
        }
        if (isset($req['fechas_acta']) && $req['fechas_acta']) {
            $fechas_separadas = $util->SepararFechas($req['fechas_acta']);

            if ($condicion != "") {
                $condicion .= " AND D.Fecha_Registro >= '" . $fechas_separadas[0] . "' AND D.Fecha_Registro <= '" . $fechas_separadas[1] . "'";
            } else {
                $condicion .= " WHERE D.Fecha_Registro >= '" . $fechas_separadas[0] . "' AND D.Fecha_Registro <= '" . $fechas_separadas[1] . "'";
            }
        }
        return $condicion;
    }

    public function mesString($mes_index)
    {
        $meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        return  $meses[($mes_index - 1)];
    }

    public function pdf()
    {
        $id_registro = (isset($_REQUEST['id_registro']) ? $_REQUEST['id_registro'] : '');
        $id_funcionario_imprime = (isset($_REQUEST['id_funcionario_elabora']) ? $_REQUEST['id_funcionario_elabora'] : '');
        $tipo_valor = (isset($_REQUEST['tipo_valor']) ? $_REQUEST['tipo_valor'] : '');
        $id_modulo = 31;
        $titulo = $tipo_valor != '' ? "CONTABILIZACIÓN NIIF" : "CONTABILIZACIÓN PCGA";
        if ($tipo_valor == 'PCGA') {
            $titulo = 'CONTABILIZACIÓN PCGA';
        }
        $queryObj = new QueryBaseDatos();
        $oItem = new complex('Configuracion', "Id_Configuracion", 1);
        $config = $oItem->getData();
        unset($oItem);
        /* FIN DATOS GENERALES DE CABECERAS Y CONFIGURACION */
        $oItem = new complex('Depreciacion', 'Id_Depreciacion', $id_registro);
        $datos = $oItem->getData();
        unset($oItem);
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
         Id_Modulo = ' . $id_modulo . ' AND Id_registro_Modulo =' . $id_registro . ' ORDER BY Debe DESC';
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
         Id_Modulo = ' . $id_modulo . ' AND Id_registro_Modulo =' . $id_registro;

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
        <h4 style="margin:5px 0 0 0;font-size:19px;line-height:22px;">Depreciación</h4>
        <h4 style="margin:5px 0 0 0;font-size:19px;line-height:22px;">' . optional(isset($movimientos[0]))->Numero_Comprobante . '</h4>
        <h5 style="margin:5px 0 0 0;font-size:16px;line-height:16px;">Fecha ' . $this->fecha(optional(isset($movimientos[0]))->Fecha) . '</h5>
        ';

        $contenido = '<table style="font-size:10px;margin-top:10px;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:78px;font-weight:bold;text-align:center;background:#cecece;;border:1px solid #cccccc;">
                Cuenta ' . $tipo_valor . '
            </td>
            <td style="width:170px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
               Nombre Cuenta ' . $tipo_valor . '
            </td>
            <td style="width:115px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
               Documento
            </td>
            <td style="width:115px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                Nit
            </td>
            <td style="width:115px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                Debitos ' . $tipo_valor . '
            </td>
            <td style="width:115px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                Crédito ' . $tipo_valor . '
            </td>
        </tr>';

        if (count($movimientos) > 0) {
            foreach ($movimientos as $value) {
                if ($tipo_valor != '') {
                    $codigo = $value->Codigo_Niif;
                    $nombre_cuenta = $value->Nombre_Niif;
                    $debe = $value->Debe_Niif;
                    $haber = $value->Haber_Niif;
                    $total_debe = $movimientos_suma["Debe_Niif"];
                    $total_haber = $movimientos_suma["Haber_Niif"];
                } else {
                    $codigo = $value->Codigo;
                    $nombre_cuenta = $value->Nombre;
                    $debe = $value->Debe;
                    $haber = $value->Haber;
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
                        ' . $value->Documento . '
                    </td>
                    <td style="width:100px;padding:4px;text-align:right;border:1px solid #cccccc;">
                       ' . $value->Nombre_Cliente . ' - ' . $value->Nit . '
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
        $cabecera = '<table style="" >
              <tbody>
                <tr>
                  <td style="width:70px;">
                  </td>
                  <td class="td-header" style="width:410px;font-weight:thin;font-size:14px;line-height:20px;">
                  </td>
                  <td style="width:250px;text-align:right">
                        ' . $codigos . '
                  </td>
                </tr>
              </tbody>
            </table><hr style="border:1px dotted #ccc;width:730px;">';
        /* FIN CABECERA GENERAL DE TODOS LOS ARCHIVOS PDF*/
        $marca_agua = '';
        if ($datos['Estado'] == 'Anulada') {
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
        $pdf = PDF::loadHtml($contenido);
        return $pdf->download('NIIF.pdf');
        /* try {
            $html2pdf = new HTML2PDF('P', 'A4', 'Es', true, 'UTF-8', array(5, 5, 5, 5));
            $html2pdf->writeHTML($content);
            $direc = $data["Codigo"] . '.pdf'; // NOMBRE DEL ARCHIVO ES EL CODIGO DEL DOCUMENTO
            $html2pdf->Output($direc); // LA D SIGNIFICA DESCARGAR, 'F' SE PODRIA HACER PARA DEJAR EL ARCHIVO EN UNA CARPETA ESPECIFICA
        } catch (HTML2PDF_exception $e) {
            echo $e;
            exit;
        } */
    }

    private function fecha($str)
    {
        $parts = explode(" ", $str);
        $date = explode("-", $parts[0]);
        return isset($date[2]) . "/" . isset($date[1]) . "/" . isset($date[0]);
    }

    private function fecha2($fecha)
    {
        return date('d/m/Y', strtotime($fecha));
    }

    private function getDatosTiposActivos($tipo, $mes, $year, $guardar = false)
    {
        $tipos_activos = $this->getTiposActivos($mes, $year);
        //dd($tipo);
        foreach ($tipos_activos as $i => $tipo_act) {
            $activos_fijos = $this->activosFijosDepreciar($tipo_act->ID, $tipo_act->{'Vida_Util_' . $tipo}, $tipo, $mes, $year, $guardar);
            if (count($activos_fijos) > 0) {
                $tipos_activos[$i]->activos_fijos = $activos_fijos;
            } else {
                unset($tipos_activos[$i]);
            }
        }
        return $tipos_activos;
    }

    private function activosFijosDepreciar($id_tipo_activo, $vida_util, $tipo_reporte, $mes, $year, $guardar)
    {
        $mes_dep = $this->mesFormat($mes);
        //$fecha = date('Y') . '-'. $mes_dep;
        $fecha = $year . '-' . $mes_dep;
        $fecha_adificion = $fecha . '-01';
        //$fecha_anterior = $mes != 1 ? date('Y') . '-' . (mesFormat($mes-1)) : strval((intval(date('Y'))-1)) . '-12';
        $fecha_anterior = $mes != 1 ? $year . '-' . ($this->mesFormat($mes - 1)) : ($year - 1) . '-12';
        $query = "
        SELECT AF.Id_Activo_Fijo AS ID, AF.Nombre, DATE(AF.Fecha) AS Fecha,
        (AF.Costo_PCGA + COALESCE( A.Adiciones_PCGA ,0) ) AS Costo_PCGA ,
         (AF.Costo_NIIF + COALESCE( A.Adiciones_NIIF ,0) ) AS Costo_NIIF ,
        AF.Tipo_Depreciacion, $vida_util AS Vida_Util, R.Vida_Util_Acum, R.Depreciacion_Acum_$tipo_reporte
        FROM Activo_Fijo AF
        LEFT JOIN
        (SELECT
            r.Id_Activo_Fijo,
            SUM(r.Vida_Util_Acum) AS Vida_Util_Acum,
            SUM(r.Depreciacion_Acum_$tipo_reporte) AS Depreciacion_Acum_$tipo_reporte
            FROM
            (
            (SELECT Id_Activo_Fijo, ($vida_util-Vida_Util_Restante_$tipo_reporte) AS Vida_Util_Acum ,
            IFNULL(SUM(Depreciacion_Acum_$tipo_reporte),0) AS Depreciacion_Acum_$tipo_reporte
            FROM Balance_Inicial_Activo_Fijo
            GROUP BY  Id_Activo_Fijo
            )
            UNION ALL( SELECT Id_Activo_Fijo,   SUM(IF(AFD.Valor_$tipo_reporte>0,1,0)) AS Vida_Util_Acum, IFNULL(SUM(AFD.Valor_$tipo_reporte),0) AS Depreciacion_Acum_$tipo_reporte
            FROM Activo_Fijo_Depreciacion AFD
            INNER JOIN Depreciacion D ON AFD.Id_Depreciacion = D.Id_Depreciacion WHERE D.Estado = 'Activo' GROUP BY AFD.Id_Activo_Fijo )
            UNION ALL(SELECT Id_Activo_Fijo, 0 AS Vida_Util_Acum, 0 AS Depreciacion_Acum_$tipo_reporte
            FROM Activo_Fijo WHERE DATE_FORMAT(Fecha, '%Y-%m') = '$fecha_anterior' AND Estado != 'Anulada')
            ) r
            GROUP BY r.Id_Activo_Fijo) R  ON AF.Id_Activo_Fijo = R.Id_Activo_Fijo
            LEFT JOIN(
                SELECT  SUM(A.Costo_NIIF) AS Adiciones_NIIF,  SUM(A.Costo_PCGA) AS Adiciones_PCGA, A.Id_Activo_Fijo
                                        FROM Adicion_Activo_Fijo A
                                        WHERE DATE(A.Fecha) < '" . $fecha_adificion . "'
                                    /* AND A.Id_Activo_Fijo =  AF.Id_Activo_Fijo*/
                                        GROUP BY A.Id_Activo_Fijo
            )A ON A.Id_Activo_Fijo =  AF.Id_Activo_Fijo
            WHERE AF.Id_Tipo_Activo_Fijo = $id_tipo_activo
            AND DATE_FORMAT(AF.Fecha, '%Y-%m') < '$fecha' AND R.Vida_Util_Acum <= $vida_util  AND AF.Estado='Activo'
            ";

        // if($guardar==true){
        //$query .= " AND ( ( AF.Tipo_Depreciacion =1 AND Costo_PCGA < R.Depreciacion_Acum_$tipo_reporte) OR AF.Tipo_Depreciacion !=1 )";
        //}
        # AND ( ( AF.Tipo_Depreciacion =1 AND Vida_Util_Acum = 0) OR AF.Tipo_Depreciacion !=1 )
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $resultado = $oCon->getData();
        unset($oCon);
        return $resultado;
    }

    private function getTiposActivos($mes, $year)
    {
        $mes = $this->mesFormat($mes);
        //$fecha = date('Y') . '-'. $mes;
        $fecha = $year . '-' . $mes;
        $query = "SELECT Id_Tipo_Activo_Fijo AS ID,
        Nombre_Tipo_Activo AS Nombre,
        Vida_Util_PCGA,
        Vida_Util AS Vida_Util_NIIF,
        Porcentaje_Depreciacion_Anual_PCGA AS Porcentaje_PCGA,
        Porcentaje_Depreciacion_Anual AS Porcentaje_NIIF,
        Id_Plan_Cuenta_Depreciacion_PCGA AS Id_Plan_Cuenta_Depreciacion,
        Id_Plan_Cuenta_Credito_Depreciacion_PCGA AS Id_Plan_Cuenta_Credito_Depreciacion
        FROM Tipo_Activo_Fijo TAF
        WHERE EXISTS
                    (SELECT Id_Activo_Fijo
                     FROM Activo_Fijo
                     WHERE Id_Tipo_Activo_Fijo = TAF.Id_Tipo_Activo_Fijo
                     AND DATE_FORMAT(Fecha, '%Y-%m') < '$fecha' AND Estado!='Anulada'
                     )";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $resultado = $oCon->getData();
        unset($oCon);

        return $resultado;
    }

    private function mesFormat($mes)
    {
        $mes = $mes > 9 ? $mes : '0' . $mes; // Para que me dé el formato 01,02,03...

        return $mes;
    }

    private function getDepreciacionAcum($tipo_reporte, $id_activo)
    {
        $query = "SELECT
        SUM(r.Depreciacion_Acum_$tipo_reporte) AS Depreciacion_Acum_$tipo_reporte
        FROM
        (
        (SELECT
            IFNULL(SUM(Depreciacion_Acum_$tipo_reporte),0) AS Depreciacion_Acum_$tipo_reporte
            FROM Balance_Inicial_Activo_Fijo
            WHERE Id_Activo_Fijo = $id_activo)
        UNION ALL
            ( SELECT IFNULL(SUM(AFD.Valor_$tipo_reporte),0) AS Depreciacion_Acum_$tipo_reporte
            FROM Activo_Fijo_Depreciacion AFD INNER JOIN Depreciacion D ON AFD.Id_Depreciacion = D.Id_Depreciacion
            WHERE  AFD.Id_Activo_Fijo = $id_activo AND D.Estado = 'Activo')
        ) r";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $resultado = $oCon->getData();
        unset($oCon);

        return $resultado['Depreciacion_Acum_' . $tipo_reporte];
    }

    private function numberFormat($num)
    {
        return number_format($num, 2, ",", "");
    }

    private function strUltimoMesAnioAnterior()
    {
        $anio_anterior = intval(date('Y')) - 1;
        return $anio_anterior . "-12";
    }

    private function activoDepreciado($mes, $anio, $id_activo, $tipo_reporte)
    {
        //   $mes_dep = $mes != 1 ? ($mes-1) : 12;
        $mes_dep = $mes;
        // $anio = $mes != 1 ? $anio : (intval(date('Y'))-1);
        $query = "SELECT AFD.Id_Activo_Fijo_Depreciacion,  AFD.Valor_$tipo_reporte AS Valor_Depreciacion
           FROM Activo_Fijo_Depreciacion AFD
           INNER JOIN Depreciacion D ON D.Id_Depreciacion = AFD.Id_Depreciacion WHERE D.Anio = $anio AND D.Mes = $mes_dep AND AFD.Id_Activo_Fijo = $id_activo AND D.Estado = 'Activo'";
        /*if($id_activo == 1){
            var_dump($mesReporte);
            //  $mes_compra = date('n', strtotime($fecha_compra));
                //   var_dump($fecha_compra);
                    var_dump($query);


            }*/
        $oCon = new consulta();
        $oCon->setQuery($query);
        $resultado = $oCon->getData();
        unset($oCon);
        if ($resultado) {
            return $resultado;
        }
        return false;
    }

    private function calcularDepreciacionMes($mes, $year, $id_activo, $mesReporte, $porcentaje, $costo, $vida_util, $vida_util_acum, $fecha_compra, $depreciacion_acum, $tipo_dep)
    {
        $valorDepreciacion = '0';
        //$anio = date('Y');
        $anio = $year;
        $mesReporte = (int)$mesReporte;
        $mes = (int)$mes;
        $depreciadoTotal = $vida_util == $vida_util_acum ? true : false;
        $anio_compra = date('Y', strtotime($fecha_compra));
        $anio_mes_compra = date('Y-m', strtotime($fecha_compra));
        $ultimo_mes_anio_anterior = $this->strUltimoMesAnioAnterior();

        //  if( floatval($costo) > floatval($depreciacion_acum)  ){

        if (!$depreciadoTotal || $vida_util == 1) { // Solo se depreciaran los de este año, o los que se compraron en diciembre del año anterior.
            $estaDepreciado = $this->activoDepreciado($mesReporte, $anio, $id_activo, $tipo_dep);
            if ($vida_util != 1) {
                if ($mes <= $mesReporte) { // 3 <= 1 --> NO
                    if (($estaDepreciado && ($mesReporte < $mes)) || ($mes == $mesReporte  && floatval($costo) > floatval($depreciacion_acum))) {
                        $porcentaje = $porcentaje / 100;
                        $valorDepreciacion = $costo * $porcentaje;
                        if (($valorDepreciacion + $depreciacion_acum) > $costo) {
                            $valorDepreciacion  =   $costo - $depreciacion_acum;
                        }
                        //   var_dump($porcentaje);
                        //    var_dump($valorDepreciacion);
                    }
                } elseif (($mesReporte < $mes) && $estaDepreciado) { // 1 < 3
                    $porcentaje = $porcentaje / 100;
                    $valorDepreciacion = $costo * $porcentaje;
                    $valorDepreciacion = $estaDepreciado['Valor_Depreciacion'] ? $estaDepreciado['Valor_Depreciacion'] : 0;
                }
            } else {
                $mes_compra = date('n', strtotime($fecha_compra));
                $mes_siguiente = $mes_compra == 12 ? 1 : $mes_compra + 1;

                if ($mes_siguiente == $mesReporte  &&   $mesReporte <= $mes) {
                    $valorDepreciacion = $costo - $depreciacion_acum;
                    //316
                    /*if($id_activo == 320){
                            var_dump('en3');
                                        var_dump($mes);
                        var_dump($mesReporte);
                        //  $mes_compra = date('n', strtotime($fecha_compra));
                            //   var_dump($fecha_compra);
                                 var_dump($mes_compra);
                                 var_dump($valorDepreciacion);
                        exit;
                        }*/
                }
            }
        }

        //}
        return $valorDepreciacion;
    }

    public function vistaPrevia()
    {
        $tipo = isset($_REQUEST['Tipo']) ? $_REQUEST['Tipo'] : '';
        $mes_hoy = isset($_REQUEST['Mes']) ? $_REQUEST['Mes'] : 1156;
        $year_select = isset($_REQUEST['Year']) ? $_REQUEST['Year'] : date('Y');

        $meses = ["ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];

        $years = ["2019", "2020"];
        /*
        $years = [];
        $year_ini=2018;
        $year_actual = date('Y');
        $i=0;

        for($i=$year_ini;$i<=$year_actual;$i++){
            $years = array_push($years, $i);
        }
        */
        /*
        $pila = array("naranja", "plátano");
        array_push($pila, "manzana", "arándano");
        print_r($pila);
        */
        $tipos_activos = $this->getDatosTiposActivos($tipo, $mes_hoy, $year_select);

        $costo_historico = 0;
        $totales_anio = 0;
        $totales_anio_actual = 0;
        $totales_depre_acum = 0;
        $totales_antes_depre_acum = 0;
        $totales_antes_saldo_neto = 0;
        $totales_saldo_neto = 0;
        $totalesDepreciadoMeses = [];

        $contenido = '';

        $contenido .= '
            <table>
            <tr>
                <td colspan="10">
                    <h2></h2>
                </td>
                <td colspan="11">
                    <h2>' . $tipo . '</h2>
                </td>
            </tr>
            </table>
            <table border="1">
            <tr>
                <td width="250"><strong>DETALLE</strong></td>
                <td><strong>FECHA ADQUISICION</strong></td>
                <td></td>
                <td></td>
                <td><strong>COSTO HISTORICO</strong></td>
                <td><strong>DEPRECIACION ACUMULADA</strong></td>
                <td><strong>SALDO</strong></td>
                <td colspan="14" align="center"><strong>DEPRECIACION ' . $year_select . '</strong></td>
                <td rowspan="2" align="center"><strong>SALDO NETO</strong></td>
            </tr>
            <tr>
            <td width="250"></td>
            <td></td>
            <td align="center"><strong>DIAS</strong></td>
            <td align="center"><strong>PER/MES</strong></td>
            <td></td>
            <td></td>
            <td></td>
            ';
        foreach ($meses as $mes) {
            $contenido .= '<td align="center"><strong>' . $mes . '</strong></td>';
        }
        $contenido .= '
        <td align="center"><strong>TOTAL AÑO</strong></td>
        <td align="center"><strong>ACUMULADO</strong></td>

        </tr>';

        foreach ($tipos_activos as $i => $tipo_act) {
            $contenido .= '<tr>
                <td colspan="23">&nbsp;</td>
            </tr>

            <tr>
                <td colspan="23">
                <h3>' . $tipo_act->Nombre . '</h3>
                </td>
            </tr>

            <tr>
                <td colspan="23">&nbsp;</td>
            </tr>';

            foreach ($tipo_act->activos_fijos as $j => $activo) {
                $costo = $activo->{'Costo_' . $tipo};
                $depreciacion_acum = $this->getDepreciacionAcum($tipo, $activo->ID);
                $saldo_activo = $costo - $depreciacion_acum;
                $totales_antes_depre_acum += $depreciacion_acum;
                $totales_antes_saldo_neto += $saldo_activo;
                //$anio_compra = date('Y', strtotime($activo['Fecha']));
                $anio_compra = date($year_select, strtotime($activo->Fecha));
                /*$vida_util = $activo['Tipo_Depreciacion'] == 1 && $anio_compra == date('Y') ? 1 :
                    $tipo_act['Vida_Util_'.$tipo]; // Si el tipo de depreciacion es 0, se deprecia
                    de manera normal, de lo contrario, solo se depreciará a 1 mes.*/
                $vida_util = $activo->Tipo_Depreciacion == 1 && $anio_compra == $year_select ? 1 : $tipo_act->{'Vida_Util_' . $tipo};
                $contenido .= '
                <tr>
                    <td width="250">' . $activo->Nombre . '</td>
                    <td>' . $this->fecha2($activo->Fecha) . '</td>
                    <td></td>
                    <td>' . $vida_util . '</td>
                    <td align="right">' . $this->numberFormat($costo) . '</td>
                    <td align="right">' . $this->numberFormat($depreciacion_acum) . '</td>
                    <td align="right">' . $this->numberFormat($saldo_activo) . ' </td>
                    ';

                foreach ($meses as $z => $mes) {
                    $mesReporte = ($z + 1);
                    $depreciadoMes = $this->calcularDepreciacionMes(
                        $mes_hoy,
                        $year_select,
                        $activo->ID,
                        $mesReporte,
                        $tipo_act->{'Porcentaje_' . $tipo},
                        $costo,
                        $vida_util,
                        $activo->Vida_Util_Acum,
                        $activo->Fecha,
                        $activo->{'Depreciacion_Acum_' . $tipo},
                        $tipo
                    );
                    $contenido .= '<td align="right">' . $this->numberFormat($depreciadoMes) . '</td>';
                    if (!array_key_exists($mes, $totalesDepreciadoMeses)) {
                        $totalesDepreciadoMeses[$mes] = $depreciadoMes;
                    } else {
                        $totalesDepreciadoMeses[$mes] += $depreciadoMes;
                    }
                    $totales_anio_actual += $depreciadoMes;
                }

                $total_anio = $totales_anio_actual; // Le sumo lo depreciado del mes.
                $totalDepreciado = $this->getDepreciacionAcum($tipo, $activo->ID) + $total_anio;
                $saldo_activo = $costo - $totalDepreciado;
                $contenido .= '<td align="right">' . $this->numberFormat($total_anio) . '</td>
                    <td align="right">' . $this->numberFormat($totalDepreciado) . '</td>
                    <td align="right">' . $this->numberFormat($saldo_activo) . '</td>
                </tr>';

                $costo_historico += $costo;
                $totales_anio += $total_anio;
                $totales_depre_acum += $totalDepreciado;
                $totales_saldo_neto += $saldo_activo;
                $totales_anio_actual = 0;
            }
            $contenido .= '<tr>
                <td colspan="23">&nbsp;</td>
            </tr>

            <tr>
                <td width="250" align="center"><strong>TOTAL ' . $tipo_act->Nombre . ' </strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td align="right"><strong>' . $this->numberFormat($costo_historico) . '</strong></td>
                <td align="right"><strong>' . $this->numberFormat($totales_antes_depre_acum) . '</strong></td>
                <td align="right"><strong>' . $this->numberFormat($totales_antes_saldo_neto) . '</strong></td>
                ';
            foreach ($totalesDepreciadoMeses as $mes => $valor) {
                $contenido .= '<td align="right"><strong>' . $this->numberFormat($valor) . '</strong></td>';
            }
            $contenido .= '<td align="right"><strong>' . $this->numberFormat($totales_anio) . '</strong></td>
                <td align="right"><strong>' . $this->numberFormat($totales_depre_acum) . '</strong></td>
                <td align="right"><strong>' . $this->numberFormat($totales_saldo_neto) . '</strong></td>
            </tr>';

            $costo_historico = 0;
            $totales_anio = 0;
            $totales_depre_acum = 0;
            $totales_saldo_neto = 0;
            $totales_antes_depre_acum = 0;
            $totales_antes_saldo_neto = 0;
            $totalesDepreciadoMeses = [];
        }
        $contenido .= '</table>';
        echo $contenido;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $datos = isset($_REQUEST['datos']) ? $_REQUEST['datos'] : false;
        $insert = [];
        //return ($_REQUEST['datos']);
        $cuentas_depreciaciacion = [
            "Debito" => [],
            "Credito" => []
        ];
        if ($datos) {
            $contabilizar  = new contabilizar();
            $datos = (array) json_decode($datos, true);
            if ($this->validarDepreciacion($datos['Mes'], $datos["Year"])) {
                $resultado['tipo'] = "info";
                $resultado['mensaje'] = "Ya ha sido depreciado el mes seleccionado.";
                $resultado['titulo'] = "Ooops!";
            } else {
                $tipo = $datos['Tipo'];
                $mes = $this->mesFormat($datos['Mes']);
                $year = $datos['Year'];
                $cod = /* $this->generarConsecutivo('Depreciacion', $mes, $year) */ '2222generico';
                $datos['Codigo'] = $cod;
                //$datos['Anio'] = date('Y');
                $datos['Anio'] = $datos['Year'];
                $oItem = new complex('Depreciacion', 'Id_Depreciacion');
                foreach ($datos as $index => $value) {
                    $oItem->$index = $value;
                }
                $oItem->save();
                $id_depreciacion = $oItem->getId();
                unset($oItem);
                $activos = [];
                $tipos_activos = $this->getDatosTiposActivos('PCGA', $datos['Mes'], $datos['Year'], true);
                // exit;
                foreach ($tipos_activos as $i => $tipo_act) {
                    foreach ($tipo_act->activos_fijos as $j => $activo) {
                        $costo_pcga = $activo->Costo_PCGA;
                        //$anio_compra = date('Y', strtotime($activo['Fecha']));
                        $anio_compra = $year;

                        /* $vida_util_pcga = $activo['Tipo_Depreciacion'] == 1 && $anio_compra == date('Y') ? 1 :
                            $tipo_act['Vida_Util_PCGA']; // Si el tipo de depreciacion es 0, se deprecia
                            de manera normal, de lo contrario, solo se depreciará a 1 mes. */

                        $vida_util_pcga = $activo->Tipo_Depreciacion == 1 && $anio_compra == $datos['Year'] ? 1 : $tipo_act->Vida_Util_PCGA;
                        $valor_depreciacion_pcga = $this->calcularDepreciacionMes(
                            $datos['Mes'],
                            $datos["Year"],
                            $activo->ID,
                            $datos['Mes'],
                            $tipo_act->Porcentaje_PCGA,
                            $costo_pcga,
                            $vida_util_pcga,
                            $activo->Vida_Util_Acum,
                            $activo->Fecha,
                            $activo->Depreciacion_Acum_PCGA,
                            'PCGA'
                        );
                        $activos[strval($activo->ID)] = ["Pcga" => $valor_depreciacion_pcga];
                        $plan_deb = strval($tipo_act->Id_Plan_Cuenta_Depreciacion);
                        $plan_cred = strval($tipo_act->Id_Plan_Cuenta_Credito_Depreciacion);
                        if (!array_key_exists($plan_deb, $cuentas_depreciaciacion["Debito"])) {
                            $cuentas_depreciaciacion["Debito"][$plan_deb]['Pcga'] = floatval(number_format($valor_depreciacion_pcga, 2, ".", ""));
                        } else {
                            $cuentas_depreciaciacion["Debito"][$plan_deb]['Pcga'] += floatval(number_format($valor_depreciacion_pcga, 2, ".", ""));
                        }
                        if (!array_key_exists($plan_cred, $cuentas_depreciaciacion["Credito"])) {
                            $cuentas_depreciaciacion["Credito"][$plan_cred]['Pcga'] = floatval(number_format($valor_depreciacion_pcga, 2, ".", ""));
                        } else {
                            $cuentas_depreciaciacion["Credito"][$plan_cred]['Pcga'] += floatval(number_format($valor_depreciacion_pcga, 2, ".", ""));
                        }
                    }
                }

                $tipos_activos = $this->getDatosTiposActivos('NIIF', $datos['Mes'], $datos['Year']);
                foreach ($tipos_activos as $i => $tipo_act) {
                    foreach ($tipo_act->activos_fijos as $j => $activo) {
                        $costo_niif = $activo->Costo_NIIF;
                        //$anio_compra = date('Y', strtotime($activo['Fecha']));
                        $anio_compra = $datos['Year'];
                        /* $vida_util_niif = $activo['Tipo_Depreciacion'] == 1 && $anio_compra == date('Y') ? 1 :
                            $tipo_act['Vida_Util_NIIF']; // Si el tipo de depreciacion es 0, se deprecia de
                            manera normal, de lo contrario, solo se depreciará a 1 mes. */
                        $vida_util_niif = $activo->Tipo_Depreciacion == 1 && $anio_compra == $datos['Year'] ? 1 : $tipo_act->Vida_Util_NIIF;
                        $valor_depreciacion_niif = $this->calcularDepreciacionMes(
                            $datos['Mes'],
                            $datos["Year"],
                            $activo->ID,
                            $datos['Mes'],
                            $tipo_act->Porcentaje_NIIF,
                            $costo_niif,
                            $vida_util_niif,
                            $activo->Vida_Util_Acum,
                            $activo->Fecha,
                            $activo->Depreciacion_Acum_NIIF,
                            'NIIF'
                        );
                        $tipo_act->activos_fijos[$j]->depre = $valor_depreciacion_niif;
                        if (!array_key_exists(strval($activo->ID), $activos)) {
                            $activos[strval($activo->ID)] = ["Pcga" => 0, "Niif" => $valor_depreciacion_niif];
                        } else {
                            $activos[strval($activo->ID)]['Niif'] = $valor_depreciacion_niif;
                        }
                        $plan_deb = strval($tipo_act->Id_Plan_Cuenta_Depreciacion);
                        $plan_cred = strval($tipo_act->Id_Plan_Cuenta_Credito_Depreciacion);
                        if (!array_key_exists($plan_deb, $cuentas_depreciaciacion["Debito"])) {
                            $cuentas_depreciaciacion["Debito"][$plan_deb]['Pcga'] = floatval(number_format($valor_depreciacion_niif, 2, ".", ""));
                            //!DEBERÍA IR Niif
                        } else {
                            $cuentas_depreciaciacion["Debito"][$plan_deb]['Pcga'] += floatval(number_format($valor_depreciacion_niif, 2, ".", ""));
                            //!DEBERÍA IR Niif
                        }
                        if (!array_key_exists($plan_cred, $cuentas_depreciaciacion["Credito"])) {
                            $cuentas_depreciaciacion["Credito"][$plan_cred]['Pcga'] = floatval(number_format($valor_depreciacion_niif, 2, ".", ""));
                            //!DEBERÍA IR Niif
                        } else {
                            $cuentas_depreciaciacion["Credito"][$plan_cred]['Pcga'] += floatval(number_format($valor_depreciacion_niif, 2, ".", ""));
                            //!DEBERÍA IR Niif
                        }
                    }
                }
                foreach ($activos as $id => $value) {
                    if ($value['Pcga'] > 0 || $value['Niif'] > 0) {
                        $insert[] = "($id_depreciacion,$id," .
                            number_format($value['Pcga'], 2, ".", "") . "," .
                            number_format($value['Niif'], 2, ".", "") . ")"; // Armo los VALUES del insert masivamente.
                    }
                }
                $query = "INSERT INTO Activo_Fijo_Depreciacion (Id_Depreciacion,Id_Activo_Fijo,Valor_PCGA,Valor_NIIF) VALUES" . implode(',', $insert);
                $oCon = new consulta();
                $oCon->setQuery($query);
                $oCon->createData();
                unset($oCon);
                $datos_contabilizacion['Id_Registro'] = $id_depreciacion;
                $datos_contabilizacion['Datos'] = $datos;
                $datos_contabilizacion['Contabilizacion'] = $cuentas_depreciaciacion;
                $contabilizar->CrearMovimientoContable('Depreciacion', $datos_contabilizacion);
                if ($id_depreciacion) {
                    $resultado['tipo'] = "success";
                    $resultado['mensaje'] = "Se ha contabilizado correctamente la depreciación de los activos fijos con el código: " . $datos['Codigo'];
                    $resultado['titulo'] = "Exito!";
                    $resultado['Id'] = $id_depreciacion;
                } else {
                    $resultado['tipo'] = "error";
                    $resultado['mensaje'] = "Ha ocurrido un error en el proceso. Por favor vuelve a intentarlo.";
                    $resultado['titulo'] = "Ooops!";
                }
            }
            return response()->json($resultado);
        } else {
            return response()->json(['error' => 'No se ha podido realizar la depreciación. Por favor vuelve a intentarlo.']);
        }
    }

    private function generarConsecutivo()
    {
        $query = "SELECT * FROM Resolucion WHERE Modulo='NOTA CREDITO' AND Consecutivo <=Numero_Final
                 AND Estado = 'Activo' AND Fecha_Fin>=CURDATE() ORDER BY Fecha_Fin ASC LIMIT 1";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $resolucion = $oCon->getData();
        //dd($resolucion);
        unset($oCon);
        if ($resolucion && $resolucion['Id_Resolucion']) {
            $oItem = new complex('Resolucion', 'Id_Resolucion', $resolucion['Id_Resolucion']); // Resolucion 3 para Facturas Ventas NoPos
            $nc = $oItem->getData();
            unset($oItem);
            $cod = $this->getConsecutivo($nc);
            return $cod;
        } else {
            return false;
        }
    }

    private function getConsecutivo($resolucion)
    {
        $cod = $resolucion['Codigo'] != '0' ? $resolucion['Codigo'] . $resolucion['Consecutivo'] : $resolucion['Consecutivo'];
        $oItem = new complex('Resolucion', 'Id_Resolucion', $resolucion['Id_Resolucion']);
        $new_cod = $oItem->Consecutivo + 1;
        $oItem->Consecutivo = number_format($new_cod, 0, "", "");
        $oItem->save();
        unset($oItem);
        sleep(strval(rand(1, 20)));
        /*   $query = "SELECT Id_Factura FROM Factura WHERE Codigo = '$cod'";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $res = $oCon->getData(); */

        /*  if($res["Id_Factura"]){
            $oItem = new complex('Resolucion','Id_Resolucion',$resolucion['Id_Resolucion']);
            $nc = $oItem->getData();
            unset($oItem);
            sleep(strval(rand(0,3)));
            getConsecutivo($nc);
        }
     */
        return $cod;
    }

    private function validarDepreciacion($mes, $year)
    {
        //$query = "SELECT Id_Depreciacion FROM Depreciacion WHERE Mes = $mes AND Anio = YEAR(CURDATE()) AND Estado = 'Activo'";
        $query = "SELECT Id_Depreciacion FROM Depreciacion WHERE Mes = $mes AND Anio = $year AND Estado = 'Activo'";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $res = $oCon->getData();
        unset($oCon);
        if ($res) {
            return true;
        }
        return false;
    }
}
