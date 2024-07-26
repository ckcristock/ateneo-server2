<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Models\Accommodation;
use App\Models\Person;

class HotelController extends Controller
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
		try {
			$nacional = Hotel::with('city', 'accommodations')
				->where('company_id', $this->getCompany())
				->where('type', '=', 'Nacional')->get();
			$internacional = Hotel::with('accommodations')
				->where('company_id', $this->getCompany())
				->where('type', '=', 'Internacional')->get();

			return $this->success(['nacional' => $nacional, 'internacional' => $internacional]);
		} catch (\Throwable $th) {
			return $this->error($th->getMessage(), 400);
		}
	}

	public function paginate()
	{
		return $this->success(
			Hotel::orderBy('type')
				->with('city', 'accommodations')
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
			$accommodations = array();

			foreach ($request->alojamientos as $values) {
				$alojami = array(
					'accommodation_id' => $values['id'],
					'price' => $values['price']
				);
				array_push($accommodations, $alojami);
			}
			$nuevo = Hotel::updateOrCreate(['id' => $request->get('id')], [
				'type' => $request->type,
				'name' => $request->name,
				'address' => $request->address,
				//'rate' => $request->rate,
				'phone' => $request->phone,
				'landline' => $request->landline,
				'city_id' => $request->city_id,
				//'simple_rate' => $request->simple_rate,
				//'double_rate' => $request->double_rate,
				'breakfast' => $request->breakfast,
				'company_id' => $this->getCompany()
			]);


			$nuevo->accommodations()->detach();
			$nuevo->accommodations()->attach($accommodations);

			//$nuevo = Accommodation::updateOrCreate($request->all());
			if ($nuevo) {
				return ($nuevo->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
				//return $this->success('Creado con éxito');
			} else {
				return $this->error('Ocurrió un error inesperado y no se pudo guardar', 406);
			}
		} catch (\Throwable $th) {
			return $this->error($th->getMessage() . ' msg: ' . $th->getLine() . ' ' . $th->getFile(), 500);
		}
	}
}
