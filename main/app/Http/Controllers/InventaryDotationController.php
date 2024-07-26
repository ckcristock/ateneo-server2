<?php

namespace App\Http\Controllers;

use App\Exports\DotationExport;
use App\Exports\DownloaDeliveriesExport;
use App\Models\InventaryDotation;
use App\Models\ProductDotationType;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LateArrivalExport;
use App\Models\Person;

class InventaryDotationController extends Controller
{
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function index(Request $request)
    {
        $page = $request->page ?? 1;
        $pageSize = $request->pageSize ?? 10;
        return $this->success(
            DB::table('inventary_dotations AS ID')
                ->selectRaw('*, (stock - (SELECT IFNULL(SUM(quantity), 0)
                            FROM dotation_products DP
                            INNER JOIN dotations D ON D.id = DP.dotation_id
                            WHERE DP.inventary_dotation_id = ID.id
                            AND (D.delivery_state <> "Entregado" OR D.delivery_state <> "Anulado"))) as remaining_stock')
                ->when($request->code, function ($q, $fill) {
                    $q->where('code', 'like', '%' . $fill . '%');
                })
                ->when($request->nombre, function ($q, $fill) {
                    $q->where('name', 'like', '%' . $fill . '%');
                })
                ->when($request->calidad, function ($q, $fill) {
                    $q->where('status', 'like', '%' . $fill . '%');
                })
                ->when($request->tipo, function ($q, $fill) {
                    $q->where('type', 'like', '%' . $fill . '%');
                })
                ->when($request->talla, function ($q, $fill) {
                    $q->where('size', 'like', '%' . $fill . '%');
                })
                ->where('company_id', $this->getCompany())
                ->orderBy('id', 'DESC')
                ->havingRaw('remaining_stock > 0')
                ->paginate($pageSize, '*', 'page', $page)
        );
    }

    public function getInventary(Request $request)
    {
        $page = $request->page ?? 1;
        $pageSize = $request->pageSize ?? 10;
        $inventaryDotations = DB::table('inventary_dotations AS ID')->select('stock', 'code', 'name', 'size', 'status', 'type', 'cost', 'id')
            ->selectRaw('(SELECT IFNULL(SUM(quantity), 0)
                            FROM dotation_products DP
                            INNER JOIN dotations D ON D.id = DP.dotation_id
                            WHERE DP.inventary_dotation_id = ID.id
                            AND (D.delivery_state <> "Entregado" OR D.delivery_state <> "Anulado")) as cantidadA')
            ->when($request->type, function ($q, $fill) {
                $q->where('type', $fill);
            })
            ->when($request->name, function ($q, $fill) {
                $q->where('name', 'like', "%$fill%");
            })
            ->when($request->entrega, function ($q, $fill) {
                $q->havingRaw('stock - cantidadA > 0');
            })
            ->where('company_id', $this->getCompany())
            ->orderBy('id', 'DESC')
            ->where('stock', '>', 0)
            ->paginate($pageSize, '*', 'page', $page);
        return $this->success($inventaryDotations);
    }

    public function getTotatInventary()
    {
        $d = DB::table('inventary_dotations as id')
            ->selectRaw('pdt.name, SUM(id.stock) as value')
            // ->join('dotation_products AS dp', 'dp.dotation_id', '=', 'D.id')
            // ->join('inventary_dotations AS id', 'id.id', '=', 'dp.inventary_dotation_id')
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
            ->where('id.company_id', $this->getCompany())
            ->groupBy('pdt.id')
            ->get();

        return $this->success($d);
    }

    public function getSelected()
    {
        $page = Request()->get('page');
        $page = $page ? $page : 1;
        $pageSize = Request()->get('pageSize');
        $pageSize = $pageSize ? $pageSize : 10;
        $id = Request()->get('id');
        $d = DB::table('inventary_dotations as ID')
            ->select(
                DB::raw(' CONCAT(P.first_name," ",P.first_surname) as nameF '),
                'PD.created_at',
                'D.delivery_code',
                'PD.quantity',
                'D.type',
                'D.delivery_state'
            )
            ->join('dotation_products AS PD', 'PD.inventary_dotation_id', '=', 'ID.id')
            ->join('dotations AS D', 'D.id', '=', 'PD.Dotation_id')
            ->join('people AS  P', 'P.id', '=', 'D.person_id')

            ->where([
                ['ID.id', $id],
                ['D.delivery_state', '<>', "Entregado"],
                ['D.delivery_state', '<>', "Anulado"],
            ])
            ->where('ID.company_id', $this->getCompany())
            ->paginate($pageSize, '*', 'page', $page);

        return $this->success($d);
    }

    public function getInventaryEpp()
    {
        $d = ProductDotationType::with('inventary')
            ->whereHas(
                'inventary',
                function ($q) {
                    $q->where('type', 'EPP');
                }
            )
            ->where('company_id', $this->getCompany())
            ->get();
        return $this->success($d);
    }

    public function indexGruopByCategory(Request $request)
    {
        $data = DB::table('inventary_dotations as ID')
            ->select('CPD.name', DB::raw('SUM(ID.stock) as stock'))
            ->join('product_dotation_types as CPD', 'ID.product_dotation_type_id', '=', 'CPD.id')
            ->groupBy('ID.product_dotation_type_id')
            ->where('ID.company_id', $this->getCompany())
            ->get();

        return $this->success($data);
    }


    public function statistics(Request $request)
    {
        $d = DB::table('dotations as D')
            ->selectRaw('IFNULL(SUM(D.cost), 0) as totalCostoMes, IFNULL(count(*),0) as totalMes')
            ->when(Request()->get('delivery'), function ($q, $fill) {
                $q->where('D.delivery_state', $fill);
            })
            ->when(Request()->get('cod'), function ($q, $fill) {
                $q->where('D.delivery_code', 'like', '%' . $fill . '%');
            })
            ->when(Request()->get('person'), function ($q, $fill) {
                $q->where('D.user_id', $fill);
            })
            ->when(Request()->get('persontwo'), function ($q, $fill) {
                $q->where('D.person_id', $fill);
            })
            ->when(Request()->get('firstDay'), function ($q, $fill) {
                $q->whereDate('D.dispatched_at', '>=', $fill);
            })
            ->when(Request()->get('lastDay'), function ($q, $fill) {
                $q->whereDate('D.dispatched_at', '<=', $fill);
            })
            ->when(Request()->get('type'), function ($q, $fill) {
                $q->where('D.type', $fill);
            })
            ->where('D.company_id', $this->getCompany())
            ->get();
        $dyear = DB::table('dotations as D')
            ->selectRaw('IFNULL(SUM(D.cost), 0) as totalCostoAnual, IFNULL(count(*),0) as totalAnual')
            ->when(Request()->get('delivery'), function ($q, $fill) {
                $q->where('D.delivery_state', $fill);
            })
            ->when(Request()->get('cod'), function ($q, $fill) {
                $q->where('D.delivery_code', 'like', '%' . $fill . '%');
            })
            ->when(Request()->get('person'), function ($q, $fill) {
                $q->where('D.user_id', $fill);
            })
            ->when(Request()->get('persontwo'), function ($q, $fill) {
                $q->where('D.person_id', $fill);
            })
            ->when(Request()->get('firstDay'), function ($q, $fill) {
                $q->whereDate('D.dispatched_at', '>=', $fill);
            })
            ->when(Request()->get('lastDay'), function ($q, $fill) {
                $q->whereDate('D.dispatched_at', '<=', $fill);
            })
            ->when(Request()->get('type'), function ($q, $fill) {
                $q->where('D.type', $fill);
            })
            ->where('D.company_id', $this->getCompany())
            ->get();
        $td = DB::table('dotations as D')
            ->selectRaw(' IFNULL(count(*),0) as totalDotacion')
            ->when(Request()->get('delivery'), function ($q, $fill) {
                $q->where('D.delivery_state', $fill);
            })
            ->when(Request()->get('cod'), function ($q, $fill) {
                $q->where('D.delivery_code', 'like', '%' . $fill . '%');
            })
            ->when(Request()->get('person'), function ($q, $fill) {
                $q->where('D.user_id', $fill);
            })
            ->when(Request()->get('persontwo'), function ($q, $fill) {
                $q->where('D.person_id', $fill);
            })
            ->when(Request()->get('type'), function ($q, $fill) {
                $q->where('D.type', $fill);
            })
            ->where('D.type', 'Dotacion')
            ->where('D.company_id', $this->getCompany())
            ->get();
        $te = DB::table('dotations as D')
            ->selectRaw(' IFNULL(count(*),0) as totalEpp')
            ->when(Request()->get('delivery'), function ($q, $fill) {
                $q->where('D.delivery_state', $fill);
            })
            ->when(Request()->get('cod'), function ($q, $fill) {
                $q->where('D.delivery_code', 'like', '%' . $fill . '%');
            })
            ->when(Request()->get('person'), function ($q, $fill) {
                $q->where('D.user_id', $fill);
            })
            ->when(Request()->get('persontwo'), function ($q, $fill) {
                $q->where('D.person_id', $fill);
            })
            ->when(Request()->get('type'), function ($q, $fill) {
                $q->where('D.type', $fill);
            })
            ->where('D.type', 'EPP')
            ->where('D.company_id', $this->getCompany())
            ->get();
        return $this->success(['month' => $d[0], 'year' => $dyear[0], 'td' => $td[0], 'te' => $te[0]]);
        // $date = explode('-', $request->get('cantMes'));
        // $d = DB::select('SELECT ifnull(count(*),0) as totalMes,
        //  ifnull(SUM(D.cost),0) as totalCostoMes
        //  FROM dotations D
        //  where DATE(D.dispatched_at) BETWEEN "'.$firstDay.'" and "'.$lastDay.'"
        //         AND D.state = "Activa" or D.person_id = "'.$person.'" ');
        // $dyear = DB::select('SELECT count(*) as totalAnual,
        //  ifnull(SUM(D.cost),0) as totalCostoAnual
        //  FROM dotations D
        //  where DATE(D.dispatched_at) BETWEEN "'.$firstDay.'" and "'.$lastDay.'"
        //         AND D.state = "Activa" or D.person_id = "'.$person.'" ');
    }

    public function download(Request $request)
    {
        return Excel::download(new DotationExport($request), 'inventario.xlsx');
    }


    public function downloadeliveries($fechaInicio, $fechaFin, Request $req)
    {
        $dates = [$fechaInicio, $fechaFin];
        return Excel::download(new DownloaDeliveriesExport($dates), 'downloadeliveries.xlsx');
    }
}
