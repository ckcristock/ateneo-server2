@include('components/cabecera', [$datosCabecera])

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liquidación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            margin: 20px;
        }

        .h1tex {
            text-align: center;
            font-size: 18px;
        }

        .tabled {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .tabled th,
        .tabled td {
            border: 1px solid #dddddd;
            padding: 8px;
        }

        th,
        td {

            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
            width: 55%;
        }

        td {
            width: 45%;
            font-size: 14px;
        }

        table+h1 {
            margin-top: 0;
        }

        h1:last-child {
            margin-top: 0;
        }

        .firma {
            font-size: 16px;
            background-color: transparent;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="h1tex">INFORMACIÓN PERSONAL</h1>
        <table class="tabled">
            <tr>
                <th>EMPLEADO</th>
                <td>{{ $data->person->full_names }}</td>
            </tr>
            <tr>
                <th>IDENTIFICACIÓN</th>
                <td>{{ $data->person->identifier }}</td>
            </tr>

        </table>

        <h1 class="h1tex">DETALLES DEL CONTRATO</h1>
        <table class="tabled">
            <tr>
                <th>MOTIVO</th>
                <td>{{ $data->motivo }}</td>
            </tr>
            <tr>
                <th>JUSTA CAUSA</th>
                <td>{{ $data->justa_causa }}</td>
            </tr>
            <tr>
                <th>FECHA DE CONTRATACIÓN</th>
                <td>{{ $data->fecha_contratacion }}</td>
            </tr>
            <tr>
                <th>FECHA DE TERMINACIÓN</th>
                <td>{{ $data->fecha_terminacion }}</td>
            </tr>
            <tr>
                <th>DÍAS LIQUIDADOS</th>
                <td>{{ $data->dias_liquidados }}</td>
            </tr>
        </table>

        <h1 class="h1tex">INFORMACIÓN FINANCIERA</h1>
        <table class="tabled">
            <tr>
                <th>DÍAS DE VACACIONES</th>
                <td>{{ $data->dias_vacaciones != null ? '' . number_format($data->dias_vacaciones) : '0' }}</td>
            </tr>
            <tr>
                <th>SALARIO BASE</th>
                <td>{{ $data->salario_base != null ? '$ ' . number_format($data->salario_base) : '$ 0' }}</td>
            </tr>
            <tr>
                <th>VACACIONES BASE</th>
                <td>{{ $data->vacaciones_base != null ? '$ ' . number_format($data->vacaciones_base) : '$ 0' }}</td>
            </tr>
            <tr>
                <th>CESANTÍAS BASE</th>
                <td>{{ $data->cesantias_base != null ? '$ ' . number_format($data->cesantias_base) : '$ 0' }}</td>
            </tr>
            <tr>
                <th>¿SE INCLUYEN DOMINICALES?</th>
                <td>{{ $data->dominicales_incluidas ? 'Sí' : 'No' }}</td>
            </tr>
            <tr>
                <th>CESANTÍAS ANTERIORES</th>
                <td>{{ $data->cesantias_anterior != null ? '$ ' . number_format($data->cesantias_anterior) : '$ 0' }}
                </td>
            </tr>
            <tr>
                <th>INTERESES SOBRE CESANTÍAS</th>
                <td>{{ $data->intereses_cesantias != null ? '$ ' . number_format($data->intereses_cesantias) : '$ 0' }}
                </td>
            </tr>
            <tr>
                <th>OTROS INGRESOS</th>
                <td>{{ $data->otros_ingresos != null ? '$ ' . number_format($data->otros_ingresos) : '$ 0' }}</td>
            </tr>
            <tr>
                <th>PRESTAMOS</th>
                <td>{{ $data->prestamos != null ? '$ ' . number_format($data->prestamos) : '$ 0' }}</td>
            </tr>
            <tr>
                <th>OTRAS DEDUCCIONES</th>
                <td>{{ $data->otras_deducciones != null ? '$ ' . number_format($data->otras_deducciones) : '$ 0' }}
                </td>
            </tr>

        </table>

        <h1 class="h1tex">INFORMACIÓN ADICIONAL</h1>
        <table class="tabled">
            <tr>
                <th>NOTAS</th>
                <td>{{ $data->notas }}</td>
            </tr>
        </table>

        <h1 class="h1tex">CÁLCULOS Y TOTALES</h1>
        <table class="tabled">
            <tr>
                <th>VALOR DE LOS DÍAS DE VACACIONES</th>
                <td>{{ $data->valor_dias_vacaciones != null ? '$ ' . number_format($data->valor_dias_vacaciones) : '$ 0' }}
                </td>
            </tr>
            <tr>
                <th>VALOR DE LAS CESANTÍAS</th>
                <td>{{ $data->valor_cesantias != null ? '$ ' . number_format($data->valor_cesantias) : '$ 0' }}</td>
            </tr>
            <tr>
                <th>VALOR DE LA PRIMA</th>
                <td>{{ $data->valor_prima != null ? '$ ' . number_format($data->valor_prima) : '$ 0' }}</td>
            </tr>
            <tr>
                <th>SUELDO PENDIENTE</th>
                <td>{{ $data->sueldo_pendiente != null ? '$ ' . number_format($data->sueldo_pendiente) : '$ 0' }}</td>
            </tr>
            <tr>
                <th>AUXILIO PENDIENTE</th>
                <td>{{ $data->auxilio_pendiente != null ? '$ ' . number_format($data->auxilio_pendiente) : '$ 0' }}
                </td>
            </tr>
            <tr>
                <th>OTROS</th>
                <td>{{ $data->otros != null ? '$ ' . number_format($data->otros) : '$ 0' }}</td>
            </tr>
            <tr>
                <th>SALUD</th>
                <td>{{ $data->salud != null ? '$ ' . number_format($data->salud) : '$ 0' }}</td>
            </tr>
            <tr>
                <th>PENSIÓN</th>
                <td>{{ $data->pension != null ? '$ ' . number_format($data->pension) : '$ 0' }}</td>
            </tr>
            <tr>
                <th>TOTAL</th>
                <td>{{ $data->total != null ? '$ ' . number_format($data->total) : '$ 0' }}</td>
            </tr>

        </table>
    </div>

    </table>

    <table style="margin-top: 100px; margin-left:15px">
        <tr>
            <td class="firma">
                <p style="margin-bottom: 50px;">Empleado:</p>
                <div>____________________________</div>
                <div>{{ $data->person->fullName }}</div>
                <div>{{ $data->person->documentType->code }}: {{ $data->person->identifier }}</div>

            </td>
            <td class="firma" style="padding-left: 30px;">
                <p style="margin-bottom: 50px;">Responsable Recursos Humanos:</p>
                <div>____________________________</div>
                <div>{{ $responsableRhName }}</div>
                <br></b>
            </td>
        </tr>
    </table>

</body>

</html>
