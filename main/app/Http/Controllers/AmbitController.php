<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use App\Models\Ambit;

class AmbitController extends Controller
{

	use ApiResponser;

	public function index()
	{
		return $this->success(Ambit::all());
	}
}
