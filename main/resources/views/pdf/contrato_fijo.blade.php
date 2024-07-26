@php
    $companyWorked = App\Models\Person::find(Auth()->user()->person_id)->company_worked_id;
    $company = App\Models\Company::find($companyWorked);
    $image = $company->page_heading;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato Individual de Trabajo</title>
    <style>
       
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .text-center {
            text-align: center;
        }

        .texto-contrato {
            margin-top: 20px;
            line-height: 1.6;
        }
        .align-top {
            vertical-align: top !important;
        }
        .w-100 {
            width: 100%;
        }

        .figure {
            display: inline-block;
        }

        .logo-container {
            position: absolute;
            top: 45px;
            left: 20px;
            width: 270px;
            height: auto;
            z-index: 9999; 
        }
        
        .logo-container img {
            width: 100%; 
            height: auto; 
            display: block;
        }

        .underline {
    text-decoration: underline;
}

    </style>
</head>

<body>

      
      <div class="logo-container">
        <img src="{{ $company->logo }}" class="figure-img img-fluid" />
      </div>
      
    <div class="container" style="max-width: 800px; margin: 0 auto; margin-top: 20px; margin-bottom: 20px; margin-left: 60px; margin-right: 40px;">
        <div class="text-center">
            <h1 class="underline">CONTRATO INDIVIDUAL DE TRABAJO</h1>

            <h1 style="padding-top: 30px">CONTRATO INDIVIDUAL DE TRABAJO A TÉRMINO FIJO INFERIOR A UN AÑO</h1>
        </div>
    </div>

    @include('components/cabecera_contrato', [$person])
    <div class="container" style="max-width: 800px; margin-left: 50px; margin-right: 40px;">
        <div class="texto-contrato">
            <p>Entre <strong>EL EMPLEADOR</strong> y <strong>EL TRABAJADOR</strong>, identificados como aparece al pie de sus firmas, se ha celebrado el presente contrato individual de trabajo, regido además por las siguientes cláusulas:</p>
            <p>{!!$person->contractultimate->position->responsibilities !!}</p>
            <p>{!!$person->contractultimate->work_contract_type->template !!}</p>
            <p>Para constancia se firma el {{ $construct_dates->diaenletras . ' (' . $construct_dates->dia . ') de ' . $construct_dates->mes . ' de ' . $construct_dates->year }}</p>
        </div>
        @include('components/footer_contrato', [$person, $responsible])
    </div>
   
</body>


</html>