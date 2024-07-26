<table>
    <thead style="border-collapse: collapse;border:1px solid #000">
        <tr>
            <th>Codigo</th>
            <th>Nombre</th>
            <th>Empresa</th>
            <th>Centro Padre</th>
            <th>Tipo Centro</th>
            <th>Asignado a</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($centro as $key => $value)
            <tr>
                <td>{{$value->Codigo}}</td>
                <td>{{$value->Centro_Costo}}</td>
                <td>{{$value->Empresa}}</td>
                <td>{{$value->Centro_Padre ? $value->Centro_Padre : 'Sin padre'}}</td>
                <td>{{$value->Tipo_Centro ? $value->Tipo_Centro : 'Sin asignar'}}</td>
                <td>{{$value->Asignado_A ? $value->Asignado_A : 'Sin asignar' }}</td>
                <td>{{$value->Estado}}</td>
            </tr>
        @endforeach
    </tbody>
</table>
