<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    protected $table = 'Subcategoria';
    protected $primaryKey = 'Id_Subcategoria';

    protected $fillable = [
        'Id_Categoria_Nueva',
        'company_id',
        'Nombre',
        'Separable',
        'Activo',
        'Fijo'
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


    public function category()
    {
        return $this->belongsto(Category::class, 'Id_Categoria_Nueva', 'Id_Categoria_Nueva');
    }

    public function scopeAlias($q, $alias)
    {
        return $q->from($q->getQuery()->from . " as " . $alias);
    }


    public function subcategoryVariables()
    {
        return $this->hasMany(SubcategoryVariable::class, "subcategory_id");
    }


    public function scopeActive($query)
    {
        $query->where('Activo', 1);
    }

}
