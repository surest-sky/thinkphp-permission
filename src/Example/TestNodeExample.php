<?php
/**
 * Created by PhpStorm.
 * User: chenf
 * Date: 19-5-28
 * Time: 上午11:13
 */

namespace Surest\Example;

use Surest\Traits\Helpers;

/**
 * 教你如何初始化权限节点
 * Class TestNodeExample
 * @package Surest\Example
 */
class TestNodeExample
{
    use Helpers;

    # 定义一个需要权限控制的目录
    protected $path = 'application/admin/controller';

    #　需要排除的文件名称　| 可选
    protected $filter_file = [
        'Auth.php'
    ];

    # 需要排除的方法 | 可选
    protected $filter_action = [
        'show'
    ];

    # 匹配方法注释的正则 | 可选 | 下面是默认的匹配正则
    protected $_action_regex = "#-(.*)-#";

    # 匹配类注释的正则 | 可选 | 下面是默认的匹配正则
    protected $_class_regex = "/&(.*)&/i";

    /**
     * 初始化设置权限节点
     */
    public function setPermissions()
    {
        $this->init_($this->path);
    }

}