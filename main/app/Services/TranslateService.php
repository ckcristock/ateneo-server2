<?php

namespace App\Services;

class TranslateService
{
    public function translateDay($day)
    {
        switch ($day) {
            case 'Monday':
                return 'Lunes';
            case 'Tuesday':
                return 'Martes';
            case 'Wednesday':
                return 'Miercoles';
            case 'Thursday':
                return 'Jueves';
            case 'Friday':
                return 'Viernes';
            case 'Saturday':
                return 'Sabado';
            case 'Sunday':
                return 'Domingo';
            default:
                break;
        }
    }

    public function traslateIntDay($nro)
    {
        switch ($nro) {
            case 1:
                return 'Lunes';
            case 2:
                return 'Martes';
            case 3:
                return 'Miércoles';
            case 4:
                return 'Jueves';
            case 5:
                return 'Viernes';
            case 6:
                return 'Sabado';
            case 0:
                return 'Dommingo';
            default:
                break;
        }
    }

    public function translateMonth($month)
    {
        switch ($month) {
            case 1:
                return 'Enero';
            case 2:
                return 'Febrero';
            case 3:
                return 'Marzo';
            case 4:
                return 'Abril';
            case 5:
                return 'Mayo';
            case 6:
                return 'Junio';
            case 7:
                return 'Julio';
            case 8:
                return 'Agosto';
            case 9:
                return 'Septiembre';
            case 10:
                return 'Octubre';
            case 11:
                return 'Noviembre';
            case 12:
                return 'Diciembre';
            default:
                break;
        }
    }
}
