<?php

namespace App\Http\Controllers;

use App\Models\BankAccounts;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class BankAccountsController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(BankAccounts::all(['name AS text', 'id AS value']));
    }

    public function paginate(Request $request)
    {
        $company_id = $request->company_id;
        $name = $request->name;
        $pageSize = $request->get('pageSize', 10);

        $query = BankAccounts::when($company_id, function ($q, $p) {
            $q->where('company_id', $p);
        })
            ->when($name, function ($q, $p) {
                $q->where('name', 'like', "%$p%");
            });

        $bankAccounts = $query->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));

        return $this->success($bankAccounts);
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
            $banksAccount = BankAccounts::updateOrCreate(['id' => $request->get('id')], $request->all());
            return ($banksAccount->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }
}
