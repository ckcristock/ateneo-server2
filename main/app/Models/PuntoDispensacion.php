<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuntoDispensacion extends Model
{
    use HasFactory;
    protected $table = 'Punto_Dispensacion';

    protected $primaryKey = 'Id_Punto_Dispensacion';

    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Tipo',
        'Tipo_Entrega',
        'Departamento',
        'Municipio',
        'Direccion',
        'Telefono',
        'Responsable',
        'No_Pos',
        'Turnero',
        'Cajas',
        'Wacom',
        'Entrega_Formula',
        'Entrega_Doble',
        'Autorizacion',
        'Tipo_Dispensacion',
        'Campo_Mipres',
        'Id_Bodega_Despacho',
        'Estado',
    ];

    protected $casts = [
        'Wacom' => 'boolean',
        'Entrega_Formula' => 'boolean',
        'Entrega_Doble' => 'boolean',
        'Autorizacion' => 'boolean',
    ];

    public function people()
    {
        return $this->belongsTo(People::class, 'dispensing_point_id', 'Id_Punto_Dispensacion');
    }

    public function departamento()
    {
        return $this->belongsTo(Department::class, 'Departamento', 'id');
    }

    public function tipoServicio()
    {
        return $this->hasMany(TipoServicioPuntoDispensacion::class, 'Id_Punto_Dispensacion', 'Id_Punto_Dispensacion');
    }

    public function servicioPuntoDispensacion()
    {
        return $this->hasMany(ServicioPuntoDispensacion::class, 'Id_Punto_Dispensacion', 'Id_Punto_Dispensacion');
    }

}
