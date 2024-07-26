@php
    function fecha($str)
    {
        $parts = explode(' ', $str);
        $date = explode('-', $parts[0]);
        return $date[2] . '/' . $date[1] . '/' . $date[0];
    }
@endphp
<table>
    <tr>
        <td rowspan="3" style="text-align:center"></td>
        <td colspan="2">{{ $company['social_reason'] }}</td>
        <td rowspan="3" colspan="3" style="text-align:center;font-weight:bold">
            <strong>PROYECCIÓN AMORTIZACIÓN</strong> <br>
            <strong>{{ fecha($loan['date']) }}</strong>
        </td>
    </tr>
    <tr>
        <td colspan="2">NIT: {{ $company['document_number'] }}</td>
    </tr>
    <tr>
        <td colspan="2">TEL: {{ $company['phone'] }}</td>
    </tr>
</table>

<table>
    <tr>
        <td><strong>NÚMERO DE IDENTIFICACIÓN</strong></td>
        <td style="text-align:right;">{{ number_format($loan['person']['identifier'], 0, ',', '.') }}</td>
        <td></td>
        <td></td>
        <td><strong>NOMBRE</strong></td>
        <td style="text-align:right;">{{ $loan['person']['full_names'] }}</td>
    </tr>
    <tr>
        <td><strong>VALOR PRÉSTAMO</strong></td>
        <td style="text-align:right;">{{ number_format($loan['value'], 2, ',', '.') }}</td>
        <td></td>
        <td></td>
        <td><strong>INTERES</strong></td>
        <td style="text-align:right;">{{ number_format($loan['interest'], 2, ',', '.') }}%</td>
    </tr>
    <tr>
        <td><strong>CUOTAS</strong></td>
        <td style="text-align:right;">{{ $loan['number_fees'] }}</td>
        <td></td>
        <td></td>
        <td><strong>VALOR CUOTAS</strong></td>
        <td style="text-align:right;">{{ number_format($loan['monthly_fee'], 2, ',', '.') }}</td>
    </tr>
</table>
<table>
    <tr>
        <td colspan="6" bgcolor="#CCCCCC" align="center" style="border: 3px solid black;"><strong> TABLA PROYECCIÓN
                AMORTIZACIÓN</strong></td>
    </tr>
    <tr >
        <th bgcolor="#E1EEC0" align="center" style="border: 3px solid black;">CUOTA</th>
        <th bgcolor="#E1EEC0" align="center" style="border: 3px solid black;">FECHA DESCUENTO</th>
        <th bgcolor="#E1EEC0" align="center" style="border: 3px solid black;">AMORTIZACIÓN</th>
        <th bgcolor="#E1EEC0" align="center" style="border: 3px solid black;">INTERESES</th>
        <th bgcolor="#E1EEC0" align="center" style="border: 3px solid black;">TOTAL CUOTA</th>
        <th bgcolor="#E1EEC0" align="center" style="border: 3px solid black;">SALDO</th>
    </tr>
    @foreach ($proyecciones['Proyeccion'] as $i => $value)
        <tr>
            <td align="center" style="border: 3px solid black;">{{ $i + 1 }}</td>
            <td align="center" style="border: 3px solid black;">{{ $value['Fecha'] }}</td>
            <td align="right" style="border: 3px solid black;">{{ number_format($value['Amortizacion'], 2, ',', '.') }}</td>
            <td align="right" style="border: 3px solid black;">{{ number_format($value['Intereses'], 2, ',', '.') }}</td>
            <td align="right" style="border: 3px solid black;">{{ number_format($value['Valor_Cuota'], 2, ',', '.') }}</td>
            <td align="right" style="border: 3px solid black;">{{ number_format($value['Saldo'], 2, ',', '.') }}</td>
        </tr>
    @endforeach
    <tr>
        <td align="right" colspan="2" style="border: 3px solid black;"><strong>TOTALES:</strong></td>
        <td align="right" style="border: 3px solid black;"><strong>{{ number_format($getTotalA, 2, ',', '.') }}</strong></td>
        <td align="right" style="border: 3px solid black;"><strong>{{ number_format($getTotalI, 2, ',', '.') }}</strong></td>
        <td align="right" style="border: 3px solid black;"><strong>{{ number_format($getTotalV, 2, ',', '.') }}</strong></td>
        <td align="right" style="border: 3px solid black;"><strong></strong></td>
    </tr>
</table>
