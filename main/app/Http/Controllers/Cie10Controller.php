<?php

namespace App\Http\Controllers;

use App\Models\Cie10;
use App\Http\Resources\Cie10Resource;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class Cie10Controller extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {

            $cies10 = Cie10::query();
            $cies10->when(request()->input('search') != '', function ($q) {
                $q->where(function ($query) {
                    $query->where('description', 'like', '%' . request()->input('search') . '%')
                        ->orWhere('code', 'like', '%' . request()->input('search') . '%');
                });
            });

            return $this->success(Cie10Resource::collection($cies10->take(10)->get()));
            // return $this->success(Cie10Resource::collection(Cie10::get()));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    public function getAll()
    {
        return $this->success(DB::table('cie10s')->get());
    }
}
