<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenCompraNacional extends Model
{
    protected $table = 'Orden_Compra_Nacional';
    protected $primaryKey = 'Id_Orden_Compra_Nacional';
    protected $fillable = [
        'Codigo',
        'Identificacion_Funcionario',
        'Id_Bodega_Nuevo',
        'Id_Proveedor',
        'Observaciones',
        'Fecha_Entrega_Probable',
        'Fecha_Entrega_Real',
        'Tipo',
        'Estado',
        'Codigo_Qr',
        'Aprobacion',
        'Id_Pre_Compra',
        'Total',
        'Iva',
        'Subtotal',
        'format_code'
    ];

    public function scopeAlias($q, $alias)
    {
        return $q->from($q->getQuery()->from . " as " . $alias);
    }

    public function productos()
    {
        return $this->belongsToMany(Product::class, "Producto_Orden_Compra_Nacional", "Id_Orden_Compra_Nacional", "Id_Producto")
            ->withPivot('Id_Inventario', 'Costo', 'Cantidad', 'Iva', 'Total')->as('detalles')
            ->withTimestamps();
    }

    public function products()
    {
        return $this->hasMany(ProductoOrdenCompraNacional::class, 'Id_Orden_Compra_Nacional', 'Id_Orden_Compra_Nacional')
            ->with('product', 'tax');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'Identificacion_Funcionario')->fullName();
    }

    public function third()
    {
        return $this->belongsTo(ThirdParty::class, 'Id_Proveedor')/* ->with('reteica', 'reteiva', 'retefuente') */->fullName();
    }

    public function store()
    {
        return $this->belongsTo(Bodegas::class, 'Id_Bodega_Nuevo');
    }

    public function activity()
    {
        return $this->hasMany(ActividadOrdenCompra::class, 'Id_Orden_Compra_Nacional', 'Id_Orden_Compra_Nacional')
            ->orderBy('created_at', 'desc')
            ->with('person');
    }

    public function factura()
    {
        return $this->hasMany(FacturaActaRecepcion::class, 'Id_Orden_Compra', 'Id_Orden_Compra_Nacional');
    }

    public function acta()
    {
        return $this->hasOne(ActaRecepcion::class, 'Id_Orden_Compra_Nacional', 'Id_Orden_Compra_Nacional');
    }

    public function purchaseRequests()
    {
        return $this->belongsToMany(PurchaseRequest::class, 'orden_compra_nacional_purchase_request', 'id_orden_compra', 'id_purchase_request')
                    ->withPivot('status');
    }

    public function scopeFromMultipleRequests($query)
    {
        return $query->whereHas('purchaseRequests', function ($q) {
            $q->havingRaw('COUNT(id_purchase_request) > 0');
        });
    }

}
