<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'Cliente';
    protected $primaryKey = 'Id_Cliente';

    protected $fillable = [
        'Digito_Verificacion',
        'Tipo_Identificacion',
        'Nombre',
        'Razon_Social',
        'Primer_Nombre',
        'Segundo_Nombre',
        'Primer_Apellido',
        'Segundo_Apellido',
        'Direccion',
        'Id_Departamento',
        'Id_Municipio',
        'Ciudad',
        'Telefono_Persona_Contacto',
        'Celular',
        'Correo_Persona_Contacto',
        'Cliente_Desde',
        'Destacado',
        'Credito',
        'Cupo',
        'Tipo',
        'Detalles',
        'Contacto_Compras',
        'Telefono_Contacto_Compras',
        'Email_Contacto_Compras',
        'Contacto_Pagos',
        'Telefono_Pagos',
        'Email_Pagos',
        'Regimen',
        'Animo_Lucro',
        'Id_Codigo_Ciiu',
        'Agente_Retencion',
        'Retencion_Factura',
        'Tipo_Reteica',
        'Id_Plan_Cuenta_Reteica',
        'Id_Plan_Cuenta_Retefuente',
        'Contribuyente',
        'Id_Plan_Cuenta_Reteiva',
        'Descuento_Pronto_Pago',
        'Descuento_Dias',
        'Rut',
        'Estado',
        'Id_Zona',
        'Condicion_Pago',
        'Impuesto',
        'Tipo_Valor',
        'Id_Lista_Ganancia',
        'Latitud',
        'Longitud',
        'Fecha_Registro',
        'Autorretenedor',
    ];
}
