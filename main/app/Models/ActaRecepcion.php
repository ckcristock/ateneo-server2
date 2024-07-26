<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActaRecepcion extends Model
{

    protected $table = 'Acta_Recepcion';
    protected $primaryKey = 'Id_Acta_Recepcion';
    protected $fillable = [
        'Id_Bodega',
        'Id_Bodega_Nuevo',
        'Id_Punto_Dispensacion',
        'Identificacion_Funcionario',
        'Factura',
        'Fecha_Factura',
        'Observaciones',
        'Codigo',
        'Fecha_Creacion',
        'Codigo_Qr_Real',
        'Id_Proveedor',
        'Tipo',
        'Tipo_Acta',
        'Id_Orden_Compra_Nacional',
        'Id_Orden_Compra_Internacional',
        'Estado',
        'Id_Causal_Anulacion',
        'Observaciones_Anulacion',
        'Funcionario_Anula',
        'Fecha_Anulacion',
        'Codigo_Qr',
        'company_id'
    ];

    public function bodega()
    {
        return $this->belongsTo(Bodegas::class, 'Id_Bodega_Nuevo', 'Id_Bodega_Nuevo');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'Identificacion_Funcionario');
    }

    public function third()
    {
        return $this->belongsTo(ThirdParty::class, 'Id_Proveedor')->fullName();
    }

    public function causal()
    {
        return $this->belongsTo(CausalAnulacion::class, 'Id_Causal_Anulacion');
    }

    public function facturas()
    {
        return $this->hasMany(FacturaActaRecepcion::class, 'Id_Acta_Recepcion', 'Id_Acta_Recepcion');
    }

    public function products()
    {
        return $this->hasMany(ProductoActaRecepcion::class, 'Id_Acta_Recepcion', 'Id_Acta_Recepcion')->with('product', 'factura');
    }

    public function orden()
    {
        return $this->belongsTo(OrdenCompraNacional::class, 'Id_Orden_Compra_Nacional', 'Id_Orden_Compra_Nacional');
    }
    public function productos()
    {
        return $this->hasMany(ProductoActaRecepcion::class, 'Id_Acta_Recepcion');
    }
    public function productosActaRecepcion()
    {
        return $this->hasMany(ProductoActaRecepcion::class, 'Id_Acta_Recepcion', 'Id_Acta_Recepcion');
    }

    // Relación con Remision
    public function remision()
    {
        return $this->hasOne(Remision::class, 'Id_Origen', 'Id_Acta_Recepcion');
    }

    public function bodegaNuevo()
    {
        return $this->belongsTo(BodegaNuevo::class, 'Id_Bodega_Nuevo', 'Id_Bodega_Nuevo');
    }

    public function puntoDispensacion()
    {
        return $this->belongsTo(PuntoDispensacion::class, 'Id_Punto_Dispensacion', 'Id_Punto_Dispensacion');
    }

    public function proveedor()
    {
        return $this->belongsTo(ThirdParty::class, 'Id_Proveedor', 'id')->where('is_supplier', true);
    }
}
