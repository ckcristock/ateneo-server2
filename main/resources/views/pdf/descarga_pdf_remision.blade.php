<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .custom-th, .custom-td {
            border: 1px solid #cccccc;
            padding: 5px;
        }
        .custom-th {
            background-color: #cecece;
            font-weight: bold;
            text-align: center;
        }
        .custom-td {
            background-color: #f3f3f3;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        /* Otros estilos personalizados */
        .custom-header h3 {
            margin: 5px 0;
            font-size: 22px;
            line-height: 22px;
        }
        .custom-header h5 {
            margin: 5px 0;
            font-size: 16px;
            line-height: 16px;
        }
        .custom-header h4 {
            margin: 5px 0;
            font-size: 14px;
            line-height: 14px;
        }
        .custom-observations {
            background-color: #e9eef0;
            border-radius: 5px;
            padding: 8px;
            font-size: 10px;
            margin-top: 10px;
        }
        .custom-signatures {
            margin-top: 10px;
            font-size: 10px;
            border: 1px solid #cccccc;
        }
        .custom-signature {
            width: 33.33%;
            border-right: 1px solid #cccccc;
            padding: 10px;
        }
        .custom-signature img {
            width: 230px;
        }
    </style>
</head>
<body>
    @include('components/cabecera', [$datosCabecera])
    <div class="container">
        
        <table>
            <tr>
                <td class="custom-td" style="width:50%;">
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
                <td class="custom-td" style="width:50%;">
                    <table>
                        <tr>
                            <th class="custom-th" colspan="2">Destino</th>
                        </tr>
                        <tr>
                            <td class="custom-td">{{ $destino['Nombre'] ?? $destino['social_reason'] ?? ($destino['first_name'] ?? '') . (isset($destino['first_surname']) ? ' ' . $destino['first_surname'] : '') }}</td>
                            <td class="custom-td">{{ $destino["Direccion"] ?? $destino["dian_address"] ?? '' }}</td>
                        </tr>
                        <tr>
                            <td class="custom-td"><strong>Tel.:</strong> {{ $destino["Telefono"] ?? $destino["cell_phone"] ?? ''}}</td>
                            <td class="custom-td"><strong>Correo:</strong> @isset($destino["Correo"]) {{ $destino["Correo"] }} @endisset</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div class="custom-observations">
            <strong>Observaciones</strong><br>
            {{ $data["Observaciones"] }}
        </div>
        <!-- Detalles de Productos -->
        <table>
            <!-- Encabezados -->
            <thead>
                <tr>
                    <th class="custom-th"></th>
                    <th class="custom-th">Producto</th>
                    <th class="custom-th">Laboratorio</th>
                    <th class="custom-th">Lote</th>
                    <th class="custom-th">F. Vencimiento</th>
                    <th class="custom-th">Cant.</th>
                </tr>
            </thead>
            <!-- Datos de Productos -->
            <tbody>
                @php
                $max = 0;
                @endphp
                @foreach ($productos as $prod)
                @php
                $max++;
                @endphp
                <tr>
                    <td class="custom-td">{{ $max }}</td>
                    <td class="custom-td">{{ $prod["Nombre_Producto"] }}</td>
                    <td class="custom-td">{{ $prod["Laboratorio_Generico"] }}</td>
                    <td class="custom-td">{{ $prod["Lote"] }}</td>
                    <td class="custom-td">{{ $prod["Fecha_Vencimiento"] }}</td>
                    <td class="custom-td">{{ $prod["Cantidad"] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Firmas -->
        <table class="custom-signatures" style="margin-top:10px;">
            <tr>
                <td class="custom-signature" style="width:33%;">
                    <strong>Persona Elaboró</strong><br><br><br><br><br><br><br>
                    {!! $imagen ?? '' !!}
                    {{ $elabora["first_name"] }} {{ $elabora["first_surname"] }}
                </td>
                <td class="custom-signature" style="width:33%;">
                    <strong>Alistamiento Fase 1</strong><br><br><br><br><br><br><br>
                    {!! $firma1 ?? '' !!}
                    {{ $data["Fase1"] }}
                </td>
                <td class="custom-signature" style="width:33%;">
                    <strong>Alistamiento Fase 2</strong><br><br><br><br><br><br><br>
                    {!! $firma2 ?? '' !!}
                    {{ $data["Fase2"] }}
                </td>
            </tr>
        </table>
        
    </div>
</body>
</html>
