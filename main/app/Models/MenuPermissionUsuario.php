<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuPermissionUsuario extends Model
{
    protected $table = 'menu_permission_usuario';

    protected $fillable = [
        'menu_permission_id',
        'usuario_id'
    ];

    public function menuPermission()
    {
        return $this->belongsTo(MenuPermission::class, 'menu_permission_id');
    }
}
