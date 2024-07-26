<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario Físico</title>
    <style>
        .page-content {
            width: 750px;
        }
        .td-header {
            font-size: 15px;
            line-height: 20px;
        }
    </style>
</head>
<body>
    <div class="page-content">
        @include('components/cabecera', [$datosCabecera])
        <hr style="border:1px dotted #ccc;width:730px;">

        <table style="font-size:10px;margin-top:10px" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width:354px;max-width:236px;font-weight:bold;background:#cecece;border:1px solid #cccccc;">
                    Funcionario Digitador
                </td>
                <td style="width:354px;max-width:236px;font-weight:bold;background:#cecece;border:1px solid #cccccc;">
                    Funcionario Contador
                </td>
            </tr>
            <tr>
                <td style="width:236px;font-size:9px;vertical-align:middle;background:#f2f2f2;word-wrap: break-word;text-align:center;border:1px solid #cccccc;">
                    {{ $datos->Funcionario_Digitador ?? 'Funcionario Digitador' }}
                </td>
                <td style="width:236px;font-size:9px;vertical-align:middle;background:#f2f2f2;word-wrap: break-word;text-align:center;border:1px solid #cccccc;">
                    {{ $datos->Funcionario_Cuenta ?? 'Funcionario Contador' }}
                </td>
            </tr>
        </table>

        <table style="font-size:10px;margin-top:20px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width:10px;max-width:10px;font-weight:bold;background:#cecece;border:1px solid #cccccc;">
                    Nro.
                </td>
                <td style="width:250px;max-width:280px;font-weight:bold;background:#cecece;border:1px solid #cccccc;">
                    Producto
                </td>
                <td style="width:50px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Lote
                </td>
                <td style="width:60px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Fecha Venc.
                </td>
                <td style="width:70px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Primer Conteo
                </td>
                <td style="width:70px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Segundo Conteo
                </td>
                <td style="width:70px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Diferencia
                </td>
                <td style="width:70px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Cantidad Final
                </td>
            </tr>
            @php
                $diferencias = 0;
            @endphp
            @foreach ($productos as $i => $prod)
                @php
                    $cantidad_diferencial = (float) $prod->Cantidad_Diferencial;
                    $diferencias += $cantidad_diferencial;
                @endphp
                <tr>
                    <td style="padding:3px 2px;width:10px;max-width:10px;font-size:9px;text-align:center;vertical-align:middle;border:1px solid #cccccc;word-break: break-all !important;"><b>{{ $i + 1 }}</b></td>
                    <td style="padding:3px 2px;width:250px;max-width:280px;font-size:9px;text-align:left;vertical-align:middle;border:1px solid #cccccc;word-break: break-all !important;"><b>{{ $prod->Nombre_Comercial }}</b><br><span style="color:gray">{{ $prod->Nombre_Producto }}</span></td>
                    <td style="width:50px;font-size:9px;vertical-align:middle;word-wrap: break-word;text-align:center;border:1px solid #cccccc;">{{ $prod->Lote }}</td>
                    <td style="width:60px;font-size:9px;vertical-align:middle;word-wrap: break-word;text-align:center;border:1px solid #cccccc;">{{ $prod->Fecha_Vencimiento }}</td>
                    <td style="width:70px;font-size:9px;vertical-align:middle;word-wrap: break-word;text-align:center;border:1px solid #cccccc;">{{ $prod->Cantidad_Encontrada }}</td>
                    <td style="width:70px;font-size:9px;vertical-align:middle;word-wrap: break-word;text-align:center;border:1px solid #cccccc;">{{ $prod->Segundo_Conteo }}</td>
                    <td style="width:70px;font-size:9px;vertical-align:middle;word-wrap: break-word;text-align:center;border:1px solid #cccccc;">{{ $cantidad_diferencial }}</td>
                    <td style="width:70px;font-size:9px;vertical-align:middle;word-wrap: break-word;text-align:center;border:1px solid #cccccc;">{{ $prod->Cantidad_Final }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="8" style="width:70px;font-weight:bold;background:#cecece;text-align:right;border:1px solid #cccccc;">
                    Diferencia Total: {{ $diferencias }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
