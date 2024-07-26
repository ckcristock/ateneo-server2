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
                    <th colspan="2"> Mensualidad: </th>
                    <th>{{$novedades['ini_date']}}</th>
                    <th> al</th>
                    <th>{{$novedades['end_date']}}</th>
                </tr>
                <tr>
                    <th style="font-weight: bold;text-align: center;">Item</th>
                    <th style="font-weight: bold;text-align: center;">Funcionario</th>
                    <th style="font-weight: bold;text-align: center;">Documento</th>
                    <th style="font-weight: bold;text-align: center;">Tipo Novedad</th>
                    <th style="font-weight: bold;text-align: center;">Novedad</th>
                    <th style="font-weight: bold;text-align: center;">Fecha Inicio</th>
                    <th style="font-weight: bold;text-align: center;">Fecha Fin</th>
                    <th style="font-weight: bold;text-align: center;">Tiempo Novedad</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($novedades['novedades'] as $key => $func)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $func['first_name'] }} {{ $func['first_surname'] }}</td>
                        <td>{{ $func['identifier'] }}</td>
                        <td>{{ $func['concept'] }}</td>
                        <td>{{ $func['observation'] }}</td>
                        <td>{{ $func['date_start'] }}</td>
                        <td>{{ $func['date_end']}}</td>
                        <td>{{ $func['number_days'] }}</td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</body>

</html>
