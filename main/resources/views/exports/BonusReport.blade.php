<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Primas</title>
</head>

<body>
    <div>
        <table>
            <thead>
                <tr>
                    <th
                        style="font-weight: bold;
                               font-size: 14px;
                               text-align: center;"
                        colspan="6"
                    >RESUMEN DE PRIMAS {{$bonuses->period}}</th>
                </tr>
                <tr>
                    <th style="font-weight: bold;text-align: center;">#</th>
                    <th style="font-weight: bold;text-align: center;">Documento</th>
                    <th style="font-weight: bold;text-align: center;">Persona</th>
                    <th style="font-weight: bold;text-align: center;">Salario</th>
                    <th style="font-weight: bold;text-align: center;">Días Trabajados</th>
                    <th style="font-weight: bold;text-align: center;">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bonuses->bonusPerson as $key=>$bonus)
                <tr>
                    <td>{{$key + 1 }}</td>
                    <td>{{$bonus->identifier}}</td>
                    <td>{{$bonus->fullname }}</td>
                    <td>{{$bonus->average_amount}}</td>
                    <td>{{$bonus->worked_days }}</td>
                    <td>{{$bonus->amount}}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="5" style="text-align: right;"><B>TOTAL</B></td>
                    <td>=SUM(F3:F{{$bonuses->total_employees+2}})</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
