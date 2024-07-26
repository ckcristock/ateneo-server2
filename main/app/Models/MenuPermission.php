<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuPermission extends Model
{
    protected $table = 'menu_permission';

    protected $fillable = [
        'menu_id',
        'permission_id'
    ];

    public function users()
    {
        return $this->belongsToMany(Usuario::class, 'menu_permission_usuario', 'menu_permission_id', 'usuario_id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
