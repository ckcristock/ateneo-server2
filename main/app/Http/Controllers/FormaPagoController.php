<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Services\consulta;
use App\Models\FormaPago;

class FormaPagoController extends Controller
{
    public function index()
    {
        $formasPago = FormaPago::orderBy('Nombre', 'ASC')->get();

        return response()->json( $formasPago);
    }
}


