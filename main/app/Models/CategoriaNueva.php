<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaNueva extends Model
{
    protected $table = 'Categoria_Nueva';
    protected $primaryKey = 'Id_Categoria_Nueva';
    protected $fillable = [
        'company_id',
        'Nombre',
        'Departamento',
        'Municipio',
        'Direccion',
        'Telefono',
        'Compra_Internacional',
        'Aplica_Separacion_Categorias',
        'Activo',
        'Fijo',
        'general_name',
        'receives_barcode',
        'is_stackable',
        'is_inventory',
        'is_listed',
        'has_lote',
        'has_expiration_date',
        'request_purchase'
    ];

    protected $appends = ['name'];
    public function getNameAttribute()
    {
        return $this->Nombre;
    }

    public function accountConfiguration()
    {
        return $this->morphOne(AccountConfiguration::class, 'configurable');
    }


    public function categoryVariables()
    {
        return $this->hasMany(CategoryVariable::class, "category_id");
    }

    public function subcategory()
    {
        return $this->hasMany(Subcategory::class, "Id_Categoria_Nueva");
    }

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class, 'Id_Categoria_Nueva', 'Id_Categoria_Nueva');
    }

    public function scopeActive($query)
    {
        $query->where('Activo', 1);
    }
}
