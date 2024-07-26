<?php

namespace App\Http\Controllers;

use App\Models\HistoryDataCompany;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class HistoryDataCompanyController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->success(
            HistoryDataCompany::with('Person')
                ->where('data_name', '<>', 'logo')
                ->where('data_name', '<>', 'typeImage')
                ->where('company_id', $request->company_id)
                ->orderByDesc('created_at')
                ->paginate(Request()->get('pageSize', 10), ['*'], 'page', Request()->get('page', 1))
        );
    }
}
