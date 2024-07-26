<?php

namespace App\Http\Controllers;

use App\Models\Dotation;
use App\Models\DotationProduct;
use App\Models\InventaryDotation;
use App\Models\Person;
use App\Models\ProductDotationType;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DotationController extends Controller
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
        $page = $request->page;
        $page = $page ? $page : 1;
        $pageSize = $request->pageSize;
        $pageSize = $pageSize ? $pageSize : 10;
        $user_id = 0;
        if ($request->person) {
            $user_id = Person::with('usuario')->where('id', $request->person)->first()->usuario->id;
        }
        /* return Dotation::with('dotation_products.inventary_dotation.product_datation_types', 'person', 'user')
            ->select('*', DB::raw('GROUP_CONCAT( dotation_products.quantity , " X  " , ID.name ) AS product_name'))
            ->get(); */
        $d = DB::table('dotations AS D')
            ->join('dotation_products AS PD', 'PD.dotation_id', '=', 'D.id')
            ->join('inventary_dotations AS ID', 'ID.id', '=', 'PD.inventary_dotation_id')
            ->join('product_dotation_types AS GI', 'GI.id', '=', 'ID.product_dotation_type_id')
            ->join('people AS  P', 'P.id', '=', 'D.person_id')
            ->join('usuario AS US', 'US.id', '=', 'D.user_id')
            ->join('people AS PF', 'PF.id', '=', 'US.person_id')
            ->select(
                DB::raw('GROUP_CONCAT( PD.quantity , " X  " , ID.name ) AS product_name'),
                DB::raw('GROUP_CONCAT( PD.inventary_dotation_id ) AS IID'),
                DB::raw('GROUP_CONCAT( PD.quantity ) AS quantity'),
                DB::raw(' SUM(PD.quantity * PD.cost) AS total'),
                DB::raw(' CONCAT(P.first_name," ",P.first_surname) as recibe '),
                DB::raw(' CONCAT(PF.first_name," ",PF.first_surname) as entrega '),
                'D.created_at',
                'D.id',
                'D.type',
                'D.delivery_code',
                'D.delivery_state',
                'D.description',
                'D.state',
            )
            ->when($request->type, function ($q, $fill) {
                $q->where('D.type', 'like', '%' . $fill . '%');
            })
            ->when($request->name, function ($q, $fill) {
                $q->where('ID.name', 'like', '%' . $fill . '%');
            })
            ->when($request->cod, function ($q, $fill) {
                $q->where('D.delivery_code', 'like', '%' . $fill . '%');
            })
            ->when($request->description, function ($q, $fill) {
                $q->where('D.description', 'like', '%' . $fill . '%');
            })
            ->when($request->type, function ($q, $fill) {
                $q->where('D.type', 'like', '%' . $fill . '%');
            })
            ->when($request->person, function ($q, $fill) use ($user_id) {
                $q->where('D.user_id', $user_id);
            })
            ->when($request->persontwo, function ($q, $fill) {
                $q->where('D.person_id', $fill);
            })
            ->when($request->delivery, function ($q, $fill) {
                $q->where('D.delivery_state', $fill);
            })
            ->when($request->firstDay, function ($q, $fill) {
                $q->whereDate('D.dispatched_at', '>=', $fill);
            })
            ->when($request->lastDay, function ($q, $fill) {
                $q->whereDate('D.dispatched_at', '<=', $fill);
            })

            // ->when(request()->get('fechaD'), function ($q) {
            //     $fechaInicio = trim(explode(' - ', Request()->get('fechaD'))[0]);
            //     $fechaFin = trim(explode(' - ', Request()->get('fechaD'))[1]);
            //     $dates = [$fechaInicio, $fechaFin];
            //     $q->whereBetween(DB::raw("DATE(D.dispatched_at)"), $dates);
            // })
            // ->when(Request()->get('recibe'), function ($q, $fill) {
            //     $q->where('D.person_id', $fill);
            // })
            // ->when(Request()->get('entrega'), function ($q, $fill) {
            //     $q->where('PF.id', $fill);
            // })
            ->groupBy('D.id')
            ->orderBy('D.created_at', 'DESC')
            ->where('D.company_id', $this->getCompany())
            ->paginate($pageSize, '*', 'page', $page);

        return $this->success($d);
    }

    public function getTotatlByTypes(Request $request)
    {
        // $date = explode('-', $request->get('cantMes'));
        // $firstDay = $request->get('firstDay');
        // $lastDay = $request->get('lastDay');

        // $d = DB::select('SELECT pdt.name, SUM(dp.quantity) as value
        //         FROM
        //         dotations d
        //         inner join dotation_products dp on dp.dotation_id = d.id
        //         inner join inventary_dotations id on id.id = dp.inventary_dotation_id
        //         INNER JOIN product_dotation_types pdt on pdt.id = id.product_dotation_type_id
        //         where DATE(dispatched_at) BETWEEN "'.$firstDay.'" and "'.$lastDay.'"
        //         GROUP BY pdt.id');

        $d = DB::table('dotations as D')
            ->selectRaw('pdt.name, SUM(dp.quantity) as value')
            ->join('dotation_products AS dp', 'dp.dotation_id', '=', 'D.id')
            ->join('inventary_dotations AS id', 'id.id', '=', 'dp.inventary_dotation_id')
            ->join('product_dotation_types AS pdt', 'pdt.id', '=', 'id.product_dotation_type_id')
            ->when(Request()->get('person'), function ($q, $fill) {
                $q->where('D.user_id', $fill);
            })
            ->when(Request()->get('persontwo'), function ($q, $fill) {
                $q->where('D.person_id', $fill);
            })
            ->when(Request()->get('cod'), function ($q, $fill) {
                $q->where('D.delivery_code', 'like', '%' . $fill . '%');
            })
            ->when(Request()->get('type'), function ($q, $fill) {
                $q->where('D.type', 'like', '%' . $fill . '%');
            })
            ->when(Request()->get('delivery'), function ($q, $fill) {
                $q->where('D.delivery_state', $fill);
            })
            ->when(Request()->get('firstDay'), function ($q, $fill) {
                $q->whereDate('D.dispatched_at', '>=', $fill);
            })
            ->when(Request()->get('lastDay'), function ($q, $fill) {
                $q->whereDate('D.dispatched_at', '<=', $fill);
            })
            ->where('D.company_id', $this->getCompany())
            ->groupBy('pdt.id')
            ->get();

        return $this->success($d);
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
            $entregaData = $request->get('entrega');
            $productos = $request->get('prods');
            $totalCost = 0;
            $productList = '';

            $entregaData['user_id'] = auth()->user()->id;
            $entregaData['company_id'] = $this->getCompany();
            $entregaData['delivery_code'] = generateConsecutive('dotations');

            $dotation = Dotation::create($entregaData);
            $dotation->refresh();

            foreach ($productos as $prod) {
                $totalCost += $prod["quantity"] * $prod["cost"];
                $productList .= trim($prod["quantity"]) . ' x ' . trim($prod["name"]) . " | ";

                $prodSave = $prod;
                $prodSave["inventary_dotation_id"] = $prodSave["id"];
                $prodSave["dotation_id"] = $dotation->id;
                DotationProduct::create($prodSave);
            }

            $entregaData["Productos"] = trim($productList, " | ");
            $entregaData["Costo"] = $totalCost;
            $dotation->cost = $totalCost;
            $dotation->save();

            sumConsecutive('dotations');
            return $this->success('Guardado con éxito');
        } catch (\Throwable $th) {
            return $this->success($th->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        //
        try {
            //code...
            $dotation = Dotation::find($id);
            $dotation->state = $request->get('state');
            $dotation->save();
            return $this->success('guardado con éxito');
        } catch (\Throwable $th) {
            //throw $th;
            return $this->error($th->getMessage(), 500);
        }
    }

    public function updateStock(Request $request)
    {
        $person_id = auth()->user()->person_id;
        if ($person_id != 1) {
            return $this->error('No autorizado', 401);
        }
        try {
            InventaryDotation::where('id', $request->id)->update(['stock' => $request->stock]);
            return $this->success('Stock actualizado con éxito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }


    public function approve(Request $request, $id)
    {
        //
        try {
            //code...
            $dotation = Dotation::find($id);
            $dotation->delivery_state = $request->get('state');
            $dotation->save();
            return $this->success('guardado con éxito');
        } catch (\Throwable $th) {
            //throw $th;
            return $this->success($th->getMessage(), 500);
        }
    }


    public function download($id, $memorandum = false)
{
    $dotation = Dotation::with('person', 'user', 'dotationProducts.inventaryDotation.productDotationType')->find($id);

    
    $datosCabecera = (object) array(
        'Titulo' => 'ACTA DE ENTREGA DE DOTACIÓN',
        'Codigo' => $dotation->code ?? '',
        'Fecha' => $dotation->created_at,
        'CodigoFormato' => $dotation->format_code ?? '',
        
    );


    $pdf = PDF::loadView('pdf.acta_entrega_dotation', [
        'data' => $dotation,
        'datosCabecera' => $datosCabecera,
    ]);
    if ($memorandum) {
        return $pdf->download('acta_entrega_dotation.pdf');
    } else {
        return $pdf->stream('acta_entrega_dotation.pdf');
    }
}


    public function statistics(Request $request)
    {
        $date = explode('-', $request->get('cantMes'));

        $d = DB::select('SELECT ifnull(count(*),0) as totalMes,
         ifnull(SUM(cost),0) as totalCostoMes
         FROM dotations
         where year(dispatched_at)= ' . $date[0] . '
               and
               month(dispatched_at)= ' . $date[1] . '
                AND state = "Activa"');

        $dyear = DB::select('SELECT count(*) as totalAnual,
         ifnull(SUM(cost),0) as totalCostoAnual
         FROM dotations
         where year(dispatched_at)= ' . $date[0] . ' AND state = "Activa"');

        return $this->success(['month' => $d[0], 'year' => $dyear[0]]);
    }

    public function getListProductsDotation(Request $request)
    {

        $code = $request->get('code');
        $page = Request()->get('page');
        $page = $page ? $page : 1;
        $pageSize = Request()->get('pageSize');
        $pageSize = $pageSize ? $pageSize : 10;

        $d = DB::table('dotation_products AS PD')
            ->select(
                'D.created_at',
                'D.id',
                'D.type',
                'D.delivery_code',
                'D.delivery_state',
                'D.description',
                'D.state',
                'ID.name as product_name',
                'PD.quantity',
                DB::raw(' CONCAT(P.first_name," ",P.first_surname) as recibe '),
                DB::raw(' CONCAT(PF.first_name," ",PF.first_surname) as entrega '),
            )
            ->join('dotations AS D', 'PD.dotation_id', '=', 'D.id')
            ->join('inventary_dotations AS ID', 'ID.id', '=', 'PD.inventary_dotation_id')
            ->join('product_dotation_types AS GI', 'GI.id', '=', 'ID.product_dotation_type_id')
            ->join('people AS  P', 'P.id', '=', 'D.person_id')
            ->join('usuario AS US', 'US.id', '=', 'D.user_id')
            ->join('people AS PF', 'PF.id', '=', 'US.person_id')

            ->where([
                ['PD.code', '' . $code . ''],
                ['PD.code', '' . $code . ''],
            ])
            ->where('D.company_id', $this->getCompany())
            ->paginate($pageSize, '*', 'page', $page);
        return $this->success($d);
    }

    public function getDotationType()
    {
        return $this->success(
            ProductDotationType::where('company_id', $this->getCompany())->orderBy('id', 'ASC')->get(['name As text', 'id As value'])
        );
    }
}
