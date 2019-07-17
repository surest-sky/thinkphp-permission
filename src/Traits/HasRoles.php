<?php
/**
 * Created by PhpStorm.
 * User: chenf
 * Date: 19-5-23
 * Time: 下午2:58
 */

namespace Surest\Traits;

use Surest\Exceptions\PermissionException;
use Surest\Exceptions\RoleException;
use Surest\Model\ModelHasPermission;
use Surest\Model\Role;
use Surest\Model\ModelHasRole;
use Surest\Model\Permission;
use Surest\Model\RoleHasPermission;
use think\Db;

Trait HasRoles
{
    use Helpers;

    use TreeNode;
    /**
     * 关联角色
     * @return mixed
     */
    public function roles()
    {
        return $this->hasManyThrough(Role::class, ModelHasRole::class, 'user_id', 'id', $this->pk);

    }

    /**
     * 关联权限
     * @return mixed
     */
    public function permissions()
    {
        return $this->hasManyThrough(Permission::class, ModelHasPermission::class, 'user_id', 'id', 'id');
    }

    /**
     * 为用户添加一个角色
     * @param $role
     */
    public function assignRole($role) :bool
    {
        if($role instanceof Role) {
            $role = $role['id'];
        }
        $role_id = $role;

        if(!$roleModel = Role::where('id' , $role_id)->find()) {
            throw new RoleException($role . '角色未找到');
        }

        return (bool)ModelHasRole::create([
            'role_id' => $roleModel->id,
            'user_id' => $this->id
        ]);
    }

    /**
     * 为用户批量设置角色
     */
    public function syncRole(array $ids)
    {
        Db::startTrans();
        try{
            // 移除之前的角色
            ModelHasRole::where('user_id', $this->id)->delete();
            foreach ($ids as $id) {
                $this->assignRole($id);
            }
        }catch (\Exception $e) {
            Db::rollback();
            throw new RoleException($e->getMessage());
        }
        Db::commit();
    }

    /**
     * 为用户直接添加一个权限
     * @param $permission
     */
    public function givePermissionTo($permission) :bool
    {
        if($permission instanceof Permission) {
            $permission = $permission['name'];
        }

        if(!$permissionModel = Permission::where('name', $permission)->find()) {
            throw new PermissionException($permission . '权限未找到');
        }

        return (bool)ModelHasPermission::create([
            'permission_id' => $permissionModel->id,
            'user_id' => $this->id
        ]);
    }

    /**
     * 判断用户是否使用某个权限
     * @param $permission mixed 或者是权限 或者 权限id
     */
    public function can($permission) :bool
    {
        if($permission instanceof Permission) {
            $permission_id = $permission->id;
        }else{
            $permission_id = $permission;
        }

        $permissions = $this->getAllPermissions();
        $permission_ids = $this->array_pluck_($permissions, 'id');

        if(in_array($permission_id, $permission_ids) || self::ADMIN_USER == $this->username) {
            return true;
        }
        return false;
    }

    /**
     * 获取当前用户的所有权限
     * @param $is_tree bool 是否渲染成树结构
     * @return array
     */
    public function getAllPermissions($is_tree = false)
    {
        $permissions = $this->permissions->toArray();
        $permissions_ = [];
        if($ids = $this->roles->toArray()) {  # 查询间接权限
            $ids = $this->array_pluck_($ids, 'id');
            $permissions_ = Role::where('id', 'in', $ids)->with('permissions')->select()->toArray();
            $permissions_ = $this->array_pluck_($permissions_, 'permissions');
            $result = [];
            foreach ($permissions_ as $item) {
                $result = array_merge($result, $item);
            }
            $permissions_ = $result;
        }

        $permissions = array_merge($permissions_, $permissions);

        # 检查是不是拥有所有权限
        $permission_names = $this->array_pluck_($permissions, 'name');
        if(in_array('*', $permission_names) || self::ADMIN_USER == $this->username) {
            return Permission::all()->toArray();
        }
        $permissions = array_unique($permissions, SORT_REGULAR);;

        return $is_tree ? self::recursive_make_tree($permissions_) : $permissions;
    }

    /**
     * 获取当前用户的所有角色
     * @return mixed
     */
    public function getAllRoles()
    {
        return $this->roles->toArray();
    }

    /**
     * 关联角色
     * 由于Tp无法进行远程一对多关联预载入, 就采用当前方法
     * 获取器获取权限
     * @param $val
     * @param $data
     * @return mixed
     */
    public function getToRolesAttr($val, $data) {
        return self::find($data['id'])->roles;
    }

    /**
     * 清空我的全部角色
     */
    public function clearRoles()
    {
        ModelHasRole::where('user_id', $this->id)->delete();
    }

    /**
     * 请空我的权限
     */
    public function clearPermissions()
    {
        RoleHasPermission::where('role_id', $this->id)->delete();
    }
}