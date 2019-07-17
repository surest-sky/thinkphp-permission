<?php
/**
 * Created by PhpStorm.
 * User: chenf
 * Date: 19-5-27
 * Time: 下午4:21
 */

namespace Surest\Example;

use Surest\Exceptions\PermissionException;
use Surest\Model\Permission;
use Surest\Model\Role;
use think\Model;

/**
 * 使用示例
 * Class TestExample
 * @package Surest\Example
 */
class TestExample
{
    public function create_role()
    {
        $role = Role::create([
            'name' => "站长",
            'remark' => '最高管理员'
        ]);

        # 创建一个权限
        $manager_permission = Permission::create([
            'name' => '*',
            'title' => '所有权限',
        ]);

        $user_permission = Permission::create([
            'name' => 'Index/User', // 根据操作方法来设置
            'title' => '用户管理'
        ]);

        $article_permission = Permission::create([
            'name' => 'Index/Article', // 根据操作方法来设置
            'title' => '文章管理'
        ]);

        # 给角色赋予一个权限
        $role->givePermissionTo($manager_permission);

        # 给角色赋予多个权限
        $role->syncPermissions([$user_permission['name'], $article_permission['name']]);

        echo "当前角色的所有权限 || ";

        # 打印当前角色所有的权限
        dump($role->getAllPermissions());

        return $role;
    }

    /**
     * 添加直接权限
     */
    public function create_permission($user)
    {
        if(!$user instanceof Model) {
            throw new PermissionException('请传入实例化的用户模型');
        }

        $message_permission = Permission::create([
            'name' => 'Index/Message', // 根据操作方法来设置
            'title' => '消息管理'
        ]);

        $info_permission = Permission::create([
            'name' => 'Index/Info', // 根据操作方法来设置
            'title' => '信息管理'
        ]);

        # 为用户赋予一个直接权限
        $user->givePermissionTo($message_permission);

        dump($user->getAllPermissions());

        # 检查用户是否能是否某个权限
        echo $user->can('Index/Message') ? "信息管理 - 能" : "信息管理 - 否";
        echo '|||';
        echo $user->can('Index/Message') ? "消息管理 - 能" : "信息管理 - 否";
    }

    /**
     * 给用户添加角色
     */
    public function add_user_role($user)
    {
        if(!$user instanceof Model) {
            throw new PermissionException('请传入实例化的用户模型');
        }

        #　创建一个带角色的权限
        $role = $this->create_role();

        $user->assignRole($role);

        echo "当前用户的所有权限 || ";
        dump($user->getAllPermissions());

        echo "当前角色的所有角色 || ";
        dump($user->getAllRoles());
    }
}