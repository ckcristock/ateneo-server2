<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraHourReport extends Model
{
    protected $guarded = ['id'];
    protected $fillable = [
        'person_id',
        'date',
        'ht',
        'hed',
        'hen',
        'heddf',
        'hendf',
        'hrndf',
        'hrn',
        'hrddf',
        'hed_reales',
        'hen_reales',
        'hedfd_reales',
        'hedfn_reales',
        'rn_reales',
        'rf_reales',
        'rnf_reales'
    ];
}
