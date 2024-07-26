<?php

namespace App\Http\Controllers;

use App\Models\WaitingList;
use App\Services\WaitingListService;
use App\Traits\ApiResponser;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;

class WaitingListController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(WaitingListService::index());
    }

    /**
     * Display statistics of waiting lists.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        //
        $stats = [];
        $stats['topAwait'] = WaitingListService::getTopAwaitBySpeciality();
        $stats['lastAppointment'] = WaitingListService::getLastAppointment();
        $stats['averageTime'] = WaitingListService::averageTime();
        $stats['averageTime'] = $stats['averageTime']->time ? CarbonInterval::seconds($stats['averageTime']->time)->cascade()->forHumans() : 0;
        return $this->success($stats);
    }


    public function cancellWaitingAppointment()
    {
        return $this->success(

            WaitingList::whereId(request()->get('id'))->update(
                [
                    'state' => 'Cancelado',
                    'message_cancell' => request()->get('message', 'sin mensaje')
                ]
            )

        );
    }
}
