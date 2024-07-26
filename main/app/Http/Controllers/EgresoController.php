<?php

namespace App\Http\Controllers;

use App\Http\Services\complex;
use App\Http\Services\comprobantes\ObtenerProximoConsecutivo;
use App\Http\Services\resumenretenciones\funciones;
use App\Models\DocumentoContable;
use App\Models\CuentaDocumentoContable;
use App\Models\MovimientoContable;
use Illuminate\Http\Request;

class EgresoController extends Controller
{
    function generarQr($id_remision)
    {
        /* AQUI GENERA QR */
        //$qr = generarqr('remision', $id_remision, '/IMAGENES/QR/');
        $oItem = new complex("Remision", "Id_Remision", $id_remision);
        //$oItem->Codigo_Qr = $qr;
        $oItem->save();
        unset($oItem);

        return;
    }
    public function guardar()
    {

        $datos = request()->input('Datos');
        $cuentas_contables = request()->input('Cuentas_Contables');

        $datos = json_decode($datos, true);
        $cuentas_contables = json_decode($cuentas_contables, true);


        $datos['Cliente'] = json_encode(isset($datos['Cliente']));
        $datos['Nom_Centro_Costo'] = json_encode(isset($datos['Nom_Centro_Costo']));

        $mes = isset($datos['Fecha_Documento']) ? date('m', strtotime($datos['Fecha_Documento'])) : date('m');
        $anio = isset($datos['Fecha_Documento']) ? date('Y', strtotime($datos['Fecha_Documento'])) : date('Y');

        $cod = ObtenerProximoConsecutivo::generarConsecutivo('Egreso', $mes, $anio);
        $datos['Codigo'] = $cod;

        $documentoContable = new DocumentoContable();

        if (!isset($datos['Id_Centro_Costo']) || $datos['Id_Centro_Costo'] == '') {
            $datos['Id_Centro_Costo'] = 0;
        }

        foreach ($datos as $index => $value) {
            $documentoContable->$index = $value;
        }
        $documentoContable->Tipo = 'Egreso';

        $documentoContable->save();

        $id_documento_contable = $documentoContable->Id_Documento_Contable;

        // Generar QR
        //$qr = generarqr('egreso', $id_documento_contable, '/IMAGENES/QR/');
        //$documentoContable->Codigo_Qr = $qr;
        //$documentoContable->save();

        foreach ($cuentas_contables as $cuenta) {
            $cuentaDocumentoContable = new CuentaDocumentoContable();
            $cuentaDocumentoContable->Id_Documento_Contable = $id_documento_contable;
            $id_plan_cuentas = $cuenta['Id_Plan_Cuentas'] ?? null;
            $cuentaDocumentoContable->Id_Plan_Cuenta = $id_plan_cuentas;
            if (isset($datos['Forma_Pago']) && $datos['Forma_Pago'] == 'Cheque' && $cuenta['Credito'] > 0) {
                $response_cheque = ObtenerProximoConsecutivo::generarConsecutivoCheque($cuenta['Id_Plan_Cuentas']);
                if ($response_cheque['status'] == 2) {
                    $cuentaDocumentoContable->Cheque = $response_cheque['consecutivo'];
                }
            }
            $cuentaDocumentoContable->Nit = isset($cuenta['Nit_Cuenta']);
            $cuentaDocumentoContable->Tipo_Nit = isset($cuenta['Tipo_Nit']);
            $cuentaDocumentoContable->Id_Centro_Costo = isset($cuenta['Id_Centro_Costo']) && $cuenta['Id_Centro_Costo'] != '' ? $cuenta['Id_Centro_Costo'] : '0';
            $cuentaDocumentoContable->Id_Empresa = isset($cuenta['Id_Empresa']);
            $cuentaDocumentoContable->Documento = isset($cuenta['Documento']);
            $cuentaDocumentoContable->Concepto = isset($cuenta['Concepto']);
            $cuentaDocumentoContable->Base = number_format($cuenta['Base'], 2, ".", "");
            $cuentaDocumentoContable->Debito = number_format($cuenta['Debito'], 2, ".", "");
            $cuentaDocumentoContable->Credito = number_format($cuenta['Credito'], 2, ".", "");
            $cuentaDocumentoContable->Deb_Niif = number_format($cuenta['Deb_Niif'], 2, ".", "");
            $cuentaDocumentoContable->Cred_Niif = number_format($cuenta['Cred_Niif'], 2, ".", "");
            $cuentaDocumentoContable->save();


            $movimientoContable = new MovimientoContable();
            $movimientoContable->Id_Plan_Cuenta = isset($cuenta['Id_Plan_Cuentas']);
            $movimientoContable->Id_Modulo = 7;
            $movimientoContable->Id_Registro_Modulo = $id_documento_contable;
            $movimientoContable->Fecha_Movimiento = $datos['Fecha_Documento'];
            $movimientoContable->Debe = number_format($cuenta['Debito'], 2, ".", "");
            $movimientoContable->Debe_Niif = number_format($cuenta['Deb_Niif'], 2, ".", "");
            $movimientoContable->Haber = number_format($cuenta['Credito'], 2, ".", "");
            $movimientoContable->Haber_Niif = number_format($cuenta['Cred_Niif'], 2, ".", "");
            $movimientoContable->Nit = isset($cuenta['Nit_Cuenta']);
            $movimientoContable->Tipo_Nit = isset($cuenta['Tipo_Nit']);
            $movimientoContable->Id_Centro_Costo = isset($cuenta['Id_Centro_Costo']) && $cuenta['Id_Centro_Costo'] != '' ? $cuenta['Id_Centro_Costo'] : '0';
            $movimientoContable->Documento = $cuenta['Documento'];
            $movimientoContable->Numero_Comprobante = $cod;
            $movimientoContable->Detalles = $cuenta['Concepto'];
            $movimientoContable->save();


            funciones::cambiarEstadoFactura(isset($cuenta['Nit_Cuenta']), isset($cuenta['Documento']), isset($cuenta['Id_Plan_Cuentas']));
        }


        if (isset($datos['Id_Borrador']) && $datos['Id_Borrador'] != '') {
            funciones::eliminarBorradorContable($datos['Id_Borrador']);
        }

        $resultado = [];
        if ($id_documento_contable) {
            $resultado['mensaje'] = "Se ha registrado un comprobante de egreso satisfactoriamente";
            $resultado['tipo'] = "success";
            $resultado['titulo'] = "Operación exitosa!";
            $resultado['id'] = $id_documento_contable;
        } else {
            $resultado['mensaje'] = "Ha ocurrido un error de conexión, comunicarse con el soporte técnico.";
            $resultado['tipo'] = "error";
        }

        return response()->json($resultado);
    }


    public function listaFacturas(Request $request)
    {
        $nit = $request->get('nit', false);
        $fecha = $request->get('fecha', false);
        $idPlanCuenta = $request->get('id_plan_cuenta', false);
        $resultado['Facturas'] = MovimientoContable::listaCartera($nit, $idPlanCuenta, $fecha);
        return response()->json($resultado);
    }
}
