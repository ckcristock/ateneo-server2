<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Nomina</title>
</head>

<body>
    <div>
        <table>
            <thead>
                <tr>
                    <th colspan="2">{{ $nomina['frecuencia_pago'] }}</th>
                    <th>{{ \Carbon\Carbon::parse(strtotime($nomina['inicio_periodo']))->toFormattedDateString()}} al</th>
                    <th>{{ \Carbon\Carbon::parse(strtotime($nomina['fin_periodo']))->toFormattedDateString()}}</th>
                </tr>
                <tr>
                    <th style="font-weight: bold;text-align: center;">Item</th>
                    <th style="font-weight: bold;text-align: center;">Nombre Empleado</th>
                    <th style="font-weight: bold;text-align: center;">C. C.</th>
                    <th style="font-weight: bold;text-align: center;">Ciudad</th>
                    <th style="font-weight: bold;text-align: center;">Cargo</th>
                    <th style="font-weight: bold;text-align: center;">Sueldo Básico</th>
                    <th style="font-weight: bold;text-align: center;">Días Trabajados</th>
                    <th style="font-weight: bold;text-align: center;">Pago días trabajados</th>
                    <th style="font-weight: bold;text-align: center;">Auxilio No Salarial</th>
                    <th style="font-weight: bold;text-align: center;">Auxilio Transporte</th>
                    <th style="font-weight: bold;text-align: center;">Días Vacaciones</th>
                    <th style="font-weight: bold;text-align: center;">Total Vacaciones</th>
                    <th style="font-weight: bold;text-align: center;">Días Incapacidades</th>
                    <th style="font-weight: bold;text-align: center;">Total Incapacidades</th>
                    <th style="font-weight: bold;text-align: center;">Días Licencias</th>
                    <th style="font-weight: bold;text-align: center;">Total Licencias</th>
                    <th style="font-weight: bold;text-align: center;">Salud</th>
                    <th style="font-weight: bold;text-align: center;">Pensión</th>
                    <th style="font-weight: bold;text-align: center;">Préstamos</th>
                    <th style="font-weight: bold;text-align: center;">Libranzas, o Sanciones</th>
                    <th style="font-weight: bold;text-align: center;">Neto a Cancelar</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($nomina['funcionarios'] as $key => $func)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $func['name'] }} {{ $func['surname'] }}</td>
                        <td>{{ $func['identifier'] }}</td>
                        <td>{{ $func['city'] }}</td>
                        <td>{{ $func['position'] }}</td>
                        <td>{{ $func['basic_salary'] }}</td>
                        <td>{{ $func['worked_days'] }}</td>
                        <td>=(H{{$key+3}}/30)*I{{$key+3}}</td>
                        <td>{{ $func['ingresos_contitutivos'] }}</td>
                        <td>{{ $func['transportation_assitance'] }}</td>
                        <td>{{ $func['novedades']['novedades']['Vacaciones'] ?? 0}}</td>
                        <td>{{ $func['novedades']['novedades_totales']['Vacaciones'] ?? 0}}</td>
                        <td> {{ $func['novedades']['novedades']['Incapacidad laboral'] ?? 0}}</td>
                        <td> {{ $func['novedades']['novedades_totales']['Incapacidad laboral'] ?? 0}}</td>
                        <td> {{ $func['dias_licencia'] ?? 0 }} </td>
                        <td> {{ $func['total_licencia'] ?? 0 }} </td>
                        <td>{{ $func['retencion']['total_retenciones']['Salud'] }}</td>
                        <td>{{ $func['retencion']['total_retenciones']['Pensión'] }}</td>
                        <td>{{ $func['prestamos'] ?? 0 }}</td>
                        <td>{{ $func['libranzas'] ?? 0 }}</td>
                        <td>{{ $func['Salario_nomina'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
