@include('components/cabecera', [$datosCabecera])
<h4>{{ $data->title }}</h4>
<h5>{{ $person }},</h5>
<div>
    {!! $content !!}
</div>
