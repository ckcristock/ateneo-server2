<?php

namespace App\Http\Controllers;

use App\Models\Formality;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class FormalityController extends Controller
{
	use ApiResponser;
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index()
	{
		return $this->success(Formality::orderBy('id')->get());
	}
}
