<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;
    protected $table = "templates";

    protected $fillable = [
        "name",
        "company_id",
    ];

    //relaciona las especialidades por cada plantilla
    public function specialities()
    {
        return $this->belongsToMany(Speciality::class);
    }
    //una plantilla puede contener muchas secciones, por lo tanto se relaciona con la tabla intermedia con secitons
    public function templateSections(){
        return $this->hasMany(TemplateSection::class);
    }
}
