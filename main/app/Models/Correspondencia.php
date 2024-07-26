<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Correspondencia extends Model
{
    use HasFactory;

    protected $table = 'Correspondencia';
    protected $primaryKey = 'Id_Correspondencia';

    protected $fillable = [
        'Id_Funcionario_Envia',
        'Fecha_Envio',
        'Cantidad_Folios',
        'Punto_Envio',
        'Fecha_Probable_Entrega',
        'Fecha_Entrega_Real',
        'Id_Funcionario_Recibe',
        'Empresa_Envio',
        'Numero_Guia',
        'Estado',
        'Observaciones_Envio',
        'Observaciones_Recibido',
        'Id_Regimen',
        'Id_Tipo_Servicio'
    ];

    protected $casts = [
        'Fecha_Envio' => 'date',
        'Fecha_Probable_Entrega' => 'date',
        'Fecha_Entrega_Real' => 'date',
    ];

    // relationships

    public function funcionarioEnvio()
    {
        return $this->belongsTo(Person::class, 'Id_Funcionario_Envia', 'identifier');
    }

    public function funcionarioRecibe()
    {
        return $this->belongsTo(Person::class, 'Id_Funcionario_Recibe', 'identifier');
    }
    
}
