<?php

namespace App\Http\Controllers;

use App\Http\Resources\TypeAppointmentResource;
use App\Models\TypeAppointment;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class TypeAppointmentController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index($typeAppointment = '')
    {
        if ($typeAppointment == 'undefined' || is_numeric($typeAppointment) || $typeAppointment == 'getall') {

            return TypeAppointmentResource::collection(TypeAppointment::all());
        }
        return TypeAppointmentResource::collection(TypeAppointment::where('name', 'like', '%' . $typeAppointment . '%')->get());
    }

    public function paginate()
    {
        return $this->success(
            TypeAppointment::orderBy('name')
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
        );
    }
}
