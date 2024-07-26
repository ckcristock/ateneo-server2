<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class ClearCacheController extends Controller
{
    public function clearCache()
    {
        $people = DB::table('people')->get();

        foreach ($people as $person) {
            if (!file_exists('../DOCUMENTOS/' . $person->id)) {
                mkdir('../DOCUMENTOS/' . $person->id, 0777, true);
                echo $person->id;
                echo '<br>';
                echo $person->first_name;
            }
        }

        $exitCode = Artisan::call('config:clear');
        $exitCode = Artisan::call('cache:clear');
        $exitCode = Artisan::call('config:cache');

        return 'DONE'; 
    }
}
