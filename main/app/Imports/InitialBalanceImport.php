<?php

namespace App\Imports;

use App\Models\AccountPlanBalance;
use App\Models\PlanCuentas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class InitialBalanceImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if ($index != 0) {
                $plan = PlanCuentas::where('Codigo_Niif', $row[0])->first();
                if ($plan) {
                    AccountPlanBalance::updateOrCreate(['account_plan_id' => $plan->Id_Plan_Cuentas], [
                        'balance' => $row[1]
                    ]);
                }
            }
        }
    }
}
