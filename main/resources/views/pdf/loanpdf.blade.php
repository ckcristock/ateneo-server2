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

    .titular {
        font-size: 11px;
        text-transform: uppercase;
        margin-bottom: 0;
    }

    .blocks {
        width: 25%;
        display: inline-block;
        text-transform: uppercase;
    }

    .blocks-50 {
        width: 50%;
        display: inline-block;
        text-transform: uppercase;
        font-size: 11px;
    }

    .div {
        width: 100%;
        font-size: 9px;
    }

    table.table-border,
    .table-border th,
    .table-border td {
        border: 1px solid;
    }

    .table-border th,
    .table-border td {
        padding: 5px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background-color: transparent;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center
    }

    .border-top {
        border-top:1px solid black
    }
</style>
@include('components/cabecera', [$company, $datosCabecera, $image])
<div class="div">
    <div class="blocks-50">
        <strong>IDENTIFICACIÓN EMPLEADO:</strong>
        {{ number_format($funcionario->identifier, 0, '', '.') }}
    </div>
    <div class="blocks-50">
        <strong>NOMBRE EMPLEADO:</strong>
        {{ $funcionario->first_name . ' ' . $funcionario->second_name . ' ' . $funcionario->first_surname . ' ' . $funcionario->second_surname }}
    </div>
</div>
<div class="div">
    <div class="blocks-50">
        <strong>VALOR PRÉSTAMOS:</strong>
        ${{ number_format($funcionario->value, 2, ',', '.') }}
    </div>
    <div class="blocks-50">
        <strong>INTERÉS:</strong>
        {{ number_format($funcionario->interest, 2, ',', '.') }}%
    </div>
</div>
<div class="div">
    <div class="blocks-50">
        <strong>CUOTAS:</strong>
        {{ $funcionario->number_fees }}
    </div>
    <div class="blocks-50">
        <strong>VALOR CUOTA:</strong>
        ${{ number_format($funcionario->monthly_fee, 2, ',', '.') }}
    </div>
</div>

<table class="div table-border" cellpadding="0" cellspacing="0">
    <thead>
        <tr style="background:#E1EEC0;">
            <th class="text-center">CUOTA</th>
            <th class="text-center">FECHA DESCUENTO</th>
            <th class="text-center">AMORTIZACIÓN</th>
            <th class="text-center">INTERESES</th>
            <th class="text-center">TOTAL CUOTA</th>
            <th class="text-center">SALDO</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($proyecciones['Proyeccion'] as $i => $value)
            <tr>
                <td class="text-center">
                    {{ $i + 1 }}
                </td>
                <td class="text-center">
                    {{ $value['Fecha'] }}
                </td>
                <td class="text-right">
                    ${{ number_format($value['Amortizacion'], 2, ',', '.') }}
                </td>
                <td class="text-right">
                    ${{ number_format($value['Intereses'], 2, ',', '.') }}
                </td>
                <td class="text-right">
                    ${{ number_format($value['Valor_Cuota'], 2, '.', ',') }}
                </td>
                <td class="text-right">
                    ${{ number_format($value['Saldo'], 2, '.', ',') }}
                </td>
            </tr>
        @endforeach
    </tbody>

    <tr>
        <td colspan="2" class="text-right">
            TOTALES:</td>
        <td class="text-right">
            <?php echo number_format($getTotalA, 2, '.', ','); ?>
        </td>
        <td class="text-right">
            <?php echo number_format($getTotalI, 2, '.', ','); ?>
        </td>
        <td class="text-right">
            <?php echo number_format($getTotalV, 2, '.', ','); ?>
        </td>
        <td class="text-right"></td>
    </tr>
</table>
<h6>Atentamente:</h6>
<table style="margin-top:4rem">
    <tbody>
        <tr>
            <td class="border-top text-center" style="margin-right: 10px">
                {{ $funcionario->first_name . ' ' . $funcionario->first_surname }}
            </td>
            <td></td>
            <td class="border-top text-center">Representante legal</td>
        </tr>
        <tr>
            <td class="text-center">C.C. {{ number_format($funcionario->identifier, 0, ',', '.') }}</td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>
