@include('components/cabecera', [$datosCabecera])
<style>
    .firma {
        font-size: 16px;
        background-color: transparent;

    }
</style>
<h4>Señor(a)</h4>
<div>{{ $data->person->full_names }}</div>
<div>{{ $data->person->contractultimate->position->name }}</div>
<small>{{ $data->person->documentType->code }}: {{ $data->person->identifier }}</small>
<h5><b>ASUNTO: LLAMADO DE ATENCIÓN</b></h5>
<p>{{ $data->details }}</p>
<p><b>Atentamente, {{ $data->user->person->full_names }}.</b></p>
<table style="margin-top: 150px">
    <tr>
        <td class="firma">
            <div>____________________________</div>
            <div>{{ $data->person->full_names }}</div>
        </td>
        <td class="firma" style="padding-left: 30px;">
            <div>____________________________</div>
            <div>{{ $data->user->person->full_names }}</div>
        </td>
    </tr>
</table>
