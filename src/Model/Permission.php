<?php
/**
 * Created by PhpStorm.
 * User: chenf
 * Date: 19-5-23
 * Time: 下午2:27
 */

namespace Surest\Model;

use Surest\Exceptions\PermissionException;
use think\Model;

class Permission extends BaseModel
{
    protected $type = [
        'method' => 'array'
    ];

    protected $table = 'Permission';

    public static function create($data = [], $field = null, $replace = false)
    {
//        if(self::where(['name' => $data['name']])
//            ->where('rule', $data['rule'])
//            ->find()) {
//            throw new PermissionException('权限名称不能重复');
//        }

        return parent::create($data, $field, $replace);
    }

    /**
     * 渲染权限节点树
     */
    public static function permissionNode(array $permissions)
    {
        $refer = array();
        $tree = array();
        foreach($permissions as $k => $v){
            $refer[$v['id']] = & $permissions[$k]; //创建主键的数组引用
        }
        foreach($permissions as $k => $v){
            $pid = $v['p_id'];  //获取当前分类的父级id
            if($pid == 0){
                $tree[] = & $permissions[$k];  //顶级栏目
            }else{
                if(isset($refer[$pid])){
                    $refer[$pid]['children'][] = & $permissions[$k]; //如果存在父级栏目，则添加进父级栏目的子栏目数组中
                }
            }
        }
        return $tree;
    }
}