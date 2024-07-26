<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PositivaData extends Model
{
    use HasFactory;
    protected $table = 'Positiva_Data';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'numeroAutorizacion',
        'Anulado',
        'fechaAnulado',
        'fechaHoraAutorizacion',
        'fechaVencimiento',
        'tieneTutela',
        'serviciosAutorizados',
        'diagnosticos',
        'PtipoDocumento',
        'PnumeroDocumento',
        'PcodigoHabilitacion',
        'PrazonSocial',
        'Pdepartamento',
        'PcodigoDepartamento',
        'Pmunicipio',
        'PcodigoMunicipio',
        'Psede',
        'Pdomicilio',
        'Pdireccion',
        'PindicativoTelefono',
        'PnumeroTelefono',
        'AFtipoDocumento',
        'AFnumeroDocumento',
        'AFprimerNombre',
        'AFsegundoNombre',
        'AFprimerApellido',
        'AFsegundoApellido',
        'AFfechaNacimiento',
        'AFdepartamento',
        'AFcodigoDepartamento',
        'AFmunicipio',
        'AFcodigoMunicipio',
        'AFzona',
        'AFlocalidad',
        'AFdireccionResidencial',
        'AFcorroElectronico',
        'AFtelefonoFijoParticularIndicativo',
        'AFtelefonoFijoParticularNumero',
        'AFtelefonoCelularParticularNumero',
        'AFcodigoCoberturaSalud',
        'AFramo',
        'RLtipoDocumento',
        'RLnumeroDocumento',
        'RLrazonSocial',
        'RLfechaVinculacion',
        'RLestadoRelacionLaboral',
        'RLmarcaEmpleador',
        'RLnumeroSolicitudSiniestro',
        'RLnumeroSiniestro',
        'PAnombre',
        'PAcargoActividad',
        'PAtelefonoContactoUno',
        'PAtelefonoContactoDos',
        'FechaCreacion',
        'usuarioPositiva',
        'Estado',
        'Detalle_Estado',
        'Id_Dispensacion',
        'Request',
        'requestAnulacion',
    ];
}
