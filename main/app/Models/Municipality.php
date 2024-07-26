<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    protected $fillable = [
        'name',
        'abbreviation',
        'department_id',
        'code',
        'dian_code',
        'dane_code',
        'municipalities_id',
        //'percentage_product',
        //'percentage_service',
        'state'
    ];


    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function department_()
    {
        return $this->hasOne(Department::class, 'id', 'department_id')->with('country');
    }
}
