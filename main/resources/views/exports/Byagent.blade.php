<table>
    <thead>
        <tr>
            <th> ID Llamada </th>
            <th> ID paciente </th>
            <th> ID agente </th>
            <th> Tipo tramite </th>
            <th> Tipo Servicio </th>
            <th> Ambito </th>
            <th> Fecha Llamada </th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $datum)
        <tr>
            <td> {{ $datum->Id_Llamada }} </td>
            <td> {{ $datum->Identificacion_Paciente }} </td>
            <td> {{ $datum->Identificacion_Agente }} </td>
            <td> {{ $datum->Tipo_Tramite }} </td>
            <td> {{ $datum->Tipo_Servicio }} </td>
            <td> {{ $datum->Ambito }} </td>
            <td> {{ $datum->Fecha_Llamada }} </td>
        </tr>
        @endforeach
    </tbody>
</table>