<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LayoffsCertificate extends Model
{
    protected $fillable = [
        'reason_withdrawal',
        'person_id',
        'reason',
        'monto',
        'valormonto',
        'document',
        'state'
    ];

    protected $appends = ['downloading'];

    public function person()
    {
        return $this->belongsTo(Person::class)->with('severance_fund')->name()->fullName();
    }

    public function reason_withdrawal_list()
    {
        return $this->belongsTo(ReasonWithdrawal::class, 'reason_withdrawal');
    }

    public function getDownloadingAttribute()
    {
        return false;
    }
}
