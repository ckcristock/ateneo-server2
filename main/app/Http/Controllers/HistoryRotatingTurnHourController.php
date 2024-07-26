<?php

namespace App\Http\Controllers;

use App\Models\HistoryRotatingTurnHour;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class HistoryRotatingTurnHourController extends Controller
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
        $pageSize = Request()->get('pageSize', 10);
        $page = Request()->get('page', 1);

        $items = HistoryRotatingTurnHour::with('rotating_turn_hour', 'person')
            ->groupBy('batch')
            ->orderBy('batch', 'desc')
            ->where('company_id', $this->getCompany())
            ->paginate($pageSize, ['*'], 'page', $page);

        // Reestructurar los resultados dentro del objeto paginado
        $result = $items->map(function ($item) {
            return [
                'batch' => $item->batch,
                'elements' => $item->where('batch', $item->batch)->with('rotating_turn_hour', 'person')->get()->toArray(),
                'created_at' => $item->created_at,
                'person' => $item->person,
                'show' => false

            ];
        });

        // Reemplazar los resultados paginados con los resultados reestructurados
        $items->setCollection($result);

        return $this->success($items);
    }
}
