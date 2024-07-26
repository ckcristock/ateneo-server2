<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Models\Person;
use App\Models\TypeLocation;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WorkContract;
use Illuminate\Support\Facades\URL;

class CompanyController extends Controller
{

    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }


    public function index($typeLocation = 0)
    {

        $brandShowCompany = 0;

        $companies = Company::query();
        // TODO: arregla la peticion de companies

        if (request()->get('owner'))
            $brandShowCompany = request()->get('owner');
        if (request()->get('owner')) {
            $companies->where('type', $brandShowCompany);
            $companies->whereIn('category', ['IPS', 'SERVICIOS']);
            return response()->success($companies->orderBy('short_name')->get(['short_name as text', 'id as value']));
        }

        // $companies->when(request()->get('professional_id'), function ($q) {
        //     $companies = Person::findOrfail(request()->get('professional_id'))->restriction()->with('companies:id,name,type')->first(['restrictions.id']);
        //     $q->whereIn('id', $companies->companies->pluck('id'));
        // });

        if ($typeLocation && $typeLocation != 3) {
            $typeLocation = TypeLocation::findOrfail($typeLocation);
            return CompanyResource::collection($companies->get());
            // $brandShowCompany = $typeLocation->show_company_owners;
        }

        if (gettype($typeLocation) != 'object' && $typeLocation == 3) {
            return CompanyResource::collection($companies->get());
        }

        return $this->success(CompanyResource::collection($companies->where('type', $brandShowCompany)->get()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBasicData($company_id)
    {
        $company = Company::with('arl', 'bank', 'companyConfiguration', 'payConfiguration')->findOrFail($company_id);

        return $this->success($company);
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
            $work_contract = WorkContract::find($request->get('id'));
            $work_contract->update($request->all());
            return response()->json(['message' => 'Se ha actualizado con éxito']);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }


    public function saveCompanyData(Request $request)
    {
        $differences = findDifference($request->all(), Company::class);
        $company = Company::findOrFail($request->get('id'));
        $company_data = $request->all();
        if ($request->has('logo')) {
            if ($company_data['logo'] != $company->logo) {
                $company_data['logo'] = URL::to('/') . '/api/image?path=' . saveBase64($company_data['logo'], 'company/');
            }
        }
        if ($request->has('page_heading')) {
            $company_data['page_heading'] = URL::to('/') . '/api/image?path=' . saveBase64($company_data['page_heading'], 'company/');
        }
        $company->update($company_data);

        saveHistoryCompanyData($differences, Company::class, $company->id);
        return $this->success('');
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Company $company)
    {
        $company->load('arl', 'bank', 'companyConfiguration', 'payConfiguration');

        return $this->success($company);
    }


    public function getCompanyBaseOnCity($municipalityId)
    {
        $data = Company::withWhereHas('locations', function ($q) use ($municipalityId) {
            $q->select('id As value', 'name As text', 'company_id');
            // ->where('city', $municipalityId);
        })
            ->get(['id As value', 'name As text', 'id']);
        return $this->success($data);
    }

    public function getGlobal()
    {
        return Company::with('payConfiguration')->with('arl')->first([
            'id',
            'arl_id',
            'payment_frequency',
            'social_reason',
            'tin as document_number',
            'transportation_assistance',
            'base_salary',
            'law_1607'
        ]);
    }


    public function getCompanyByIdentifier()
    {
        $company = Company::select('name')->firstWhere('tin', request()->get('identifier'));

        $exist = false;
        $name = null;

        if ($company) {
            $name = $company->name;
            $exist = true;
        }

        return response()->success(['name' => $name, 'existe' => $exist]);
    }


    public function getCompanies()
    {
        $companies = Company::query(); 
        return $this->success(CompanyResource::collection($companies->where('type', 1)->get()));
    }

    public function getAllCompanies()
    {
        $companies = DB::table('companies')->get();
        return $this->success(
            Company::orderBy('name')
                ->when(request()->get('name'), function ($q, $fill) {
                    $q->where('name', 'like', '%' . $fill . '%');
                })
                ->when(request()->get('tin'), function ($q, $fill) {
                    $q->where('tin', 'like', '%' . $fill . '%');
                })
                ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
        );
    }
}
