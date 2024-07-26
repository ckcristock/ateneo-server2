<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttentionRoute extends Model
{
    protected $table ="attention_routes";
    protected $fillable = [
        "name",
        "id_type_service",
        "age",
        "age_min",
        "age_max",
        "gender",
    ];
}
