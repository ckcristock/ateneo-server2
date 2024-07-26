<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            /* Establecer el fondo blanco en el body */
        }

        .pdf-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            /* Puedes mantener este color blanco si deseas */
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

    @include('components/cabecera', [$datosCabecera])
    <div class="pdf-container">

        <p class="pdf-paragraph">Empresa: {{ $data['empresa'] }}</p>
        <p class="pdf-paragraph">N.I.T.: {{ $data['tin'] }}</p>

        @if (!empty($data['cheques']))
            <p class="pdf-paragraph">Cheques: {{ $data['cheques'] }}</p>
        @endif

        <table class="pdf-table">
            <thead>
                <tr>
                    <th class="pdf-th">Código Niif</th>
                    <th class="pdf-th">Cuenta Niif</th>
                    <th class="pdf-th">Documento</th>
                    <th class="pdf-th">NIT</th>
                    <th class="pdf-th">Debitos Niif</th>
                    <th class="pdf-th">Credito Niif</th>
                </tr>
            </thead>
            @php
                $totalDeb = 0;
                $totalCred = 0;
            @endphp
            <tbody>
                @foreach ($data['cuentas'] as $cuenta)
                    <tr>
                        <td class="pdf-td">{{ $cuenta->Codigo_Niif }}</td>
                        <td class="pdf-td">{{ $cuenta->Cuenta_Niif }}</td>
                        <td class="pdf-td">{{ $cuenta->Documento_niif }}</td>
                        <td class="pdf-td">{{ $cuenta->Nit }} </td>
                        <td class="pdf-td">{{ $cuenta->Debito }}</td>
                        <td class="pdf-td">{{ $cuenta->Credito }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="pdf-signatures">
            <tr>
                <td>Elaboró:</td>
                <td>Revisó:</td>
                <td>Aprobó:</td>
                <td>Beneficiario</td>
            </tr>
            <tr>
                <td>{{ $data['elabora'] }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>

    </div>
</body>

</html>
