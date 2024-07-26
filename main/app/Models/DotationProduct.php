<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DotationProduct extends Model
{

    protected $fillable = [
        'dotation_id',
        'inventary_dotation_id',
        'quantity',
        'cost',
        'code',
        'product_id',
    ];

    public function inventary_dotation()
    {
        return $this->belongsTo(InventaryDotation::class);
    }

    public function inventaryDotation()
    {
        return $this->belongsTo(InventaryDotation::class, 'inventary_dotation_id');
    }
}
