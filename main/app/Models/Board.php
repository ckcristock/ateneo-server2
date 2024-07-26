<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $fillable = [
        'name_board',
    ];

    public function user()
    {
        return $this->hasOne(Usuario::class);
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }
}
