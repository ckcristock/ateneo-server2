<?php

use App\Models\Person;

if (!function_exists('getCompanyWorkedId')) {

    function getCompanyWorkedId()
    {
        return Person::find(auth()->user()->person_id)->company_worked_id;
    }
}
