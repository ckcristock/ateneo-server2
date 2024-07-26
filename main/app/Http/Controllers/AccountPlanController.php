<?php

namespace App\Http\Controllers;

use App\Models\AccountPlan;
use App\Models\Person;
use App\Models\PlanCuentas;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountPlanController extends Controller
{
	use ApiResponser;

	private function getCompany()
	{
		return Person::find(Auth()->user()->person_id)->company_worked_id;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index()
	{
		return $this->success(AccountPlan::all());
	}

	public function accountPlan()
	{
		return $this->success(
			DB::table('Plan_Cuentas as a')
				->select(
					'a.Id_Plan_Cuentas as id',
					'a.Porcentaje as percent',
					'a.Centro_Costo as center_cost',
					DB::raw('concat(a.Codigo," - ",a.Nombre) as code'),
					DB::raw('concat(a.Codigo_Niif," - ",a.Nombre_Niif) as niif_code')
				)
				->get()
		);
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(Request $request)
	{
		try {
			$accountPlan = AccountPlan::updateOrCreate(['id' => $request->get('id')], $request->all());
			return ($accountPlan->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
		} catch (\Throwable $th) {
			return response()->json([$th->getMessage(), $th->getLine()]);
		}
	}

	public function listBalance()
	{
		return $this->success(PlanCuentas::with('balance')

			->get(['*', DB::raw("concat(Nombre_Niif,' - ', Codigo_Niif) as text, Id_Plan_Cuentas as value")]));
	}

	public function list()
	{
		try {
			$results = PlanCuentas::select(
				'Id_Plan_Cuentas as id',
				DB::raw('CONCAT(Codigo, " - ", Nombre) as code'),
				'Centro_Costo as center_cost',
				'Porcentaje as percent'
			)
				->whereRaw('CHAR_LENGTH(Codigo) > 5')
				->where('company_id', $this->getCompany())
				->get();
			return $this->success($results);
		} catch (\Throwable $th) {
			return $this->error($th->getMessage(), 500);
		}
	}
	public function select()
	{
		return $this->success(PlanCuentas::select('Id_Plan_Cuentas as value', DB::raw("CONCAT(Codigo, ' - ', Nombre) as text"))->get());
	}
}
