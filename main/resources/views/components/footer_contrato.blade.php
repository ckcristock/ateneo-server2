<style>
    .table-borderless tbody+tbody,
    .table-borderless td,
    .table-borderless th,
    .table-borderless thead th {
        border: 0;
    }

    .text-left {
        text-align: left !important;
    }
</style>
<table class="table table-borderless" style="margin-top: 30px">
    <thead>
        <tr>
            <th class="text-left"><strong>EL EMPLEADOR</strong></th>
            <th class="text-left"><strong>EL EMPLEADO</strong></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="padding-top: 3rem">
                ____________________________________
            </td>
            <td style="padding-top: 3rem">
                ____________________________________
            </td>
        </tr>
        <tr>
            <td>{{ strtoupper($responsible->person->full_names) }}</td>
            <td>{{ strtoupper($person->person) }}
            </td>
        </tr>
        <tr>
            <td>C.C. {{ number_format($responsible->person->identifier, 0, '', '.') }}</td>
            <td>C.C. {{ number_format($person->identifier, 0, '', '.') }}</td>
        </tr>
    </tbody>
</table>
