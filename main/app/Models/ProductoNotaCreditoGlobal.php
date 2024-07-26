<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoNotaCreditoGlobal extends Model
{
    protected $table = 'Producto_Nota_Credito_Global';

    protected $primaryKey = 'Id_Producto_Nota_Credito_Global';

    protected $fillable = [
        'Id_Nota_Credito_Global',
        'Tipo_Producto',
        'Id_Producto',
        'Nombre_Producto',
        'Observacion',
        'Valor_Nota_Credito',
        'Impuesto',
        'Precio_Nota_Credito',
        'Cantidad',
        'Id_Causal_No_Conforme',
        'Id_Causal_Anulacion'
    ];

    protected $casts = [
        'Valor_Nota_Credito' => 'decimal:2',
        'Impuesto' => 'decimal:2',
        'Precio_Nota_Credito' => 'decimal:3',
        'Cantidad' => 'integer',
        'Id_Causal_No_Conforme' => 'integer',
        'Id_Causal_Anulacion' => 'integer'
    ];

}
