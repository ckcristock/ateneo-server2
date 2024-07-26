@php
    $total=0;
@endphp
<html>

<head>
    <style>
        .border {
            border: 3px solid black
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th rowspan="3" colspan="3" style="border: 3px solid black; padding: 6px">
                    {{-- <img src="{{URL::to('people/wSZfAQhloRdcIgYJw0dGVvPz8yScC51666796122.png')}}"> --}}
                </th>
                <th rowspan="3" colspan="11" style="text-align: center; font-weight: 600; border: 3px solid black">
                    {{ $company->name }} <br>
                    PROCESO: LABORATORIO CLINICO-PREANALITICO <br>
                    FORMATO DE REMISION DE MUESTRAS A ALIFE HEALTH S.A.S
                </th>
                <th colspan="5" style="width: 180px; font-weight: 600; border: 3px solid black">CODIGO:
                </th>
            </tr>
            <tr>
                <th colspan="5" style="width: 180px; font-weight: 600; border: 3px solid black">VERSION: 1</th>
            </tr>
            <tr>
                <th colspan="5" style="width: 180px; font-weight: 600; border: 3px solid black">VIGENCIA:
                </th>
            </tr>
            <tr>
                <th colspan="3" style="border: 3px solid black;">FECHA DE REMISION: </th>
                <th style="width: 277px; text-align: center; border: 3px solid black;">
                        {{date("d/m/Y")}}
                </th>
                <th colspan="3" style="border: 3px solid black;">CORREO ELECTRONICO:</th>
                <th colspan="12" style="text-align: center; border: 3px solid black;">{{$company->email_contact}}</th>
            </tr>
            <tr>
                <th colspan="3" style="border: 3px solid black;">INSTITUCION QUE REMITE: </th>
                <th style="width: 277px; text-align: center; border: 3px solid black;">{{$company->short_name}}</th>
                <th colspan="3" style="border: 3px solid black;">TELEFONO CONTACTO: </th>
                <th colspan="12" style="text-align: center; border: 3px solid black;">{{$company->phone}}</th>
            </tr>
            <tr>
                <th colspan="3" style="border: 3px solid black;">CODIGO CLIENTE: </th>
                <th style="width: 277px; text-align: center; border: 3px solid black;">{{number_format($company->tin)}}-{{$company->dv}}</th>
                <th colspan="3" style="border: 3px solid black;">CIUDAD:</th>
                <th colspan="12" style="text-align: center; border: 3px solid black;">SOACHA</th>
            </tr>
            <tr>
                <th rowspan="2"
                    style="width: 34px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    No.</th>
                <th rowspan="2"
                    style="width: 87px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    TIPO DE DOC</th>
                <th rowspan="2"
                    style="width: 140px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    NUMERO DE<br>DOCUMENTO</th>
                <th rowspan="2"
                    style="width: 277px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    APELLIDOS Y NOMBRES</th>
                <th rowspan="2"
                    style="width: 91px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    FECHA DE<br>NACIMIENTO</th>
                <th colspan="2"
                    style="font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    SEXO</th>
                <th rowspan="2"
                    style="width: 230px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    EXAMENES A PROCESAR</th>
                <th colspan="6"
                    style="font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    *TIPO DE MUESTRA</th>
                <th rowspan="2"
                    style="width: 71px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    TOTAL<br>MUESTRAS</th>
                <th colspan="3"
                    style="font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    *TEMPERATURA</th>
                <th rowspan="2"
                    style="width: 159px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    OBSERVACIONES (EPS)</th>
            </tr>
            <tr>
                <th
                    style="width: 40px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    F</th>
                <th
                    style="width: 40px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    M</th>
                <th
                    style="width: 20px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    S</th>
                <th
                    style="width: 20px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    P</th>
                <th
                    style="width: 20px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    ST</th>
                <th
                    style="width: 20px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    O</th>
                <th
                    style="width: 22px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    MF</th>
                <th
                    style="width: 29px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    OTR</th>
                <th
                    style="width: 33px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    R</th>
                <th
                    style="width: 33px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    C</th>
                <th
                    style="width: 33px; font-weight: 600; vertical-align: center; text-align: center; background-color: #D6DCE4; border: 3px solid black;">
                    A</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($laboratories as $key=>$laboratory)
                @php
                    $total = $total + $laboratory->examenes;
                @endphp
                <tr>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black;"> {{ $key + 1 }} </td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black;"> {{ $laboratory->code }} </td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black;"> {{ $laboratory->identifier }} </td>
                    <td style="vertical-align: center; border: 3px solid black;"> {{ $laboratory->full_name }} </td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black;"> {{ $laboratory->date_of_birth }} </td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black;"> {{ $laboratory->gener == 'F' ? 'X' : '' }} </td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black;"> {{ $laboratory->gener == 'M' ? 'X' : '' }} </td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black; width: 230px; word-wrap: break-word;"> {{ $laboratory->cups }} </td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black"></td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black"></td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black"></td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black"></td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black"></td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black"></td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black"> {{ $laboratory->examenes }} </td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black"></td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black"></td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black"></td>
                    <td style="vertical-align: center; text-align: center; border: 3px solid black"> {{ $laboratory->name }} </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3" rowspan="2" style="vertical-align: center; text-align: center; border: 3px solid black; font-weight: 600">Total de muestras enviadas</td>
                <td colspan="4" rowspan="2" style="vertical-align: center; text-align: center; border: 3px solid black; font-weight: 600">{{$total}}</td>
                <td colspan="12" rowspan="2" style="vertical-align: center; border: 3px solid black; font-weight: 600">Nombre completo de quien recibe las muestras: </td>
            </tr>
            <tr>

            </tr>
            <tr>
                <td colspan="100%" style="vertical-align: center; border: 3px solid black; font-weight: 600">Remisión realizada por: {{$person->first_name}} {{$person->second_name}} {{$person->first_surname}}  {{$person->second_surname}}</td>
            </tr>
            <tr>
                <td colspan="100%" style="vertical-align: center; border: 3px solid black">
                    *Tipo de muestra: S: Suero, P: Plasma, ST: Sangre total, O: Orina, MF: Materia fecal, OTR: Otras muestras         *Temperatura: R: Refrigeración, C: Congelada, A: Ambiente
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
