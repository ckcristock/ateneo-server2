<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
use App\Models\PayrollPayment;
use App\Models\Person;
use App\Models\SeveranceInterestPayment;
use App\Models\SeverancePayment;
use App\Traits\ApiResponser;
use Carbon\Carbon;

class ProvisionController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function index()
    {
        $now = now();
        $currentYear = Carbon::now()->year;
        if ($now->month >= 1 && $now->month <= 6) {
            $bonus = Bonus::whereBetween('created_at', [
                $now->subYear()->month(12)->day(1)->format('Y-m-d H:i:s'),
                $now->subYear()->month(12)->day(20)->format('Y-m-d H:i:s')
            ])
                ->where('company_id', $this->getCompany())
                ->first();
        } else {
            $bonus = Bonus::whereBetween('created_at', [
                $now->month(6)->day(1)->startOfDay()->format('Y-m-d H:i:s'),
                $now->month(6)->day(30)->endOfDay()->format('Y-m-d H:i:s')
            ])
                ->where('company_id', $this->getCompany())
                ->first();
        }

        $severanceInterestPayments = SeveranceInterestPayment::where('year', $currentYear)
            ->where('company_id', $this->getCompany())
            ->first();

        $severancePayments = SeverancePayment::where('year', $currentYear)
            ->where('company_id', $this->getCompany())
            ->first();

        $vacations = PayrollPayment::where([
            ['start_period', '>', $now->month(1)->day(1)->startOfDay()->format('Y-m-d'),],
            ['company_id', '=', $this->getCompany()],
        ])
            ->with('provisionsPersonPayrollPayment')
            ->get();

        $tempVacations = $vacations->map(function ($item) {
            $provisions = $item->provisionsPersonPayrollPayment ?? [];
            return collect($provisions)->sum(function ($provision) {
                return $provision->vacations ?? 0;
            });
        });

        $totalVacations = $tempVacations->sum();
        $bonusRate = $bonus ? $bonus->total_bonuses : 0;
        $interestRate = $severanceInterestPayments ? $severanceInterestPayments->total : 0;
        $paymentRate = $severancePayments ? $severancePayments->total : 0;
        $total = $bonusRate + $interestRate + $paymentRate;
        return $this->success([
            'bonus' => $bonusRate,
            'severanceInterest' => $interestRate,
            'severance' => $paymentRate,
            'vacations' => $totalVacations,
            'total' => $total
        ]);
    }
}
