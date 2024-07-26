<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Responsible;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class ResponsibleController extends Controller
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
    public function index()
    {
        return $this->success(Responsible::with('person')->where('company_id', $this->getCompany())->get());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Responsible $responsible)
    {
        $responsible->update($request->all());
        return $this->success('Actualizado con éxito');
    }
}
