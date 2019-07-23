## thinkphp-permission RABC 权限管理包

![图片描述][1]

Github地址： 

https://github.com/surest-sky/thinkphp-permission

DEMO展示:

[后台管理 ： http://v-web.surest.cn](http://v-web.surest.cn)

由此开发出的一个demo: 

[https://github.com/surest-sky/think-vue-admin-api](https://github.com/surest-sky/think-vue-admin-api)

账号 admin
密码 admin123

### 支持功能如下

- 直接、间接获取用户权限

- 角色获取权限

- 角色赋予权限

- 用户赋予角色

- 一键生成权限节点（支持多种生成节点方式）

    支持路由生成

    控制器文件扫描生成

### 为什么要开发它

`ThinkPhp` 生态包生态杂乱无章，它支持 `composer` 一键安装，整合tp最新的预加载及其关联语法进行操作

参阅了其他的包之后，它

1、能快速完成权限管理的一系列操作

2、使用 `Trait` 引入, 极其简单

3、遵循 `OPP` 面向对象思路，可扩展，部分参数可在您的需求上配置

4、权限控制撇弃以往的继承 `Base` 父方法的方式进行，全新引入了 中间件 思想， 何为中间件： [tp5.1 文档- 定义中间件](https://www.kancloud.cn/manual/thinkphp5_1/564279)

5、权限控制支持方法级别的控制，当然如果在此基础上，可以完成细粒度的权限控制

### 缺点

1.1版本 暂时只支持 `大于 > thinkphp 5.1`  

1.1版本 只能使用路由生成节点

> 技术永远是需要进步的，也不准备兼容低版本


### 安装

#### 镜像源

[阿里云 Composer 全量镜像](http://surest.cn/archives/98/)

### 引入方式

    命令行下输入：

    composer require surest/thinkphp-permission

    或者在 composer.json 中添加

    ....
    "require": {
        "php": ">=5.6.0",
        "surest/thinkphp-permission": "^0.1.0"
    },
    .....

    执行命令

    composer update

### 使用

#### 数据库导入

    root  #> mysql -uusername -p 
    mysql #> create database permission;
    mysql #> source vendor/surest/thinkphp-permission/permission.sql;

#### 初始化权限节点方式

引入生成节点文件: [demo](https://github.com/surest-sky/think-vue-admin-api/blob/master/application/admin/controller/Permission.php#L21)

    use Surest\Model\Permission as PermissionModel;
    use Surest\Traits\TreeNode;
    ..
    
    class {
        use TreeNode; // 引入这个 trait 即可
        public function init_permission() {

            // 执行这个方法后，节点就自动生成了
            $this->init_node((bool) request()->param('is_delete', 0));
        }
    }

> 生成权限节点的原理大概如下， 获取到所有已知的路由，反射获取他们的注释、方法名称等等

~~!! 权限节点的名称注释如下

1、类

    /**
    * 权限管理
    * Class Permission
    * @package app\admin\controller
    */
    class Permission extends BaseController
    {
        ..
    }


    /**
     * 权限管理
     */
    public function index()
    {
        
        ....
    }


以上的即为路由名称

    

~~ 当然，我已经在开发中的一个基于 [vue-element-amdin](https://github.com/PanJiaChen/vue-element-admin) 的快速开发后台管理 ， 见： [https://github.com/surest-sky/think-vue-admin-api](https://github.com/surest-sky/think-vue-admin-api)


### 使用

创建权限

    PermissionModel::create($data);

创建角色

    RoleModel::create(['name' => $data['name'], 'remark' => $data['remark'] ?? ''])

以上的没什么好说的， 引入的上面model 的方式如下

    use Surest\Model\Role as RoleModel;
    use Surest\Model\Permission as PermissionModel;

#### 实例化获取一个user

    使用  `use HasRoles`;
    
    ...
    use Surest\Traits\Helpers;
    
    class User extends Model
    {
        use HasRoles;
    ...

    `$user = User::find(1);`

#### 获取所有权限

    $user->getAllPermissions();

    --
    $is_tree bool 是否渲染成树结构
    传入 getAllPermissions(true); 会生成一个树结构的权限节点，可以用来生成菜单
    
#### 检查是否拥有某个权限

    // 传入的值或者 权限模型 或者 权限id
    $user->can(1);
    $permission = PermissionModel::find(1);
    $user->can(permission);  # 返回 bool
    
#### 获取当前用户的所有角色

    $user->getAllRoles();

#### 给角色赋予一个权限

    // 传入的值或者 权限模型 或者 权限id
    $role->givePermissionTo($permission);
    
#### 给角色赋予多个权限
    
    $role->syncPermissions([$user_permission['id'], $article_permission['id']]);
    
#### 当前角色所有的权限

    $role->getAllPermissions()
    
#### 为用户赋予一个直接权限
        
    $user->givePermissionTo($permission);
    
#### 获取用户的所有权限

    $user->getAllPermissions()


### 使用 DEMO 

见github： [https://github.com/surest-sky/think-vue-admin-api/tree/master/application/admin/controller](https://github.com/surest-sky/think-vue-admin-api/tree/master/application/admin/controller)

> 使用的操作没有详尽覆盖，可以参考源码，全程中文注释，也可以用来学习

MIT


  [1]: https://s2.ax1x.com/2019/07/20/eSuofe.png
