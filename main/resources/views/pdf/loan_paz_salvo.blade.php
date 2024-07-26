<style>
    .blocks-50 {
        width: 50%;
        display: inline-block;
        text-transform: uppercase;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    .div {
        width: 100%;
        font-size: 15px;
    }
</style>
@include('components/cabecera', [$company, $datosCabecera, $image])
@php
    function fecha($str)
    {
        $parts = explode(' ', $str);
        $date = explode('-', $parts[0]);
        return $date[2] . '/' . $date[1] . '/' . $date[0];
    }

    $dia_solicitud = date('d');
    $mes_solicitud = date('m');
    $anio_solicitud = date('Y');
@endphp
<div class="div">
    <div class="blocks-50">
        <strong>NÚMERO DE IDENTIFICACIÓN:</strong>
        {{ number_format($data['person']['identifier'], 0, '', '.') }}
    </div>
    <div class="blocks-50">
        <strong>NOMBRE:</strong>
        {{ $data['person']['full_names'] }}
    </div>
</div>
<div class="div">
    <div class="blocks-50">
        <strong>VALOR PRÉSTAMO:</strong>
        ${{ number_format($data['value'], 2, ',', '.') }}
    </div>
    <div class="blocks-50">
        <strong>INTERES:</strong>
        {{ number_format($data['interest'], 2, ',', '.') }}%
    </div>
</div>
{{-- <div class="div">
    <div class="blocks-50">
        <strong>CUOTAS PAGADAS:</strong>
        {{ $data['Nro_Cuotas'] }}
    </div>
    <div class="blocks-50">
        <strong>VALOR CUOTA PAGADA:</strong>
        ${{ number_format($data['Cuota_Mensual'], 2, ',', '.') }}
    </div>
</div> --}}
<div class="div">{{ $company['social_reason'] }} se permite certificar que el funcionario
    <strong>{{ $data['person']['full_names'] }}</strong> identificado con C.C.
    {{ number_format($data['person']['identifier'], 0, ',', '.') }} se encuentra a Paz y Salvo por el
    concepto del prestamo <strong>PE0{{ $data['id'] }}</strong> de fecha {{ fecha($data['date']) }}.
</div>
<div class="div">
    La presente certificación se expide en Bogotá, al(los) {{ $diaEnletras }} ({{ $dia_solicitud }}) día(s) del mes de
    {{ $mes }} de {{ $anio_solicitud }} a solicitud del interesado.
</div>
<div class="div mt-4">Atentamente:</div>

<table class="mt-4">
    <tr>
        <td style="border-top:1px solid black;"></td>
    </tr>
    <tr>
        <th>Representante legal</th>
    </tr>
</table>
