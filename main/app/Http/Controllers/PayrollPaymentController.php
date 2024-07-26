<?php

namespace App\Http\Controllers;

use App\Models\PayrollPayment;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class PayrollPaymentController extends Controller
{
	//
	use ApiResponser;

	private function getCompany()
	{
		return Person::find(Auth()->user()->person_id)->company_worked_id;
	}
	/**
	 * Retorna JSON todos los pagos de nómina hechos hasta la fecha
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getPagosNomina(Request $req)
	{
		return $this->success(
			PayrollPayment::orderByDesc('created_at')
				->where('company_id', $this->getCompany())
				->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
		);
	}
}
