@php
    $companyWorked = App\Models\Person::find(Auth()->user()->person_id)->company_worked_id;
    $company = App\Models\Company::find($companyWorked);
    $image = $company->page_heading;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
      figure {
            display: block;
            margin: 0 0 1rem;
        }

        .figure {
            display: inline-block;
        }

        .figure-img {
            margin-bottom: 0.5rem;
            line-height: 1;
            object-fit: cover;
            height: auto;
            max-width: 70px;
        }
    </style>
</head>

<body>
<header>
<table style="" >
              <tbody>
                <tr>
                  <td style="width:70px;">
                    <img src="{{ $company->logo }}" class="figure-img img-fluid" />
                  </td>
                  <td class="td-header" style="width:310px;font-weight:thin;font-size:13px;line-height:18px;">
                    <strong>{{$company->name}}</strong><br> 
                    N.I.T.: {{ $company->tin }}-{{ $company->dv }}<br> 
                    {{ $company->dian_address}}<br> 
                    Bucaramanga, Santander<br>
                    TEL: {{ $company->cell_phone}}
                  </td>
                  <td style="width:250px;text-align:right">
                  <span style="margin:-5px 0 0 0;font-size:16px;line-height:16px;">{{$datosCabecera->titulo}}</span>
                    <h3 style="margin:0 0 0 0;font-size:22px;line-height:22px;">{{$data["Codigo"]}}</h3>
                    <h5 style="margin:5px 0 0 0;font-size:11px;line-height:11px;">F. Expe.:{{ date('d/m/Y', strtotime($data["Fecha_Documento"]))}}</h5>
                    <h5 style="margin:5px 0 0 0;font-size:11px;line-height:11px;">H. Expe.:&nbsp;&nbsp;&nbsp; {{date('H:i:s', strtotime($data['Fecha_Documento']))}}</h5>
                    <h4 style="margin:5px 0 0 0;font-size:11px;line-height:11px;">F. Venc.:{{date('d/m/Y', strtotime($data["Fecha_Pago"]))}}</h4>
                  </td>
                  <td style="width:150px;">
                  @php
                    $nombre_fichero = '';
                    $ruta_imagen_default = asset('main/public/images/sinqr.png');
                    if ($fact["Tipo_Resolucion"] != "Resolucion_Electronica") {
                        $nombre_fichero = public_path('IMAGENES/QR/' . $data["Codigo_Qr"]);
                    } else {
                        $nombre_fichero = public_path('ARCHIVOS/FACTURACION_ELECTRONICA/' . $data["Codigo_Qr"]);
                    }
                @endphp

                @if ($data["Codigo_Qr"] == '' || !File::exists($nombre_fichero))
                    <img src="{{ $ruta_imagen_default }}" style="max-width:100%;margin-top:-10px;" />
                @else
                    <img src="{{ asset($nombre_fichero) }}" style="max-width:100%;margin-top:-10px;" />
                @endif
                </td>
                </tr>
                <tr>
                <td colspan="3" style="font-size:11px">
                <strong>NO SOMOS GRANDES CONTRIBUYENTES</strong><br>
                <strong>NO SOMOS AUTORETENEDORES DE RENTA</strong><br>
                <strong>POR FAVOR ABSTENERSE PRACTICAR RETENCIÓN EN LA FUENTE POR ICA,</strong><BR>
                <strong>SOMOS GRANDES CONTRIBUYENTES DE ICA EN BUCARAMANGA. RESOLUCIÓN 3831 DE 18/04/2022</strong><br>
                {{$datosCabecera->regimen}}<br>                
                </td>
                <td colspan="1" style="font-size:11px;text-align:right;vertical-align:top;">
                <strong >{{$tipoCopia}} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong><br>
                <script type="text/php">
                    if ( isset($pdf) ) {
                        $pdf->page_script('
                            $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "lighter");
                            $pdf->text(530, 170, "Pagina $PAGE_NUM de $PAGE_COUNT", $font, 9);
                        ');
                    }
                </script>
                </td>
                </tr>
                    </tbody>
                </table>


                <table cellspacing="0" cellpadding="0" style="text-transform:uppercase;margin-top:20px;">
                <tr>
                    <td style="font-size:10px;width:60px;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    <strong>Cliente:</strong>
                    </td>
                    <td style="font-size:10px;width:490px;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    {{trim($cliente["NombreCliente"])}}
                    </td>
                    <td style="font-size:10px;width:95px;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    <strong>N.I.T. o C.C.:</strong>
                    </td>
                    <td style="font-size:10px;width:100px;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    {{number_format($cliente["IdCliente"],0,",",".")}}
                    </td>
                </tr>
                
                <tr>
                    <td style="font-size:10px;width:60px;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    <strong>Dirección:</strong>
                    </td>
                    <td style="font-size:10px;width:490px;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    {{trim($cliente["DireccionCliente"])}}
                    </td>
                    <td style="font-size:10px;width:95px;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    <strong>Teléfono:</strong>
                    </td>
                    <td style="font-size:10px;width:100px;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    {{$cliente["Telefono"]}}
                    </td>
                </tr>
                
                <tr>
                    <td style="font-size:10px;width:60px;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    <strong>Ciudad: </strong>
                    </td>
                    <td style="font-size:10px;width:490px;background:#f3f3f3;vertical-align:middle;padding:3px;">
                        {{trim($cliente["CiudadCliente"])}}
                    </td>
                    <td style="font-size:10px;width:95px;background:#f3f3f3;vertical-align:middle;padding:3px; align-self: center;">
                    <strong>Forma de Pago:</strong>
                    </td>
                    <td style="font-size:10px;width:100px;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    {{$datosCabecera->condicion_pago }}
                    </td>
                  </tr>
                    
                  <tr>
                    <td style="font-size:10px;width:60px;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    <strong>Régimen: </strong>
                    </td>
                    <td style="font-size:10px;width:490px;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    {{$datosCabecera->regimen}}
                    </td>
                    <td style="font-size:10px;width:95px;background:#f3f3f3;vertical-align:middle;padding:3px;">
                      <strong>Medio de Pago:</strong>
                    </td>
                    <td style="font-size:10px;width:100px;background:#f3f3f3;vertical-align:middle;padding:3px;">
                    {{($cliente['Condicion_Pago'] > 1 ?  'Transferencia Crédito'  : 'Transferencia Débito')}}
                    </td>
                </tr>
                
            </table>
            <hr style="border:1px dotted #ccc;width:730px;">
</header>
</body>