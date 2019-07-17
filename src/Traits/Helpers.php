<?php
/**
 * Created by PhpStorm.
 * User: chenf
 * Date: 19-5-23
 * Time: 下午3:24
 */

namespace Surest\Traits;

use Surest\Exceptions\ClassNoFundException;
use Surest\Exceptions\PathException;
use Surest\Model\Permission;

Trait Helpers
{
    protected $_path = null;
    protected $_filter_file = [
        '.',
        '..'
    ];
    protected $files = [];

    protected $class_full_name;

    # 方法注释的正则
    protected $_action_regex = "#-(.*)-#";

    #　类注释的正则
    protected $_class_regex = "/&(.*)&/i";

    protected $_isNullityNodeDelete = true; // 是否删除无效的节点

    /*
    * 可参考 laravel array_pluck 数组
    * $data 数组
    * $key 要获取的值
    */
    public function array_pluck_($data, $key, $o = false): array
    {
        if (!$data || empty($data)) {
            return [];
        }
        $new_data = [];
        foreach ($data as $value) {
            if (isset($value[$key])) {
                if ($temp = $value[$key]) {
                    $new_data[] = $temp;
                }
            }
        }
        return $new_data;
    }

    /**
     * 初始化合并配置属性　和　自定义文件属性
     */
    private function init_set()
    {
        if(!isset($this->path)) {
            throw new PathException('引导地址属性不存在');
        }
        $this->_action_regex = $this->action_regex　?? $this->_action_regex;
        $this->_class_regex = $this->class_regex_ ?? $this->_class_regex;
        $this->_filter_file = array_merge($this->_filter_file, $this->filter_file ?? []);
        $this->_isNullityNodeDelete = $this->isNullityNodeDelete ?? $this->_isNullityNodeDelete;
        $this->clearNullityNode();
    }

    /**
     * 初始化权限控制,并且生成节点
     */
    public function init_($path)
    {
        $this->_path = getcwd() . '/../' . $path;
        $this->init_set();
        $files = $this->getCurrentDirFilesList($this->_path);
        $files = $this->fileterFiles($files);

        $this->getFilesBody($files);
    }

    /**
     * 忽略掉一些不需要的文件
     * @param $files
     */
    public function fileterFiles($files)
    {
        $files = array_filter($files, function ($file){
            if(preg_match('/\.php/',$file)) {
                foreach ($this->_filter_file as $filter_file_) {
                    if(!preg_match("#$filter_file_#", $file)) {
                        return $file;
                    }
                }
            }
        });

        return $files;
    }

    /**
     * 获取指定目录下的所有文件, 忽略掉配置属性设置的文件
     * @return array
     */
    private function getCurrentDirFilesList($dir, $parent_path = null)
    {
        if(!is_dir($dir)){
            return false;
        }else{
            $handle = opendir($dir);
            while(($file = readdir($handle)) !== false){
                if($file != '.' && $file != '..'){
                    if(is_dir($dir. DIRECTORY_SEPARATOR . $file)){
                        $this->getCurrentDirFilesList($dir. DIRECTORY_SEPARATOR . $file);//如果是目录的话，递归
                    }else{
                        array_push($this->files, $dir .DIRECTORY_SEPARATOR . $file);
                    }
                }
            }
            closedir($handle);
        }
        return $this->files;
    }

    /**
     * 忽略掉方法名称
     * @param $actions
     */
    public function getCurrentClassActionsList($actions)
    {
        $actions = array_diff($actions, $this->filter_action);
        $actions = array_map(function ($action){
            return trim($action);
        }, $actions);
        return $actions;
    }

    /**
     * 获取方法注释, 拿到方法名称
     * @param $action
     * @return bool
     */
    private function getActionDoc($action)
    {
        $method = new \ReflectionMethod($this->class_full_name, $action);
        $method_doc = $method->getDocComment();
        if(!preg_match($this->_action_regex, $method_doc, $match)) {
            return false;
        }
        $action_title = $match[1];
        return $action_title;
    }

    /**
     * 获取文件内容
     * @param $files
     */
    private function getFilesBody($files)
    {
        foreach ($files as $file) {
            $body = file_get_contents($file);
            # 获取类注释
            preg_match_all($this->_class_regex, $body, $titles); // TODO: check
            if(!$temp = $titles[1]) {
                continue;
            }
            $class_title = $temp[0];

            preg_match('/namespace(.*);/', $body, $matches);

            # 匹配控制器
            if(!isset($matches[1]) || !$class = $matches[1]) {
                continue;
            }

            $class_name = trim($class);
            # 匹配方法名称
            preg_match('/\nclass(.*)\sextend/', $body, $matches);
            if(!isset($matches[1]) || !$action = $matches[1]) {
                continue;
            }

            $action_name = trim($action);
            $class_name = $class_name . '\\' . $action_name;

            if(!$this->checkClassExist($class_name)) {
                throw new ClassNoFundException("{$this->class_full_name} 未找到");
            }

            preg_match_all("/.*?public.*?function(.*?)\(.*?\)/i", $body, $matches); // TODO: check

            if(!$actions = $matches[1]) {
                continue;
            }
            dump($actions);
            $this->create_permissions($class_name, $class_title, $actions);
        }
    }

    /**
     * 检查类是否存在
     * @param $name
     */
    public function checkClassExist($class) :bool
    {
        $this->setClassFullName($class);
        return class_exists($class);
    }

    /**
     * 创建权限节点
     * @param $class_name
     * @param $class_title
     * @param $actions
     */
    public function create_permissions($class_name, $class_title, $actions)
    {
        $class_name = str_replace("app\admin\controller\\",'', $class_name);
        $class_name = strtolower(str_replace("app\admin\controller\\",'', $class_name));

        $pid = $this->PermissExistToCreate("#", $class_name, $class_title);
        $actions = $this->getCurrentClassActionsList($actions);

        if(empty($actions)) {
            return;
        }

        # 读取方法名称, 反射获取类注释
        foreach ($actions as $action) {
            if(!$action_title = $this->getActionDoc($action)) {
                continue;
            }

            $route_path = $class_name . '\\' . $action;

            # 将方法解释为： 控制器/方法
            if($pid !== 0) {
                $arr = explode('\\', $route_path);
                $class = $arr[count($arr) - 2] . '\\' . $arr[count($arr) - 1];
            }
            $this->PermissExistToCreate($route_path, $class, $action_title, $pid);

        }
    }

    /**
     * 设置类的全名, 如 app\admin\controller\Auth;
     * @param $class
     */
    public function setClassFullName($class)
    {
        $this->class_full_name = $class;
    }

    /**
     * 检查权限是否存在
     * 不存在则创建
     * 返回一个权限id , pid使用
     */
    private function PermissExistToCreate($route_path, $class_name, $class_title, $pid = 0)
    {
        $data = [
            'path' => $route_path,
            'name' => $class_name,
            'title' => $class_title,
            'remark' => $class_title,
            'p_id' => $pid
        ];

        if(!$permission = Permission::where('name', $class_name)->find()) {
            # 写入权限节点
            $permission = Permission::create($data);
        }else{
            Permission::where('id', $permission['id'])->update($data);
        }

        return $permission['id']; # pid
    }

    /**
     * 清除无效的节点
     */
    private function clearNullityNode()
    {
        if(!$this->_isNullityNodeDelete) {
            return;
        }
        $permissions = Permission::where('status', 1)->select();
        while ($permissions->toArray()) {
            $permission = $permissions->shift();
            $name = $permission->name;
            if(!class_exists($name)) {
                Permission::where('id', $permission->id)->whereOr('p_id', $permission->id)->delete();
            }
        }
    }
}