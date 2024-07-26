<!DOCTYPE html>
<html>

<head>

    <title>Detalle de Comprobante</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: auto;
            padding-left: 20px;
            padding-right: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .footer-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            padding: 6px;
            text-align: right;
            border: 1px solid #ccc;

        }

        .footer-table td:first-child {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container">
        @include('components/cabecera', [$datosCabecera])

        <h3>Información del Comprobante y Tercero</h3>

        <div class="row">
            <div class="col">
                <table class="table" style="border-spacing: 0; border-collapse: collapse;">
                    <tr style="line-height: 1;">
                        <th style="padding: 5px;">Codigo Comprobante</th>
                        <td style="padding: 5px;">{{ $comprobante['Codigo'] }}</td>
                    </tr>
                    <tr style="line-height: 1;">
                        <th style="padding: 5px;">Dirección</th>
                        <td style="padding: 5px;">{{ $comprobante['cod_dian_address'] }}</td>
                    </tr>
                    <tr style="line-height: 1;">
                        <th style="padding: 5px;">Ciudad</th>
                        <td style="padding: 5px;">{{ $comprobante['CiudadTercero'] }}</td>
                    </tr>
                    <tr style="line-height: 1;">
                        <th style="padding: 5px;">Teléfono</th>
                        <td style="padding: 5px;">{{ $comprobante['TelefonoTercero'] }}</td>
                    </tr>
                    <tr style="line-height: 1;">
                        <th style="padding: 5px;">Método de Pago</th>
                        <td style="padding: 5px;">{{ $comprobante['FormaPago'] }}</td>
                    </tr>
                    <tr style="line-height: 1;">
                        <th style="padding: 5px;">NIT</th>
                        <td style="padding: 5px;">{{ $comprobante['NitTercero'] }}</td>
                    </tr>

                    <tr style="line-height: 1;">
                        <th style="padding: 5px;">Observaciones</th>
                        <td style="padding: 5px;">{{ $comprobante['Observaciones'] }}</td>
                    </tr>

                    <tr style="line-height: 1;">
                        <th style="padding: 5px;">Plan de Cuenta</th>
                        <td style="padding: 5px;">{{ $comprobante->PlanCuenta }}</td>
                    </tr>
                    <tr style="line-height: 1;">
                        <th style="padding: 5px;">Tercero</th>
                        <td style="padding: 5px;">{{ $comprobante->first_name }} {{ $comprobante->first_surname }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <?php
        $subtotal = isset($facCcontableComprobante->Subtotal) ? $facCcontableComprobante->Subtotal : 0;
        $total_retenciones = 0;
        $total_ajuste = 0;
        $total_descuentos = 0;
        $total = 0;
        
        $subtotal = isset($facCcontableComprobante->Subtotal) ? $facCcontableComprobante->Subtotal : 0;
        $nit = $comprobante['Id_Cliente'] == '0' ? $comprobante['Id_Proveedor'] : $comprobante['Id_Cliente'];
        
        $retenciones = 'No se aplicaron retenciones';
        $ind = 0;
        $total_retenciones = 0;
        
        foreach ($comprobanteRetenciones as $key) {
            $factura = $key['Factura'] != '' ? 'FRA. ' . $key['Factura'] . ' - ' : '';
            $total_retenciones += $key['Valor'];
            if ($ind == 0) {
                $retenciones = $factura . $key['Retencion'] . ' = $ ' . number_format($key['Valor'], 2, ',', '.') . '<br>';
            } else {
                $retenciones .= $factura . $key['Retencion'] . ' = $ ' . number_format($key['Valor'], 2, ',', '.') . '<br>';
            }
        
            $ind++;
        }
        
        $total = $subtotal - $total_retenciones - $total_descuentos + $total_ajuste;
        
        $totales_ajustes = 0;
        $totales_descuentos = 0;
        $totales_retenciones = 0;
        
        ?>

        <!-- Retenciones del Comprobante -->
        <h3>Retenciones del Comprobante</h3>
        <table class="table">
            <tr>
                <th>Id Retención</th>
                <th>Nombre Retención</th>
                <th>Factura</th>
            </tr>
            @foreach ($comprobanteRetenciones as $retencion)
                <tr>
                    <td>{{ $retencion->Id_Retencion }}</td>
                    <td>{{ $retencion->Retencion }}</td>
                    <td>{{ $retencion->Factura }}</td>
                </tr>
            @endforeach
        </table>
        <table style="margin-top:10px;font-size:12px; padding-left:180px;" cellpadding="0" cellspacing="0">
            <tr>
                <td
                    style="font-size: 14px;text-align:left;width:737px;max-width:400px;font-weight:bold;border:1px solid #cccccc;background:#ededed;">
                    Retenciones Aplicadas
                </td>
            </tr>
            <tr>
                <div class="footer-table"
                    style="text-align:center;width:737px;max-width:400px;font-weight:bold;border:1px solid #cccccc;">
                    <table>
                        <tr>
                            <td style="width: 60%; text-align: right; font-size: 14px;"><strong>Total
                                    Retenciones</strong></td>
                            <td style="width: 60%; text-align: right; font-size: 14px;">$
                                {{ number_format(floatval($total_retenciones), 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="width: 40%; text-align: right; font-size: 14px;"><strong>Total
                                    descuentos</strong></td>
                            <td style="width: 40%; text-align: right; font-size: 14px;">$
                                {{ number_format(floatval($totales_descuentos), 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="width: 40%; text-align: right; font-size: 14px;"><strong>Total ajustes</strong>
                            </td>
                            <td style="width: 40%; text-align: right; font-size: 14px;">$
                                {{ number_format(floatval($totales_ajustes), 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="width: 40%; text-align: right; font-size: 14px;"><strong>Subtotal</strong></td>
                            <td style="width: 40%; text-align: right; font-size: 14px;">$
                                {{ number_format(floatval($subtotal), 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="width: 40%; text-align: right; font-size: 14px;"><strong>Total</strong></td>
                            <td style="width: 40%; text-align: right; font-size: 14px;">$
                                {{ number_format(floatval($total), 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </tr>
        </table>

    </div>

</body>

</html>
