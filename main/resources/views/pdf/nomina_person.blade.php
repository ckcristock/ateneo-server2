@include('components.cabecera_dos')
<div class="mx-4">
    <div style="height: 70px">
        <table class="w-100 uppercase">
            <tbody class="text-left">
                <tr>
                    <th>Funcionario</th>
                    <td>{{ $info['name'] }} {{ $info['surname'] }}</td>
                    <th>Salario</th>
                    <td> $ {{ number_format($info['salario_neto'], 2, ',', '.') }} </td>

                </tr>
                <tr>
                    <th>Documento</th>
                    <td>{{ number_format($info['identifier'], 0, ',', '.') }}</td>
                    <th>Frecuencia pago</th>
                    <td>{{ $data['frecuencia_pago'] }}</td>

                </tr>
                <tr>
                    <th>Cargo</th>
                    <td>{{ $info['position'] }}</td>
                    <th>Días Laborados</th>
                    <td>{{ $info['worked_days'] }}</td>

                </tr>
                <tr>
                    <th>Fecha de Ingreso</th>
                    <td>
                        {{ Carbon\Carbon::parse($info['date_of_admission'])->locale('es_ES')->isoFormat('dddd, D \d\e MMMM YYYY') }}
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
    </div>
    <div>
        <table class="w-100 uppercase table-border">
            <thead class="bg">
                <tr>
                    <th colspan="2">Resumen del Pago</th>
                </tr>
                <tr>
                    <th>Concepto</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b>Salario neto</b></td>
                    <td class="text-right">$ {{ number_format($info['basic_salary'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><b>Subsidio de Transporte</b></td>
                    <td class="text-right">$ {{ number_format($info['transportation_assitance'], 2, ',', '.') }}</td>
                </tr>
                @if (false)
                    // hay_vacaciones
                    <tr>
                        <td><b>Vacaciones</b></td>
                        <td class="text-right">$ {{ number_format($info['transportation_assitance'], 2, ',', '.') }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td><b>Retenciones y Deducciones</b></td>
                    <td class="text-right">$
                        {{ number_format(optional($info['deducciones'])['valor_total'] + optional($info['retencion'])['valor_total'], 2, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td><b>Total neto a pagar al empleado</b></td>
                    <td class="text-right">$ {{ number_format($info['salario_neto'], 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="uppercase text-justify">
        <b>
            <br>
            Nota: Lo expuesto en este comprobante representa el pago {{ $data['frecuencia_pago'] }} del empleado, y en
            este
            se
            listan el salario neto, deducciones e ingresos adicionales y su firma representa su entera satisfacción.
            <br>
            <br>
        </b>
    </div>

    <div>
        <table class="w-100">
            <tbody class="text-center">
                <tr style="color:transparent">
                    <td> FIRMA </td>
                    <td>
                        @if ($viewImage)
                            <img src="{{ asset('main/public/images/firma.svg') }}">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>________________________________</td>
                    <td>________________________________</td>
                </tr>
                <tr>
                    <td class="uppercase"><b>
                            {{ $info['name'] }} {{ $info['surname'] }} <br>
                            {{ $info['position'] }}</b>
                    </td>
                    <td class="uppercase"><b> {{ $responsableRhName }}
                            <br>{{ $company->name }}</b>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</section>
