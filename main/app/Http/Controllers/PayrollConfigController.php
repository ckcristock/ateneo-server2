<?php

namespace App\Http\Controllers;

use App\Models\CompanyCountableLiquidation;
use App\Models\CompanyCountableSalary;
use App\Models\CompanyPayrollOvertime;
use App\Models\CompanyPayrollParafiscal;
use App\Models\CompanyPayrollRisksArl;
use App\Models\CompanyPayrollSocialSecurityCompany;
use App\Models\CompanyPayrollSocialSecurityPerson;
use App\Models\CountableDeduction;
use App\Models\CountableIncome;
use App\Models\CountableLiquidation;
use App\Models\CountableSalary;
use App\Models\DisabilityLeave;
use App\Models\PayrollManager;
use App\Models\PayrollOvertime;
use App\Models\PayrollParafiscal;
use App\Models\PayrollRisksArl;
use App\Models\PayrollSocialSecurityCompany;
use App\Models\PayrollSocialSecurityPerson;
use App\Models\PayrollDisabilityLeave;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class PayrollConfigController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function payrollManagers()
    {
        return $this->success(PayrollManager::with('companyPayroll')->get());
    }

    public function getParametrosNomina()
    {
        //$responsables = PayrollManager::with('companyPayroll')->get();

        $salarios = CountableSalary::with('accounts')->get();

        $extras = PayrollOvertime::with('accounts')->get();

        $segSocFunc = PayrollSocialSecurityPerson::with('accounts')->get();

        $segSocEmp = PayrollSocialSecurityCompany::with('accounts')->get();

        $riesgos = PayrollRisksArl::with('accounts')->get();

        $parafiscales = PayrollParafiscal::with('accounts')->get();

        $novedades = DisabilityLeave::with('accounts')->get();

        $ingresos = CountableIncome::with('accounts')
            ->whereHas('company', function ($query) {
                $query->where('id', Person::find(Auth()->user()->person_id)->company_worked_id);
            })
            ->get()
            ->map(function ($ingreso) {
                if ($ingreso->accounts) {
                    $account_plan = clone $ingreso->accounts;
                    $ingreso->accounts->account_plan = $account_plan;
                }
                return $ingreso;
            });

        $egresos = CountableDeduction::with('accounts')
            ->whereHas('company', function ($query) {
                $query->where('id', Person::find(Auth()->user()->person_id)->company_worked_id);
            })
            ->get()
            ->map(function ($ingreso) {
                if ($ingreso->accounts) {
                    $account_plan = clone $ingreso->accounts;
                    $ingreso->accounts->account_plan = $account_plan;
                }
                return $ingreso;
            });

        $liquidacion = CountableLiquidation::with('accounts')->get();

        return $this->success([
            //'responsables' => $responsables,
            'salarios' => $salarios,
            'extras' => $extras,
            'segSocFunc' => $segSocFunc,
            'segSocEmp' => $segSocEmp,
            'riesgos' => $riesgos,
            'parafiscales' => $parafiscales,
            'novedades' => $novedades,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'liquidacion' => $liquidacion,
        ]);
    }

    public function horasExtrasDatos()
    {
        /*  $horasExtras =PayrollOvertime::all(); //Evaluar si el id relacional es igual
        foreach ($horasExtras as $horaE) {
            $cuenta = $this->consultaAPI($horaE->account_plan_id);//son varias consultas a la DB, optimizar
            if (gettype($cuenta)=="array" && !empty($cuenta)){
                $horaE->cuenta_contable = $cuenta[0];
            }
        }
        return $this->success($horasExtras); */
        return PayrollOvertime::with('cuentaContable:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif')->get();
    }

    public function incapacidadesDatos()
    {
        return PayrollDisabilityLeave::with('cuentaContable:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif')->get();
    }

    /**
     * consulta que tarda 3seg o 5,95seg (09-11-22)
     * porque consulta api de PHP, mejorar
     * dy
     * */
    public function novedadesList()
    {
        $novedades = DisabilityLeave::with('cuentaContable:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif')->get();
        /* foreach ($novedades as $novedad) {
            $cuenta = $this->consultaAPI($novedad->accounting_account);//son varias consultas a la DB, optimizar
            if (gettype($cuenta)=="array" && !empty($cuenta)){
                $novedad->cuenta_contable = $cuenta[0];
            }
        } */
        return $this->success($novedades);
    }

    private function consultaAPI($coincidencia = '', $tipo = '') //tipo es pcga o nada
    {
        $direcccion = 'http://inventario.sigmaqmo.com/php/plancuentas/filtrar_cuentas.php?coincidencia=' . $coincidencia . '&tipo=' . $tipo . '';
        $data = json_decode(file_get_contents($direcccion), false, 3);
        return $data;
    }

    public function parafiscalesDatos()
    {
        return PayrollParafiscal::with(
            'cuentaContable:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif',
            'contrapartida:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif'
        )->get();
    }

    public function riesgosArlDatos()
    {
        return PayrollRisksArl::with(
            'cuentaContable:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif',
            'contrapartida:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif'
        )->get();
    }

    public function sSocialEmpresaDatos()
    {
        return PayrollSocialSecurityCompany::with(
            'cuentaContable:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif',
            'contrapartida:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif'
        )->get();
    }

    public function sSocialFuncionarioDatos()
    {
        return PayrollSocialSecurityPerson::with(
            'cuentaContable:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif',
            'contrapartida:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif'
        )->get();
    }

    public function incomeDatos()
    {
        return CountableIncome::with('cuentaContable:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif')->get();
    }

    public function deductionsDatos()
    {
        return CountableDeduction::with('cuentaContable:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif')->get();
    }

    public function liquidationsDatos()
    {
        return CountableLiquidation::with('cuentaContable:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif')->get();
    }

    public function SalariosSubsidiosDatos()
    {
        return CountableSalary::with(
            'cuentaContable:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif',
            'contrapartida:Id_Plan_Cuentas,Codigo_Niif,Nombre_Niif'
        )->get();
    }

    /*
     * Updates
     */
    public function horasExtrasUpdate($id, Request $request)
    {
        try {
            if ($request->id) {
                $companPayrollOvertime = CompanyPayrollOvertime::where([
                    ['payroll_overtime_id', '=', $request->id],
                    ['company_id', '=', $this->getCompany()],
                ])->first();

                if ($companPayrollOvertime) {
                    if ($request->account_plan_id) {
                        $companPayrollOvertime->update([
                            'account_plan_id' => $request->account_plan_id,
                        ]);
                    } else if ($request->percentage) {
                        $companPayrollOvertime->update([
                            'percentage' => $request->percentage,
                        ]);
                    }
                    return $this->success('Actualizado con éxito');
                } else {
                    if ($request->account_plan_id) {
                        CompanyPayrollOvertime::create([
                            'payroll_overtime_id' => $request->id,
                            'account_plan_id' => $request->account_plan_id,
                            'company_id' => $this->getCompany()
                        ]);
                    } else if ($request->percentage) {
                        CompanyPayrollOvertime::create([
                            'payroll_overtime_id' => $request->id,
                            'percentage' => $request->percentage,
                            'company_id' => $this->getCompany()
                        ]);
                    }
                    return $this->success('Asignado con éxito');
                }
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }
    }

    public function sSocialPerson($id, Request $request)
    {
        try {
            if ($request->id) {
                $companPayrollSocialSecurityPerson = CompanyPayrollSocialSecurityPerson::where([
                    ['payroll_social_security_person_id', '=', $request->id],
                    ['company_id', '=', $this->getCompany()],
                ])->first();

                if ($companPayrollSocialSecurityPerson) {
                    if ($request->account_plan_id) {
                        $companPayrollSocialSecurityPerson->update([
                            'account_plan_id' => $request->account_plan_id,
                        ]);
                    } else if ($request->percentage) {
                        $companPayrollSocialSecurityPerson->update([
                            'percentage' => $request->percentage,
                        ]);
                    } else if ($request->account_setoff) {
                        $companPayrollSocialSecurityPerson->update([
                            'account_setoff' => $request->account_setoff,
                        ]);
                    }
                    return $this->success('Actualizado con éxito');
                } else {
                    if ($request->account_plan_id) {
                        CompanyPayrollSocialSecurityPerson::create([
                            'payroll_social_security_person_id' => $request->id,
                            'account_plan_id' => $request->account_plan_id,
                            'company_id' => $this->getCompany()
                        ]);
                    } else if ($request->percentage) {
                        CompanyPayrollSocialSecurityPerson::create([
                            'payroll_social_security_person_id' => $request->id,
                            'percentage' => $request->percentage,
                            'company_id' => $this->getCompany()
                        ]);
                    } else if ($request->account_setoff) {
                        CompanyPayrollSocialSecurityPerson::create([
                            'payroll_social_security_person_id' => $request->id,
                            'account_setoff' => $request->account_setoff,
                            'company_id' => $this->getCompany()
                        ]);
                    }
                    return $this->success('Asignado con éxito');
                }
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }
    }

    public function sSocialCompany($id, Request $request)
    {
        try {
            if ($request->id) {
                $companPayrollSocialSecurityCompany = CompanyPayrollSocialSecurityCompany::where([
                    ['payroll_social_security_company_id', '=', $request->id],
                    ['company_id', '=', $this->getCompany()],
                ])->first();

                if ($companPayrollSocialSecurityCompany) {
                    if ($request->account_plan_id) {
                        $companPayrollSocialSecurityCompany->update([
                            'account_plan_id' => $request->account_plan_id,
                        ]);
                    } else if ($request->percentage) {
                        $companPayrollSocialSecurityCompany->update([
                            'percentage' => $request->percentage,
                        ]);
                    } else if ($request->account_setoff) {
                        $companPayrollSocialSecurityCompany->update([
                            'account_setoff' => $request->account_setoff,
                        ]);
                    }
                    return $this->success('Actualizado con éxito');
                } else {
                    if ($request->account_plan_id) {
                        CompanyPayrollSocialSecurityCompany::create([
                            'payroll_social_security_company_id' => $request->id,
                            'account_plan_id' => $request->account_plan_id,
                            'company_id' => $this->getCompany()
                        ]);
                    } else if ($request->percentage) {
                        CompanyPayrollSocialSecurityCompany::create([
                            'payroll_social_security_company_id' => $request->id,
                            'percentage' => $request->percentage,
                            'company_id' => $this->getCompany()
                        ]);
                    } else if ($request->account_setoff) {
                        CompanyPayrollSocialSecurityCompany::create([
                            'payroll_social_security_company_id' => $request->id,
                            'account_setoff' => $request->account_setoff,
                            'company_id' => $this->getCompany()
                        ]);
                    }
                    return $this->success('Asignado con éxito');
                }
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }
    }

    public function riesgosArlUpdate($id, Request $request)
    {
        try {
            if ($request->id) {
                $companPayrollRiskArl = CompanyPayrollRisksArl::where([
                    ['payroll_risks_arl_id', '=', $request->id],
                    ['company_id', '=', $this->getCompany()],
                ])->first();

                if ($companPayrollRiskArl) {
                    if ($request->account_plan_id) {
                        $companPayrollRiskArl->update([
                            'account_plan_id' => $request->account_plan_id,
                        ]);
                    } else if ($request->percentage) {
                        $companPayrollRiskArl->update([
                            'percentage' => $request->percentage,
                        ]);
                    } else if ($request->account_setoff) {
                        $companPayrollRiskArl->update([
                            'account_setoff' => $request->account_setoff,
                        ]);
                    }
                    return $this->success('Actualizado con éxito');
                } else {
                    if ($request->account_plan_id) {
                        CompanyPayrollRisksArl::create([
                            'payroll_risks_arl_id' => $request->id,
                            'account_plan_id' => $request->account_plan_id,
                            'company_id' => $this->getCompany()
                        ]);
                    } else if ($request->percentage) {
                        CompanyPayrollRisksArl::create([
                            'payroll_risks_arl_id' => $request->id,
                            'percentage' => $request->percentage,
                            'company_id' => $this->getCompany()
                        ]);
                    } else if ($request->account_setoff) {
                        CompanyPayrollRisksArl::create([
                            'payroll_risks_arl_id' => $request->id,
                            'account_setoff' => $request->account_setoff,
                            'company_id' => $this->getCompany()
                        ]);
                    }
                    return $this->success('Asignado con éxito');
                }
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }
    }

    public function parafiscalesUpdate($id, Request $request)
    {
        try {
            if ($request->id) {
                $companPayrollParafiscal = CompanyPayrollParafiscal::where([
                    ['payroll_parafiscal_id', '=', $request->id],
                    ['company_id', '=', $this->getCompany()],
                ])->first();

                if ($companPayrollParafiscal) {
                    if ($request->account_plan_id) {
                        $companPayrollParafiscal->update([
                            'account_plan_id' => $request->account_plan_id,
                        ]);
                    } else if ($request->percentage) {
                        $companPayrollParafiscal->update([
                            'percentage' => $request->percentage,
                        ]);
                    } else if ($request->account_setoff) {
                        $companPayrollParafiscal->update([
                            'account_setoff' => $request->account_setoff,
                        ]);
                    }
                    return $this->success('Actualizado con éxito');
                } else {
                    if ($request->account_plan_id) {
                        CompanyPayrollParafiscal::create([
                            'payroll_parafiscal_id' => $request->id,
                            'account_plan_id' => $request->account_plan_id,
                            'company_id' => $this->getCompany()
                        ]);
                    } else if ($request->percentage) {
                        CompanyPayrollParafiscal::create([
                            'payroll_parafiscal_id' => $request->id,
                            'percentage' => $request->percentage,
                            'company_id' => $this->getCompany()
                        ]);
                    } else if ($request->account_setoff) {
                        CompanyPayrollParafiscal::create([
                            'payroll_parafiscal_id' => $request->id,
                            'account_setoff' => $request->account_setoff,
                            'company_id' => $this->getCompany()
                        ]);
                    }
                    return $this->success('Asignado con éxito');
                }
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }
    }

    public function novedadesUpdate($id, Request $request)
    {
        try {
            if ($request->id) {
                $payrollDisabilityLeaves = PayrollDisabilityLeave::where([
                    ['disability_leave_id', '=', $request->id],
                    ['company_id', '=', $this->getCompany()],
                ])->first();
                $disabilityLeave = DisabilityLeave::find($request->id);
                if ($payrollDisabilityLeaves) {
                    if ($request->account_plan_id) {
                        $payrollDisabilityLeaves->update([
                            'account_plan_id' => $request->account_plan_id,
                        ]);
                    }
                    if ($disabilityLeave && $request->modality) {
                        $disabilityLeave->update([
                            'modality' => $request->modality,
                        ]);
                    }
                    return $this->success('Actualizado con éxito');
                } else {
                    if ($request->account_plan_id) {
                        PayrollDisabilityLeave::create([
                            'disability_leave_id' => $request->id,
                            'account_plan_id' => $request->account_plan_id,
                            'company_id' => $this->getCompany()
                        ]);
                    }
                    return $this->success('Asignado con éxito');
                }
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }
    }

    public function incapacidadesUpdate($id, Request $request)
    {
        PayrollDisabilityLeave::find($id)->update($request->all());
        return $this->success('Actualizado con éxito');
    }

    public function createUptadeIncomeDatos(Request $request)
    {
        try {
            $data = $request->all();
            $data['company_id'] = $this->getCompany();
            $nuevo = CountableIncome::updateOrCreate(['id' => $request->id], $data);
            return ($nuevo->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode());
        }
    }

    public function createUpdateDeductionsDatos(Request $request)
    {
        try {
            $data = $request->all();
            $data['company_id'] = $this->getCompany();
            $nuevo = CountableDeduction::updateOrCreate(['id' => $request->get('id')], $data);
            return ($nuevo->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode());
        }
    }

    public function createUpdateLiquidationsDatos(Request $request)
    {
        try {
            if ($request->id) {
                $companyCountableLiquidation = CompanyCountableLiquidation::where([
                    ['countable_liquidation_id', '=', $request->id],
                    ['company_id', '=', $this->getCompany()],
                ])->first();

                if ($companyCountableLiquidation) {
                    if ($request->account_plan_id) {
                        $companyCountableLiquidation->update([
                            'account_plan_id' => $request->account_plan_id,
                        ]);
                    }
                    return $this->success('Actualizado con éxito');
                } else {
                    if ($request->account_plan_id) {
                        CompanyCountableLiquidation::create([
                            'countable_liquidation_id' => $request->id,
                            'account_plan_id' => $request->account_plan_id,
                            'company_id' => $this->getCompany(),
                            'status' => 1
                        ]);
                    }
                    return $this->success('Asignado con éxito');
                }
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }
        try {
            $nuevo = CountableLiquidation::updateOrCreate(['id' => $request->get('id')], $request->all());
            return ($nuevo->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode());
        }
    }

    public function createUpdateSalariosSubsidiosDatos(Request $request)
    {
        try {
            if ($request->id) {
                $companyCountableSalary = CompanyCountableSalary::where([
                    ['countable_salary_id', '=', $request->id],
                    ['company_id', '=', $this->getCompany()],
                ])->first();

                if ($companyCountableSalary) {
                    if ($request->account_plan_id) {
                        $companyCountableSalary->update([
                            'account_plan_id' => $request->account_plan_id,
                        ]);
                    } else if ($request->account_setoff) {
                        $companyCountableSalary->update([
                            'account_setoff' => $request->account_setoff,
                        ]);
                    }
                    return $this->success('Actualizado con éxito');
                } else {
                    if ($request->account_plan_id) {
                        CompanyCountableSalary::create([
                            'countable_salary_id' => $request->id,
                            'account_plan_id' => $request->account_plan_id,
                            'company_id' => $this->getCompany(),
                            'status' => 1
                        ]);
                    } else if ($request->account_setoff) {
                        CompanyCountableSalary::create([
                            'countable_salary_id' => $request->id,
                            'account_setoff' => $request->account_setoff,
                            'company_id' => $this->getCompany(),
                            'status' => 1
                        ]);
                    }
                    return $this->success('Asignado con éxito');
                }
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }
    }
}
