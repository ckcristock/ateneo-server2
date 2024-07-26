<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Crédito</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .custom-h3,
        .custom-h5 {
            margin: 5px 0 0 0;
            font-size: 22px;
            line-height: 22px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .custom-th,
        .custom-td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        .custom-th {
            background: #cecece;
            font-size: 10px;
            font-weight: bold;
        }

        .custom-td {
            font-size: 10px;
        }

        .observations {
            width: 100%;
            padding: 8px;
            margin-top: 10px;
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
    @include('components/cabecera', [$datosCabecera])

    <div class="observations">
        Cliente: {{ $Nota_Credito['Id_Cliente'] }}<br>
        Factura: {{ $Nota_Credito['Codigo'] }}<br>
        <strong>Observaciones: </strong>{{ $Nota_Credito['Observaciones'] }}<br>
    </div>

    <table>
        @php
            $total = 0;
            $total_iva = 0;
        @endphp
        <tr>
            <th class="custom-th">#</th>
            <th class="custom-th">Producto</th>
            <th class="custom-th">Motivo</th>
            <th class="custom-th">Observaciones</th>
            <th class="custom-th">Iva</th>
            <th class="custom-th">Total Iva</th>
            <th class="custom-th">Total</th>
        </tr>
        @foreach ($Productos_Nota as $key => $producto)
            <tr>
                <td class="custom-td">{{ $key + 1 }}</td>
                <td class="custom-td">{{ $producto['Nombre_Producto'] }}</td>
                <td class="custom-td">{{ $producto['Motivo'] }}</td>
                <td class="custom-td">{{ $producto['Observacion'] }}</td>
                <td class="custom-td">{{ $producto['Impuesto'] }}</td>
                <td class="custom-td">{{ number_format($producto['Total_Impuesto'], 2, ',', '.') }}</td>
                <td class="custom-td">{{ number_format($producto['Valor_Nota_Credito'], 2, ',', '.') }}</td>
            </tr>
            @php
                $total += $producto['Valor_Nota_Credito'];
                $total_iva += $producto['Total_Impuesto'];
            @endphp
        @endforeach
    </table>

    <div class="footer-table" style="padding-left:570px;">
        <table>
            <tr>
                <td style="width: 60%; text-align: right; "><strong>IVA</strong></td>
                <td style="width: 60%; text-align: right;">$ {{ number_format($total_iva, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="width: 40%; text-align: right;"><strong>TOTAL</strong></td>
                <td style="width: 40%; text-align: right;">$ {{ number_format($total, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>
</body>

</html>
