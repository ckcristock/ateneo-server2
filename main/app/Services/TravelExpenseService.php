<?php

namespace App\Services;

use App\Models\TravelExpense;

class TravelExpenseService
{

    static public function show($id)
    {

        return TravelExpense::with("destiny")
            ->with([
                "expenseTaxiCities" => function ($q) {
                    $q->select("*")
                        ->with("taxiCity")
                        ->with("taxiCity.city")
                        ->with("taxiCity.taxi");
                },
                "person" => function ($q) {
                    $q->select("id", "first_name", "first_surname", 'passport_number', 'visa');
                },
            ])
            ->with("origin")
            /* ->with("user") */
            ->with("person.contractultimate")
            ->with("hotels.accommodations")
            ->with("transports")
            ->with("airTransports")
            ->with("feedings")
            ->with("te_hotel")
            ->where("id", $id)
            ->first();
    }

    static public function paginate($companyId)
    {
        return TravelExpense::with("destiny")
            ->with("origin")
            ->with("user")
            ->with("user.person")
            ->with("person", "hotels")
            ->when(request()->get('person_id'), function ($q, $fill) {
                $q->where('person_id', 'like', '%' . $fill . '%');
            })
            ->when(request()->get('creation_date'), function ($q, $fill) {
                $q->where('created_at', 'like', '%' . $fill . '%');
            })
            ->when(request()->get('departure_date'), function ($q, $fill) {
                $q->where('departure_date', 'like', '%' . $fill . '%');
            })
            ->when(request()->get('state'), function ($q, $fill) {
                if (request()->get('state') == 'Todos') {
                    return null;
                } else {
                    $q->where('state', 'like', '%' . $fill . '%');
                }
            })
            ->whereHas('person.contractultimate', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->orderByDesc('created_at')
            ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1));
    }
}
