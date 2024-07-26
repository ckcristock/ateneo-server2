<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\ThirdPartyPerson;
use App\Traits\ApiResponser;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThirdPartyPersonController extends Controller
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
            ThirdPartyPerson::with('thirdParty')
                ->orderBy('name', 'asc')
                ->when($request->third, function ($q, $fill) {
                    $q->where('third_party_id', $fill);
                })
                ->when($request->name, function ($q, $fill) {
                    $q->where('name', 'like', "%$fill%");
                })
                ->when($request->phone, function ($q, $fill) {
                    $q->where('cell_phone', 'like', "%$fill%");
                })
                ->when($request->email, function ($q, $fill) {
                    $q->where('email', 'like', "%$fill%");
                })
                ->when($request->cargo, function ($q, $fill) {
                    $q->where('position', 'like', "%$fill%");
                })
                ->when($request->observacion, function ($q, $fill) {
                    $q->where('observation', 'like', "%$fill%");
                })
                ->when($request->documento, function ($q, $fill) {
                    $q->where('n_document', 'like', "%$fill%");
                })
                /* ->whereHas('thirdParty', function ($q) {
                    $q->where('company_id', $this->getCompany());
                }) */
                ->where('company_id', $this->getCompany())
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
        );
    }


    public function getThirdPartyPersonForThird($id)
    {
        return $this->success(ThirdPartyPerson::where('third_party_id', $id)->get(['*', 'id as value', 'name as text']));
    }

    public function getThirdPartyPersonIndex()
    {
        return $this->success(
            ThirdPartyPerson::selectRaw('*, id as value, UPPER(name) as text')
                ->get()
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
            $data = $request->all();
            $validator = false;
            $validatorDocument = false;
            if ($request->id) {
                $third_Person = ThirdPartyPerson::find($request->id);
                if ($third_Person->n_document != $request->n_document || $third_Person->name != $request->name || $third_Person->third_party_id != $request->third_party_id) {
                    $validator = ThirdPartyPerson::where('name', $request->name)->where('id', '!=', $request->id)->where('third_party_id', $request->third_party_id)->exists();
                    if ($request->n_document) {
                        $validatorDocument = ThirdPartyPerson::where('n_document', $request->n_document)->where('id', '!=', $request->id)->exists();
                    }
                }
            } else {
                $validator = ThirdPartyPerson::where('name', $request->name)->where('third_party_id', $request->third_party_id)->exists();
                if ($request->n_document) {
                    $validatorDocument = ThirdPartyPerson::where('n_document', $request->n_document)->exists();
                }
            }
            if (!$validator && !$validatorDocument) {
                $data['company_id'] = $this->getCompany();
                $person = ThirdPartyPerson::updateOrCreate(['id' => $request->id], $data);
                return ($person->wasRecentlyCreated) ? $this->success('Creado con éxito') : $this->success('Actualizado con éxito');
            } else if ($validator) {
                throw new ModelNotFoundException('Ya existe una persona con el mismo nombre asignada a este tercero');
            } else if ($validatorDocument) {
                throw new ModelNotFoundException('Ya existe una persona con el mismo documento');
            } else {
                throw new ModelNotFoundException('Ocurrio un error');
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }
    }
}
