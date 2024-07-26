<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioFisicoNuevo extends Model
{
    use HasFactory;

    protected $table = 'Inventario_Fisico_Nuevo';

    protected $primaryKey = 'Id_Inventario_Fisico_Nuevo';

    public $timestamps = false;

    protected $fillable = [
        'Funcionario_Autoriza',
        'Id_Bodega_Nuevo',
        'Id_Grupo_Estiba',
        'Fecha'
    ];

    public function docInventarioFisico()
    {
        return $this->hasMany(DocInventarioFisico::class, 'Id_Inventario_Fisico_Nuevo', 'Id_Inventario_Fisico_Nuevo');
    }
}
