<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountPlan extends Model
{
    //use HasFactory;

    protected $table = 'Plan_Cuentas';

    protected $primaryKey = 'Id_Plan_Cuentas';

    protected $fillable = [
        'Tipo_P',
        'Tipo_Niif',
        'Codigo',
        'Nombre',
        'Codigo_Niif',
        'Nombre_Niif',
        'Estado',
        'Ajuste_Contable',
        'Cierra_Terceros',
        'Movimiento',
        'Documento',
        'Base',
        'Valor',
        'Porcentaje',
        'Centro_Costo',
        'Depreciacion',
        'Amortizacion',
        'Exogeno',
        'Naturaleza',
        'Maneja_Nit',
        'Cie_Anual',
        'Nit_Cierre',
        'Banco',
        'Cod_Banco',
        'Nit',
        'Clase_Cta',
        'Cta_Numero',
        'Reporte',
        'Niif',
        'Porcentaje_Real',
        'Tipo_Cierre_Mensual',
        'Tipo_Cierre_Anual',
    ];

    /* protected $table = 'account_plan'; */
    public function balance()
    {
        return $this->hasOne(AccountPlanBalance::class);
    }

    public function movimientosContables()
    {
        return $this->hasMany(MovimientoContable::class, 'Id_Plan_Cuenta', 'Id_Plan_Cuentas');
    }

    public function cuentaHorasExtras()
    {
        return $this->hasMany(PayrollOvertime::class, 'account_plan_id', 'Codigo');
    }

    public function cuentaSegSocialFunc()
    {
        return $this->hasMany(PayrollSocialSecurityPerson::class, 'account_plan_id', 'Codigo');
    }

    public function cuentaSegSocialEmpresa()
    {
        return $this->hasMany(PayrollSocialSecurityCompany::class, 'account_plan_id', 'Codigo');
    }

    public function cuentaRiesgos()
    {
        return $this->hasMany(PayrollRisksArl::class, 'account_plan_id', 'Codigo');
    }

    public function cuentaParafiscales()
    {
        return $this->hasMany(PayrollParafiscal::class, 'account_plan_id', 'Codigo');
    }

    public function cuentaNovedades()
    {
        return $this->hasMany(DisabilityLeave::class, 'account_plan_id', 'Codigo');
    }

    public function cuentaIncapacidades()
    {
        return $this->hasMany(PayrollDisabilityLeave::class, 'accounting_account', 'Codigo');
    }

    public function cuentaIngresos()
    {
        return $this->hasMany(CountableIncome::class, 'accounting_account', 'Codigo_Niif');
    }

    public function cuentaEgresos()
    {
        return $this->hasMany(CountableDeduction::class, 'accounting_account', 'Codigo_Niif');
    }

    public function cuentaLiquidaciones()
    {
        return $this->hasMany(CountableLiquidation::class, 'account_plan_id', 'Codigo_Niif');
    }

    public function cuentaSalarios()
    {
        return $this->hasMany(CountableSalary::class, 'account_plan_id', 'Codigo_Niif');
    }

    public function contrapartidaSegSocialFunc()
    {
        return $this->hasMany(DisabilityLeave::class, 'account_setoff', 'Codigo_Niif');
    }

    public function contrapartidaSegSocialEmpresa()
    {
        return $this->hasMany(DisabilityLeave::class, 'account_setoff', 'Codigo_Niif');
    }

    public function contrapartidaRiesgosArl()
    {
        return $this->hasMany(DisabilityLeave::class, 'account_setoff', 'Codigo_Niif');
    }

    public function contrapartidaParafiscales()
    {
        return $this->hasMany(DisabilityLeave::class, 'account_setoff', 'Codigo_Niif');
    }
}
