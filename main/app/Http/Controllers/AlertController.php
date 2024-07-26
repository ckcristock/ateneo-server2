<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Person;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    //
    use ApiResponser;

    private function getCompany()
    {
        return Person::find(Auth()->user()->person_id)->company_worked_id;
    }

    public function index(Request $req)
    {
        # code...
        $alerts = Alert::where('user_id', $req->user_id)->with('transmitter')->orderByDesc('created_at');
        $temp = $alerts->get();
        $data = $alerts->limit(99)->get();
        $count = 0;
        foreach ($temp as $dat) {
            if ($dat->read_boolean == 0) {
                $count++;
            }
        }
        return $this->success(
            $data,
            $count
        );
    }

    public function read(Request $request)
    {
        $alert = Alert::where('id', $request->id)->first();
        $alert->update(['read_boolean' => 1]);

        $alerts = Alert::where('user_id', $request->user_id)->with('transmitter')->orderByDesc('created_at');
        $temp = $alerts->get();
        $data = $alerts->limit(99)->get();
        $count = 0;
        foreach ($temp as $dat) {
            if ($dat->read_boolean == 0) {
                $count++;
            }
        }
        return $this->success(
            $data,
            $count
        );
    }

    public function markAllAsRead()
    {
        $person_id = auth()->user()->person_id;
        Alert::where('read_boolean', 0)->where('user_id', $person_id)->update(['read_boolean' => 1]);
        return $this->success('Todas las notificaciones han sido marcadas como leidas');
    }

    public function paginate(Request $req)
    {
        $alerts = Alert::with('receiver')
            ->whereHas('receiver.contractultimate', function ($query) {
                $query->where('company_id', $this->getCompany());
            })
            ->orderBy('id', 'Desc')
            ->when($req->person_id, function ($q, $fill) {
                $q->where('user_id', $fill);
            })
            ->when($req->type, function ($q, $fill) {
                $q->where('type', 'like', "%$fill%");
            })
            ->when($req->end_date, function ($q, $fill) use ($req) {
                $q->whereBetween('created_at', [$req->start_date, $fill]);
            })
            ->when($req->dependency_id, function ($query, $fill) {
                $query->whereHas('receiver.contractultimate.position.dependency', function ($query) use ($fill) {
                    $query->where('id', $fill);
                });
            })
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));
        return $this->success($alerts);
    }

    public function store(Request $request)
    {
        $userId = $request->get("user_id");
        $groupId = $request->get("group_id");
        $dependencyId = $request->get("dependency_id");
        $people = Person::whereHas('contractultimate', function ($query) {
            $query->where('company_id', $this->getCompany());
        });
        if ($groupId != 0) {
            $people->whereHas('contractultimate.position.dependency.group', function ($query) use ($groupId) {
                $query->where('id', $groupId);
            });
        }
        if ($dependencyId != 0) {
            $people->whereHas('contractultimate.position.dependency', function ($query) use ($dependencyId) {
                $query->where('id', $dependencyId);
            });
        }
        if ($userId != 0) {
            Alert::create($request->all());
        } else {
            $people = $people->get();
            $this->createAlert($request, $people);
        }
        return $this->success('Agregada correctamente');
    }
    

    private function createAlert($request, $people)
    {
        foreach ($people as $person) {
            $user_id = $person->id;
            Alert::create(
                [
                    'person_id' => $request->person_id,
                    'user_id' => $user_id,
                    'type' => $request->type,
                    'description' => $request->description
                ],
            );
        }
    }
}
