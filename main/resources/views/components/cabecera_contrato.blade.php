<style>
    @page {
        margin: 20px;
    }

    * {
        font-family: 'Times New Roman','Montserrat', sans-serif;
        font-size: 0.98em;
    }

    body,
    html {
        margin: 0;
        width: 100vw;
        height: 100vh;
        padding: 0;
        background-repeat: no-repeat;
        background-image: url({{ $person->work_contract->company->page_heading }});
        background-position: center;
        opacity: 0.1;
    }

    body {
        margin-top: 1rem;
        opacity: 1;
    }

    .table {
    border-collapse: collapse;

}

.table-bordered {
    border: 2px double black;
}


.table-bordered td,
.table-bordered th {
    border: 1px solid black;
    padding: 0.2rem;
}

.table thead th {
    border-bottom: 2px solid #000;
}

.center-table {
    margin-left: auto;
    margin-right: auto;
}

</style>

<table class="table table-bordered center-table" style="margin-top: 5px; margin-bottom: 15px; text-transform: uppercase;">
    <tbody>
        <tr>
            <td colspan="2" style="text-align: center">
                <strong><em>EL EMPLEADOR</em></strong>
            </td>
        </tr>
        <tr>
            <td style="width: 350px">El Empleador:</td>
            <td>{{ $person->work_contract->company->social_reason ?? $person->work_contract->company->name }}</td>
        </tr>
        <tr>
            <td>NIT:</td>
            <td>{{ number_format($person->work_contract->company->tin, 0, '', '.') . '-' . $person->work_contract->company->dv }}
            </td>
        </tr>
        <tr>
            <td>Dirección Principal:</td>
            <td>{{ $person->work_contract->company->address }}</td>
        </tr>
        <tr>
            <td>Ciudad:</td>
            <td>{{ $person->work_contract->company->cityCompany->name }}</td>
        </tr>
        <tr>
            <td>Representante Legal:</td>
            <td>{{ $responsible->person->full_names ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Cédula:</td>
            <td>{{ number_format($responsible->person->identifier, 0, '', '.') ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="text-align: center" colspan="2">
                <strong><em>EL TRABAJADOR</em></strong>
            </td>
        </tr>
        <tr>
            <td>El Trabajador:</td>
            <td>{{ $person->person }}</td>
        </tr>
        <tr>
            <td>Cédula:</td>
            <td>{{ number_format($person->identifier, 0, '', '.') }}</td>
        </tr>
        <tr>
            <td>Dirección:</td>
            <td>{{ $person->direction ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Ciudad:</td>
            <td>{{ $person->place_of_birth ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Teléfono:</td>
            <td>{{ $person->cell_phone ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Correo electrónico:</td>
            <td>{{ $person->email ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="2" valign="bottom" style="text-align: center">
                <strong><em>DEL CONTRATO</em></strong>
            </td>
        </tr>
        <tr>
            <td>Cargo contratado:</td>
            <td valign="bottom">{{ $person->contractultimate->position->name }}</td>
        </tr>
        <tr>
            <td>Salario mensual:</td>
            <td valign="bottom">
                ${{ number_format($person->contractultimate->salary, 0, '', '.') . ' (' . $construct_dates->enLetras . ' MCTE)' }}
            </td>
        </tr>
        <tr>
            <td>Forma de pago:</td>
            <td valign="bottom">{{ $person->work_contract->company->payment_frequency }}</td>
        </tr>
        <tr>
            <td>Fecha de Inicio:</td>
            <td valign="bottom">{{ $construct_dates->fechaInicio }}</td>
        </tr>
        @if ($person->contractultimate->work_contract_type->name == 'Fijo')
            <tr>
                <td>Duración:</td>
                <td valign="bottom">{{ $construct_dates->diferencia->format('%m') }} meses</td>
            </tr>
            <tr>
                <td>Fecha de Terminación:</td>
                <td valign="bottom">{{ $construct_dates->fechaFin }}</td>
            </tr>
        @endif

        @if ($person->contractultimate->work_contract_type->name == 'Obra/Labor')
            <tr>
                <td>Fecha de Terminación:</td>
                <td valign="bottom">A LA TERMINACIÓN TOTAL O PARCIAL DE LA OBRA O LABOR
                    CONTRATADA</td>
            </tr>
            <tr>
                <td>Fecha de Suscripción:</td>
                <td>{{ $construct_dates->fechaCreado }}</td>
            </tr>
        @endif
        @if (
            $person->contractultimate->work_contract_type->name == 'Fijo' ||
                $person->contractultimate->work_contract_type->name == 'Indefinido')
            <tr>
                <td>PERIODO DE PRUEBA:</td>
                <td valign="bottom">{{ $construct_dates->prueba }} DIAS</td>
            </tr>
            <tr>
                <td>FECHA DE PERIODO DE PRUEBA:</td>
                <td valign="bottom">
                    {{ $construct_dates->prueba . ' DIAS (' . $construct_dates->fechaInicio . ' AL ' . $construct_dates->fechaFinPrueba . ')' }}
                </td>
            </tr>
            <tr>
                <td>Lugar de la prestación del servicio:</td>
                <td valign="bottom">N/A</td>
            </tr>
        @endif
        @if (
            $person->contractultimate->work_contract_type->name == 'Obra/Labor' ||
                $person->contractultimate->work_contract_type->name == 'Indefinido')
            <tr>
                <td>Auxilio de transporte:</td>
                <td valign="bottom">$
                    {{ number_format($person->work_contract->company->transportation_assistance, 0, '', '.') }}
                </td>
            </tr>
        @endif
    </tbody>
</table>
