<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuPermissionUsuario;
use App\Models\Usuario;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class MenuController extends Controller
{
    use ApiResponser;
    public static $mymenu = [];
    public static $user;
    public static $menuIds;
    static private $permissionSelectedPlain = [];


    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            self::$user = Usuario::where('person_id', $request->person_id)->first();

            $this->getAllPermissions($request->get('filteredMenu'));

            MenuPermissionUsuario::where('usuario_id', self::$user->id)->delete();

            $this->insertNewPermissions();

            self::$user->menu = $request->get('filteredMenu');
            self::$user->save();

            DB::commit();

            return $this->success('Actualización exitosa');
        } catch (Throwable $th) {
            DB::rollBack();

            return $this->error([
                $th->getMessage(),
                $th->getLine(),
                $th->getFile()
            ], $th->getCode());
        }
    }

    /**
     * Retrieve all permissions from the provided menu.
     *
     * @param mixed $menu The menu to extract permissions from.
     */
    private function getAllPermissions($menu)
    {
        $permissionStack = new \SplStack();
        $permissionStack->push($menu);

        while (!$permissionStack->isEmpty()) {
            $currentElement = $permissionStack->pop();
            foreach ($currentElement as $currentItem) {
                if (!empty($currentItem['child'])) {
                    $permissionStack->push($currentItem['child']);
                }
                if (isset($currentItem['permissions'])) {
                    self::$permissionSelectedPlain = array_merge(self::$permissionSelectedPlain, $currentItem['permissions']);
                }
            }
        }
    }

    /**
     * Insert new permissions into the database.
     */
    private function insertNewPermissions()
    {
        $permissionsToInsert = array_filter(self::$permissionSelectedPlain, function ($menu) {
            return $menu['Activo'] == true;
        });

        $menuPermissionIds = array_column($permissionsToInsert, 'menu_permission_id');

        $data = array_map(function ($menuPermissionId) {
            return [
                'menu_permission_id' => $menuPermissionId,
                'usuario_id' => self::$user->id
            ];
        }, $menuPermissionIds);

        DB::table('menu_permission_usuario')->insert($data);
    }

    /**
     * Get the menu by person ID.
     *
     * @param int $id
     * @throws Throwable
     * @return JsonResponse
     */
    public function getMenuByPerson(int $id): JsonResponse
    {
        try {
            self::$user = Usuario::with('menuPermissions.menuPermission')
                ->select(['id'])
                ->where('person_id', $id)
                ->firstOrFail();

            self::$menuIds = self::$user->menuPermissions->map(function ($permission) {
                return [
                    'menu_id' => $permission->menuPermission->menu_id,
                    'permission_id' => $permission->menuPermission->permission_id
                ];
            })->toArray();

            $menus = Menu::with('child')
                ->whereNull('parent_id')
                ->get(['id', 'name', 'icon']);

            $this->getPermissions($menus);

            return response()->json($menus);
        } catch (Throwable $th) {
            return $this->error([$th->getMessage(), $th->getLine(), $th->getFile()], $th->getCode());
        }

    }

    /**
     * Get the permissions for the given menus.
     *
     * @param Builder[]|Collection $menus
     * @return void
     */
    private function getPermissions($menus)
    {
        $menuIds = self::$menuIds;
        $menus->each(function ($menu) use ($menuIds) {
            if ($menu->link) {
                $this->processMenuPermissions($menu, $menuIds);
            } else {
                $menu->unsetRelation('permissions');
                $this->getPermissions($menu->child);
            }
        });
    }

    /**
     * Process the permissions for a given menu.
     *
     * @param mixed $menu
     * @param array $menuIds
     * @return void
     */
    private function processMenuPermissions($menu, $menuIds)
    {
        $menu->permissions->each(function ($permission) use ($menuIds) {
            $activo = collect($menuIds)->contains(function ($item) use ($permission) {
                return $item['menu_id'] == $permission->menu_id && $item['permission_id'] == $permission->permission_id;
            });
            $permission->Activo = $activo ? 1 : 0;
            $permission->menu_permission_id = $permission->id;
            $permission->name = $permission->permission->name;
            $permission->description = $permission->permission->description;
            $permission->public_name = $permission->permission->public_name;
            $permission->unsetRelation('permission');
        });
    }

    //? Comentada porque era una manera antigua de solicitar el menú del usuario, era demasiado lenta y hacía muchas peticiones a la base de datos. Ahora se usa el método getMenuByPerson
    /* public function getByPerson(Request $request)
    {
        self::$user = Usuario::where('person_id', $request->person_id)->first();
        $menus = Menu::whereNull('parent_id')->get(['id', 'name', 'icon']);
        foreach ($menus as &$item) {
            $item['child'] = [];
            if (!$item->link) {
                $item['child'] = $this->getChilds($item);
            }
        }
        return response()->json($menus);
    }

    private function getChilds($item)
    {
        $menus = Menu::where('parent_id', $item->id)->get();
        foreach ($menus as &$itemChild) {
            $itemChild->child = [];
            $itemChild->child = $this->getChilds($itemChild);
        }
        if ($item->link) {
            $query = DB::table('menu_permission AS MP')
                ->select(
                    'MP.menu_id',
                    'MP.permission_id',
                    'MP.id as menu_permission_id',
                    'P.name',
                    'P.public_name',
                    'P.description',
                    DB::raw('if(MPU.id,TRUE,FALSE) AS Activo')
                )
                ->leftJoin('menu_permission_usuario AS MPU', function ($join) {
                    $join->on('MPU.menu_permission_id', 'MP.id')
                        ->where('MPU.usuario_id', self::$user->id);
                })
                ->Join('permissions AS P', 'P.id', 'MP.permission_id')
                ->where('MP.menu_id', $item->id)
                ->get();

            $item->permissions = $query;
        }

        return $menus;
    } */

    //!! Versión antigua, lenta y poco óptima de guardar los permisos
    /* foreach (self::$permissionSelectedPlain as $menu) {
        if ($menu['Activo'] == true) {
            DB::insert(
                'insert into menu_permission_usuario (menu_permission_id, usuario_id) values (?, ?)',
                [$menu['menu_permission_id'], self::$user->id]
            );
        }
    } */
}
