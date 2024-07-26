<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <page backtop="0mm" backbottom="0mm">
        <div class="page-content">
            @include('components/cabecera', [$datosCabecera])

            <table style="">
                <tr>
                    <td style="width:720px; padding-right:0px;">
                        <table cellspacing="0" cellpadding="0" style="text-transform:uppercase;">
                            <tr>
                                <th
                                    style=" width:230px;font-size:10px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                                    Codigo Remision</th>
                                <th
                                    style=" width:250px;font-size:10px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                                    Bodega Origen</th>
                                <th
                                    style=" width:250px;font-size:10px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                                    {{$titulo_punto_bodega}}
                                </th>
                            </tr>
                            <tr>
                                <td
                                    style="font-size:10px;width:175px;background:#f3f3f3;border:1px solid #cccccc;text-align:center;">
                                    {{$datos["Codigo_Remision"]}}
                                </td>
                                <td
                                    style="font-size:10px;width:175px;background:#f3f3f3;border:1px solid #cccccc; text-align:center;">
                                    {{$datos["Nombre_Origen"]}}
                                </td>
                                <td
                                    style="font-size:10px;width:175px;background:#f3f3f3;border:1px solid #cccccc;text-align:center;">
                                    {{$punto_bodega}}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table style="margin-top:10px">
                <tr>
                    <td style="font-size:10px;width:710px;background:#e9eef0;border-radius:5px;padding:8px;">
                        <strong>Observaciones</strong><br>
                        {{$datos["Observaciones"]}}
                    </td>
                </tr>
            </table>
            <table style="font-size:10px;margin-top:10px;" cellpadding="0" cellspacing="0">
                <tr>
                    < <td style="width:10px;background:#cecece;;border:1px solid #cccccc;">
                        </td>
                        <td
                            style="width:235px;max-width:235px;font-weight:bold;background:#cecece;;border:1px solid #cccccc;">
                            Producto
                        </td>
                        <td
                            style="width:70px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                            Presentación
                        </td>
                        <td
                            style="width:70px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                            Laboratorios
                        </td>
                        <td
                            style="width:60px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                            Invima
                        </td>
                        <td
                            style="width:70px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                            Lote
                        </td>
                        <td
                            style="width:70px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                            F. Vencimiento
                        </td>
                        <td
                            style="width:50px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                            Cantidad
                        </td>
                        <td
                            style="width:40px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                            Temp.
                        </td>
                </tr>
                @php
                    $max = 0;
                @endphp
                @foreach ($productos as $prod)
                                @php
                                    $max++;
                                    $temperatura = $prod->Temperatura == '' ? 'No' : $prod->Temperatura;
                                @endphp
                                <tr>
                                    <td
                                        style="vertical-align:middle;width:10px;background:#f3f3f3;border:1px solid #cccccc;text-align:center;">
                                        {{$max}}
                                    </td>
                                    <td
                                        style="vertical-align:middle;padding:3px 2px;width:235px;max-width:235px;font-size:9px;text-align:left;background:#f3f3f3;border:1px solid #cccccc;word-break: break-all !important;">
                                        {{$prod->Nombre_Producto}}
                                    </td>
                                    <td
                                        style="vertical-align:middle;width:70px;font-size:9px;word-wrap: break-word;text-align:center;background:#f3f3f3;border:1px solid #cccccc;">
                                        {{$prod->Embalaje}}
                                    </td>
                                    <td
                                        style="vertical-align:middle;width:70px;font-size:9px;word-wrap: break-word;text-align:center;background:#f3f3f3;border:1px solid #cccccc;">
                                        {{$prod->Laboratorios}}
                                    </td>
                                    <td
                                        style="vertical-align:middle;width:60px;font-size:9px;word-wrap: break-word;text-align:center;background:#f3f3f3;border:1px solid #cccccc;">
                                        {{$prod->Invima}}
                                    </td>
                                    <td
                                        style="vertical-align:middle;width:70px;font-size:9px;word-wrap: break-word;text-align:center;background:#f3f3f3;border:1px solid #cccccc;">
                                        {{$prod->Lote}}
                                    </td>
                                    <td
                                        style="vertical-align:middle;width:70px;font-size:9px;word-wrap: break-word;text-align:center;background:#f3f3f3;border:1px solid #cccccc;">
                                        {{$prod->Fecha_Vencimiento}}
                                    </td>
                                    <td
                                        style="vertical-align:middle;width:50px;font-size:9px;word-wrap: break-word;text-align:center;background:#f3f3f3;border:1px solid #cccccc;">
                                        {{$prod->Cantidad}}
                                    </td>
                                    <td
                                        style="vertical-align:middle;width:40px;font-size:9px;word-wrap: break-word;text-align:center;background:#f3f3f3;border:1px solid #cccccc;">
                                        {{$temperatura}}
                                    </td>
                                </tr>
                @endforeach
            </table>
            <table style="font-size:10px;margin-top:10px;" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="width:362px;border:1px solid #cccccc;">
                        <strong>Persona Elaborá</strong><br><br><br><br><br>
                        {{$datos["Elabora"]}}
                    </td>
                    <td style="width:364px;border:1px solid #cccccc;">
                        <strong>Persona Recibe</strong><br><br><br><br><br>
                        {{$recibe["full_name"] ?? ''}}
                    </td>

                </tr>
            </table>
        </div>
    </page>
</body>

</html>