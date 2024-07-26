<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoNoConforme extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table='Producto_No_Conforme';

    protected $fillable = [
        'Id_Producto',
        'Id_No_Conforme',
        'Id_Compra',
        'Tipo_Compra',
        'Id_Acta_Recepcion',
        'Cantidad',
        'Id_Causal_No_Conforme',
        'Observaciones'
    ];

    protected $primaryKey = 'Id_Producto_No_Conforme';
}
