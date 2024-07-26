<table>
    <thead>
        <tr class="title">
            <th>NOMBRE</th>
            <th>IDENTIFICACIÓN</th>
            <th>SEXO</th>
            <th>FECHA DE NACIMIENTO</th>
            <th>LUGAR DE NACIMIENTO</th>
            <th>TIPO DE SANGRE</th>
            <th>TELÉFONO</th>
            <th>CELULAR</th>
            <th>EMAIL</th>
            <th>DIRECCIÓN</th>
            <th>ESTADO CIVIL</th>
            <th>NIVEL ACADÉMICO</th>
            <th>TÍTULO</th>
            <th>TALLA PANTALÓN</th>
            <th>TALLA BOTAS</th>
            <th>TALLA CAMISA</th>
            <th>EPS</th>
            <th>FONDO DE PENSIONES</th>
            <th>FONDO DE CESANTÍAS</th>
            <th>CAJA DE COMPENSACIÓN</th>
            <th>ARL</th>
            <th>TIPO DE TURNO</th>
            <th>TIPO DE CONTRATO</th>
            <th>TÉRMINO DE CONTRATO</th>
            <th>DEPENDENCIA</th>
            <th>CARGO</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($people as $key => $person)
            <tr>
                <td> {{ strtoupper($person->full_names) }} </td>
                <td>{{ $person->identifier }}</td>
                <td>{{ $person->gener }}</td>
                <td>{{ $person->birth_date }}</td>
                <td>{{ strtoupper($person->place_of_birth) }}</td>
                <td>{{ $person->blood_type }}</td>
                <td>{{ $person->phone }}</td>
                <td>{{ $person->cell_phone }}</td>
                <td>{{ $person->email }}</td>
                <td>{{ strtoupper($person->address) }}</td>
                <td>{{ $person->marital_status }}</td>
                <td>{{ $person->degree_instruction }}</td>
                <td>{{ strtoupper($person->title) }}</td>
                <td>{{ $person->pants_size }}</td>
                <td>{{ $person->shue_size }}</td>
                <td>{{ $person->shirt_size }}</td>
                <td>{{ optional($person->eps)->name }}</td>
                <td>{{ optional($person->pension_funds)->name }}</td>
                <td>{{ optional($person->severance_fund)->name }}</td>
                <td>{{ optional($person->compensation_fund)->name }}</td>
                <td>{{ optional($person->arl)->name }}</td>
                <td>{{ optional($person->contractultimate)->turn_type }}</td>
                <td>{{ optional(optional($person->contractultimate)->contract_term)->name }}</td>
                <td>{{ optional(optional($person->contractultimate)->work_contract_type)->name }}</td>
                <td>{{ optional(optional(optional($person->contractultimate)->position)->dependency)->name }}</td>
                <td>{{ optional(optional($person->contractultimate)->position)->name }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
