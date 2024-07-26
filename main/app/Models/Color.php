<?php

namespace App\Models;

use App\Models\Cup;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $table = 'colors';
    protected $fillable = ['color'];

    /* RELATIONSHIPS */

    public function cup()
    {
        return $this->belongsTo(Cup::class, 'color_id', 'id');
    }
}
