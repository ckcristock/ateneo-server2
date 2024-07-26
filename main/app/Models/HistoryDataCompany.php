<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryDataCompany extends Model
{
    protected $fillable = [
        'namespace',
        'data_name',
        'date_end',
        'value',
        'person_id',
        'company_id'
    ];

    public function Person()
    {
        return $this->belongsTo(Person::class)->completeName();
    }
}
