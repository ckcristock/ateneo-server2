<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura Venta</title>
    <style>
        body.factura-body {
            font-family: Arial, sans-serif;
        }
        .factura-page-content {
            width: 100%;
            max-width: 750px;
            margin: auto;
            padding: 0;
            box-sizing: border-box;
        }
        .factura-table {
            width: 100%;
            border-collapse: collapse;
        }
        .factura-td, .factura-th {
            border: 1px solid rgb(131, 103, 103);
            padding: 5px;
            font-size: 10px;
        }
        .factura-td-header {
            font-size: 13px;
            line-height: 18px;
        }
        .factura-titular {
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 0;
        }
    </style>
</head>
<body class="factura-body">
    @include('components/cabecera', [$datosCabecera])
    <div class="factura-page-content">
        <table class="factura-table">
            <tbody>
                <tr>
                    <td colspan="2.5" style="font-size:12px">
                        <strong>NO SOMOS GRANDES CONTRIBUYENTES</strong><br>
                        <strong>NO SOMOS AUTORETENEDORES DE RENTA</strong><br>
                        <strong>POR FAVOR ABSTENERSE PRACTICAR RETENCIÓN EN LA FUENTE POR ICA,</strong><br>
                        <strong>SOMOS GRANDES CONTRIBUYENTES DE ICA EN BUCARAMANGA. RESOLUCIÓN 3831 DE 18/04/2022</strong><br>
                        {{ $regimen }}<br>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="factura-table" cellspacing="0" cellpadding="0" style="text-transform:uppercase;margin-top:20px;">
            <tr>
                <td style="font-size:10px;width:15%;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    <strong>Cliente:</strong>
                </td>
                <td style="font-size:10px;width:60%;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    {{ trim($cliente->NombreCliente ?? 'Nombre del Cliente') }}
                </td>
                <td style="font-size:10px;width:10%;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    <strong>N.I.T. o C.C.:</strong>
                </td>
                <td style="font-size:10px;width:15%;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    {{ number_format((int)($cliente->IdCliente ?? 0), 0, ",", ".") }}
                </td>
            </tr>
        </table>
        <hr style="border:1px dotted #ccc;width:100%;">

        <!-- Productos -->
        <table class="factura-table" cellspacing="0" cellpadding="0">
            <tr>
                <td style="font-size:10px;background:#c6c6c6;text-align:center;width:40%;">Descripción</td>
                <td style="font-size:10px;background:#c6c6c6;text-align:center;width:10%;">Lote</td>
                <td style="font-size:10px;background:#c6c6c6;text-align:center;width:10%;">F. Venc.</td>
                <td style="font-size:10px;background:#c6c6c6;text-align:center;width:5%;">Und</td>
                <td style="font-size:10px;background:#c6c6c6;text-align:center;width:5%;">Iva</td>
                <td style="font-size:10px;background:#c6c6c6;text-align:center;width:10%;">Precio</td>
                <td style="font-size:10px;background:#c6c6c6;text-align:center;width:10%;">Total</td>
            </tr>
            @php
                $total_iva = 0;
                $total_descuento = 0;
                $subtotal = 0;
            @endphp
            @foreach ($productos as $prod)
                @php
                    $cantidad = (float) $prod->Cantidad;
                    $precio_venta = (float) $prod->PrecioVenta;
                    $descuento = (float) ($prod->Descuento ?? 0);
                    $iva = (float) ($prod->Impuesto) / 100;
                    $total_producto = $cantidad * $precio_venta;
                    $total_iva_producto = $total_producto * $iva;
                    $total_descuento_producto = $total_producto * $descuento / 100;

                    $total_iva += $total_iva_producto;
                    $total_descuento += $total_descuento_producto;
                    $subtotal += $total_producto;
                @endphp
                <tr>
                    <td class="factura-td" style="padding:4px;font-size:9px;text-align:left;border:1px solid #c6c6c6;vertical-align:middle;">
                        {{ $prod->producto }}
                    </td>
                    <td class="factura-td" style="padding:4px;font-size:9px;text-align:center;border:1px solid #c6c6c6;vertical-align:middle;">
                        {{ $prod->Lote }}
                    </td>
                    <td class="factura-td" style="padding:4px;font-size:9px;text-align:center;border:1px solid #c6c6c6;vertical-align:middle;">
                        {{ $prod->Vencimiento }}
                    </td>
                    <td class="factura-td" style="padding:4px;font-size:9px;text-align:center;border:1px solid #c6c6c6;vertical-align:middle;">
                        {{ number_format($cantidad, 0, "", ".") }}
                    </td>
                    <td class="factura-td" style="padding:4px;font-size:9px;text-align:center;border:1px solid #c6c6c6;vertical-align:middle;">
                        {{ $prod->Impuesto }}
                    </td>
                    <td class="factura-td" style="padding:4px;font-size:9px;text-align:right;border:1px solid #c6c6c6;vertical-align:middle;">
                        $ {{ number_format($precio_venta, 0, ",", ".") }}
                    </td>
                    <td class="factura-td" style="padding:4px;font-size:9px;text-align:right;border:1px solid #c6c6c6;vertical-align:middle;">
                        $ {{ number_format($total_producto, 0, ",", ".") }}
                    </td>
                </tr>
            @endforeach
            @php
                $total = $subtotal + $total_iva - $total_descuento;
                $numero = (float) $total;
                $letras = $numero;
            @endphp
        </table>

        <!-- Totales -->
        <table class="factura-table" style="margin-top:20px;margin-bottom:0;">
            <tr>
                <td colspan="2" style="padding:4px;font-size:9px;border:1px solid #c6c6c6;"><strong>Valor a Letras:</strong><br>{{ $letras }} PESOS MCTE</td>
                <td rowspan="3" style="padding:4px;font-size:9px;border:1px solid #c6c6c6;width:130px;">
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="padding:4px;font-size:9px;"><strong>Subtotal</strong></td>
                            <td style="padding:4px;font-size:9px;text-align:right;">$ {{ number_format($subtotal, 0, ",", ".") }}</td>
                        </tr>
                        <tr>
                            <td style="padding:4px;font-size:9px;"><strong>Dcto.</strong></td>
                            <td style="padding:4px;font-size:9px;text-align:right;">$ {{ number_format($total_descuento, 0, ",", ".") }}</td>
                        </tr>
                        <tr>
                            <td style="padding:4px;font-size:9px;"><strong>Iva 19%</strong></td>
                            <td style="padding:4px;font-size:9px;text-align:right;">$ {{ number_format($total_iva, 0, ",", ".") }}</td>
                        </tr>
                        <tr>
                            <td style="padding:4px;font-size:9px;"><strong>Retención</strong></td>
                            <td style="padding:4px;font-size:9px;text-align:right;">$ 0</td>
                        </tr>
                        <tr>
                            <td style="padding:4px;font-size:9px;"><strong>Total</strong></td>
                            <td style="padding:4px;font-size:9px;text-align:right;"><strong>$ {{ number_format($subtotal + $total_iva - $total_descuento, 0, ",", ".") }}</strong></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="padding:4px;font-size:9px;border:1px solid #c6c6c6;">
                    <strong>Observaciones:</strong><br>
                    {{ $cliente->observacion ?? '' }} - {{ $cliente->Observaciones2 ?? '' }}
                </td>
                <td style="padding:4px;font-size:9px;border:1px solid #c6c6c6;"></td>
            </tr>
        </table>

        <!-- Firma -->
        <table class="factura-table">
            <tr>
                <td style="font-size:10px;width:355px;vertical-align:middle;padding:5px;text-align:center;">
                    <br><br>______________________________<br>
                    Elaborado Por<br>{{ $func->first_name ?? '' }} {{ $func->second_name ?? '' }} {{ $func->first_surname ?? '' }} {{ $func->second_surname ?? '' }}
                </td>
                <td style="font-size:10px;width:355px;vertical-align:middle;padding:5px;text-align:center;">
                    <br><br>______________________________<br>
                    Recibí Conforme<br>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
