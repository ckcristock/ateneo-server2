<?php

namespace App\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class RCuentaMedica extends Model
{
    public function tercero()
    {
        // if ($this->type == 1) {
        return $this->belongsTo(Company::class, 'third_part_id', 'tin');
        // }
        // if ($this->type == 2) {
        // return $this->belongsTo(Company::class, 'third_part_id');
        // }
    }
}
