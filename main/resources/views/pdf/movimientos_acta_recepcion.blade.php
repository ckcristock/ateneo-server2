<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF</title>
    <style>
        .page-content {
            width: 750px;
            font-size: 10px;
        }

        .row {
            display: inline-block;
            width: 750px;
        }

        .td-header {
            font-size: 15px;
            line-height: 20px;
        }

        .titular {
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 0;
        }

        .table-header-cell {
            font-weight: bold;
            background: #cecece;
            text-align: center;
            border: 1px solid #cccccc;
        }

        .table-cell {
            width: 100px;
            padding: 4px;
            text-align: right;
            border: 1px solid #cccccc;
        }

        .table-cell-wide {
            width: 150px;
        }

        .table-total-cell {
            padding: 4px;
            text-align: right;
            border: 1px solid #cccccc;
        }

        .footer-cell {
            width: 168px;
            font-weight: bold;
            border: 1px solid #cccccc;
            padding: 4px;
        }

        .footer-content {
            font-size: 10px;
        }

        .persona-elaboro {
            margin-top: 10px;
        }
    </style>
</head>

<body>

    @include('components/cabecera', [$datosCabecera])

    <table class="page-content">
        <tr>
            <td class="table-header-cell" colspan="6">Cuenta {{$tipo_valor}}</td>
        </tr>
        <tr>
            <td class="table-header-cell">Cuenta</td>
            <td class="table-header-cell table-cell-wide">Nombre Cuenta</td>
            <td class="table-header-cell">Documento</td>
            <td class="table-header-cell">Nit</td>
            <td class="table-header-cell">Débitos {{$tipo_valor}}</td>
            <td class="table-header-cell">Crédito {{$tipo_valor}}</td>
        </tr>

        @forelse($movimientos as $value)
        @php
            $codigo = ($tipo_valor != '') ? $value['Codigo_Niif'] : $value['Codigo'];
            $nombre_cuenta = ($tipo_valor != '') ? $value['Nombre_Niif'] : $value['Nombre'];
            $debe = ($tipo_valor != '') ? $value['Debe_Niif'] : $value['Debe'];
            $haber = ($tipo_valor != '') ? $value['Haber_Niif'] : $value['Haber'];
        @endphp
            <tr>
                <td class="table-cell">{{$codigo}}</td>
                <td class="table-cell table-cell-wide">{{$nombre_cuenta}}</td>
                <td class="table-cell">{{$value["Documento"]}}</td>
                <td class="table-cell">{{$value['Nombre_Cliente']}} - {{$value["Nit"]}}</td>
                <td class="table-cell">${{number_format($debe, 2, ".", ",")}}</td>
                <td class="table-cell">${{number_format($haber, 2, ".", ",")}}</td>
            </tr>
        @empty
            <tr>
                <td class="table-cell" colspan="6">No hay movimientos.</td>
            </tr>
        @endforelse

        <tr>
            <td class="table-total-cell" colspan="4">TOTAL</td>
            <td class="table-total-cell">${{number_format($total_debe ?? 0, 2, ".", ",")}}</td>
            <td class="table-total-cell">${{number_format($total_haber ?? 0, 2, ".", ",")}}</td>
        </tr>
    </table>

    <table class="page-content">
        <tr>
            <td class="footer-cell">Elaboró:</td>
            <td class="footer-cell">Imprimió:</td>
            <td class="footer-cell">Revisó:</td>
            <td class="footer-cell">Aprobó:</td>
        </tr>
        <tr>
            <td class="footer-content">{{$elabora}}</td>
            <td class="footer-content">{{$imprime['Nombre_Funcionario']}}</td>
            <td class="footer-content"></td>
            <td class="footer-content"></td>
        </tr>
    </table>

    <div class="persona-elaboro">
        <strong>Persona Elaboró</strong><br><br>
        {{$elabora}}
    </div>

</body>

</html>
