<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use App\Models\TypeDocument;
use Illuminate\Http\Request;

class TypeDocumentController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(TypeDocument::get(['name As text', 'id As value']));
    }
}
