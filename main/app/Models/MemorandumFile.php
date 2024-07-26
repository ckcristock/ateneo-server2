<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemorandumFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'memorandum_id',
        'file',
        'name',
        'type',
    ];

    public function memorandum()
    {
        return $this->belongsTo(Memorandum::class);
    }
}
