<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AccountConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'configurable_id',
        'configurable_type',
        'retention_type_id',
        'income_account',
        'inventory_account',
        'expense_account',
        'cost_account',
        'entry_account',
        'sale_iva_account',
        'purchase_iva_account',
        'sale_discount_account',
        'purchase_discount_account',
        'retefuente_sale_account',
        'retefuente_purchase_account',
        'retefuente_percentage',
        'reteica_sale_account',
        'reteica_purchase_account',
        'reteica_percentage',
        'reteiva_sale_account',
        'reteiva_purchase_account',
        'reteiva_percentage',
    ];

    public static function boot()
    {
        parent::boot();
    
        static::saving(function ($model) {
            // Verificar si ya existe un registro con el mismo ID
            $existing = self::where('configurable_id', $model->configurable_id)
                ->where('configurable_type', $model->configurable_type)
                ->where('id', $model->id) // Verificar solo el ID del modelo actual
                ->exists();
    
            // Si no existe, verificar duplicados sin incluir el modelo actual
            if (!$existing) {
                $duplicate = self::where('configurable_id', $model->configurable_id)
                    ->where('configurable_type', $model->configurable_type)
                    ->where('id', '!=', $model->id) // Excluir el modelo actual
                    ->exists();
    
                if ($duplicate) {
                    throw new \Exception('No se puede guardar un registro duplicado.');
                }
            }
        });
    }
    


    public function configurable() 
    {
        return $this->morphTo();
    }

    public function incomeAccount()
    {
        return $this->belongsTo(PlanCuentas::class, 'income_account', 'Id_Plan_Cuentas');
    }

    public function inventoryAccount()
    {
        return $this->belongsTo(PlanCuentas::class, 'inventory_account', 'Id_Plan_Cuentas');
    }

    public function expenseAccount()
    {
        return $this->belongsTo(PlanCuentas::class, 'expense_account', 'Id_Plan_Cuentas');
    }

    public function costAccount()
    {
        return $this->belongsTo(PlanCuentas::class, 'cost_account', 'Id_Plan_Cuentas');
    }

    public function entryAccount()
    {
        return $this->belongsTo(PlanCuentas::class, 'entry_account', 'Id_Plan_Cuentas');
    }

    public function saleIvaAccount()
    {
        return $this->belongsTo(PlanCuentas::class, 'sale_iva_account', 'Id_Plan_Cuentas');
    }

    public function purchaseIvaAccount()
    {
        return $this->belongsTo(PlanCuentas::class, 'purchase_iva_account', 'Id_Plan_Cuentas');
    }

    public function saleDiscountAccount()
    {
        return $this->belongsTo(PlanCuentas::class, 'sale_discount_account', 'Id_Plan_Cuentas');
    }

    public function purchaseDiscountAccount()
    {
        return $this->belongsTo(PlanCuentas::class, 'purchase_discount_account', 'Id_Plan_Cuentas');
    }

    public function retefuenteSaleAccount()
    {
        return $this->belongsTo(PlanCuentas::class, 'retefuente_sale_account', 'Id_Plan_Cuentas');
    }

    public function retefuentePurchaseAccount()
    {
        return $this->belongsTo(PlanCuentas::class, 'retefuente_purchase_account', 'Id_Plan_Cuentas');
    }

    public function reteicaSaleAccount()
    {
        return $this->belongsTo(PlanCuentas::class, 'reteica_sale_account', 'Id_Plan_Cuentas');
    }

    public function reteicaPurchaseAccount()
    {
        return $this->belongsTo(PlanCuentas::class, 'reteica_purchase_account', 'Id_Plan_Cuentas');
    }

    public function reteivaSaleAccount()
    {
        return $this->belongsTo(PlanCuentas::class, 'reteiva_sale_account', 'Id_Plan_Cuentas');
    }

    public function reteivaPurchaseAccount()
    {
        return $this->belongsTo(PlanCuentas::class, 'reteiva_purchase_account', 'Id_Plan_Cuentas');
    }

    public function retentionType()
    {
        return $this->belongsTo(RetentionType::class);
    }

}
