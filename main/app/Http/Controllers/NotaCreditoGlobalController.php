<?php

namespace App\Http\Controllers;

use App\Models\NotaCreditoGlobal;
use App\Models\ProductoNotaCreditoGlobal;
use App\Models\Factura;
use App\Models\Cliente;
use App\Models\Resolucion;
use App\Models\ThirdParty;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class NotaCreditoGlobalController extends Controller
{
    public function descargaPdf(Request $request)
    {

        $id = $request->input('id');


        // Obtener la nota de crédito global por su ID
        $nota_credito = NotaCreditoGlobal::find($id);

        $res = Factura::select('Factura.Codigo', 'Resolucion.Tipo_Resolucion')
            ->join('Resolucion', 'Factura.Id_Resolucion', '=', 'Resolucion.Id_Resolucion')
            ->where('Factura.Nota_Credito', 1)
            ->where('Resolucion.Tipo_Resolucion', 'Resolucion_Electronica')
            ->first();

        // Obtener los productos asociados a la nota de crédito global
        $descripciones_nota = ProductoNotaCreditoGlobal::select('Producto_Nota_Credito_Global.*', 'C.Nombre as Motivo')
            ->leftJoin('Causal_No_Conforme as C', 'C.Id_Causal_No_Conforme', '=', 'Producto_Nota_Credito_Global.Id_Causal_No_Conforme')
            ->where('Producto_Nota_Credito_Global.Id_Nota_Credito_Global', '=', $nota_credito->Id_Nota_Credito_Global)
            ->get();



        // Obtener los detalles de la factura asociada a la nota de crédito global
        $factura = Factura::select('Tipo as Id_Factura', 'Codigo', 'Fecha_Documento', 'Id_Cliente')
            ->where('Id_Factura', '=', 1)
            ->first();



        // Obtener los detalles del cliente asociado a la factura
        if ($nota_credito->Tipo_Factura == 'Factura_Administrativa') {
            $cliente = ThirdParty::select('Tipo_Cliente')
                ->where('id', '=', $factura->Id_Cliente)
                ->where('is_client', '=', true)
                ->first();
        } else {
            $cliente = ThirdParty::select()
                ->where('id', '=', $factura->Id_Cliente)
                ->where('is_client', '=', true)
                ->first();
        }


        $Nota_Credito = $nota_credito;
        $Productos_Nota = $descripciones_nota;
        $Factura = $factura;
        $Cliente = $cliente;

        $datosCabecera = (object) array(
            'Titulo' => 'NOTA CREDITO',
            'Codigo' => $nota_credito->code ?? '',
            'Fecha' => $nota_credito->created_at,
            'CodigoFormato' => $nota_credito->format_code ?? '',

        );

        $pdf = Pdf::loadView('pdf.nota_credito', [
            'Nota_Credito' => $Nota_Credito,
            'Productos_Nota' => $Productos_Nota,
            '$Factura' => $Factura,
            '$Cliente' => $Cliente,
            'datosCabecera' => $datosCabecera,
        ]);

        return $pdf->download("nota_credito" . $Nota_Credito['Codigo'] . "pdf");


    }
}
