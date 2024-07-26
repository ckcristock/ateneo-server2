<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestActivity extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'user_id',
        'date',
        'details',
        'status'
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'user_id')->fullName();
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }
}
