<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function mycita(Request $request)
    {
        return response()->json($request->all());
    }
}
