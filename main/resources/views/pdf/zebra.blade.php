<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            size: 10in 7in;
            margin: 0;
            /* Sin márgenes */
        }

        .body1 {
            font-family: Arial, sans-serif;
            margin: 0px;
            padding: 0px;
        }

        .container1 {
            width: calc(95% - 10px);
            height: 85%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            border: 2px solid black;
            padding: 20px;

        }

        .info-text1 {
            font-size: 50px;
            line-height: 54px;
            font-weight: bold;
            margin-top: 30px;
        }
    </style>
</head>
@include('components/cabecera', [$datosCabecera])

<body class="body1">
    <div class="container1">
        <div class="info-text1">{{ $nom }}</div>
        <div class="info-text1">{{ $direccion }}</div>
        <div class="info-text1">{{ $mun }} - {{ $dep }}</div>
        <div class="info-text1"> {{ $tel }}</div>
        <span class="info-text1">{{ $cod }}</span>
    </div>
</body>

</html>
