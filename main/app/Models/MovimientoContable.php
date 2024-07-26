<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MovimientoContable extends Model
{
    protected $table = 'Movimiento_Contable';
    protected $primaryKey = 'Id_Movimiento_Contable';
    public $timestamps = true;

    protected $fillable = [
        'Id_Plan_Cuenta',
        'Fecha_Movimiento',
        'Id_Modulo',
        'Id_Registro_Modulo',
        'Debe',
        'Haber',
        'Debe_Niif',
        'Haber_Niif',
        'Nit',
        'Tipo_Nit',
        'Estado',
        'Documento',
        'Detalles',
        'Fecha_Registro',
        'Id_Centro_Costo',
        'Mantis',
        'Numero_Comprobante',
    ];

    protected $casts = [
        'Fecha_Movimiento' => 'datetime',
        'Fecha_Registro' => 'datetime',
    ];

    public function planCuenta()
    {
        return $this->belongsTo(AccountPlan::class, 'Id_Plan_Cuenta', 'Id_Plan_Cuentas');
    }

    public static function listaCartera($nit, $idPlanCuenta = null, $fecha = null)
    {
        $query = self::select(
            'Movimiento_Contable.Id_Plan_Cuenta',
            'Plan_Cuentas.Codigo',
            'Plan_Cuentas.Nombre',
            DB::raw("DATE_FORMAT(Movimiento_Contable.Fecha_Movimiento, '%d/%m/%Y') AS Fecha"),
            'Movimiento_Contable.Documento AS Factura',
            'Movimiento_Contable.Id_Registro_Modulo AS Id_Factura',
            DB::raw("CASE Plan_Cuentas.Naturaleza WHEN 'C' THEN SUM(Movimiento_Contable.Haber) ELSE SUM(Movimiento_Contable.Debe) END AS Valor_Factura"),
            DB::raw("CASE Plan_Cuentas.Naturaleza WHEN 'C' THEN SUM(Movimiento_Contable.Debe) ELSE SUM(Movimiento_Contable.Haber) END AS Valor_Abono"),
            DB::raw("CASE Plan_Cuentas.Naturaleza WHEN 'C' THEN SUM(Movimiento_Contable.Haber) - SUM(Movimiento_Contable.Debe) ELSE SUM(Movimiento_Contable.Debe) - SUM(Movimiento_Contable.Haber) END AS Valor_Saldo"),
            'Plan_Cuentas.Naturaleza AS Nat',
            DB::raw("SUM(Movimiento_Contable.Debe) AS Movimiento_Debito"),
            DB::raw("SUM(Movimiento_Contable.Haber) AS Movimiento_Credito"),
            DB::raw("CASE
                        WHEN Plan_Cuentas.Naturaleza = 'D' AND SUM(Movimiento_Contable.Debe) > SUM(Movimiento_Contable.Haber) THEN 'C'
                        WHEN Plan_Cuentas.Naturaleza = 'D' AND SUM(Movimiento_Contable.Haber) > SUM(Movimiento_Contable.Debe) THEN 'D'
                        WHEN Plan_Cuentas.Naturaleza = 'C' AND SUM(Movimiento_Contable.Haber) > SUM(Movimiento_Contable.Debe) THEN 'D'
                        WHEN Plan_Cuentas.Naturaleza = 'C' AND SUM(Movimiento_Contable.Debe) > SUM(Movimiento_Contable.Haber) THEN 'C'
                    END AS Movimiento"),
            DB::raw('0 AS Abono')
        )
            ->join('Plan_Cuentas', 'Movimiento_Contable.Id_Plan_Cuenta', '=', 'Plan_Cuentas.Id_Plan_Cuentas')
            ->where('Movimiento_Contable.Nit', $nit)
            ->where('Movimiento_Contable.Estado', '!=', 'Anulado')
            //! A falta de revisión puntual, se dejan estos condicionales, es posible que esto se deba cambiar en el futuro
            ->where(function ($query) {
                $query->where('Plan_Cuentas.Codigo', 'like', '2335%')
                    ->orWhere('Plan_Cuentas.Codigo', 'like', '220501')
                    ->orWhere('Plan_Cuentas.Codigo', 'like', '13%');
            })
            ->where('Plan_Cuentas.Codigo', 'not like', '1355%');

        if ($idPlanCuenta) {
            $query->where('Movimiento_Contable.Id_Plan_Cuenta', $idPlanCuenta);
        }

        if ($fecha) {
            $query->whereDate('Movimiento_Contable.Fecha_Movimiento', '<=', $fecha);
        }

        $query->groupBy('Movimiento_Contable.Id_Plan_Cuenta', 'Movimiento_Contable.Documento')
            ->having('Valor_Saldo', '!=', 0)
            ->orderBy('Movimiento_Contable.Fecha_Movimiento');

        return $query->get();
    }
}
