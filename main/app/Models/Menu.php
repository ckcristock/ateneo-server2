<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menus';

    protected $fillable = [
        'parent_id',
        'name',
        'icon',
        'link',
    ];

    public function child2()
    {
        return $this->hasMany(Menu::class, 'parent_id', 'id');
    }

    public function child()
    {
        return $this->hasMany(Menu::class, 'parent_id')->with('child', 'permissions');
    }

    public function scopeFindCustom($query, $id)
    {
        return $query->select(['name', 'id', 'icon'])->firstWhere('id', $id);
    }

    public function usuario()
    {
        return $this->BelongsToMany(Usuario::class);
    }

    /* public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    } */

    public function permissions()
    {
        return $this->hasMany(MenuPermission::class)->with('permission');
    }
}
