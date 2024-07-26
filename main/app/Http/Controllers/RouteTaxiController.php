<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\TaxiCity;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RouteTaxiController extends Controller
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
			TaxiCity::where('type', '=', 'Nacional')->get(),
			TaxiCity::where('type', '=', 'Internacional')->get()

		);
	}

	public function cities()
	{
		$route = TaxiCity::find(1);
		return $route->cities;
	}
}
