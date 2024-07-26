<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationPurchaseRequest extends Model
{
    protected $fillable = [
        'product_purchase_request_id',
        'code',
        'format_code',
        'third_party_id',
        'total_price',
        'status',
        'file',
        'purchase_request_id',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function productPurchaseRequest()
    {
        return $this->belongsTo(ProductPurchaseRequest::class);
    }

    public function thirdParty()
    {
        return $this->belongsTo(ThirdParty::class, 'third_party_id')->fullName();
    }

}
