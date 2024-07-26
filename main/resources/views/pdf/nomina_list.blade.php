@foreach ($data['funcionarios'] as $info)
    @include('pdf.nomina_person')
    {{-- //@break --}}
    <div style="page-break-after: always;"></div>
@endforeach
