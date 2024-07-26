<?php

namespace App\Http\Controllers;

use App\Http\Services\complex;
use App\Http\Services\consulta;
use App\Http\Services\Contabilizar;
use App\Models\ContabilidadComprobante;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Models\Comprobante;
use App\Models\Configuration;
use App\Models\FacturaComprobante;
use App\Models\RetencionComprobante;
use App\Models\CuentaContableComprobante;
use App\Models\CuentaDocumentoContable;
use App\Models\DocumentoContable;
use App\Models\FacturaActaRecepcion;
use App\Models\Factura;
use App\Models\FacturaVenta;
use App\Models\FacturaCapita;
use App\Models\MovimientoContable;
use App\Models\Person;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ComprobanteController extends Controller
{
    use ApiResponser;
    public function detalleComprobante(Request $request)
    {
        $id = $request->input('id');

        $comprobante = Comprobante::select('Comprobante.*')
            ->selectRaw('IF((CL.first_name IS NULL OR CL.first_name = ""), CONCAT_WS(" ", P.first_name, P.first_surname), CONCAT_WS(" ", CL.first_name, CL.first_surname)) AS Nombre')
            ->selectRaw('(SELECT PC.Codigo FROM Plan_Cuentas PC WHERE PC.Id_Plan_Cuentas=Comprobante.Id_Cuenta) as Codigo_Debito')
            ->selectRaw('(SELECT PC.Nombre FROM Plan_Cuentas PC WHERE PC.Id_Plan_Cuentas=Comprobante.Id_Cuenta) as Cuenta_Debito')
            ->selectRaw('(SELECT PC.Codigo FROM Plan_Cuentas PC WHERE PC.Id_Plan_Cuentas=Comprobante.Id_Cuenta) as Codigo_Credito')
            ->selectRaw('(SELECT PC.Nombre FROM Plan_Cuentas PC WHERE PC.Id_Plan_Cuentas=Comprobante.Id_Cuenta) as Cuenta_Credito')
            ->join('people as F', 'F.id', '=', 'Comprobante.Id_Funcionario')
            ->leftJoin('third_parties as CL', function ($join) {
                $join->on('Comprobante.Id_Cliente', '=', 'CL.id')
                    ->where('CL.is_client', '=', 1);
            })
            ->leftJoin('third_parties as P', function ($join) {
                $join->on('Comprobante.Id_Proveedor', '=', 'P.id')
                    ->where('CL.is_supplier', '=', 1);
            })
            ->where('Comprobante.Id_Comprobante', $id)
            ->first();

        return response()->json($comprobante);
    }


    public function guardarComprobante(Request $request)
    {
        $request->validate([
            'Datos' => 'required|array',
            'Datos.Fecha_Comprobante' => 'required|date',
            'Datos.Id_Cliente' => 'required',
            'Datos.Id_Banco' => 'required',
            'Datos.Valor_Banco' => 'required|numeric',
            'Datos.Forma_Pago' => 'required',
        ]);

        $contabilizar = new Contabilizar();
        $datos = $request->input('Datos');
        $facturas = $request->input('Facturas');
        $categorias = $request->input('Categorias');
        $retenciones = $request->input('Retenciones');

        $datos_movimiento_contable = [];

        $fecha_comprobante = $datos['Fecha_Comprobante'] ?? now();
        $mes = date('m', strtotime($fecha_comprobante));
        $anio = date('Y', strtotime($fecha_comprobante));

        if ($contabilizar->validarMesOrAnioCerrado("$anio-$mes-01")) {
            $cod = generateConsecutive('Comprobante_Ingreso');
            $datos['Codigo'] = $cod;

            if ($facturas) {
                $datos['Tipo_Movimiento'] = $request->get('Tipo_Movimiento', 2);
            }

            $comprobante = Comprobante::create($datos);
            $id_comprobante = $comprobante->Id_Comprobante;
            sumConsecutive('Comprobante_Ingreso');

            /* AQUÍ GENERA QR */
            /* $qr = $this->generarqr('comprobante', $id_comprobante, '/IMAGENES/QR/');
            $comprobante->Codigo_Qr = $qr;
            $comprobante->save(); */
            /* HASTA AQUÍ GENERA QR */

            $facturaComprobantes = [];
            $retencionComprobantes = [];
            $cuentaContableComprobantes = [];

            if ($facturas) {
                foreach ($facturas as $fact) {
                    if ($fact) {
                        $fact['Id_Comprobante'] = $id_comprobante;
                        $facturaComprobantes[] = $fact;

                        if ($fact['RetencionesFacturas']) {
                            foreach ($fact['RetencionesFacturas'] as $ret) {
                                if ($ret && $ret['Id_Retencion'] != '') {
                                    $ret['Id_Factura'] = $fact['Id_Factura'];
                                    $ret['Id_Comprobante'] = $id_comprobante;
                                    $retencionComprobantes[] = $ret;
                                }
                            }
                        }

                        if ($fact['DescuentosFactura']) {
                            foreach ($fact['DescuentosFactura'] as $ret) {
                                if ($ret) {
                                    $ret['Id_Factura'] = $fact['Id_Factura'];
                                    $ret['Id_Comprobante'] = $id_comprobante;
                                }
                            }
                        }
                    }
                }
            } else {
                unset($categorias[count($categorias) - 1]);
                foreach ($categorias as $cat) {
                    if ($cat) {
                        $cat->Id_Comprobante = $id_comprobante;
                        $cuentaContableComprobantes[] = $cat;
                    }
                }
                if ($retenciones) {
                    foreach ($retenciones as $ret) {
                        if ($ret) {
                            $ret->Id_Comprobante = $id_comprobante;
                            $retencionComprobantes[] = $ret;
                        }
                    }
                }
            }
            foreach ($facturaComprobantes as $fact) {
                FacturaComprobante::create($fact);
            }
            foreach ($retencionComprobantes as $ret) {
                RetencionComprobante::create($ret);
            }
            foreach ($cuentaContableComprobantes as $cat) {
                CuentaContableComprobante::create($cat);
            }

            $datos_movimiento_contable['Id_Registro'] = $id_comprobante;
            $datos_movimiento_contable['Nit'] = $datos['Tipo'] == 'Ingreso' ? $datos['Id_Cliente'] : $datos['Id_Proveedor'];
            $datos_movimiento_contable['Id_Cuenta'] = $datos['Id_Banco'];
            $datos_movimiento_contable['Valor_Banco'] = $datos['Valor_Banco'];
            $datos_movimiento_contable['Tipo_Comprobante'] = $datos['Tipo'];
            $datos_movimiento_contable['Fecha_Comprobante'] = $fecha_comprobante;
            $datos_movimiento_contable['Facturas'] = $facturas;
            $datos_movimiento_contable['Valores_Comprobante'] = $categorias;
            $datos_movimiento_contable['Retenciones'] = $retenciones;

            $this->pagarFacturas($facturas, $datos['Id_Cliente']);

            $resultado['mensaje'] = "Se ha registrado un comprobante de " . strtolower($datos['Tipo']) . " satisfactoriamente";
            $resultado['tipo'] = "success";
            $resultado['titulo'] = "Operación exitosa";
            $resultado['id'] = $id_comprobante;
        } else {
            $resultado['mensaje'] = "El documento no se puede guardar porque existe un cierre para la fecha del documento";
            $resultado['titulo'] = "Error!";
            $resultado['tipo'] = "error";
        }
        return response()->json($resultado);
    }



    function generarqr($tipo, $id, $ruta)
    {
        $errorCorrectionLevel = 'H';
        $matrixPointSize = min(max((int) 5, 1), 10);
        $nombre = md5($tipo . '|' . $id . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
        $filename = $_SERVER["DOCUMENT_ROOT"] . "/" . $ruta . $nombre;

        return ($nombre);
    }


    function pagarFacturas($facturas, $id_cliente)
    {

        foreach ($facturas as $i => $fact) {

            $this->cambiarEstadoFactura($id_cliente, $fact['Codigo'], 57); // 57 es plan de cuenta 130505 que es para las facturas.
        }
    }


    function cambiarEstadoFactura($nit, $documento, $id_plan_cuenta)
    {
        $documento = substr($documento, 0, -1);

        FacturaActaRecepcion::where('Estado', 'Pendiente')
            ->whereIn('Factura', explode(',', $documento))
            ->update(['Estado' => 'Pagada']);

        Factura::where('Estado_Factura', 'Sin Cancelar')
            ->whereIn('Codigo', explode(',', $documento))
            ->update(['Estado_Factura' => 'Pagada']);

        FacturaVenta::where('Estado', 'Pendiente')
            ->whereIn('Codigo', explode(',', $documento))
            ->update(['Estado' => 'Pagada']);

        FacturaCapita::where('Estado_Factura', 'Sin Cancelar')
            ->whereIn('Codigo', explode(',', $documento))
            ->update(['Estado_Factura' => 'Pagada']);
    }

    function fecha($str)
    {
        $parts = explode(" ", $str);
        $date = explode("-", $parts[0]);
        return $date[2] . "/" . $date[1] . "/" . $date[0];
    }



    public function egresoDescargaPdf(Request $request)
    {
        $id = $request->input('id', '');
        $tipo = $request->input('tipo', '');
        $titulo = 'CONT. PCGA';

        if ($tipo != '') {
            $titulo = 'CONT. NIFF';
        }

        //$config = Configuration::find(1);


        $data = DocumentoContable::selectRaw('Documento_Contable.*,
            CASE
                WHEN Tipo_Beneficiario = "Cliente" THEN IFNULL(third_parties.social_reason, CONCAT_WS(" ", third_parties.first_name, third_parties.first_surname))
                WHEN Tipo_Beneficiario = "Proveedor" THEN IFNULL(third_parties.social_reason, CONCAT_WS(" ", third_parties.first_name, third_parties.first_surname))
                WHEN Tipo_Beneficiario = "Funcionario" THEN CONCAT_WS(" ", people.first_name, people.first_surname)
            END AS Tercero,
            companies.name AS Empresa,
            companies.tin AS Tin,
            IFNULL(Centro_Costo.Nombre, "Sin Centro Costo") AS Centro_Costo')
            ->leftJoin('third_parties', function ($join) {
                $join->on('third_parties.id', '=', 'Documento_Contable.Beneficiario')
                    ->where('third_parties.is_client', 1)
                    ->orWhere('third_parties.is_supplier', 1);
            })
            ->leftJoin('people', 'people.id', '=', 'Documento_Contable.Beneficiario')
            ->leftJoin('companies', 'companies.id', '=', 'Documento_Contable.Id_Empresa')
            ->leftJoin('Centro_Costo', 'Centro_Costo.Id_Centro_Costo', '=', 'Documento_Contable.Id_Centro_Costo')
            ->where('Documento_Contable.Id_Documento_Contable', $id)
            ->first();

        $cuentas = CuentaDocumentoContable::selectRaw('Plan_Cuentas.Codigo, Plan_Cuentas.Nombre AS Cuenta, Plan_Cuentas.Nombre_Niif AS Cuenta_Niif, Plan_Cuentas.Codigo_Niif,
            Cuenta_Documento_Contable.Concepto, Cuenta_Documento_Contable.Documento, Cuenta_Documento_Contable.Nit,
            CASE
                WHEN Tipo_Nit = "Cliente" THEN IFNULL(third_parties.social_reason, CONCAT_WS(" ", third_parties.first_name, third_parties.first_surname))
                WHEN Tipo_Nit = "Proveedor" THEN IFNULL(third_parties.social_reason, CONCAT_WS(" ", third_parties.first_name, third_parties.first_surname))
                WHEN Tipo_Nit = "Funcionario" THEN CONCAT_WS(" ", people.first_name, people.first_surname)
            END AS Tercero,
            IFNULL(Centro_Costo.Nombre, "Sin Centro Costo") AS Centro_Costo, Cuenta_Documento_Contable.Debito,
            Cuenta_Documento_Contable.Credito, Cuenta_Documento_Contable.Cred_Niif, Cuenta_Documento_Contable.Deb_Niif')
            ->join('Plan_Cuentas', 'Plan_Cuentas.Id_Plan_Cuentas', '=', 'Cuenta_Documento_Contable.Id_Plan_Cuenta')
            ->leftJoin('third_parties', function ($join) {
                $join->on('third_parties.id', '=', 'Cuenta_Documento_Contable.Nit')
                    ->where('third_parties.is_client', 1)
                    ->orWhere('third_parties.is_supplier', 1);
            })
            ->leftJoin('people', 'people.id', '=', 'Cuenta_Documento_Contable.Nit')
            ->leftJoin('Centro_Costo', 'Centro_Costo.Id_Centro_Costo', '=', 'Cuenta_Documento_Contable.Id_Centro_Costo')
            ->where('Cuenta_Documento_Contable.Id_Documento_Contable', $id)
            ->get();

        $cheques = CuentaDocumentoContable::select(DB::raw('GROUP_CONCAT(Cheque SEPARATOR " | ") AS Cheques'))
            ->where('Id_Documento_Contable', $id)
            ->whereNotNull('Cheque')
            ->first();


        $oItem = new complex('people', "identifier", $data["Identificacion_Funcionario"]);
        $elabora = $oItem->getData();

        $chequesHtml = '';
        if ($data['Forma_Pago'] == 'Cheque') {
            $chequesHtml = '<tr style="min-height: 100px;
            background: #e6e6e6;
            padding: 15px;
            border-radius: 10px;
            margin: 0;">
            <td style="font-size:11px;font-weight:bold;width:100px;padding:3px">
                Cheque(s):
            </td>
            <td style="font-size:11px;width:610px;padding:3px">
                ' . ($cheques ? $cheques->Cheques : '') . '
            </td>
        </tr>';
        }

        $contenidoCentroCostoHtml = '';
        if ($data['Id_Centro_Costo'] != '' && $data['Id_Centro_Costo'] != '0') {
            $contenidoCentroCostoHtml = '<tr style="min-height: 100px;
            background: #e6e6e6;
            padding: 15px;
            border-radius: 10px;
            margin: 0;">
            <td style="font-size:11px;font-weight:bold;width:100px;padding:3px">
                Centro Costo:
            </td>
            <td style="font-size:11px;width:610px;padding:3px">
                ' . $data['Centro_Costo'] . '
            </td>
        </tr>';
        }

        $elaboraNombre = '';


        if ($elabora !== null && isset($elabora['first_surname']) && isset($elabora['first_name'])) {
            $elaboraNombre = $elabora['first_surname'] . ' ' . $elabora['first_name'];
        }

        $datosCabecera = (object) array(
            'Titulo' => 'Egreso',
            'Codigo' => $data->code ?? '',
            'Fecha' => $data->created_at,
            'CodigoFormato' => $data->format_code ?? ''
        );


        $pdf = PDF::loadView('pdf.egreso', compact('titulo', 'data', 'tipo', 'chequesHtml', 'cheques', 'contenidoCentroCostoHtml', 'cuentas', 'elaboraNombre', 'datosCabecera'));
        return $pdf->download('egreso.pdf');
    }

    public function comprobantesPdf(Request $request)
    {
        $id = $request->input('id');

        $comprobante = Comprobante::select('Comprobante.*', 'Forma_Pago.Nombre as FormaPago', 'Plan_Cuentas.Nombre as PlanCuenta', 'people.first_name', 'people.first_surname', 'third_parties.social_reason', 'third_parties.cod_dian_address', 'municipalities.name as CiudadTercero', 'third_parties.cell_phone as TelefonoTercero', 'third_parties.nit as NitTercero')
            ->leftJoin('Forma_Pago', 'Comprobante.Id_Forma_Pago', '=', 'Forma_Pago.Id_Forma_Pago')
            ->leftJoin('Plan_Cuentas', 'Comprobante.Id_Cuenta', '=', 'Plan_Cuentas.Id_Plan_Cuentas')
            ->leftJoin('people', 'Comprobante.Id_Funcionario', '=', 'people.id')
            ->leftJoin('third_parties', function ($join) use ($id) {
                $join->on('Comprobante.Id_Cliente', '=', 'third_parties.id')
                    ->where('third_parties.is_client', '=', 1)
                    ->orWhere(function ($query) use ($id) {
                        $query->on('Comprobante.Id_Proveedor', '=', 'third_parties.id')
                            ->where('third_parties.is_supplier', '=', 1);
                    });
            })
            ->leftJoin('municipalities', function ($join) {
                $join->on('third_parties.municipality_id', '=', 'municipalities.id');
            })
            ->where('Comprobante.Id_Comprobante', $id)
            ->first();

        $comprobanteRetenciones = RetencionComprobante::select('Retencion_Comprobante.*', 'Retencion.Nombre as Retencion', 'Factura_Comprobante.Factura')
            ->leftJoin('Retencion', 'Retencion_Comprobante.Id_Retencion', '=', 'Retencion.Id_Retencion')
            ->leftJoin('Factura_Comprobante', function ($join) use ($id) {
                $join->on('Retencion_Comprobante.Id_Factura', '=', 'Factura_Comprobante.Id_Factura')
                    ->where('Retencion_Comprobante.Id_Comprobante', '=', $id);
            })
            ->where('Retencion_Comprobante.Id_Comprobante', $id)
            ->get();

        $facCcontableComprobante = Comprobante::select('Plan_Cuentas.*', 'Cuenta_Contable_Comprobante.Subtotal')
            ->join('Cuenta_Contable_Comprobante', 'Comprobante.Id_Comprobante', '=', 'Cuenta_Contable_Comprobante.Id_Comprobante')
            ->join('Plan_Cuentas', 'Cuenta_Contable_Comprobante.Id_Plan_Cuenta', '=', 'Plan_Cuentas.Id_Plan_Cuentas')
            ->where('Comprobante.Id_Comprobante', $id)
            ->first();

        $contabilidadComprobantes = ContabilidadComprobante::select('Contabilidad_Comprobante.*')
            ->selectRaw('GROUP_CONCAT(DISTINCT FC.Factura SEPARATOR " | ") AS Documento')
            ->join('Factura_Comprobante as FC', 'Contabilidad_Comprobante.Id_Factura_Comprobante', '=', 'FC.Id_Factura_Comprobante')
            ->where('Contabilidad_Comprobante.Id_Comprobante', $id)
            ->where(function ($query) {
                $query->where('Contabilidad_Comprobante.Debito', '!=', 0)
                    ->orWhere('Contabilidad_Comprobante.Credito', '!=', 0);
            })
            ->groupBy('Contabilidad_Comprobante.Id_Comprobante', 'Contabilidad_Comprobante.Id_Plan_Cuentas')
            ->get();

        $datosCabecera = (object) array(
            'Titulo' => 'Detalle Comprobante',
            'Codigo' => $comprobante->code ?? '',
            'Fecha' => $comprobante->created_at,
            'CodigoFormato' => $comprobante->format_code ?? '',

        );

        $pdf = PDF::loadView('pdf.detalleComprobante', [
            'comprobante' => $comprobante,
            'comprobanteRetenciones' => $comprobanteRetenciones,
            'facCcontableComprobante' => $facCcontableComprobante,
            'datosCabecera' => $datosCabecera,
            'contabilidadComprobantes' => $contabilidadComprobantes
        ]);

        return $pdf->download('detalleComprobante.pdf');

    }

    public function listaFacturaClientes(Request $request)
    {
        $id = $request->input('id', '');
        $query = "SELECT F.Codigo, F.Id_Factura_Venta AS Id_Factura, (SELECT IFNULL(SUM((PAR.Cantidad*PAR.Precio_Venta)),0)
            FROM Producto_Factura_Venta PAR
            WHERE PAR.Id_Factura_Venta = F.Id_Factura_Venta AND PAR.Impuesto=0) as Exenta,
            (SELECT IFNULL(SUM((PAR.Cantidad*PAR.Precio_Venta)),0)
            FROM Producto_Factura_Venta PAR
            WHERE PAR.Id_Factura_Venta = F.Id_Factura_Venta AND PAR.Impuesto!=0) as Gravada,
            (SELECT IFNULL(SUM((PAR.Cantidad*PAR.Precio_Venta*(PAR.Impuesto/100))),0)
                FROM Producto_Factura_Venta PAR INNER JOIN Producto PDT ON PDT.Id_Producto = PAR.Id_Producto
                WHERE PAR.Id_Factura_Venta = F.Id_Factura_Venta) as Iva,
            ((SELECT IFNULL(SUM((PAR.Cantidad*PAR.Precio_Venta)),0)
            FROM Producto_Factura_Venta PAR
            WHERE PAR.Id_Factura_Venta = F.Id_Factura_Venta AND PAR.Impuesto=0)+ (SELECT IFNULL(SUM((PAR.Cantidad*PAR.Precio_Venta)),0)
            FROM Producto_Factura_Venta PAR
            WHERE PAR.Id_Factura_Venta = F.Id_Factura_Venta AND PAR.Impuesto!=0))AS Total_Compra,
            ((SELECT IFNULL(SUM((PAR.Cantidad*PAR.Precio_Venta)),0)
            FROM Producto_Factura_Venta PAR
            WHERE PAR.Id_Factura_Venta = F.Id_Factura_Venta AND PAR.Impuesto=0)+ (SELECT IFNULL(SUM((PAR.Cantidad*PAR.Precio_Venta)),0)
            FROM Producto_Factura_Venta PAR
            WHERE PAR.Id_Factura_Venta = F.Id_Factura_Venta AND PAR.Impuesto!=0)+ (SELECT IFNULL(SUM((PAR.Cantidad*PAR.Precio_Venta*(PAR.Impuesto/100))),0)
                FROM Producto_Factura_Venta PAR INNER JOIN Producto PDT ON PDT.Id_Producto = PAR.Id_Producto
                WHERE PAR.Id_Factura_Venta = F.Id_Factura_Venta)) as Neto_Factura,
            0 as ValorIngresado,0 as ValorMayorPagar, 0 as ValorDescuento,
            ((SELECT IFNULL(SUM((PAR.Cantidad*PAR.Precio_Venta)),0)
            FROM Producto_Factura_Venta PAR
            WHERE PAR.Id_Factura_Venta = F.Id_Factura_Venta AND PAR.Impuesto=0)+ (SELECT IFNULL(SUM((PAR.Cantidad*PAR.Precio_Venta)),0)
            FROM Producto_Factura_Venta PAR
            WHERE PAR.Id_Factura_Venta = F.Id_Factura_Venta AND PAR.Impuesto!=0)+ (SELECT IFNULL(SUM((PAR.Cantidad*PAR.Precio_Venta*(PAR.Impuesto/100))),0)
                FROM Producto_Factura_Venta PAR INNER JOIN Producto PDT ON PDT.Id_Producto = PAR.Id_Producto
                WHERE PAR.Id_Factura_Venta = F.Id_Factura_Venta) - (
                SELECT
                IFNULL(SUM(MC.Debe)-(SUM(MC.Debe)-SUM(MC.Haber)),0)
                FROM
                Movimiento_Contable MC
                WHERE
                    MC.Nit = F.Id_CLiente AND MC.Documento = F.Codigo AND MC.Estado != 'Anulado' AND MC.Id_Plan_Cuenta = 57
            )) AS Por_Pagar,
            (
                SELECT
                IFNULL(SUM(MC.Debe)-(SUM(MC.Debe)-SUM(MC.Haber)),0)
                FROM
                Movimiento_Contable MC
                WHERE
                    MC.Nit = F.Id_Cliente AND MC.Documento = F.Codigo AND MC.Estado != 'Anulado' AND MC.Id_Plan_Cuenta = 57
            ) AS Pagado
            FROM Factura_Venta F
            INNER JOIN third_parties C ON F.Id_Cliente = C.id
            WHERE F.Estado='Pendiente' AND YEAR(F.Fecha_Documento) >= 2019 AND F.Id_Cliente= '$id'
            GROUP BY F.Id_Factura_Venta
            UNION ALL (
                SELECT
            FCM.Factura,
            FCM.Id_Facturas_Cliente_Mantis AS Id_Factura,
            0 AS Exenta,
            0 AS Gravada,
            0 AS Iva,
            FCM.Saldo AS Total_Compra,
            FCM.Saldo AS Neto_Factura,
            0 as ValorIngresado,0 as ValorMayorPagar, 0 as ValorDescuento,
            FCM.Saldo - (
                SELECT
                IFNULL(SUM(MC.Debe)-(SUM(MC.Debe)-SUM(MC.Haber)),0)
                FROM
                Movimiento_Contable MC
                WHERE
                    MC.Nit = FCM.Nit_Cliente AND MC.Documento = FCM.Factura AND MC.Estado != 'Anulado' AND MC.Id_Plan_Cuenta = 57
            ) AS Por_Pagar,
            (
                SELECT
                IFNULL(SUM(MC.Debe)-(SUM(MC.Debe)-SUM(MC.Haber)),0)
                FROM
                Movimiento_Contable MC
                WHERE
                    MC.Nit = FCM.Nit_Cliente AND MC.Documento = FCM.Factura AND MC.Estado != 'Anulado' AND MC.Id_Plan_Cuenta = 57
            )  AS Pagado
            FROM
            Facturas_Cliente_Mantis FCM
            WHERE FCM.Estado = 'Pendiente'
            AND FCM.Nit_Cliente = $id
            )
            UNION(
                SELECT
                F.Codigo,
                F.Id_Nota_Credito,
                (SELECT
                        IFNULL(SUM((PAR.Cantidad * PAR.Precio_Venta)),
                                    0)
                    FROM
                        Producto_Nota_Credito PAR
                    WHERE
                        PAR.Id_Nota_Credito = F.Id_Nota_Credito
                            AND PAR.Impuesto = 0) AS Exenta,
                (SELECT
                        IFNULL(SUM((PAR.Cantidad * PAR.Precio_Venta)),
                                    0)
                    FROM
                        Producto_Nota_Credito PAR
                    WHERE
                        PAR.Id_Nota_Credito = F.Id_Nota_Credito
                            AND PAR.Impuesto != 0) AS Gravada,
                (SELECT
                        IFNULL(SUM((PAR.Cantidad * PAR.Precio_Venta * (PAR.Impuesto / 100))),
                                    0)
                    FROM
                        Producto_Nota_Credito PAR
                    WHERE
                        PAR.Id_Nota_Credito = F.Id_Nota_Credito
                            AND PAR.Impuesto != 0) AS Iva,
                ((SELECT
                        IFNULL(SUM((PAR.Cantidad * PAR.Precio_Venta)),
                                    0)
                    FROM
                        Producto_Nota_Credito PAR
                    WHERE
                        PAR.Id_Nota_Credito = F.Id_Nota_Credito
                            AND PAR.Impuesto = 0) + (SELECT
                        IFNULL(SUM((PAR.Cantidad * PAR.Precio_Venta)),
                                    0)
                    FROM
                        Producto_Nota_Credito PAR
                    WHERE
                        PAR.Id_Nota_Credito = F.Id_Nota_Credito
                            AND PAR.Impuesto != 0)) AS Total_Compra,
                ((SELECT
                        IFNULL(SUM((PAR.Cantidad * PAR.Precio_Venta)),
                                    0)
                    FROM
                        Producto_Nota_Credito PAR
                    WHERE
                        PAR.Id_Nota_Credito = F.Id_Nota_Credito
                            AND PAR.Impuesto = 0) + (SELECT
                        IFNULL(SUM((PAR.Cantidad * PAR.Precio_Venta)),
                                    0)
                    FROM
                        Producto_Nota_Credito PAR
                    WHERE
                        PAR.Id_Nota_Credito = F.Id_Nota_Credito
                            AND PAR.Impuesto != 0) + (SELECT
                        IFNULL(SUM((PAR.Cantidad * PAR.Precio_Venta) * (PAR.Impuesto / 100)),
                                    0)
                    FROM
                        Producto_Nota_Credito PAR
                    WHERE
                        PAR.Id_Nota_Credito = F.Id_Nota_Credito
                            AND PAR.Impuesto != 0)) AS Neto_Factura,
                            0 as ValorIngresado,0 as ValorMayorPagar, 0 as ValorDescuento,
                            ((SELECT IFNULL(SUM((PAR.Cantidad*PAR.Precio_Venta)),0)
                            FROM Producto_Nota_Credito PAR
                            WHERE PAR.Id_Nota_Credito = F.Id_Nota_Credito AND PAR.Impuesto=0)+ (SELECT IFNULL(SUM((PAR.Cantidad*PAR.Precio_Venta)),0)
                            FROM Producto_Nota_Credito PAR
                            WHERE PAR.Id_Nota_Credito = F.Id_Nota_Credito AND PAR.Impuesto!=0)+ (SELECT IFNULL(SUM((PAR.Cantidad*PAR.Precio_Venta)*(PAR.Impuesto/100)),0)
                            FROM Producto_Nota_Credito PAR
                            WHERE PAR.Id_Nota_Credito = F.Id_Nota_Credito AND PAR.Impuesto!=0)) - (
                                SELECT
                                IFNULL(SUM(MC.Haber)-(SUM(MC.Haber)-SUM(MC.Debe)),0)
                                FROM
                                Movimiento_Contable MC
                                WHERE
                                    MC.Nit = F.Id_Cliente AND MC.Documento = F.Codigo AND MC.Estado != 'Anulado' AND MC.Id_Plan_Cuenta = 57
                            ) AS Por_Pagar,
                            (
                                SELECT
                                IFNULL(SUM(MC.Haber)-(SUM(MC.Haber)-SUM(MC.Debe)),0)
                                FROM
                                Movimiento_Contable MC
                                WHERE
                                    MC.Nit = F.Id_Cliente AND MC.Documento = F.Codigo AND MC.Estado != 'Anulado' AND MC.Id_Plan_Cuenta = 57
                            ) AS Pagado
            FROM
                Nota_Credito F
            WHERE
                F.Estado != 'Rechazada' AND   F.Estado != 'Pendiente'
                    AND F.Id_Cliente = '$id'
            GROUP BY F.Id_Nota_Credito
            )
            UNION (
                SELECT
                FT.Codigo,
                FT.Id_Factura,
                IFNULL((SELECT SUM(PF.Cantidad*PF.Precio)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura AND PF.Impuesto=0),0) as Exenta,
                IFNULL((SELECT SUM(PF.Cantidad*PF.Precio)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura AND PF.Impuesto!=0),0) as Gravada,
                IFNULL((SELECT ROUND(SUM((PF.Cantidad*PF.Precio)*(19/100)),2)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura AND PF.Impuesto!=0),0) as Iva,
                (IFNULL((SELECT SUM(PF.Cantidad*PF.Precio)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura AND PF.Impuesto!=0),0) + IFNULL((SELECT SUM(PF.Cantidad*PF.Precio)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura AND PF.Impuesto=0),0) - IFNULL((SELECT SUM(PF.Cantidad*PF.Descuento)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura),0) - FT.Cuota) AS Total_Venta,

                ((IFNULL((SELECT SUM(PF.Cantidad*PF.Precio)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura AND PF.Impuesto!=0),0) + IFNULL((SELECT SUM(PF.Cantidad*PF.Precio)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura AND PF.Impuesto=0),0)) + IFNULL((SELECT ROUND(SUM((PF.Cantidad*PF.Precio)*(19/100)),2)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura AND PF.Impuesto!=0),0) - IFNULL((SELECT SUM(PF.Cantidad*PF.Descuento)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura),0) - FT.Cuota) AS Neto_Factura,

                0 ValorIngresado,0 as ValorMayorPagar, 0 as ValorDescuento,

                IF(FC.Pagado IS NOT NULL, ((IFNULL((SELECT SUM(PF.Cantidad*PF.Precio)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura AND PF.Impuesto!=0),0) + IFNULL((SELECT SUM(PF.Cantidad*PF.Precio)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura AND PF.Impuesto=0),0)) + IFNULL((SELECT ROUND(SUM((PF.Cantidad*PF.Precio)*(19/100)),2)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura AND PF.Impuesto!=0),0)) - IFNULL((SELECT SUM(PF.Cantidad*PF.Descuento)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura),0) - FT.Cuota - FC.Pagado, ((IFNULL((SELECT SUM(PF.Cantidad*PF.Precio)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura AND PF.Impuesto!=0),0) + IFNULL((SELECT SUM(PF.Cantidad*PF.Precio)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura AND PF.Impuesto=0),0)) + IFNULL((SELECT ROUND(SUM((PF.Cantidad*PF.Precio)*(19/100)),2)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura AND PF.Impuesto!=0),0) - IFNULL((SELECT SUM(PF.Cantidad*PF.Descuento)
                FROM Producto_Factura PF
                WHERE PF.Id_Factura = FT.Id_Factura),0) - FT.Cuota)) AS Por_Pagar,

                IFNULL(FC.Pagado, 0) AS Pagado

                FROM Factura FT
                LEFT JOIN (SELECT Id_Factura, Factura, SUM(Valor) AS Pagado FROM Factura_Comprobante FC INNER JOIN Comprobante C ON FC.Id_Comprobante = C.Id_Comprobante WHERE C.Tipo = 'Ingreso' AND C.Estado = 'Activa' GROUP BY FC.Id_Factura) FC ON FT.Id_Factura = FC.Id_Factura AND FT.Codigo = FC.Factura
                WHERE FT.Estado_Factura = 'Sin Cancelar' AND YEAR(FT.Fecha_Documento) >= 2019 AND FT.Id_Cliente = $id
            )
            UNION(
                SELECT
                F.Codigo,
                F.Id_Factura_Capita AS Id_Factura,

                IFNULL((SELECT SUM(DFC.Cantidad*DFC.Precio)
                FROM Descripcion_Factura_Capita DFC
                WHERE DFC.Id_Factura_Capita = F.Id_Factura_Capita),0) as Exenta,

                0 AS Gravada,
                0 AS Iva,

                IFNULL((SELECT SUM(DFC.Cantidad*DFC.Precio)
                FROM Descripcion_Factura_Capita DFC
                WHERE DFC.Id_Factura_Capita = F.Id_Factura_Capita),0) as Total_Venta,

                (IFNULL((SELECT SUM(DFC.Cantidad*DFC.Precio)
                FROM Descripcion_Factura_Capita DFC
                WHERE DFC.Id_Factura_Capita = F.Id_Factura_Capita),0) - F.Cuota_Moderadora) AS Neto_Factura,

                0 AS ValorIngresado,0 as ValorMayorPagar, 0 as ValorDescuento,

                IF(FC.Pagado IS NOT NULL, IFNULL((SELECT SUM(DFC.Cantidad*DFC.Precio)
                FROM Descripcion_Factura_Capita DFC
                WHERE DFC.Id_Factura_Capita = F.Id_Factura_Capita),0) - F.Cuota_Moderadora - FC.Pagado, IFNULL((SELECT SUM(DFC.Cantidad*DFC.Precio)
                FROM Descripcion_Factura_Capita DFC
                WHERE DFC.Id_Factura_Capita = F.Id_Factura_Capita),0)) as Por_Pagar,

                IFNULL(FC.Pagado, 0) AS Pagado

                FROM
                Factura_Capita F
                LEFT JOIN (SELECT Id_Factura, Factura, SUM(Valor) AS Pagado FROM Factura_Comprobante FC INNER JOIN Comprobante C ON FC.Id_Comprobante = C.Id_Comprobante WHERE C.Tipo = 'Ingreso' AND C.Estado = 'Activa' GROUP BY FC.Id_Factura) FC ON F.Id_Factura_Capita = FC.Id_Factura AND F.Codigo = FC.Factura
                WHERE F.Estado_Factura = 'Sin Cancelar' AND YEAR(F.Fecha_Documento) >= 2019 AND F.Id_Cliente = $id
            )";
        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $resultado = $oCon->getData();

        unset($oCon);
        $i = -1;
        foreach ($resultado as $value) {
            $i++;
            $resultado[$i]->RetencionesFacturas = [];
            $resultado[$i]->DescuentosFactura = [];
        }

        $datos['Facturas'] = $resultado;

        return response()->json($datos);
    }



}
