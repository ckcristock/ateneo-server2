<?php

namespace App\Http\Controllers;

use App\Models\FixedTurnHour;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class FixedTurnHourController extends Controller
{
	use ApiResponser;
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index(Request $request)
	{
		//
		return $this->success(
			FixedTurnHour::where(
				"fixed_turn_id",
				$request->get("fixed_turn_id")
			)
				->orderBy("id")
				->get()
		);
	}
}
