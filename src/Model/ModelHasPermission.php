<?php
/**
 * Created by PhpStorm.
 * User: chenf
 * Date: 19-5-23
 * Time: 下午2:28
 */

namespace Surest\Model;

use Surest\Exceptions\PermissionException;
use think\Model;

class ModelHasPermission extends BaseModel
{
    public $pk = 'permission_id';
    /**
     * 重写父方法
     */
    public static function create($data = [], $field = null, $replace = false)
    {
        if(!isset($data['permission_id']) || !isset($data['user_id'])) {
            throw new PermissionException('请检查参数是否正确');
        }

        if(self::where('permission_id', $data['permission_id'])->where('user_id', $data['user_id'])->find()) {
            throw new PermissionException('权限已经添加过了');
        }

        return parent::create($data, $field, $replace);
    }
}