<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductDotationType extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'company_id'];

    public function inventary()
    {
        return $this->hasMany(InventaryDotation::class)->where('stock', '>', '0');
    }
}
