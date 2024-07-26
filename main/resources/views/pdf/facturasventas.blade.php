<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF</title>
    <style>
        @page {
            margin-left: 0.2cm;
            margin-right: 0.2cm;
        }

        /* Esta regla CSS asegura que cada <page> comience en una nueva página */
        .page-container {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="page-container">
        <page backtop="330px" backbottom="195px">
            @include('components/cabecera_factura', [$datosCabecera, $data, $fact, $cliente, $tipoCopia='ORIGINAL'])
            @include('components/contenido', [$data, $fact, $cliente,$letras])
            @include('components/pieFactura', [$fact, $elabora])
        </page>
    </div>
    
    <div class="page-container">	
        <page backtop="330px" backbottom="195px">	
            @include('components/cabecera_factura', [$datosCabecera, $data, $fact, $cliente, $tipoCopia='CLIENTE'])
            @include('components/contenido', [$data, $fact, $cliente,$letras])
            @include('components/pieFactura', [$fact, $elabora])
        </page>
    </div>
            
    <div>
        <page backtop="330px" backbottom="195px">
            @include('components/cabecera_factura', [$datosCabecera, $data, $fact, $cliente, $tipoCopia='ARCHIVO'])
            @include('components/contenido', [$data, $fact, $cliente,$letras])
            @include('components/pieFactura', [$fact, $elabora])
        </page>
    </div>
</body>
</html>
