<?php
/**
 * Created by PhpStorm.
 * User: chenf
 * Date: 19-7-8
 * Time: 下午5:57
 */

namespace Surest\Traits;
use app\common\Traits\ApiResponse;
use GatewayWorker\Lib\Db;
use Surest\Exceptions\CreateTreeNodeException;
use Surest\Model\Permission;
use think\Container;

/**
 * 生成权限节点
 * Trait TreeNode
 * @package Surest\Traits
 */
Trait TreeNode
{
    public $app_ = ['admin'];
    public $regex_ = "\/\*\*\s+\*(.*?)\s+\*";

    /**
     * 获取所有的路由权限
     * @return array
     */
    private function getAllnode() : array
    {
        Container::get('route')->setTestMode(true);
        // 路由检测
        $path = Container::get('app')->getRoutePath();

        $files = is_dir($path) ? scandir($path) : [];

        foreach ($files as $file) {
            if (strpos($file, '.php')) {
                $filename = $path . DIRECTORY_SEPARATOR . $file;
                // 导入路由配置
                $rules = include $filename;

                if (is_array($rules)) {
                    Container::get('route')->import($rules);
                }
            }
        }
        $routeList = Container::get('route')->getRuleList();

        return $routeList;
    }

    /**
     * 初始化权限节点
     * @param bool $isDeleteAllPermission 是否清空以往权限节点
     * @return bool
     * @throws CreateTreeNodeException
     */
    public function init_node(bool $isDeleteAllPermission = false)
    {
        try {
            \think\Db::startTrans();
            if($isDeleteAllPermission) {
                Permission::where('id', '>', 0)->delete();
            }
            $data = $this->filter_node();
            foreach ($data as $v) {
                if($v['method'] == "get") {
                    $v['hidden'] = 0;
                }else{
                    $v['hidden'] = 1;
                }
                if($permission = Permission::where('rule', $v['rule'])->where('method', 'like', "%{$v['method']}%" )->find()) {
                    Permission::update($v, ['id' => $permission->id]);
                }else{
                    Permission::create($v);
                }

            \think\Db::commit();
            }
        }catch (\Exception $exception) {

            \think\Db::rollback();
            throw new CreateTreeNodeException("生成节点发生错误: " . $exception->getMessage());
        }

        return true;
    }

    /**
     * 处理权限节点的数据
     */
    public function filter_node()
    {
        $nodes = $this->getAllnode();
        $data = [];
        $nodes = $nodes[request()->host()];
        foreach ($nodes as $k => $node) {
            if ($this->checkAppModule($node['route'])) {
                $data[$k]['method'] = $node['method'];  // 路由方法
                $data[$k]['name'] = $this->getNodeName($node['route']);
                $data[$k]['route'] = $node['route']; // 路由对应的控制器
                $data[$k]['rule'] = $node['rule']; // 路由地址
                $data[$k]['p_id'] = $this->getClassPid($node['route']);
            }
        }
        return $data;
    }

    /**
     * 反射读取节点注释 || 获取方法的控制器位置
     * @param $route
     * @return string
     */
    public function getNodeName($route)
    {
        # 先获取类名称
        $classAndMethod = $this->getClassAndMethod($route);

        # 读取方法的注释
        $method_name = $this->getMethodReflectDoc($classAndMethod['class'] , $classAndMethod['method']);

        return $method_name;
    }

    /**
     * 获取pid节点
     * @param $node
     */
    public function getClassPid($route) :int
    {
        # 先获取类名称
        $classAndMethod = $this->getClassAndMethod($route);
        # 读取类的名称, 不存在则创建他
        $p_id = $this->findClassCreate($classAndMethod['class']);

        return (int)$p_id;
    }

    /**
     * 获取类注释
     * @param $route
     */
    private function getClassAndMethod($route) :array {
        $index = strripos($route, '/');
        $class = substr($route, 0, $index);
        $method = substr($route, ($index+1));
        $class_n = explode('/', $class);
        $class_n[count($class_n) - 1] = ucfirst($class_n[count($class_n) - 1]); // 改变为首字母大写
        $app_name = array_shift($class_n);  // 返回的是例如 index , admin 之类的应用模块名称
        $p_class = implode('\\', $class_n); // 子模块进行拼接组合
        $class = '\app' . '\\' .$app_name . '\\' . "controller" . '\\' . $p_class; // 获取到的类

        return compact('class', 'method');
    }

    /**
     * 获取方法名称 / 注释
     * @param $class
     */
    public function getMethodReflectDoc($class, $method)
    {
        $reflection = new \ReflectionClass($class);
        try {
            $methodReflection = $reflection->getMethod($method);
        }catch (\ReflectionException $e) {
            return $class . '\\' . $method;
        }
        $doc = $methodReflection->getDocComment();
        if(preg_match("/$this->regex_/", $doc, $match)) {
            if(isset($match[1]) && $match[1]) {
                return $match[1];
            }
        }
        return $class . '\\' . $method;
    }


    /**
     * 获取类名称 / 注释
     * @param $class
     */
    public function getClassReflectDoc($route)
    {
        $reflection = new \ReflectionClass($route);
        $doc = $reflection->getDocComment();
        if(preg_match("/$this->regex_/", $doc, $match)) {
            if(isset($match[1]) && $match[1]) {
                return $match[1];
            }
        }
        return $route;
    }

    /**
     * 获取类的id值
     */
    public function findClassCreate($route)
    {
        $data = [
            'name' => $this->getClassReflectDoc($route), // 类的名称
            'route' => $route , // 路由对应的控制器
            'rule' => '#',
            'method' => '*',
        ];

        # 检查数据库中是否已经生成了这个类节点
        $permission = Permission::where('route', $route)->find();
        if($permission) {
            Permission::update($data, ['id' => $permission->id]);
            return $permission->id;
        }

        $permission = Permission::create($data);

        return $permission->id;
    }

    /**
     *  检查是否需要生成当前模块的权限节点
     *  默认会读取所有的权限节点,
     *  index , admin 只要有路由的地方都会被当成一个模块来处理
     * @param $node
     * @return bool
     */
    public function checkAppModule($node)
    {
        $nodes = explode('/', $node);
        $current_node = $nodes[0];
        if(!in_array($current_node, $this->app_)) {
            return false;
        }

        return true;
    }

    /**
     * 递归生成树
     * @param  array  $list  要转换的数据集
     * @param  string  $pk    自增字段（栏目id）
     * @param  string  $pid   parent标记字段
     * @param  string  $child 孩子节点key
     * @param  integer $root  根节点标识
     * @return array
     */
    public static function recursive_make_tree($list, $pk = 'id', $pid = 'p_id', $child = 'children', $root = 0)
    {
        $tree = [];
        foreach ($list as $key => $val) {
            if ($val[$pid] == $root) {
                //获取当前$pid所有子类
                unset($list[$key]);
                if (!empty($list)) {
                    $child = self::recursive_make_tree($list, $pk, $pid, $child, $val[$pk]);
                    if (!empty($child)) {
                        $val['children'] = $child;
                    }
                }
                $tree[] = $val;
            }
        }
        return $tree;
    }
}