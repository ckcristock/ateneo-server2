<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            font-family: sans-serif;
        }

        table {
            border-collapse: collapse;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
        }

        .table td,
        .table th {
            padding: 0.5rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
        }

        .table tbody+tbody {
            border-top: 2px solid #dee2e6;
        }

        .table-borderless tbody+tbody,
        .table-borderless td,
        .table-borderless th,
        .table-borderless thead th {
            border: 0;
        }

        .text-left {
            text-align: left !important;
        }

        body,
        html {
            margin: 0;
            padding: 0;
            background-size: cover;
            background-image: url({{ $company->page_heading }});
        }

        .text-right {
            text-align: right !important;
        }

        .my-0 {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .mb-0 {
            margin-bottom: 0 !important;
        }

        figure {
            display: block;
            margin: 0 0 1rem;
        }

        .figure {
            display: inline-block;
        }

        .figure-img {
            margin-bottom: 0.5rem;
            line-height: 1;
            width: 250px;
        }

        .figure-caption {
            font-size: 90%;
            color: #6c757d;
        }

        .text-primary {
            color: #95C11F !important;
        }

        .mr-2 {
            margin-right: 1rem !important;
        }

        .mt-2 {
            margin-top: 1rem !important;
        }

        .mt-1 {
            margin-top: 0.5rem !important;
        }

        .mt-4 {
            margin-top: 2rem !important;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .pb-0 {
            padding-bottom: 0;
        }

        .w-100 {
            width: 100%;
        }

        .mx-4 {
            margin-left: 2rem;
            margin-right: 2rem;
        }

        .m-2 {
            padding: 0.5rem !important;
        }

        .body-certificate {
            /* margin-left: 2rem;
            margin-right: 2rem;
            margin-top: 3rem; */
            text-transform: uppercase;
        }

        footer {
            position: absolute;
            bottom: 2rem;
            width: 100%;
            margin-left: 2rem;
            margin-right: 2rem;
            text-transform: uppercase;
        }
    </style>
</head>
@include('components/cabecera', [$company, $datosCabecera, $image])
<body>
    <div class="body-certificate">
        <p><b>Girón, {{ $date }}</b></p>
        <div class="mt-4"><b>Señores</b></div>
        <div>{{ $layoffs_certificate->person ? ($layoffs_certificate->person->severance_fund ? $layoffs_certificate->person->severance_fund->name : '') : '' }}
        </div>
        <p class="mt-4"><b>ASUNTO:</b>
            @if ($layoffs_certificate->monto == 'parcial')
                RETIRO PARCIAL DE CESANTIAS <br><br>
                Respetados señores: <br><br>
                Para efectos de lo dispuesto en el decreto 2076 de 1967 y normas concordantes,
                nos permitimos solicitar a ustedes que se sirvan autorizar el pago de la referencia
                a favor del siguiente empleado.
            @endif
            @if ($layoffs_certificate->monto == 'total')
                Autorización retiro total de Cesantías
            @endif
        </p>
        <table class="table table-borderless mt-4">
            <tbody>
                <tr>
                    <td><b>NOMBRE DEL EMPLEADO(A):</b></td>
                    <td>{{ optional($layoffs_certificate->person)->person }}</td>
                </tr>
                <tr>
                    <td><b>IDENTIFICACION:</b></td>
                    <td>{{ number_format(optional($layoffs_certificate->person)->identifier, 0, '', '.') }}</td>
                </tr>
                @if ($layoffs_certificate->monto == 'parcial')
                    <tr>
                        <td><b>VALOR CESANTIAS SOLICITADO:</b></td>
                        <td>${{ number_format($layoffs_certificate->valormonto, 0, '', '.') }}</td>
                    </tr>
                    <tr>
                        <td><b>INVERSION O DESTINO:</b></td>
                        <td>{{ optional($layoffs_certificate->reason_withdrawal_list)->name }}</td>
                    </tr>
                @endif
                @if ($layoffs_certificate->monto == 'total')
                    <tr>
                        <td><b>MOTIVO:</b></td>
                        <td>{{ optional($layoffs_certificate->reason_withdrawal_list)->name }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
        @if ($layoffs_certificate->monto == 'parcial')
            <div class="mt-4">
                Lo anterior previa presentación de los soportes presentados para tal fin de conformidad con lo
                establecido en la
                ley 1429
                artículo 21 de 2010, por lo anterior, confirmamos el retiro en mención al trabajador, quien va a
                utilizar los
                recursos de sus
                cesantías conforme a las condiciones previstas en la ley. <br><br>
                MAQUINADOS Y MONTAJES S.A.S, Se compromete a vigilar la inversión conforme a lo dispuesto por el decreto
                antes
                citado y según
                resolución No 04250 de 1973, en concordancia con la circular N°000 de 1974.
            </div>
        @endif
    </div>
    <footer>
        <table class="table table-borderless">
    <tr>
        <td>Cordialmente,</td>
        <td>Trabajador,</td>
    </tr>
    <tr>
        <td style="padding-top: 3rem">____________________________________</td>
        <td style="padding-top: 3rem">____________________________________</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">{{ $responsableRhName }}</td>
        <td style="font-weight:bold;">{{ optional($layoffs_certificate->person)->full_name }}</td>
    </tr>
    <tr>
        <td>Representante legal</td>
        <td>C.C {{ number_format(optional($layoffs_certificate->person)->identifier, 0, '', '.') }}</td>
    </tr>
</table>

    </footer>
</body>

</html>
