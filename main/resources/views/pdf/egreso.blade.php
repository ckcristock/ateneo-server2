<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Egreso PDF</title>
   
</head>
<body>

    @include('components/cabecera', [$datosCabecera])

    
    <div class="page-content">
        <h3 style="margin:5px 0 0 0;font-size:22px;line-height:22px;padding-top:20px;">{{ $data["Codigo"] }}</h3>
        <h4 style="margin:5px 0 0 0;font-size:18px;line-height:22px;">{{ $titulo }}</h4>
       
        <table style="background: #e6e6e6">
            <tr style="min-height: 100px;
                background: #e6e6e6;
                padding: 15px;
                border-radius: 10px;
                margin: 0;">
                <td style="font-size:11px;font-weight:bold;width:100px;padding:3px">
                    Beneficiario:
                </td>
                <td style="font-size:11px;width:610px;padding:3px">
                    {{ $data['Tercero'] }}
                </td>
            </tr>
            
            <tr style="min-height: 100px;
                background: #e6e6e6;
                padding: 15px;
                border-radius: 10px;
                margin: 0;">
                <td style="font-size:11px;font-weight:bold;width:100px;padding:3px">
                    Documento:
                </td>
                <td style="font-size:11px;width:610px;padding:3px">
                    {{ $data['Beneficiario'] }}
                </td>
            </tr>
            
            <tr style="min-height: 100px;
                background: #e6e6e6;
                padding: 15px;
                border-radius: 10px;
                margin: 0;">
                <td style="font-size:11px;font-weight:bold;width:100px;padding:3px">
                    Concepto:
                </td>
                <td style="font-size:13px;width:610px;padding:3px">
                    {{ $data['Concepto'] }}
                </td>
            </tr>
            
            <tr style="min-height: 100px;
                background: #e6e6e6;
                padding: 15px;
                border-radius: 10px;
                margin: 0;">
                <td style="font-size:11px;font-weight:bold;width:100px;padding:3px">
                    Forma Pago:
                </td>
                <td style="font-size:11px;width:610px;padding:3px">
                    {{ $data['Forma_Pago'] }}
                </td>
            </tr>

            {!! $cheques !!}
            {!! $contenidoCentroCostoHtml !!}
        </table>

        <table style="font-size:10px;margin-top:10px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width:60px;max-width:60px;font-weight:bold;background:#cecece;;border:1px solid #cccccc;">
                    Codigo {{ $tipo }}
                </td>
                <td style="width:90px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Cuenta {{ $tipo }}
                </td>
                <td style="width:130px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Concepto
                </td>
                <td style="width:40px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Doc.
                </td>
                <td style="width:100px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Centro Costo
                </td>
                <td style="width:100px;max-width:100px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Nit
                </td>
                <td style="width:70px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Debito {{ $tipo }}
                </td>
                <td style="width:70px;font-weight:bold;background:#cecece;text-align:center;border:1px solid #cccccc;">
                    Credito {{ $tipo }}
                </td>
            </tr>
            @php
        $totalCred = 0;
        $totalDeb = 0;
        $cuentasMostradas = [];
    @endphp
    @foreach ($cuentas as $cuenta)
        @php
            if (!in_array($cuenta->Codigo, $cuentasMostradas)) {
                if ($tipo != '') {
                    $codigo = $cuenta->Codigo_Niif;
                    $nombre_cuenta = $cuenta->Cuenta_Niif;
                    $debe = $cuenta->Deb_Niif;
                    $haber = $cuenta->Cred_Niif;
                } else {
                    $codigo = $cuenta->Codigo;
                    $nombre_cuenta = $cuenta->Cuenta;
                    $debe = $cuenta->Debito;
                    $haber = $cuenta->Credito;
                }
                $totalDeb += $debe;
                $totalCred += $haber;
                $documento = wordwrap($cuenta->Documento, 17, "<br />\n", true);
                $cuentasMostradas[] = $cuenta->Codigo;
            } else {
                continue;
            }
        @endphp
                <tr>
                    <td style="vertical-align:center;font-size:11px;width:50px;max-width:50px;text-align:center;border:1px solid #cccccc;">
                        {{ $codigo }}
                    </td>
                    <td style="vertical-align:center;font-size:11px;width:90px;border:1px solid #cccccc;">
                        {{ $nombre_cuenta }}
                    </td>
                    <td style="vertical-align:center;font-size:11px;width:84px;border:1px solid #cccccc;">
                        {{ $cuenta->Concepto }}
                    </td>
                    <td style="vertical-align:center;font-size:11px;word-break:break-all;width:60px;max-width:60px;border:1px solid #cccccc;">
                        {!! $documento !!}
                    </td>
                    <td style="vertical-align:center;font-size:11px;width:100px;border:1px solid #cccccc;">
                        {{ $cuenta->Centro_Costo }}
                    </td>
                    <td style="width:100px;max-width:100px;font-size:11px;word-break:break-all;border:1px solid #cccccc;">
                        {{ $cuenta->Tercero }} - {{ $cuenta->Nit }}
                    </td>
                    <td style="vertical-align:center;font-size:11px;text-align:right;width:75px;border:1px solid #cccccc;">
                        $.{{ number_format($debe, 2, '.', ',') }}
                    </td>
                    <td style="vertical-align:center;font-size:11px;text-align:right;width:75px;border:1px solid #cccccc;">
                        $.{{ number_format($haber, 2, '.', ',') }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="6" style="padding:4px;text-align:left;border:1px solid #cccccc;font-weight:bold;font-size:12px">Totales:</td>
                <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                    $.{{ number_format($totalDeb, 2, ".", ",") }}
                </td>
                <td style="padding:4px;text-align:right;border:1px solid #cccccc;">
                    $.{{ number_format($totalCred, 2, ".", ",") }}
                </td>
            </tr>
        </table>

        <table style="margin-top:10px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="font-weight:bold;width:170px;border:1px solid #cccccc;padding:4px">
                    Elaboró:
                </td>
                <td style="font-weight:bold;width:168px;border:1px solid #cccccc;padding:4px">
                    Revisó:
                </td>
                <td style="font-weight:bold;width:168px;border:1px solid #cccccc;padding:4px">
                    Aprobó:
                </td>
                <td style="font-weight:bold;width:168px;border:1px solid #cccccc;padding:4px">
                    Beneficiario
                </td>
            </tr>
            <tr>
                <td style="font-size:10px;width:170px;border:1px solid #cccccc;padding:4px">
                    {{ $elaboraNombre }}
                </td>
                <td style="width:168px;border:1px solid #cccccc;padding:4px">
    
                </td>
                <td style="width:168px;border:1px solid #cccccc;padding:4px">
    
                </td>
                <td style="width:168px;border:1px solid #cccccc;padding:4px">
    
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
