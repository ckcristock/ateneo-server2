<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Nota de Crédito</title>
    <style>
        /* Estilos CSS con nombres personalizados */
        body {
            font-family: Arial, sans-serif;
        }

        h3, h4, h5 {
            margin: 5px 0 0 0;
        }

        h3 {
            font-size: 22px;
            line-height: 22px;
        }

        h4 {
            font-size: 14px;
            line-height: 14px;
        }

        h5 {
            font-size: 16px;
            line-height: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-th, .custom-td {
            border: 1px solid #cccccc;
            padding: 8px;
            font-size: 10px;
        }

        .custom-th {
            background: #cecece;
            font-weight: bold;
            text-align: center;
        }

        .custom-td {
            background: #f3f3f3;
        }

        /* Clases personalizadas */
        .custom-obs-table td {
            background: #e9eef0;
            border-radius: 5px;
            padding: 8px;
            font-size: 10px;
        }

        .custom-text-right {
            text-align: right;
        }
    </style>
    
</head>

<body>
    @include('components/cabecera', [$datosCabecera])
    <!-- Contenido HTML con clases personalizadas -->
    <h3>{{ $data["Codigo"] }}</h3>
    <h4>Tipo {{ $data["Tipo"] }}</h4>

    <table>
        <!-- Detalles de Origen y Destino -->
        <tr>
            <td style="width:50%;">
                <table>
                    <tr>
                        <th class="custom-th" colspan="2">Origen</th>
                    </tr>
                    <tr>
                        <td class="custom-td">{{ $origen["Nombre"] }}</td>
                        <td class="custom-td">{{ $origen["Direccion"] }}</td>
                    </tr>
                    <tr>
                        <td class="custom-td"><strong>Tel.:</strong> {{ $origen["Telefono"] }}</td>
                        <td class="custom-td"><strong>Correo:</strong> @isset($origen["Correo"]) {{ $origen["Correo"] }} @endisset</td>

                        
                    </tr>
                </table>
            </td>
            <td style="width:50%;">
                <table>
                    <tr>
                        <th class="custom-th" colspan="2">Destino</th>
                    </tr>
                    <tr>
                        <td class="custom-td">{{ $destino["Nombre"] ?? ''}}</td>
                        <td class="custom-td">{{ $destino["Direccion"] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="custom-td"><strong>Tel.:</strong> {{ $destino["Telefono"] ?? '' }}</td>
                        <td class="custom-td"><strong>Correo:</strong> @isset($destino["Correo"]) {{ $destino["Correo"] }} @endisset</td>
                        
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Observaciones con clase personalizada -->
    <table class="custom-obs-table">
        <tr>
            <td class="custom-td">
                <strong>Observaciones</strong><br>
                {{ $data["Observaciones"] }}
            </td>
        </tr>
    </table>

    <!-- Detalles de Productos -->
    <table>
        <!-- Encabezados -->
        <tr>
            <th class="custom-th"></th>
            <th class="custom-th">Producto</th>
            <th class="custom-th">Lote</th>
            <th class="custom-th">F. Vencimiento</th>
            <th class="custom-th">Cant.</th>
            <th class="custom-th">Precio</th>
            <th class="custom-th">Desc.</th>
            <th class="custom-th">IVA</th>
            <th class="custom-th">Subtotal</th>
        </tr>
        <!-- Datos de Productos -->
        @php
        $max = 0;
        $subtotal = 0;
        $iva = 0;
        $total = 0;
        @endphp
        @foreach ($productos as $prod)
        @php
        $max++;
        $subtotal += $prod["Subtotal"];
        $iva += ($prod["Cantidad"] * $prod["Precio"]) * ($prod["Impuesto"]/100);
        $total = $subtotal + $iva;
        @endphp
        <tr>
            <td class="custom-td">{{ $max }}</td>
            <td class="custom-td">{{ $prod["Nombre_Producto"] }}</td>
            <td class="custom-td">{{ $prod["Lote"] }}</td>
            <td class="custom-td">{{ $prod["Fecha_Vencimiento"] }}</td>
            <td class="custom-td">{{ $prod["Cantidad"] }}</td>
            <td class="custom-td">$ {{ number_format($prod["Precio"], 2, ',', '.') }}</td>
            <td class="custom-td">{{ $prod["Descuento"] }}%</td>
            <td class="custom-td">{{ $prod["Impuesto"] }}</td>
            <td class="custom-td">$ {{ number_format($prod["Subtotal"], 2, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <!-- Totales -->
    <table style="margin-top:10px;">
        <tr>
            <td class="custom-text-right custom-td" colspan="2">
                <strong>SubTotal: </strong> $ {{ number_format($subtotal, 2, ",", ".") }}<br>
                <strong>Iva: </strong> $ {{ number_format($iva, 2, ",", ".") }}<br>
                <strong>Total: </strong> $ {{ number_format($total, 2, ",", ".") }}
            </td>
        </tr>
    </table>

    <!-- Firmas -->
    <table style="margin-top:10px;">
        <tr>
            <td class="custom-td" style="width:33%;">
                <strong>Persona Elaboró</strong><br><br><br><br><br><br><br>
                {{ $elabora["first_name"] }} {{ $elabora["first_surname"] }}
            </td>
            <td class="custom-td" style="width:33%;">
                <strong>Alistamiento Fase 1</strong><br><br><br><br><br><br><br>
            </td>
            <td class="custom-td" style="width:33%;">
                <strong>Alistamiento Fase 2</strong><br><br><br><br><br><br><br>
            </td>
        </tr>
    </table>
</body>

</html>

