<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\Section;
use App\Http\Requests\StoresectionsRequest;
use App\Http\Requests\UpdatesectionsRequest;

class SectionsController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    public function listSections(Request $request)
    {
        $name = $request->input('name', '');
        $fixed = $request->input('fixed', 0);
        $sections = Section::searchByName($name, $fixed);
        return $this->success($sections);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoresectionsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Section $sections)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatesectionsRequest $request, Section $sections)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Section $sections)
    {
        //
    }
}
