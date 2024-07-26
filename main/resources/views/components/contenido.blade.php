<table  cellspacing="0" cellpadding="0" >
    <tr>
    <td style="font-size:10px;background:#c6c6c6;text-align:center;">Descripción</td>
    <td style="font-size:10px;background:#c6c6c6;text-align:center;">Laboratorio</td>
    <td style="font-size:10px;background:#c6c6c6;text-align:center;">Lote</td>
    <td style="font-size:10px;background:#c6c6c6;text-align:center;">F. Venc.</td>
    <td style="font-size:10px;background:#c6c6c6;text-align:center;">Presentación</td>
    <td style="font-size:10px;background:#c6c6c6;text-align:center;">Und</td>
    <td style="font-size:10px;background:#c6c6c6;text-align:center;">Iva</td>
    <td style="font-size:10px;background:#c6c6c6;text-align:center;">Precio</td>
    <td style="font-size:10px;background:#c6c6c6;text-align:center;">Total</td>
    </tr>

    @php
    $total_iva = 0;
    $total_descuento = 0;
    $subtotal = 0;
    @endphp
    @foreach ($productos as $prod) 
    @php
        $iva =(intval(str_replace("%", "", $prod->Impuesto)) / 100);
        $total_iva += (($prod->Cantidad * $prod->PrecioVenta ) * $iva);
        $total_producto = $prod->Cantidad * ($prod->PrecioVenta);
        $total_descuento += ($prod->Cantidad * $prod->PrecioVenta) * ($prod->Descuento ?? 0) / 100;
        $subtotal += $total_producto;
    @endphp
    <tr>
	        		<td style="padding:4px;font-size:9px;text-align:left;border:1px solid #c6c6c6;width:265px;vertical-align:middle;">
	        		{{$prod->producto}}
	        		</td>
	        		<td style="padding:4px;font-size:9px;text-align:left;border:1px solid #c6c6c6;text-align:left;width:70px;vertical-align:middle;">
	        		{{$prod->Laboratorio}}
	        		</td>
	        		<td style="padding:4px;font-size:9px;text-align:center;border:1px solid #c6c6c6;width:50px;vertical-align:middle;"> 
	        		{{$prod->Lote}}
	        		</td>
	        		<td style="padding:4px;font-size:9px;text-align:center;border:1px solid #c6c6c6;width:35px;vertical-align:middle;">
	        		{{ date('d/m/Y', strtotime($prod->Vencimiento))}}
	        		</td>
	        		<td style="padding:4px;font-size:9px;text-align:left;border:1px solid #c6c6c6;width:60px;vertical-align:middle;">
	        		{{$prod->Presentacion}}
	        		</td>
	        		<td style="padding:4px;font-size:9px;text-align:center;border:1px solid #c6c6c6;width:20px;vertical-align:middle;">
	        		{{number_format($prod->Cantidad, 0, "", ".")}}
	        		</td>
	        		<td style="padding:4px;font-size:9px;;text-align:center;border:1px solid #c6c6c6;vertical-align:middle;">
	        		{{$prod->Impuesto}}
	        		</td>
	        		<td style="padding:4px;font-size:9px;text-align:right;border:1px solid #c6c6c6;vertical-align:middle;width:40px;">
	        		${{number_format($prod->PrecioVenta, 0, ",", ".")}}
	        		</td>
	        		<td style="padding:4px;font-size:9px;text-align:right;border:1px solid #c6c6c6;width:60px;vertical-align:middle;">
	        		${{number_format($total_producto, 0, ",", ".")}}
	        		</td>
	        	    </tr>

    @endforeach
    @php
        $total = $subtotal + $total_iva - $total_descuento;
        $numero = number_format($total, 0, '.','');
        $letras = $letras->convertir($numero);
    @endphp
    </table>
	             <table style="margin-top:20px;margin-bottom:0;">
	             	<tr>
	             	   <td colspan="2" style="padding:4px;font-size:9px;border:1px solid #c6c6c6;width:586px;"><strong>Valor a Letras:</strong><br> {{$letras}} PESOS MCTE</td>
	             	   <td rowspan="3" style="padding:4px;font-size:9px;border:1px solid #c6c6c6;width:130px;">
	             	   	<table cellpadding="0" cellspacing="0">
	             	   	   <tr>
	             	   		<td style="padding:4px;font-size:9px;width:60px;"><strong>Subtotal</strong></td>
	             	   		<td style="padding:4px;font-size:9px;width:60px;text-align:right;">$ {{number_format($subtotal, 0, ",", ".")}} </td>
	             	   	   </tr>
	             	   	   <tr>
	             	   		<td style="padding:4px;font-size:9px;width:60px;"><strong>Dcto.</strong></td>
	             	   		<td style="padding:4px;font-size:9px;width:60px;text-align:right;">$  {{number_format($total_descuento, 0, ",", ".")}}</td>
	             	   	   </tr>
	             	   	   <tr>
	             	   		<td style="padding:4px;font-size:9px;width:60px;"><strong>Iva 19%</strong></td>
	             	   		<td style="padding:4px;font-size:9px;width:60px;text-align:right;">$ {{number_format($total_iva, 0, ",", ".")}}</td>
	             	   	   </tr>
	             	   	   <tr>
	             	   		<td style="padding:4px;font-size:9px;width:60px;"><strong>Retención</strong></td> 
	             	   		<td style="padding:4px;font-size:9px;width:60px;text-align:right;">$ 0</td>
	             	   	   </tr>
	             	   	   <tr>
	             	   		<td style="padding:4px;font-size:9px;width:60px;"><strong>Total</strong></td>
	             	   		<td style="padding:4px;font-size:9px;width:60px;text-align:right;"><strong>$ {{number_format($subtotal + $total_iva - $total_descuento, 0, ",", ".")}} </strong></td>
	             	   	   </tr>
	             	   	</table>
	             	   </td>
	             	</tr>
	             	<tr>
	             	   <td style="padding:4px;font-size:9px;border:1px solid #c6c6c6;width:486px;">
	             	   	<strong>Obsrvaciones:</strong><br>
	             	   	{{$cliente["observacion"]}} - {{$cliente["Observaciones2"]}}
	             	   </td>
	             	   <td style="padding:4px;font-size:9px;border:1px solid #c6c6c6;width:90px;"></td>
	             	</tr>
               </table>
    
