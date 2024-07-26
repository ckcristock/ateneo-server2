<head>
    <style>
        body {
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif,
        }

        .text-center {
            text-align: center !important;
        }

        .badge {
            display: inline-block;
            padding: 0.25em 0.4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out,
                border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .badge-pill {
            padding-right: 0.6em;
            padding-left: 0.6em;
            border-radius: 10rem;
        }

        .badge-success {
            color: #fff;
            background-color: #28a745;
        }

        .float-right {
            float: right !important;
        }

        .font-weight-bold {
            font-weight: 700 !important;
        }

        .font-weight-normal {
            font-weight: 400 !important;
        }

        .table {
            border-collapse: collapse !important;
        }

        .table td,
        .table th {
            background-color: #fff !important;
        }

        .table-bordered td,
        .table-bordered th {
            border: 1px solid #dee2e6 !important;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
        }

        .table td,
        .table th {
            padding: 0.75rem;
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

        .table-sm td,
        .table-sm th {
            padding: 0.3rem;
        }

        .table-bordered {
            border: 1px solid #dee2e6;
        }

        .table-bordered td,
        .table-bordered th {
            border: 1px solid #dee2e6;
        }

        .table-bordered thead td,
        .table-bordered thead th {
            border-bottom-width: 2px;
        }

        .text-uppercase {
            text-transform: uppercase !important;
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }
    </style>
</head>
<div>

    <div class="text-center">
        COLILLA DE PAGO DE PRIMA DE SERVICIOS</div>
    <span class="badge badge-pill badge-success">Periodo {{ $bonus->lapse }} </span>
    <div class="float-right">Comprobante Nro.</div>
    <br>
    <div>DATOS DEL TRABAJADOR</div>
    <br>
    <div class="font-weight-bold">Nombre: <span class="font-weight-normal"> {{ $bonus->fullname }}</span></div>

    <div class="font-weight-bold">Documento: <span class="font-weight-normal"> {{ $bonus->identifier }}</span></div>
    <div class="font-weight-bold">Día de pago:
        <span class="font-weight-normal">
            {{ Carbon\Carbon::parse($bonus->payment_date)->locale('es_ES')->isoFormat('dddd, D \d\e MMMM YYYY') }}</span>
    </div>

    <br>
</div>
<div class="rounded-top table-responsive">
    <table class="table table-bordered table-sm text-nowrap">
        <thead class="bg-light">
            <tr class="text-center text-uppercase">
                <th>Fecha inicio</th>
                <th>Dias trabajados</th>
                <th>Salario Promedio</th>
                <th>Prima</th>
            </tr>
        </thead>
        <tbody>
            <tr class="text-center">
                <td>
                    {{ $bonus->fecha_inicio }}</td>
                <td>
                    {{ $bonus->worked_days }}</td>
                <td class="text-right">
                    ${{ number_format($bonus->average_amount, 2, ',', '.') }}</td>
                <td class="text-right">
                    ${{ number_format($bonus->amount, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</div>
<div class="text-center">
    {{ Carbon\Carbon::parse(Carbon\Carbon::today())->locale('es_ES')->isoFormat('dddd, D \d\e MMMM YYYY') }}
</div>
<br>
