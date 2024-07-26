<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Color;

class Cup extends Model
{
    protected $fillable = [
        'code',
        'description',
        'speciality',
        'nickname',
        'type_service_id',
        'color_id'
    ];

    /* RELATIONSHIPS */
    public function specialities()
    {
        return $this->belongsToMany(Speciality::class);
    }

    public function colors()
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function type_service()
    {
        return $this->belongsToMany(TypeService::class);
    }
}
