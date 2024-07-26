<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{

    // 'person_id' => $person->id,
    // 'usuario' => $person->identifier,
    // 'password' => Hash::make($person->identifier),
    // 'change_password' => 1,

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'usuario';
    protected $fillable = [
        'usuario',
        'person_id',
        'menu',
        'password',
        'change_password',
        'password_updated_at',
        'state',
        'board_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
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

    public function person()
    {
        return $this->belongsTo(Person::class)->fullName();
    }

    public function personImageName()
    {
        return $this->belongsTo(Person::class, 'person_id', 'id')->imageName();
    }

    protected $casts = [
        'menu' => 'array'
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    public function personName()
    {
        return $this->belongsTo(Person::class, 'person_id', 'id')->completeName();
    }

    public function menuPermissions()
    {
        return $this->hasMany(MenuPermissionUsuario::class, 'usuario_id', 'id');
    }
}
