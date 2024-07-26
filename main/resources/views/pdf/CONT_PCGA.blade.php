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

        <p class="pdf-paragraph">Beneficiario: {{ $data->Tercero }}</p>
        <p class="pdf-paragraph">Documento: {{ $data->Beneficiario }}</p>
        <p class="pdf-paragraph">Concepto: {{ $data->Concepto }}</p>


        <table class="pdf-table">
            <thead>
                <tr>
                    <th class="pdf-th">Código {{ $tipo }}</th>
                    <th class="pdf-th">Cuenta {{ $tipo }}</th>
                    <th class="pdf-th">Concepto</th>
                    <th class="pdf-th">Documento</th>
                    <th class="pdf-th">Centro Costo</th>
                    <th class="pdf-th">NIT</th>
                    <th class="pdf-th">Debito {{ $tipo }}</th>
                    <th class="pdf-th">Credito {{ $tipo }}</th>
                </tr>
            </thead>
            @php
                $totalDeb = 0;
                $totalCred = 0;
            @endphp
            <tbody>
                @foreach ($cuentas as $cuenta)
                    <tr>
                        @php
                            if ($tipo != '') {
                                $Codigo = $cuenta['Codigo_Niif'];
                                $nombre_cuenta = $cuenta['Cuenta_Niif'];
                                $debe = $cuenta['Deb_Niif'];
                                $haber = $cuenta['Cred_Niif'];
                            } else {
                                $codigo = $cuenta['Codigo'];
                                $nombre_cuenta = $cuenta['Cuenta'];
                                $debe = $cuenta['Debito'];
                                $haber = $cuenta['Credito'];
                            }
                            $totalDeb += $debe;
                            $totalCred += $haber;
                        @endphp


                        <td class="pdf-td">{{ $cuenta->Codigo }}</td>
                        <td class="pdf-td">{{ $cuenta->Cuenta }}</td>
                        <td class="pdf-td">{{ $cuenta->Concepto }}</td>
                        <td class="pdf-td">{{ $cuenta->Documento }}</td>
                        <td class="pdf-td">{{ $cuenta->Nombre_Centro_Costo }}</td>
                        <td class="pdf-td">{{ $cuenta->Nit }}</td>
                        <td class="pdf-td">${{ number_format($debe, 2, ',', '.') }}</td>
                        <td class="pdf-td">${{ number_format($haber, 2, ',', '.') }}</td>


                    </tr>
                @endforeach
                <tr>
                    <td colspan="6" class="pdf-td">Total</td>
                    <td class="pdf-td">${{ number_format($totalDeb, 2, ',', '.') }}</td>
                    <td class="pdf-td">${{ number_format($totalCred, 2, ',', '.') }}</td>
                </tr>

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
                <td>{{ $elabora }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>

    </div>
</body>

</html>
