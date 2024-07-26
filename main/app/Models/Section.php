<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'fixed'];

    /**
     * Obtener el atributo 'name' como 'title'.
     *
     * @return string
     */
    public function getTitleAttribute()
    {
        return $this->name;
    }

    /**
     * Buscar secciones por el atributo 'name'.
     *
     * @param string $name
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function searchByName($name, $fixed)
    {
        return self::where('name', 'LIKE', "%$name%")->where('fixed', $fixed)->orderBy('name', 'ASC')->get();
    }

    //las secciones son las encargadas de agrupar todas las variables, por lo tanto las variables pertenecen a una seccion
    public function variables()
    {
        return $this->hasMany(Variable::class);
    }

    public function templateSections()
    {
        return $this->hasMany(TemplateSection::class);
    }
}
