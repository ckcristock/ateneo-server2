<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateSection extends Model
{
    use HasFactory;
    protected $table = "template_section";

    protected $fillable = [
        'template_id',
        'name',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    //las secciones son los apartados que contendran las variables, por lo tanto una plantilla o template puede tener muchas secciones
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
