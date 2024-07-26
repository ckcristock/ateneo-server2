<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Usuario;
use App\Traits\ApiResponser;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MaintenanceController extends Controller
{
    use ApiResponser;

    public function generateUsers()
    {
        $people = Person::all(['id', 'identifier']);

        foreach ($people as $person) {
            if (Usuario::where('person_id', $person->id)->exists() === false) {
                Usuario::create([
                    'usuario' => $person->identifier,
                    'person_id' => $person->id,
                    "password" => Hash::make($person->identifier),
                    "change_password" => 1,
                    "state" => "activo"
                ]);
                Log::info("El usuario {$person->identifier} ha sido creado");
            }
        }

        return $this->success("Todos los usuarios han sido generados");
    }

    public function runCommands()
    {
        $output = [];
        Artisan::call('optimize');
        $output[] = Artisan::output();
        Artisan::call('migrate');
        $output[] = Artisan::output();
        return $this->success(['output' => $output]);
    }
}
