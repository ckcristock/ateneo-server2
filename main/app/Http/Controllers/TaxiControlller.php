<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Taxi;
use App\Models\TaxiCity;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxiControlller extends Controller
{
	use ApiResponser;

	private function getCompany()
	{
		return Person::find(Auth()->user()->person_id)->company_worked_id;
	}

	/**
	 * 
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index()
	{
		try {
			return $this->success(Taxi::with(
				[
					'taxiCities' => function ($q) {
						$q->select('*');
					},
					'taxiCities.city' => function ($q) {
						$q->select('*');
					}
				]
			)
				->whereHas('taxiCities', function ($query) {
					$query->where('company_id', $this->getCompany());
				})
				->get());
		} catch (\Throwable $th) {
			return $this->error($th->getMessage(), 400);
		}
	}

	public function paginate()
	{
		return $this->success(
			TaxiCity::with('city', 'taxi')
				->when(request()->get('tipo'), function ($q, $fill) {
					$q->where('type', '=', $fill);
				})
				->where('company_id', $this->getCompany())
				->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
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
			$taxi = Taxi::create(['route' => $request->route]);
			TaxiCity::create([
				'type' => $request->type,
				'taxi_id' => $taxi->id,
				'city_id' => $request->city_id,
				'value' => $request->value,
				'company_id' => $this->getCompany()
			]);
			return $this->success('Creado Con éxito');
		} catch (\Throwable $th) {
			return $this->error($th->getMessage(), 500);
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update(Request $request, $id)
	{
		try {
			Taxi::find($id)->update(['route' => $request->route]);
			TaxiCity::where('taxi_id', $id)->update([
				'type' => $request->type,
				'city_id' => $request->city_id,
				'value' => $request->value,
				'taxi_id' => $request->taxi_id,
				'company_id' => $this->getCompany()
			]);
			return $this->success('Actualizado Con éxito');
		} catch (\Throwable $th) {
			return $this->error([$th->getMessage(), $th->getLine(), $th->getFile()], 500);
		}
	}
}
