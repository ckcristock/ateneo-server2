<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class CaracterizacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('verifyjson');
    }

    public function PacienteEdadSexo()
    {
        $data = DB::select('
            SELECT
                CASE 
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 0 AND 4) THEN "a-0 a 4"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 5 AND 9) THEN "b-5 a 9"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 10 AND 14) THEN "c-10 a 14"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 15 AND 19) THEN "d-15 a 19"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 20 AND 24) THEN "e-20 a 24"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 25 AND 29) THEN "f-25 a 29"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 30 AND 34) THEN "g-30 a 34"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 35 AND 39) THEN "h-35 a 39"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 40 AND 44) THEN "i-40 a 44"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 45 AND 49) THEN "j-45 a 49"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 50 AND 54) THEN "k-50 a 54"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 55 AND 59) THEN "l-55 a 59"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 60 AND 64) THEN "m-60 a 64"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 65 AND 69) THEN "n-65 a 69"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 70 AND 74) THEN "o-70 a 74"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 75 AND 79) THEN "p-75 a 79"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) BETWEEN 80 AND 84) THEN "q-80 a 84"
                    WHEN ((YEAR(CURDATE())-YEAR(Fecha_Nacimiento)) >= 85) THEN "r-85 o más"
                END age,
                SUM(IF(Sexo="F",1,0)) female,
                SUM(IF(Sexo="M",1,0)) male,
                COUNT(*) Total
            FROM Caracterizacion
            GROUP BY age  
            ORDER BY age ASC
        ');
        return $data;
    }

    public function PacientePatologiaSexo()
    {
        return [];
    }
}
