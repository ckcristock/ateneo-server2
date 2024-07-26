@php
    $companyWorked = App\Models\Person::find(Auth()->user()->person_id)->company_worked_id;
    $company = App\Models\Company::find($companyWorked);
    $image = $company->page_heading;
@endphp
<table cellspacing="0" cellpadding="0" style="text-transform:uppercase;margin:0px;">';
    @if($fact["Tipo_Resolucion"]=="Resolucion_Electronica")
    <tr>
        <td style="font-size:10px;width:770px;background:#c6c6c6;vertical-align:middle;padding:5px;text-align:center;">
        <strong>CUFE: {{$cliente["Cufe"]}}</strong>
        </td>
    </tr>
    @endif
<tr>
		<td style="font-size:10px;width:770px;background:#f3f3f3;vertical-align:middle;padding:5px;height:40px;">
			<strong>Resolución Facturación {{($fact["Tipo_Resolucion"]=="Resolucion_Electronica" ? 'Electrónica' : '')}}:</strong><br>
			Autorizacion de Facturacion # {{$fact["Resolucion"]}}<br>
			Desde {{date('d/m/Y', strtotime($fact["Fecha_Inicio"]))}} Hasta {{date('d/m/Y', strtotime($fact["Fecha_Fin"]))}}<br>
			Habilita Del No. {{$fact["Codigo"]}}{{$fact["Numero_Inicial"]}} Al No. {{$fact["Codigo"]}}{{$fact["Numero_Final"]}}<br>
			Actividad economica principal 4645<br>
      PROVEEDOR TECNOLOGICO - Productos Hospitalarios S.A.<br>
		</td>
	
	</tr>
	<tr>
	   <td style="font-size:10px;width:770px;background:#c6c6c6;vertical-align:middle;padding:5px;text-align:center;">
		<strong>Esta Factura se asimila en sus efectos legales a una letra de cambio Art. 774 del Codigo de Comercio</strong>
	   </td>
	</tr>
	<tr>
	   <td style="font-size:10px;width:770px;background:#f3f3f3;vertical-align:middle;padding:5px;">
		<strong>Nota:</strong> No se aceptan devoluciones de ningun medicamento de cadena de frio o controlados.<br>
		<strong>Cuentas Bancarias:</strong> {{$company->account_number}}
	   </td>
	</tr>
</table>
<table>
 <tr>
 	<td style="font-size:10px;width:355px;vertical-align:middle;padding:5px;text-align:center;">
 	<br><br>______________________________<br>
 		Elaborado Por<br>{{$elabora}}
 	</td>
 	<td style="font-size:10px;width:355px;vertical-align:middle;padding:5px;text-align:center;">
 	<br><br>______________________________<br>
 		Recibí Conforme<br>
 	</td>
 </tr>
</table>