@foreach($data->bonusPerson as $bonus)
    @include('pdf.bonus_person')
    <div style="page-break-after: always;"></div>
@endforeach
