<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventaryDotation extends Model
{
    protected $fillable = [
        'product_id',
        'product_dotation_type_id',
        'name',
        'code',
        'type',
        'status',
        'cost',
        'company_id',
        'stock',
        'size',
    ];

    public function dotacionProducto()
    {
        return $this->hasMany(DotationProduct::class);
    }

    public function product_datation_types()
    {
        return $this->hasOne(ProductDotationType::class, 'id', 'product_dotation_type_id');
    }

    public function productDotationType()
    {
        return $this->belongsTo(ProductDotationType::class, 'product_dotation_type_id');
    }
}
