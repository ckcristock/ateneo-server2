<?php

namespace App\Http\Controllers;

use App\Models\ActionType;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class ActionTypeController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    /**
     * Display a listing of the resource.
     */
    public function paginate(Request $request)
    {
        $data = $request->all();
        $page = key_exists('page', $data) ? $data['page'] : 1;
        $pageSize = key_exists('pageSize', $data) ? $data['pageSize'] : 5;
        return $this->success(
            ActionType::select(
                'id as value',
                'name as text',
                'status'
            )
                ->where('company_id', $this->getCompany())
                ->paginate($pageSize, ['*'], 'page', $page)
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function index()
    {
        return $this->success(
            ActionType::where('status', 'Activo')
                ->where('company_id', '=', $this->getCompany())
                ->get(['id as value', 'name as text'])
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $data['company_id'] = $this->getCompany();
            ActionType::updateOrCreate(['id' => $request->get('id')], $data);
            return $this->success('Creado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }
}
