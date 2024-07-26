<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\Dispensing;
use App\Models\Person;

class DispensingPointController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            return $this->success(Dispensing::get(['Nombre As text', 'Id_Punto_Dispensacion As value']));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    public function setPerson($personId, Request $req)
    {
        try {
            $dispensing_point_id = $req->get('dispensing_point_id');
            $person = Person::find($personId);
            $person->dispensing_point_id = $dispensing_point_id;
            $person->save();
            return $this->success('Guardado correctamente');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }
}
