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
    </style>
</head>

<body>

    @include('components/cabecera', [$datosCabecera])

    <table cellspacing="0" cellpadding="0" style="text-transform:uppercase;margin-top:20px;">
        <tr>
            <td style="font-size:10px;width:70px;background:#f3f3f3;vertical-align:middle;padding:6px;">
                <strong>Funcionario:</strong>
            </td>
            <td style="font-size:10px;width:430px;background:#f3f3f3;vertical-align:middle;padding:6px;">
                {{ trim($encabezado['Funcionario']) }}
            </td>
            <td style="font-size:10px;width:70px;background:#f3f3f3;vertical-align:middle;padding:6px;">
                <strong>Cargo:</strong>
            </td>
            <td style="font-size:10px;width:120px;background:#f3f3f3;vertical-align:middle;padding:6px;">
                {{ $encabezado['Cargo_Funcionario'] }}
            </td>
        </tr>
    </table><br>
    <hr style="border:1px dotted #ccc;width:730px;"><br><br>

    <table cellspacing="0" cellpadding="0">
        <tr>
            <td style="font-weight:bold;font-size:10px;background:#c6c6c6;text-align:center;">Descripción</td>
            <td style="font-weight:bold;font-size:10px;background:#c6c6c6;text-align:center;">Lote</td>
            <td style="font-weight:bold;font-size:10px;background:#c6c6c6;text-align:center;">F. Venc.</td>
            <td style="font-weight:bold;font-size:10px;background:#c6c6c6;text-align:center;">Cantidad</td>
            <td style="font-weight:bold;font-size:10px;background:#c6c6c6;text-align:center;">Observaciones</td>
        </tr>
        @foreach ($productos as $producto)
            <tr>
                <td
                    style="padding:4px;font-size:9px;text-align:left;border:1px solid #c6c6c6;width:280px;vertical-align:middle;">
                    <strong>{{ $producto->Nombre_Comercial }}</strong><br>
                    <span style="color: gray">{{ $producto->Nombre_Producto }}</span>
                </td>
                <td
                    style="padding:4px;font-size:9px;text-align:center;border:1px solid #c6c6c6;width:50px;vertical-align:middle;">
                    {{ $producto->Lote }}
                </td>
                <td
                    style="padding:4px;font-size:9px;text-align:center;border:1px solid #c6c6c6;width:35px;vertical-align:middle;">
                    {{ $producto->Fecha_Vencimiento }}
                </td>
                <td
                    style="padding:4px;font-size:9px;text-align:center;border:1px solid #c6c6c6;width:60px;vertical-align:middle;">
                    {{ $producto->Cantidad }}
                </td>
                <td
                    style="padding:4px;font-size:9px;text-align:center;border:1px solid #c6c6c6;width:150px;vertical-align:middle;">
                    {{ $producto->Observaciones }}
                </td>
            </tr>
        @endforeach
    </table><br><br><br>

    @if($encabezado['Firma'] != '')
        @php
            $imagen = '<img src="' . $MY_FILE . 'DOCUMENTOS/' . $encabezado['Identificacion_Funcionario'] . '/' . $encabezado['Firma'] . '"  width="130"><br>';
        @endphp
    @else
        @php
            $imagen = '<br><br>______________________________<br>';
        @endphp
    @endif

    <table>
        <tr>
            <td style="font-size:10px;width:355px;vertical-align:middle;padding:5px;text-align:center;">
                {!! $imagen !!}
                Elaborado Por<br>{{ $encabezado['Funcionario'] }}
            </td>
            <td style="font-size:10px;width:355px;vertical-align:middle;padding:5px;text-align:center;">
                <br><br>______________________________<br>
                Recibí Conforme<br>
            </td>
        </tr>
    </table>
</body>

</html>