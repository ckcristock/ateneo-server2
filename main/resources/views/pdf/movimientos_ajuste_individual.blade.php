<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF</title>
    <style>
        .page-content {
            width: 750px;
        }

        .row {
            display: inlinie-block;
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
                Débitos {{ $tipo_valor }}
            </td>
            <td style="width:115px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                Crédito {{ $tipo_valor }}
            </td>
        </tr>
        @if (count($movimientos) > 0)
            @foreach ($movimientos as $value)
                @php
                    $codigo = $tipo_valor != '' ? $value->Codigo_Niif : $value->Codigo;
                    $nombre_cuenta = $tipo_valor != '' ? $value->Nombre_Niif : $value->Nombre;
                    $debe = $tipo_valor != '' ? $value->Debe_Niif : $value->Debe;
                    $haber = $tipo_valor != '' ? $value->Haber_Niif : $value->Haber;
                    $total_debe = $tipo_valor != '' ? $movimientos_suma['Debe_Niif'] : $movimientos_suma['Debe'];
                    $total_haber = $tipo_valor != '' ? $movimientos_suma['Haber_Niif'] : $movimientos_suma['Haber'];
                @endphp
                <tr>
                    <td style="width:78px;padding:4px;text-align:left;border:1px solid #cccccc;">
                        {{ $codigo }}
                    </td>
                    <td style="width:150px;padding:4px;text-align:left;border:1px solid #cccccc;">
                        {{ $nombre_cuenta }}
                    </td>
                    <td style="width:100px;padding:4px;text-align:right;border:1px solid #cccccc;">
                        {{ $value->Documento }}
                    </td>
                    <td style="width:100px;padding:4px;text-align:right;border:1px solid #cccccc;">
                        {{ $value->Nombre_Cliente }} - {{ $value->Nit }}
                    </td>
                    <td style="width:100px;padding:4px;text-align:right;border:1px solid #cccccc;">
                        $ {{ number_format($debe, 2, '.', ',') }}
                    </td>
                    <td style="width:100px;padding:4px;text-align:right;border:1px solid #cccccc;">
                        $ {{ number_format($haber, 2, '.', ',') }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4" style="padding:4px;text-align:center;border:1px solid #cccccc;">
                    TOTAL
                </td>
                <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                    $ {{ number_format($total_debe, 2, '.', ',') }}
                </td>
                <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                    $ {{ number_format($total_haber, 2, '.', ',') }}
                </td>
            </tr>
        @endif
    </table>

    <table style="margin-top:10px;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="font-weight:bold;width:172px;border:1px solid #cccccc;padding:4px">
                Elaboró:
            </td>
            <td style="font-weight:bold;width:172px;border:1px solid #cccccc;padding:4px">
                Imprimió:
            </td>
            <td style="font-weight:bold;width:172px;border:1px solid #cccccc;padding:4px">
                Revisó:
            </td>
            <td style="font-weight:bold;width:170px;border:1px solid #cccccc;padding:4px">
                Aprobó:
            </td>
        </tr>
        <tr>
            <td style="font-size:10px;width:172px;border:1px solid #cccccc;padding:4px">
                {{ $elabora }}
            </td>
            <td style="font-size:10px;width:172px;border:1px solid #cccccc;padding:4px">
                {{ $imprime['Nombre_Funcionario'] }}
            </td>
            <td style="font-size:10px;width:172px;border:1px solid #cccccc;padding:4px">
            </td>
            <td style="font-size:10px;width:170px;border:1px solid #cccccc;padding:4px">
            </td>
        </tr>
    </table>
</body>

</html>