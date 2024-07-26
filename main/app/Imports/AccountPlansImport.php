<?php

namespace App\Imports;

use App\Models\AccountPlanBalance;
use App\Models\Person;
use App\Models\PlanCuentas;
use App\Models\PrettyCash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class AccountPlansImport implements ToCollection
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $rows)
    {
        $planData = [];
        $accountPlanBalanceData = [];
        $prettyCashData = [];
        $idCounter = 0;
        $companyWorkedId = Person::find(auth()->user()->person_id)['company_worked_id'];
        foreach ($rows as $index => $row) {
            if ($index != 0) {
                $Tipo_P = '';
                $Movimiento = 'N';
                $length = strlen(strval($row[0]));
                $Tipo_P = match (true) {
                    $length == 1 => 'CLASE',
                    $length == 2 => 'GRUPO',
                    $length == 4 => 'CUENTA',
                    $length == 6 => 'SUBCUENTA',
                    $length >= 8 => 'AUXILIAR',
                    default => null,
                };
                if ($length >= 8) {
                    $Movimiento = 'S';
                }
                $planData[] = [
                    'Codigo' => $row[0],
                    'Codigo_Padre' => intval($row[2]),
                    'Nombre' => trim($row[1]),
                    'Codigo_Niif' => $row[0],
                    'Nombre_Niif' => trim($row[1]),
                    'Tipo_Niif' => $Tipo_P,
                    'Tipo_P' => $Tipo_P,
                    'Movimiento' => $Movimiento,
                    'company_id' => $companyWorkedId
                ];

                $accountPlanBalanceData[] = [
                    'balance' => 0,
                    'account_plan_id' => ++$idCounter
                ];

                if (strval($row[2]) === '110510') {
                    $prettyCashData[] = [
                        'user_id' => auth()->user()->id,
                        'initial_balance' => 0,
                        'description' => trim($row[1]),
                        'status' => 'Inactiva',
                        'account_plan_id' => $idCounter
                    ];
                }
            }
        }

        PlanCuentas::insert($planData);

        AccountPlanBalance::insert($accountPlanBalanceData);

        PrettyCash::insert($prettyCashData);
    }
}
