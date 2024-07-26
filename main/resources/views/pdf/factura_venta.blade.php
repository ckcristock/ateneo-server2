<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura Venta</title>
    <style>
        .page-content {
            width: 750px;
        }
        .row {
            display: inline-block;
            width: 750px;
        }
        .td-header {
            font-size: 15px;
            line-height: 20px;
        }
        .titular {
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    @include('components/cabecera', [$datosCabecera])
    <div class="page-content">
        
        <hr style="border:1px dotted #ccc;width:730px;">

        <table style="font-size:10px;margin-top:10px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width:78px;font-weight:bold;text-align:center;background:#cecece;border:1px solid #cccccc;">
                    Cuenta {{ $tipo_valor }}
                </td>
                <td style="width:170px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Nombre Cuenta {{ $tipo_valor }}
                </td>
                <td style="width:115px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Documento
                </td>
                <td style="width:115px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Nit
                </td>
                <td style="width:115px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Debitos {{ $tipo_valor }}
                </td>
                <td style="width:115px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Crédito {{ $tipo_valor }}
                </td>
            </tr>

            @foreach ($movimientos as $value)
                @php
                dd($imprime);
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
                @endphp
                <tr>
                    <td style="width:78px;padding:4px;text-align:left;border:1px solid #cccccc;">
                        {{ $codigo }}
                    </td>
                    <td style="width:150px;padding:4px;text-align:left;border:1px solid #cccccc;">
                        {{ $nombre_cuenta }}
                    </td>
                    <td style="width:100px;padding:4px;text-align:right;border:1px solid #cccccc;">
                        {{ $value["Documento"] }}
                    </td>
                    <td style="width:100px;padding:4px;text-align:right;border:1px solid #cccccc;">
                        {{ $value['Nombre_Cliente'] }} - {{ $value["Nit"] }}
                    </td>
                    <td style="width:100px;padding:4px;text-align:right;border:1px solid #cccccc;">
                        $ {{ number_format($debe, 2, ".", ",") }}
                    </td>
                    <td style="width:100px;padding:4px;text-align:right;border:1px solid #cccccc;">
                        $ {{ number_format($haber, 2, ".", ",") }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4" style="padding:4px;text-align:center;border:1px solid #cccccc;">
                    TOTAL
                </td>
                <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                    $ {{ isset($total_debe) ? number_format($total_debe, 2, ".", ",") : '0.00' }}
                </td>
                <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                    $ {{ isset($total_haber) ? number_format($total_haber, 2, ".", ",") : '0.00' }}
                </td>
                
            </tr>
        </table>

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
                    {{ $elabora['Nombre_Funcionario'] }}
                </td>
                <td style="font-size:10px;width:168px;border:1px solid #cccccc;padding:4px">
                    {{ isset($imprime['Nombre_Funcionario']) ? $imprime['Nombre_Funcionario'] : 'N/A' }}
                </td>
                <td style="font-size:10px;width:168px;border:1px solid #cccccc;padding:4px">
                </td>
                <td style="font-size:10px;width:168px;border:1px solid #cccccc;padding:4px">
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
