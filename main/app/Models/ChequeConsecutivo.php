<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChequeConsecutivo extends Model
{
    use HasFactory;

    protected $table = 'Cheque_Consecutivo';
    protected $primaryKey = 'Id_Cheque_Consecutivo';
    protected $fillable = [
        'Id_Plan_Cuentas',
        'Prefijo',
        'Inicial',
        'Final',
        'Consecutivo',
        'Estado'
    ];
}
