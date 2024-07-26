<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accommodation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'company_id'
    ];

    public function hotels()
    {
        return $this->belongsToMany(Hotel::class);
    }

}