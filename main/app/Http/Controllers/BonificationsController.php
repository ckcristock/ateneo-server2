<?php

namespace App\Http\Controllers;

use App\Models\Bonifications;
use App\Models\CountableIncome;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;

class BonificationsController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->success(
            DB::table('bonifications as b')
                ->select(
                    'c.id as countable_income_id',
                    'c.concept',
                    'b.countable_income_id',
                    'b.id',
                    'b.value',
                    'b.status',
                    'b.work_contract_id'
                )
                ->join('countable_income as c', function ($join) {
                    $join->on('c.id', '=', 'b.countable_income_id');
                })
                ->where('b.work_contract_id', '=', $request->get('id'))
                ->get()
        );
    }

    public function countable_income(Request $request)
    {
        return $this->success(
            CountableIncome::when($request->get('bonusType'), function ($q, $p) {
                $q->where('type', $p);
            })->get(['id as value', 'concept as text'])
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            Bonifications::updateOrCreate(['id' => $request->get('id')], $request->all());
            return $this->success('creacion exitosa');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }
    }
}
