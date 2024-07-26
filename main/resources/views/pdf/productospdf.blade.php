<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento de Dispensación</title>
    <style>
        @page {
            size: 3in 9in;
            margin: 0;
           
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center; 
            min-height: 100vh; 
            margin: 0;
        }
        table {
            width: 60mm;
            margin: 0 auto; 
            padding: 0;
            border-spacing: 0;
            border-collapse: collapse;
        }
        td {
            font-size: 11px;
            padding: 5px;
        }
        p {
            font-size: 11px;
            text-align: center;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="2">
                <p>
                    -----------------------------------------------------------------------------------<br>
                    PRODUCTOS HOSPITALARIOS S.A.<br>804.016.084-5<br>
                    {{ $data['Punto_Dispensacion'] ?? '' }}
                </p>
            </td>
        </tr>
        <tr>
            <td><br><br><br>DISPENSACION</td>
            <td><br><br><br>{{ $data['Codigo'] ?? '' }}</td>
        </tr>
        <tr>
            <td>FECHA</td>
            <td>{{ $data['Fecha_Actual'] ?? '' }}</td>
        </tr>
        <tr>
            <td>PACIENTE</td>
            <td>{{ $data['Nombre_Paciente'] ?? '' }}</td>
        </tr>
        <tr>
            <td>DOCUMENTO</td>
            <td>{{ $data['Tipo_Documento'] ?? '' }} {{ $data['Numero_Documento'] ?? '' }}</td>
        </tr>
        <tr>
            <td>TELEFONO</td>
            <td>{{ $paciente['Telefono'] ?? '' }}</td>
        </tr>
        <tr>
            <td>REGIMEN</td>
            <td>{{ $regimen['Nombre'] ?? '' }}</td>
        </tr>
        <tr>
            <td>EPS</td>
            <td>{{ $paciente['EPS'] ?? '' }}</td>
        </tr>
        <tr>
            <td>CUOTA {{ $cuota ?? '' }}</td>
            <td>{{ $data['Cuota'] ?? '' }}</td>
        </tr>
        <tr>
            <td>TIPO SERVICIO</td>
            <td>{{ $data['Tipo'] ?? '' }}</td>
        </tr>
        @if(isset($data['Tipo']) && $data['Tipo'] != 'Pos-Capita')
        <tr>
            <td>AUTORIZACION</td>
            <td>{{ $productos[0]->Numero_Autorizacion ?? '' }}</td>

        </tr>
        @endif
        <tr>
            <td colspan="2" style="text-align:center;"><br><br>LISTADO DE PRODUCTOS</td>
        </tr>
    </table>
    <table>
        <tr>
            <td>Sol</td>
            <td>Ent</td>
            <td>Producto</td>
        </tr>
        @foreach ($productos as $prod)
        <tr>
            <td>{{ $prod->Cantidad_Formulada ?? '' }}</td>
            <td>{{ $prod->Cantidad_Entregada ?? '' }}</td>
            <td>{{ $prod->Nombre_Comercial ?? '' }}</td>
        </tr>
        @endforeach
    </table>
    <table>
        <tr>
            <td><br><br><br>ARTICULOS</td>
            <td><br><br><br>{{ count($productos) }}</td>
        </tr>
        <tr>
            <td>CLIENTE</td>
            <td>{{ $cliente['Nombre'] ?? '' }}</td>
        </tr>
        <tr>
            <td>NIT/CC</td>
            <td>{{ $cliente['Id_Cliente'] ?? '' }}</td>
        </tr>
        <tr>
            <td>DIRECCION</td>
            <td>{{ $cliente['Direccion'] ?? '' }}</td>
        </tr>
        <tr>
            <td>TELEFONO</td>
            <td>{{ $cliente['Telefono'] ?? '' }}</td>
        </tr>
    </table>
    <table>
        <tr>
            <td>
                <div style="display: flex; flex-direction: column; align-items: flex-start;">
                    {!! isset($data['Firma_Reclamante']) ? '<img style="width:60mm;" src="'. asset('IMAGENES/FIRMAS-DIS/' . $data['Firma_Reclamante']) .'" />' : '' !!}
                    <div style="width: 100%;">
                        <span style="float: left;">Nombre:</span>
                        <span style="float: right;">_____________________________</span>
                        <br><br><br>
                    </div>
                    <div style="width: 100%;">
                        <span style="float: left;">Cedula:</span>
                        <span style="float: right;">_____________________________</span>
                        <br><br><br>
                    </div>
                    <div style="width: 100%;">
                        <span style="float: left;">Telefono:</span>
                        <span style="float: right;">_____________________________</span>
                        <br><br><br>
                    </div>
                </div>
            </td>
            
        </tr>
    </table>
</body>
</html>

