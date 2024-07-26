@include('components/cabecera', [$datosCabecera])
<style>
    .firma {
        font-size: 16px;
        background-color: transparent;

    }
</style>
<h4>Señor(a)</h4>
<div>{{ $dataMemo->person->full_names }}</div>
<div>{{ $dataMemo->person->contractultimate->position->name }}</div>
<small>{{ $dataMemo->person->documentType->code }}: {{ $dataMemo->person->identifier }}</small>
<h5><b>ASUNTO: MEMORANDO</b></h5>
<p>{{ $dataMemo->details }}</p>
<p><b>Atentamente, {{ $dataMemo->approveUser->person->full_names }}.</b></p>

<table style="margin-top: 150px">
    <tr>
        <td class="firma">
            <div>____________________________</div>
            <div>{{ $dataMemo->person->full_names }}</div>
        </td>
        <td class="firma" style="padding-left: 30px;">
            <div>____________________________</div>
            <div>{{ $dataMemo->approveUser->person->full_names }}</div>
        </td>
    </tr>
</table>


<div style="page-break-after: always;"></div>

@foreach ($dataMemo->attentionCalls as $data)
    @include('pdf.attention_call', [$data, 'datosCabecera' => $data->datosCabecera])
    <div style="page-break-after: always;"></div>
@endforeach