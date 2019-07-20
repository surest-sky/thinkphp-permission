## 说明

这是一个基于 thinkphp5.1 开发的权限管理包

功能支持

- 直接获取用户权限

- rabc 通过角色获取权限

- 角色赋予权限

- 用户赋予角色

- 一键生成权限节点

**目前没有测试过其他版本**

## 安装

    composer require surest/thinkphp-permission:dev-master
    
数据库 :
    
    source ./vendor/surest/thinkphp-permission/permission.sql
    User表可以选择删除,这里只是演示使用


## 使用实例

使用  `use HasRoles`;
    
    ...
    use Surest\Traits\Helpers;
    
    class User extends Model
    {
        use HasRoles;
    ...

`$user = User::find(1);`

### 获取所有权限

    $user->getAllPermissions();
    
### 检查是否拥有某个权限

    $user->can('permission_name');
    
### 获取当前用户的所有角色

    $user->getAllRoles();
    
## 权限管理

### 给角色赋予一个权限

    $role->givePermissionTo($manager_permission);
    
### 给角色赋予多个权限
    
    $role->syncPermissions([$user_permission['name'], $article_permission['name']]);
    
### 当前角色所有的权限

    $role->getAllPermissions()
    
### 为用户赋予一个直接权限
        
    $user->givePermissionTo($message_permission);
    
### 获取用户的所有权限

    $user->getAllPermissions()

## 使用参考文件

https://github.com/surest-sky/thinkphp-permission/tree/master/src/Example



