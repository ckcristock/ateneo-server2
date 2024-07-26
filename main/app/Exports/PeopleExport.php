<?php

namespace App\Exports;

use App\Models\Person;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PeopleExport implements FromView, ShouldAutoSize
{
    private $people;
    public function __construct($people)
    {
        $this->people = $people;
    }
    public function view(): View
    {
        return view('exports.PeopleExport', ['people' => $this->people]);
    }
}
