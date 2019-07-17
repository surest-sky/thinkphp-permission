<?php
/**
 * Created by PhpStorm.
 * User: chenf
 * Date: 19-5-23
 * Time: 下午2:24
 */

namespace Surest\Model;

use Surest\Exceptions\PermissionException;
use Surest\Exceptions\RoleException;
use Surest\Traits\HasRoles;
use Surest\Traits\Helpers;
use think\Db;
use think\Model;

class Role extends BaseModel
{
    use HasRoles;

    protected $table = 'role';

    /**
     * 关联权限
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, RoleHasPermission::class);
    }

    /**
     * 为角色添加一个权限
     * @param $permission
     */
    public function givePermissionTo($permission)
    {
        if($permission instanceof Permission) {
            $permission = $permission['id'];
        }

        if(!$permissionModel = Permission::where('id', $permission)->find()) {
            throw new PermissionException($permission . '权限未找到');
        }

        # 检查当前权限是否存在
        $permission_ids = $this->array_pluck_($this->getAllPermissions(), 'id');
        if(!in_array($permissionModel->id, $permission_ids)) {
            return (bool)RoleHasPermission::create([
                'permission_id' => $permissionModel->id,
                'role_id' => $this->id
            ]);
        }
        return false;
    }

    /**
     * 为角色批量设置权限
     */
    public function syncPermissions(array $ids)
    {
        Db::startTrans();
        try{
            foreach ($ids as $id) {
                $this->givePermissionTo($id);
            }
        }catch (\Exception $e) {
            Db::rollback();
            throw new RoleException($e->getMessage());
        }
        Db::commit();
    }

    /**
     * 获取角色下的所有权限
     * @return mixed
     */
    public function getAllPermissions()
    {
        return $this->permissions->toArray();
    }

}