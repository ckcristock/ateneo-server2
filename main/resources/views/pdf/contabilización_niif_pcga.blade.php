<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Estilos que proporcionaste */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .pdf-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .pdf-paragraph {
            margin: 5px 0;
        }
        .pdf-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .pdf-table,
        .pdf-th,
        .pdf-td {
            border: 1px solid #ddd;
        }
        .pdf-th,
        .pdf-td {
            padding: 6px;
            text-align: left;
        }
        .pdf-th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .pdf-totals {
            font-weight: bold;
        }
        .pdf-signatures {
            margin-top: 20px;
            width: 100%;
            text-align: left;
        }
        .pdf-signatures {
            border-collapse: collapse;
        }
        .pdf-signatures td {
            border: 1px solid #999999;
            padding: 8px;
        }
    </style>
</head>
<body>
    <div class="pdf-container">
        @include('components/cabecera', [$datosCabecera])
        <table class="pdf-table">
            <thead>
                <tr>
                    <th class="pdf-th">Cuenta {{ $tipo_valor }}</th>
                    <th class="pdf-th">Nombre Cuenta {{ $tipo_valor }}</th>
                    <th class="pdf-th">Documento</th>
                    <th class="pdf-th">Nit</th>
                    <th class="pdf-th">Debitos {{ $tipo_valor }}</th>
                    <th class="pdf-th">Crédito {{ $tipo_valor }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($movimientos as $movimiento)
                    <tr>
                        <td class="pdf-td">{{ $movimiento->codigo }}</td>
                        <td class="pdf-td">{{ $movimiento->nombre }}</td>
                        <td class="pdf-td">{{ $movimiento->documento }}</td>
                        <td class="pdf-td">{{ $movimiento->nombre_cliente }}  {{ $movimiento->Nit }}</td>
                        <td class="pdf-td">$ {{ number_format($movimiento->debe, 2, '.', ',') }}</td>
                        <td class="pdf-td">$ {{ number_format($movimiento->haber, 2, '.', ',') }}</td>
                    </tr>
                @endforeach
                <tr class="pdf-totals">
                    <td colspan="4">TOTAL</td>
                    <td class="pdf-td">$ {{ number_format($total_debe, 2, '.', ',') }}</td>
                    <td class="pdf-td">$ {{ number_format($total_haber, 2, '.', ',') }}</td>
                </tr>
            </tbody>
        </table>
        <table class="pdf-signatures">
            <tr>
                <td>Elaboró:</td>
                <td>Revisó:</td>
                <td>Aprobó:</td>
                <td>Imprimió:</td>
            </tr>
            <tr>
                <td>{{ $elabora->nombre_funcionario ?? '' }}</td>
                <td>{{ $imprime->nombre_funcionario ?? '' }}</td>                
                <td></td>
                <td></td>
                
            </tr>
        </table>
    </div>
</body>
</html>
