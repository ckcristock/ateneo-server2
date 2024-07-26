<?php

namespace App\Http\Controllers;

use App\Http\Services\complex;
use App\Http\Services\Configuracion;
use App\Http\Services\QueryBaseDatos;
use App\Models\CuentaDocumentoContable;
use App\Models\DocumentoContable;
use App\Models\MovimientoContable;
use App\Models\NotaCreditoGlobal;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class MovimientoContableController extends Controller
{
    public function movimientosComprobante(Request $request)
    {
        $Id_Documento_Contable = $request->input('id_registro', '');
        $id_funcionario_imprime = $request->input('id_funcionario_elabora', '');
        $tipo_valor = (isset($_REQUEST['tipo_valor']) ? $_REQUEST['tipo_valor'] : '');

        $documentoContable = DocumentoContable::with('empresa')->find($Id_Documento_Contable);

        $empresa = optional($documentoContable->empresa)->name;
        $tin = optional($documentoContable->empresa)->tin;
        $centroCosto = optional($documentoContable->centroCosto)->Nombre ?? 'Sin Centro Costo';

        $cuentas = CuentaDocumentoContable::where('Id_Documento_Contable', $Id_Documento_Contable)
            ->join('Plan_Cuentas', 'Cuenta_Documento_Contable.Id_Plan_Cuenta', '=', 'Plan_Cuentas.Id_Plan_Cuentas')
            ->select(
                'Cuenta_Documento_Contable.*',
                'Plan_Cuentas.Codigo',
                'Plan_Cuentas.Nombre AS Cuenta',
                'Plan_Cuentas.Nombre_Niif AS Cuenta_Niif',
                'Plan_Cuentas.Codigo_Niif',
                'Plan_Cuentas.Documento AS Documento_niif',
                'Cuenta_Documento_Contable.Concepto',
                'Cuenta_Documento_Contable.Documento',
                'Cuenta_Documento_Contable.Nit'
            )
            ->get();

        foreach ($cuentas as $cuenta) {
            $person = Person::where('id', $cuenta->Nit)
                ->when($cuenta->Tipo_Nit === 'Cliente', function ($query) {
                    $query->whereHas('thirdParty', function ($query) {
                        $query->where('is_client', true);
                    });
                })
                ->when($cuenta->Tipo_Nit === 'Proveedor', function ($query) {
                    $query->whereHas('thirdParty', function ($query) {
                        $query->where('is_supplier', true);
                    });
                })
                ->value(DB::raw("IFNULL(CONCAT_WS(' ', first_name, first_surname), 'Nombre no disponible')"));
        }

        $cheques = implode(' | ', CuentaDocumentoContable::where('Id_Documento_Contable', $Id_Documento_Contable)
            ->whereNotNull('Cheque')
            ->pluck('Cheque')
            ->toArray());

        $nombre_funcionario_imprime = Person::where('id', $id_funcionario_imprime)
            ->value(DB::raw("IFNULL(CONCAT_WS(' ', first_name, first_surname), 'Nombre no disponible')"));

        $data = [
            'person' => $person ?? '',
            'empresa' => $empresa,
            'tin' => $tin,
            'centroCosto' => $centroCosto,
            'cheques' => $cheques,
            'cuentas' => $cuentas,
            'elabora' => $nombre_funcionario_imprime,
        ];

        $header = (object) [
            'Titulo' => 'CONTABILIZACIÓN NIIF',
            'Codigo' => $documentoContable->code ?? '',
            'Fecha' => $documentoContable->created_at,
            'CodigoFormato' => $documentoContable->format_code ?? '',
        ];

        $pdf = PDF::loadView('pdf.documento_contable', [
            'data' => $data,
            'datosCabecera' => $header,
        ]);

        return $pdf->download("documento_contable");
    }

    public function movimientosNotaCreditoGlobal()
    {


        $id_registro = request()->input('id_registro', '');
        $idFuncionarioImprime = request()->input('id_funcionario_elabora', '');
        $tipo_valor = request()->input('tipo_valor', '');


        //$config = Configuracion::find(1);

        $datos = NotaCreditoGlobal::find($id_registro);

        $movimientos = MovimientoContable::select(
            'Plan_Cuentas.codigo',
            'Plan_Cuentas.nombre',
            'Plan_Cuentas.codigo_niif',
            'Plan_Cuentas.nombre_niif',
            'Movimiento_Contable.Nit',
            'Movimiento_Contable.fecha_movimiento as fecha',
            'Movimiento_Contable.tipo_nit',
            'Movimiento_Contable.id_registro_modulo',
            'Movimiento_Contable.documento',
            'Movimiento_Contable.numero_comprobante',
            'Movimiento_Contable.debe',
            'Movimiento_Contable.haber',
            'Movimiento_Contable.debe_niif',
            'Movimiento_Contable.haber_niif',
            DB::raw("(CASE
                WHEN Movimiento_Contable.tipo_nit = 'Cliente' THEN (SELECT nombre FROM third_parties WHERE id = Movimiento_Contable.Nit AND is_client = 1)
                WHEN Movimiento_Contable.tipo_nit = 'Proveedor' THEN (SELECT nombre FROM third_parties WHERE id = Movimiento_Contable.Nit AND is_supplier = 1)
                WHEN Movimiento_Contable.tipo_nit = 'Funcionario' THEN (SELECT CONCAT(first_name, ' ', first_surname) FROM people WHERE identifier = Movimiento_Contable.Nit)
            END) as nombre_cliente"),
            DB::raw("'Factura Venta' as registro")
        )
            ->join('Plan_Cuentas', 'Movimiento_Contable.id_plan_cuenta', '=', 'Plan_Cuentas.Id_Plan_Cuentas')
            ->where('Movimiento_Contable.estado', 'Activo')
            ->where('Movimiento_Contable.id_modulo', 34)
            ->where('Movimiento_Contable.id_registro_modulo', $id_registro)
            ->orderBy('Movimiento_Contable.debe', 'desc')
            ->get();



        $movimientosSuma = MovimientoContable::where('estado', 'Activo')
            ->where('id_modulo', 34)
            ->where('id_registro_modulo', $id_registro)
            ->selectRaw('SUM(debe) as debe, SUM(haber) as haber, SUM(debe_niif) as debe_niif, SUM(haber_niif) as haber_niif')
            ->first();

        $imprime = Person::where('identifier', $idFuncionarioImprime)
            ->selectRaw('CONCAT(first_name, " ", first_surname) as nombre_funcionario')
            ->first();



        $elabora = Person::where('identifier', $datos->id_funcionario)
            ->selectRaw('CONCAT(first_name, " ", first_surname) as nombre_funcionario')
            ->first();

        $total_debe = 0;
        $total_haber = 0;

        if (count($movimientos) > 0) {

            foreach ($movimientos as $value) {
                if ($tipo_valor != '') {
                    $codigo = $value['Codigo_Niif'];
                    $nombre_cuenta = $value['Nombre_Niif'];
                    $debe = $value['debe_niif'];
                    $haber = $value['haber_niif'];
                } else {
                    $codigo = $value['Codigo'];
                    $nombre_cuenta = $value['Nombre'];
                    $debe = $value['debe'];
                    $haber = $value['haber'];
                }
                $total_debe += $debe;
                $total_haber += $haber;
            }
        }


        $header = (object) [
            'Titulo' => $tipo_valor != '' ? "CONTABILIZACIÓN NIIF" : "CONTABILIZACIÓN PCGA",
            'Codigo' => $datos->code ?? '',
            'Fecha' => $datos->created_at,
            'CodigoFormato' => $datos->format_code ?? '',
        ];

        $pdf = PDF::loadView('pdf.contabilización_niif_pcga', [
            'data' => $datos,
            'movimientos' => $movimientos,
            'movimientosSuma' => $movimientosSuma,
            'imprime' => $imprime,
            'elabora' => $elabora,
            'datosCabecera' => $header,
            'tipo_valor' => $tipo_valor,
            'total_debe' => $total_debe,
            'total_haber' => $total_haber,
        ]);

        return $pdf->download("contabilización_niif_pcga");

    }

    private function fecha($str)
    {
        $parts = explode(" ", $str);
        $date = explode("-", $parts[0]);
        return isset($date[2]) . "/" . isset($date[1]) . "/" . isset($date[0]);
    }

    public function movimientosDevolucionCompras(Request $request)
    {
        $id_registro = $request->input('id_registro', '');
        $id_funcionario_imprime = $request->input('id_funcionario_elabora', '');
        $tipo_valor = $request->input('tipo_valor', '');
        $titulo = $tipo_valor != '' ? "CONTABILIZACIÓN NIIF" : "CONTABILIZACIÓN PCGA";

        $queryObj = new QueryBaseDatos();

        /* DATOS GENERALES DE CABECERAS Y CONFIGURACION */
        // $oItem = new complex('Configuracion', "Id_Configuracion", 1);
        // $config = $oItem->getData();
        // unset($oItem);
        /* FIN DATOS GENERALES DE CABECERAS Y CONFIGURACION */

        $oItem = new complex('Devolucion_Compra', 'Id_Devolucion_Compra', $id_registro);
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
        MC.Debe,
        MC.Haber,
        MC.Debe_Niif,
        MC.Haber_Niif,
        MC.Numero_Comprobante,
            (CASE
                WHEN MC.Tipo_Nit = "Cliente" THEN (SELECT  IFNULL(third_parties.social_reason, CONCAT_WS(" ", third_parties.first_name, third_parties.first_surname)) FROM third_parties WHERE third_parties.nit = MC.Nit and third_parties.is_client=1)
                WHEN MC.Tipo_Nit = "Proveedor" THEN (SELECT  IFNULL(third_parties.social_reason, CONCAT_WS(" ", third_parties.first_name, third_parties.first_surname)) FROM third_parties WHERE third_parties.nit = MC.Nit and third_parties.is_supplier=1)
                WHEN MC.Tipo_Nit = "Funcionario" THEN (SELECT full_name FROM people WHERE people.identifier = MC.Nit)
            END) AS Nombre_Cliente,
            "Factura Venta" AS Registro
        FROM Movimiento_Contable MC
        INNER JOIN Plan_Cuentas PC ON MC.Id_Plan_Cuenta = PC.Id_Plan_Cuentas
        WHERE
            Id_Modulo = 16 AND Id_registro_Modulo =' . $id_registro . ' ORDER BY Debe DESC';

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
            Id_Modulo = 16 AND Id_registro_Modulo =' . $id_registro;

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
        identifier =' . $datos['Identificacion_Funcionario'];

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
        // try {
        //     /* CREO UN PDF CON LA INFORMACION COMPLETA PARA DESCARGAR*/
        //     $html2pdf = new HTML2PDF('P', 'A4', 'Es', true, 'UTF-8', array(5, 5, 5, 5));
        //     $html2pdf->writeHTML($content);
        //     $direc = $data["Codigo"] . '.pdf'; // NOMBRE DEL ARCHIVO ES EL CODIGO DEL DOCUMENTO
        //     $html2pdf->Output($direc); // LA D SIGNIFICA DESCARGAR, 'F' SE PODRIA HACER PARA DEJAR EL ARCHIVO EN UNA CARPETA ESPECIFICA
        // } catch (HTML2PDF_exception $e) {
        //     echo $e;
        //     exit;
        // }
    }


    public function movimientosActaRecepcion(Request $request)
    {

        $id_registro = $request->input('id_registro', '');
        $id_funcionario_imprime = $request->input('id_funcionario_elabora', '');
        $tipo_valor = $request->input('tipo_valor', '');
        $titulo = $tipo_valor != '' ? "CONTABILIZACIÓN NIIF" : "CONTABILIZACIÓN PCGA";


        $queryObj = new QueryBaseDatos();

        $oItem = new complex('Acta_Recepcion', 'Id_Acta_Recepcion', $id_registro);
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
        SUM(MC.Debe) AS Debe,
        SUM(MC.Haber) AS Haber,
        SUM(MC.Debe_Niif) AS Debe_Niif,
        SUM(MC.Haber_Niif) AS Haber_Niif,
            (CASE
            WHEN MC.Tipo_Nit = "Cliente" THEN (SELECT  IFNULL(third_parties.social_reason, CONCAT_WS(" ", third_parties.first_name, third_parties.first_surname)) FROM third_parties WHERE third_parties.nit = MC.Nit and third_parties.is_client=1)
            WHEN MC.Tipo_Nit = "Proveedor" THEN (SELECT  IFNULL(third_parties.social_reason, CONCAT_WS(" ", third_parties.first_name, third_parties.first_surname)) FROM third_parties WHERE third_parties.nit = MC.Nit and third_parties.is_supplier=1)
            WHEN MC.Tipo_Nit = "Funcionario" THEN (SELECT full_name FROM people WHERE people.identifier = MC.Nit)
        END) AS Nombre_Cliente,
            "Factura Venta" AS Registro
        FROM Movimiento_Contable MC
        INNER JOIN Plan_Cuentas PC ON MC.Id_Plan_Cuenta = PC.Id_Plan_Cuentas
        WHERE
            Id_Modulo = 15 AND Id_registro_Modulo ='.$id_registro.'
            GROUP BY MC.Id_Plan_Cuenta
            ORDER BY Debe DESC';

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
            Id_Modulo = 15 AND Id_registro_Modulo ='.$id_registro;

    $queryObj->SetQuery($query);
    $movimientos_suma = $queryObj->ExecuteQuery('simple');

    $query = '
        SELECT
        CONCAT(first_name, " ", first_surname) AS Nombre_Funcionario
        FROM people
        WHERE
            id ='.$id_funcionario_imprime;

    $queryObj->SetQuery($query);
    $imprime = $queryObj->ExecuteQuery('simple');

    $elabora = Person::where('id', $id_funcionario_imprime)
            ->value(DB::raw("IFNULL(CONCAT_WS(' ', first_name, first_surname), 'Nombre no disponible')"));

    $header = (object) [
        'Titulo' => $titulo,
        'Codigo' => $this->getCodigoActa($id_registro) ?? '',
        'Fecha' => !empty($movimientos) ? ($movimientos[0]['Fecha'] ?? '') : '',
        'CodigoFormato' => '',
    ];

    $pdf = Pdf::loadView('pdf.movimientos_acta_recepcion', [
        'movimientos_suma' => $movimientos_suma,
        'movimientos' => $movimientos,
        'imprime' => $imprime,
        'datosCabecera' => $header,
        'elabora' => $elabora,
        'tipo_valor' => $tipo_valor

    ]);

    return $pdf->stream("movimientos_acta_recepcion");

    }

    private function getCodigoActa($id)
    {
        $oItem = new complex('Acta_Recepcion','Id_Acta_Recepcion',$id);
    
        return $oItem->Codigo;
    }
}
