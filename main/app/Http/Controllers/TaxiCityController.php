<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\RouteTaxi;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxiCityController extends Controller
{
	use ApiResponser;
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index()
	{
		return $this->success(
			RouteTaxi::where('type', '=', 'Nacional')->get(),
			RouteTaxi::where('type', '=', 'Internacional')->get()

		);
	}

	public function cities()
	{
		$route = RouteTaxi::find(1);
		return $route->cities;
	}
}
