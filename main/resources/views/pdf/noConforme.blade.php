<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        .container {
            width: 750px;
            margin: 0 auto;
        }

        /* Estilos de tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #cecece;
            font-weight: bold;
        }

        .gray {
            color: gray;
        }

        /* Estilos de la sección de observaciones */
        .observaciones {
            background-color: #e9eef0;
            border-radius: 5px;
            padding: 8px;
            font-size: 10px;
            margin-top: 10px;
        }

        /* Estilos de la sección de subtotal, IVA y total */
        .subtotal-section {
            background-color: #e9eef0;
            border-radius: 5px;
            padding: 8px;
            text-align: right;
            margin-top: 10px;
            padding: 30px 20px;
            font-size: 10px;
        }

        /* Estilos de la sección de persona que elaboró */
        .persona-elaboro {
            border: 1px solid #ccc;
            width: 740px;
            text-align: center;
            padding: 10px;
            font-size: 10px;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    @include('components/cabecera', [$datosCabecera])

    <table>
        <tr>
            <th>Proveedor</th>
            <th>Nit</th>
            <th>Bodega</th>
        </tr>
        <tr>
            <td>{{$compra["Proveedor"]}}</td>
            <td>{{$compra["Id_Proveedor"]}}</td>
            <td>{{$compra["Bodega"]}}</td>
        </tr>
    </table>

    <div class="observaciones">
        <strong>Observaciones</strong><br>
        {{$data["Observaciones"]}}
    </div>

    <table>
        <tr>
            <th>Producto</th>
            <th>Embalaje</th>
            <th>Cantidad</th>
            <th>Lote</th>
            <th>Fecha Venc.</th>
            <th>Motivo</th>
            <th>Costo</th>
        </tr>
        @php
            $subtotal = 0;
            $iva = 0;
            $total = 0;
        @endphp
        @foreach($productos as $prod)
        <tr>
            <td>{{$prod->Nombre_Comercial}} <br><span class="gray">{{$prod->Nombre_Producto}}</span></td>
            <td>{{$prod->Embalaje}}</td>
            <td>{{$prod->Cantidad}}</td>
            <td>{{$prod->Lote}}</td>
            <td>{{$prod->Fecha_Vencimiento}}</td>
            <td>{{$prod->Motivo}}</td>
            <td>${{number_format($prod->Costo,2,",",".")}}</td>
        </tr>
        @php
            $subtotal += $prod->Cantidad * $prod->Costo;
            $iva += ($prod->Cantidad * $prod->Costo) * ($prod->Impuesto/100);
            $total = $subtotal+$iva;
        @endphp
        @endforeach
    </table>

    <div class="subtotal-section">
        <strong>SubTotal:</strong> ${{number_format($subtotal,2,",",".")}}<br><br>
        <strong>Iva:</strong> ${{number_format($iva,2,",",".")}}<br><br>
        <strong>Total:</strong> ${{number_format($total,2,",",".")}}
    </div>

    <div class="persona-elaboro">
        <strong>Persona Elaboró</strong><br><br>
        {{$elabora}}
    </div>

</body>

</html>
