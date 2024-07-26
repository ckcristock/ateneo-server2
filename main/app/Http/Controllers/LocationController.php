<?php

namespace App\Http\Controllers;

use App\Http\Resources\SedeResource;
use App\Models\Location;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class LocationController extends Controller
{

    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $companyId = $request->company_id ?? getCompanyWorkedId();
        return $this->success(
            Location::where('company_id', $companyId)
                ->get(['id as value', 'name as text'])
        );
    }

    public function paginate(Request $request)
    {
        $name = $request->name;
        return $this->success(
            Location::with('city.department.country')
                ->when($request->get('company_id'), function ($q, $p) {
                    $q->where('company_id', $p);
                })
                ->when($name, function ($q, $p) {
                    $q->where('name', 'like', "%$p%");
                })
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
        );
    }
}
