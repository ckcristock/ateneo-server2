
<!DOCTYPE html>
<html>
<head>
    <style>
        .firma {
            font-size: 16px;
            background-color: transparent;
        }
    </style>
</head>
<body>
    @include('components/cabecera', [$datosCabecera])
    
    <h4>Señor(a) {{ $data->person->fullName }}</h4> 
   

    <p  style="margin-top: 50px">Con la presente acta se le hace entrega de la siguiente dotación:</p>

    <table style="border-collapse: collapse; width: 100%; margin-top: 30px;">
        <thead>
            <tr>
                <th style="border: 0.1em solid black;">Cantidad</th>
                <th style="border: 0.1em solid black;">Tipo</th>
                <th style="border: 0.1em solid black;">Descripción</th>
                <th style="border: 0.1em solid black;">Talla</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data->dotationProducts as $product)
            <tr>
                <td style="border: 0.1em solid black; font-size: 14px;">{{ $product->quantity }}</td>
                <td style="border: 0.1em solid black; font-size: 14px;">{{ $product->inventaryDotation->productDotationType->name }}</td>
                <td style="border: 0.1em solid black; font-size: 14px;">{{ $product->inventaryDotation->name }}</td>
                <td style="border: 0.1em solid black; font-size: 14px;">{{ $product->inventaryDotation->size }}</td>
            </tr>
            
            @endforeach
        </tbody>
    </table>
    

    

    <h5 style="margin-top: 50px" >El trabajador manifiesta que:</h5>
    <p>La dotación que a aquí se entrega es y será de la empresa en todo momento, en caso de terminación del contrato de trabajo o entrega de una nueva dotación, me comprometo a hacer la devolución de forma inmediata.</p>
    <p>En caso de daño de la dotación o parte de ella, el trabajador debe devolverla a la empresa.</p>
    <p>Autorizo expresamente a la empresa mediante este documento a descontar de salarios y liquidación de prestaciones los valores de la dotación cuando en cualquiera de los casos anteriores no la devuelve al empleador.</p>


    <table style="margin-top: 100px">
        <tr>
            <td class="firma">
                <p style="margin-bottom: 50px;">Entregado por:</p>
                <div>____________________________</div>
                <div>{{ $data->user->person->full_names }}</div>
                <div>{{ $data->user->person->contractultimate->position->name }}</div>
         
            </td>
            <td class="firma" style="padding-left: 30px;">
                <p style="margin-bottom: 50px;">Recibido por:</p>
                <div>____________________________</div>
                <div>{{ $data->person->fullName }}</div>
                <div>{{ $data->person->documentType->code }}: {{ $data->person->identifier }}</div>
                
            </td>
        </tr>
    </table>
    
</body>
</html>
