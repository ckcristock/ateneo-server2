<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\Pqrs;
use Illuminate\Support\Facades\DB;

class PortalController extends Controller
{
    public function pqrs()
    {
        try {
            $email = DB::table('site_settings')->select('email')->first();
            Mail::to($email)->send(new Pqrs());
            return response()->success('Operación realizada correctamente');
        } catch (\Exception $th) {
            return response()->error([$th->getMessage(), 404]);
        }
    }
}
