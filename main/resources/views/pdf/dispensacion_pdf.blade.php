<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura</title>
    <style>
        /* Estilos generales */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            /* Centra el contenido horizontalmente */
            min-height: 100vh;
            /* Altura mínima del viewport */
            align-items: start;
            /* Alinea el contenido hacia arriba */
        }

        .page-content {
            width: 100%;
            /* Usa todo el ancho */
            max-width: 750px;
            /* Ancho máximo para mantener el diseño */
        }

        .row {
            display: flex;
            /* Cambiado a flex para mejor control del layout */
            width: 100%;
            /* Ancho total del contenedor */
            justify-content: space-between;
            /* Espacio distribuido entre elementos */
        }

        .td-header,
        .inner-content,
        .info-text,
        .sub-info-text,
        .small-text,
        .extra-small-text {
            width: 100%;
            /* Ajusta elementos para usar todo el ancho disponible */
        }

        .highlight,
        .bordered,
        .table-header,
        .table-cell,
        .footer-table,
        .signature {
            width: 100%;
            /* Asegura que las tablas y celdas usen todo el ancho disponible */
        }

        .table-header,
        .table-cell {
            padding: 6px 4px;
            /* Ajuste de padding para celdas */
        }

        .signature {
            padding: 0 5%;
            /* Ajuste de padding para la sección de firma */
        }

        .row {
            display: inline-block;
            width: 750px;
        }

        .td-header {
            font-size: 15px;
            line-height: 20px;
        }

        /* Estilos específicos */
        .inner-content {
            width: 750px;
        }

        .info-text {
            font-size: 22px;
            line-height: 22px;
        }

        .sub-info-text {
            font-size: 15px;
            line-height: 15px;
        }

        .small-text {
            font-size: 13px;
            line-height: 15px;
        }

        .extra-small-text {
            font-size: 10px;
            line-height: 10px;
        }

        .highlight {
            background: #f3f3f3;
        }

        .bordered {
            border: 1px solid #c6c6c6;
        }

        .table-header {
            font-weight: bold;
            font-size: 10px;
            background: #c6c6c6;
            text-align: center;
            padding: 6px;
        }

        .table-header1 {
            font-weight: bold;
            font-size: 10px;
            background: #eeecec;
            text-align: center;
            padding: 6px;
        }

        .table-cell {
            font-size: 9px;
            text-align: center;
            padding: 4px;
        }

        .text-right {
            text-align: right;
        }

        .footer-table {
            font-size: 10px;
            background: #e9eef0;
            border-radius: 5px;
            padding: 8px;
            text-align: right !important;
        }

        .signature {
            text-transform: uppercase;
            margin: 10px 10%;
        }

        .signature-header {
            font-weight: bold;
            font-size: 10px;
            background: #f3f3f3;
            vertical-align: middle;
            padding: 6px;
        }

        .signature-cell {
            font-size: 9px;
            vertical-align: middle;
            padding: 6px;
        }
    </style>
</head>
@include('components/cabecera', [$datosCabecera])

<body>
    <div class="page-content">


        <table cellspacing="0" cellpadding="0" class="highlight">
            <tr>
                <td class="table-header1">Paciente</td>
                <td class="table-header1">Identificación</td>
                <td class="table-header1">Dirección</td>
                <td class="table-header1">Regimen</td>
                <td class="table-header1">Telefono</td>
            </tr>
            <tr>
                <td class="table-cell">{{ utf8_decode($encabezado['Nombre_Paciente'] ?? '') }}</td>
                <td class="table-cell">{{ $encabezado['Id_Paciente'] ?? '' }}</td>
                <td class="table-cell">{{ $encabezado['Direccion_Paciente'] ?? '' }}</td>
                <td class="table-cell">{{ $encabezado['Regimen_Paciente'] ?? '' }}</td>
                <td class="table-cell">{{ $encabezado['Telefono_Paciente'] ?? '' }}</td>
            </tr>
            {{ $contenido_nro_prescripcion ?? '' }}
        </table>
        <br>
        <br>
        @php
            $solicitados = 0;
            $entregados = 0;
            $diferencia = 0;
        @endphp
        <table cellspacing="0" cellpadding="0" class="highlight bordered">
            <tr>
                <td class="table-header">Producto</td>
                <td class="table-header">Cum</td>
                <td class="table-header">Lote</td>
                <td class="table-header">Cant. Formulada</td>
                <td class="table-header">Cant.Entregada</td>
            </tr>
            @foreach ($productos as $producto)
                <tr>
                    <td class="table-cell">{{ $producto->Nombre_Producto ?? '' }}</td>
                    <td class="table-cell">{{ $producto->Cum ?? '' }}</td>
                    <td class="table-cell">{{ $producto->Lote ?? '' }}</td>
                    <td class="table-cell">{{ $producto->Cantidad_Formulada ?? '' }}</td>
                    <td class="table-cell">{{ $producto->Cantidad_Entregada ?? '' }}</td>

                </tr>
            @endforeach

            @php
                $solicitados += $producto->Cantidad_Formulada;
                $entregados += $producto->Cantidad_Entregada;
            @endphp
        </table>
        <table class="footer-table">
            <tr>
                <td>
                    <strong>Productos Solicitados:</strong> {{ $solicitados ?? '' }}
                    <br>
                    <strong>Productos Entregados:</strong> {{ $entregados ?? '' }}
                    <br>
                    <strong>Diferencia:</strong> {{ $solicitados - $entregados ?? '' }}
                    <br>
                    @if (isset($encabezado['Tipo']) == 'Capita')
                        <strong>Cuota Moderadora:</strong>
                        ${{ number_format($encabezado['Cuota'] ?? '', 2, '.', ',') }}
                    @else
                        <strong>Cuota Recuperacion:</strong>
                        ${{ isset($encabezado['Couta']) && is_numeric($encabezado['Couta']) ? number_format($encabezado['Couta'], 2, '.', ',') : '' }}
                    @endif
                </td>
            </tr>
        </table>

        <table cellspacing="0" cellpadding="0" class="signature">
            <tr>
                <td class="signature-header">Reclamante</td>
                <td class="signature-header">Identificación</td>
                <td class="signature-header">Parentesco</td>
            </tr>
            <tr>
                <td class="signature-cell">{{ utf8_decode($customReclamante['Nombre'] ?? '') }}</td>
                <td class="signature-cell">{{ $customReclamante['Id_Reclamante'] ?? '' }}</td>
                <td class="signature-cell">{{ $customReclamante['Parentesco'] ?? '' }}</td>
            </tr>
        </table>

        @if (isset($encabezado['Firma']) != '')
            <img src="{{ $encabezado['Firma'] }}" style="max-height: 100px; max-width: 230px;">
        @else
            <br>
        @endif

        <table style="margin-top:20px">
            <tr>
                <td style="font-size:10px; width:355px; vertical-align:middle; padding:5px; text-align:center;">
                    <br>
                    <hr style="border: none; border-bottom: 1px solid black; width: 60%; margin: 7px auto;">

                    Elaborado Por<br>{{ $encabezado['Funcionario'] }}
                </td>
                <td style="font-size:10px; width:355px; vertical-align:middle; padding:5px; text-align:center;">
                    <hr style="border: none; border-bottom: 1px solid black; width: 60%; margin: 7px auto;">
                    Recibí Conforme<br>

                </td>
            </tr>
        </table>



    </div>
</body>

</html>
