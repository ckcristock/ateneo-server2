<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubTypeAppointmentResource;
use App\Models\SubTypeAppointment;
use App\Models\TypeAppointment;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class SubTypeAppointmentController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index($typeAppointment = '')
    {
        if ($typeAppointment != 'undefined' || is_numeric($typeAppointment)) {
            return SubTypeAppointmentResource::collection(TypeAppointment::with('subTypeAppointment')->find($typeAppointment)['subTypeAppointment']);
        } else {
            return SubTypeAppointmentResource::collection(SubTypeAppointment::all());
        }
    }

    public function paginate()
    {
        return $this->success(
            SubTypeAppointment::orderBy('name')
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
        );
    }
}
