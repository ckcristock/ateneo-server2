<!DOCTYPE html>
<html>

<head>
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
            <table>
                <tr>
                    <td style="width:350px; padding-right:10px;">
                        <table cellspacing="0" cellpadding="0" style="text-transform:uppercase;">
                            <tr>
                                <td colspan="2"
                                    style="font-size:10px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                                    Origen</td>
                            </tr>
                            <tr>
                                <td style="font-size:10px;width:175px;background:#f3f3f3;border:1px solid #cccccc;">
                                    {{$origen["Nombre"]}}
                                </td>
                                <td style="font-size:10px;width:175px;background:#f3f3f3;border:1px solid #cccccc;">
                                    {{$origen["Direccion"]}}
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size:10px;width:175px;background:#f3f3f3;border:1px solid #cccccc;">
                                    <strong>Tel.:</strong> {{$origen["Telefono"] ?? $origen["cell_phone"] ?? ''}}
                                </td>
                                <td style="font-size:10px;width:175px;background:#f3f3f3;border:1px solid #cccccc;">
                                    <strong>Correo:</strong> {{$origen["email"] ?? ''}}
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width:350px; padding-leftt:10px;">
                        <table cellspacing="0" cellpadding="0" style="text-transform:uppercase;">
                            <tr>
                                <td colspan="2"
                                    style="font-size:10px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                                    Destino</td>
                            </tr>
                            <tr>
                                <td style="font-size:10px;width:175px;background:#f3f3f3;border:1px solid #cccccc;">
                                    {{$destino["Nombre"]}}
                                </td>
                                <td style="font-size:10px;width:175px;background:#f3f3f3;border:1px solid #cccccc;">
                                    {{$destino["Direccion"]}}
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size:10px;width:175px;background:#f3f3f3;border:1px solid #cccccc;">
                                    <strong>Tel.:</strong> {{$destino["Telefono"] ?? $destino["cell_phone"] ?? ''}}
                                </td>
                                <td style="font-size:10px;width:175px;background:#f3f3f3;border:1px solid #cccccc;">
                                    <strong>Correo:</strong> {{$destino["email"] ?? ''}}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table style="font-size:10px;margin-top:10px;" cellpadding="0" cellspacing="0">
                <tr>
                    < <td style="width:10px;background:#cecece;;border:1px solid #cccccc;">
                        </td>
                        <td
                            style="width:300px;max-width:300px;font-weight:bold;background:#cecece;;border:1px solid #cccccc;">
                            Producto
                        </td>
                        <td
                            style="width:200px;max-width:200px;font-weight:bold;background:#cecece;;border:1px solid #cccccc;">
                            Paciente
                        </td>
                        <td
                            style="width:70px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                            Lote
                        </td>
                        <td
                            style="width:70px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                            Dispen
                        </td>
                        <td
                            style="width:50px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                            Cant.
                        </td>
                </tr>
                @php
                    $max = 0;
                @endphp
                @foreach ($productos as $prod)
                                @php
                                    $max++;
                                @endphp
                                <tr>
                                    <td style="width:10px;background:#f3f3f3;border:1px solid #cccccc;text-align:center;">{{$max}}</td>
                                    <td
                                        style="padding:3px 2px;width:250px;max-width:280px;font-size:9px;text-align:left;vertical-align:middle;border:1px solid #cccccc;word-break: break-all !important;">
                                        <b>{{$prod["Nombre_Comercial"]}} </b><br><span
                                            style="color:gray">{{$prod["Nombre_Producto"]}}</span>
                                    </td>

                                    <td
                                        style="padding:3px 2px;width:200px;max-width:100px;font-size:9px;text-align:left;background:#f3f3f3;border:1px solid #cccccc;word-break: break-all !important;">
                                        {{$prod["Paciente"]}}
                                    </td>
                                    <td
                                        style="width:70px;font-size:9px;word-wrap: break-word;text-align:center;background:#f3f3f3;border:1px solid #cccccc;">
                                        {{$prod["Lote"]}}
                                    </td>
                                    <td
                                        style="width:70px;font-size:9px;word-wrap: break-word;text-align:center;background:#f3f3f3;border:1px solid #cccccc;">
                                        {{$prod["DIS"]}}
                                    </td>
                                    <td
                                        style="width:50px;font-size:9px;word-wrap: break-word;text-align:center;background:#f3f3f3;border:1px solid #cccccc;">
                                        {{$prod["Cantidad"]}}
                                    </td>
                                </tr>
                @endforeach
            </table>
            <table style="margin-top:10px;font-size:10px;">
                <tr>
                    <td style="width:180px;border:1px solid #cccccc;">
                        <strong>Persona Elaboró</strong><br><br><br><br><br><br><br>
                        {{$elabora["first_name"]}} {{$elabora["first_surname"]}}
                    </td>
                    <td style="width:180px;border:1px solid #cccccc;">
                        <strong>Alistamiento Fase 1</strong><br><br><br><br><br><br><br>
                    </td>
                    <td style="width:180px;border:1px solid #cccccc;">
                        <strong>Alistamiento Fase 2</strong><br><br><br><br><br><br><br>
                    </td>
                    <td style="width:180px;border:1px solid #cccccc;">
                        <strong>Entrega Pendientes</strong><br><br><br><br><br><br><br>{{$rem['Funcionario'] ?? ''}}
                    </td>
                </tr>
            </table>
        </div>
    </page>
</body>

</html>