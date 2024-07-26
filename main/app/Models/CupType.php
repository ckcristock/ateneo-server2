<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CupType extends Model
{
    protected $table = "cups_type";

    protected $fillable = ['name', 'color_id'];

    protected $timestamps = false;

    /* RELATIONSHIPS */

    public function color()
    {
        return $this->hasOne(Color::class, 'color_id');
    }
}
