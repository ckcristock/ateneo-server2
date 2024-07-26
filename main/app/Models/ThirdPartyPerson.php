<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class ThirdPartyPerson extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $fillable = [
        'name',
        'n_document',
        'landline',
        'cell_phone',
        'email',
        'position',
        'observation',
        'third_party_id',
        'laboratory_id',
        'photo',
        'usuario',
        'password',
        'company_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function laboratory()
    {
        return $this->belongsTo(LaboratoriesPlace::class, 'laboratory_id', 'id');
    }

    public function thirdParty()
    {
        return $this->belongsTo(ThirdParty::class)->name();
    }

}
