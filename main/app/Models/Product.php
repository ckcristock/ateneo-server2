<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'Producto';
    protected $primaryKey = 'Id_Producto';

    protected $fillable = [
        'Id_Producto',
        'Principio_Activo',
        'Presentacion',
        'Concentracion',
        'Nombre_Comercial',
        'Nombre_General',
        'Embalaje',
        'Laboratorio_Generico',
        'Laboratorio_Comercial',
        'Familia',
        'Codigo_Cum',
        'Cantidad_Minima',
        'Cantidad_Maxima',
        'ATC',
        'Descripcion_ATC',
        'Invima',
        'Fecha_Expedicion_Invima',
        'Fecha_Vencimiento_Invima',
        'Precio_Minimo',
        'Precio_Maximo',
        'Precio',
        'impuesto_id',
        'Embalaje_id',
        'Tipo_Regulacion',
        'Tipo_Pos',
        'Via_Administracion',
        'Unidad_Medida',
        'Cantidad',
        'Regulado',
        'Tipo',
        'Peso_Presentacion_Minima',
        'Peso_Presentacion_Regular',
        'Peso_Presentacion_Maxima',
        'Codigo_Barras',
        'Cantidad_Presentacion',
        'Mantis',
        'Imagen',
        'Id_Categoria',
        'Nombre_Listado',
        'Referencia',
        'Gravado',
        'RotativoC',
        'RotativoD',
        'Tolerancia',
        'Actualizado',
        'Unidad_Empaque',
        'Porcentaje_Arancel',
        'Forma_Farmaceutica',
        'Estado',
        'Estado_DIAN_Covid19',
        'Id_Subcategoria',
        'Tipo_Catalogo',
        'Orden_Compra',
        'Producto_Dotation_Type_Id',
        'Producto_Dotacion_Tipo',
        'CantUnMinDis',
        'Id_Tipo_Activo_Fijo',
        'Orden_Compra'
    ];

    protected $appends = ['name'];
    public function getNameAttribute()
    {
        return $this->Nombre_Comercial;
    }

    public function accountConfiguration()
    {
        return $this->morphOne(AccountConfiguration::class, 'configurable');
    }


    public function scopeAlias($q, $alias)
    {
        return $q->from($q->getQuery()->from . " as " . $alias);
    }

    public function ordenes_compra_nacionales()
    {
        return $this->belongsToMany(OrdenCompraNacional::class, "Producto_Orden_Compra_Nacional", "Id_Producto", "Id_Orden_Compra_Nacional")
            ->withPivot('Id_Inventario', 'Costo', 'Cantidad', 'Iva', 'Total')->as('detalles')
            ->withTimestamps();
    }

    /* public function unit()
    {
        return $this->hasOne(Unit::class, 'id', 'Unidad_Medida');
    } */


    public function unit()
    {
        return $this->hasOne(Unit::class, 'id', 'Unidad_Medida')
            ->select(['*', 'id as value', 'name as text']);
    }


    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'Id_Subcategoria', 'Id_Subcategoria');
    }

    public function category()
    {
        return $this->belongsTo(CategoriaNueva::class, 'Id_Categoria', 'Id_Categoria_Nueva');
    }

    public function activity()
    {
        return $this->hasMany(ActividadProducto::class, 'Id_Producto', 'Id_Producto')->with('funcionario')->orderByDesc('Id_Actividad_Producto');
    }

    public function variables()
    {
        return $this->hasMany(VariableProduct::class, 'product_id', 'Id_Producto')->with('categoryVariables', 'subCategoryVariables');
    }

    // solo para actualizar medicamentos
    public function variablesProducts()
    {
        return $this->hasMany(VariableProduct::class, 'product_id', 'Id_Producto');
    }

    public function unitProduct()
    {
        return $this->belongsTo(Unit::class, 'Unidad_Medida');
    }
    //fin actualizar medicamentos

    public function packaging()
    {
        return $this->belongsTo(Packaging::class, 'Embalaje_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'impuesto_id', 'Id_Impuesto');
    }

    public function variableProducts()
    {
        return $this->hasMany(VariableProduct::class, 'product_id', 'Id_Producto')
        ->whereHas('categoryVariable', function (Builder $query) {
            $query->where('lists', 1);
        });
    }

    public function inventarioNuevo()
    {
        return $this->hasMany(InventarioNuevo::class, 'Id_Producto', 'Id_Producto');
    }

    public function variableProductsSinRecepcion()
    {
        return $this->hasMany(VariableProduct::class, 'product_id', 'Id_Producto')
        ->whereHas('categoryVariable', function (Builder $query) {
            $query->where('lists', 1)
            ->where('reception', 0);
        });
    }
    public function impuesto()
    {
        return $this->belongsTo(Impuesto::class, 'impuesto_id', 'Id_Impuesto');
    }
}



