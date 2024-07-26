<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'category_id',
        'expected_date',
        'observations',
        'quantity_of_products',
        'status',
        'code',
        'format_code',
        'user_id',
        'dispensation_point_id',
    ];

    public function productPurchaseRequest()
    {
        return $this->hasMany(ProductPurchaseRequest::class)->with('product', 'quotation');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'user_id')->fullName()->with('contractultimate');
    }

    public function quotationPurchaseRequest()
    {
        return $this->hasMany(QuotationPurchaseRequest::class, 'purchase_request_id', 'id');
    }

    public function activity()
    {
        return $this->hasMany(PurchaseRequestActivity::class, 'purchase_request_id', 'id')->with('person')->orderByDesc('created_at');
    }
    public function ordenesCompraNacional()
    {
        return $this->belongsToMany(OrdenCompraNacional::class, 'orden_compra_nacional_purchase_request', 'id_purchase_request' , 'id_orden_compra' )
            ->withPivot('status');
    }

    public function user()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
    public function dispensationPoint()
    {
        return $this->belongsTo(PuntoDispensacion::class, 'dispensation_point_id');
    }

}
