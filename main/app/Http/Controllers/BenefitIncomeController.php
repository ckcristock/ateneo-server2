<?php

namespace App\Http\Controllers;

use App\Models\BenefitIncome;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BenefitIncomeController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request)
    {
        $person = Person::findOrFail($request->get('person_id'));
        return $this->success(
            BenefitIncome::where('person_id', '=', $person->id)->with('ingreso')->get()
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
        //
        try {
            //code...
            BenefitIncome::create($request->all());
            return $this->success('creado con éxito');
        } catch (\Throwable $th) {
            //throw $th;
            return $this->error($th->getMessage(), 500);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::table('benefit_incomes')->where('id', $id)->delete();
            return $this->success('Eliminado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }
}
