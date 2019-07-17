<?php
/**
 * Created by PhpStorm.
 * User: chenf
 * Date: 19-5-23
 * Time: 下午2:28
 */

namespace Surest\Model;

use Surest\Exceptions\RoleException;
use think\Model;

class ModelHasRole extends BaseModel
{
    public $pk = 'role_id';

    /**
     * 重写父方法
     */
    public static function create($data = [], $field = null, $replace = false)
    {
        if(!isset($data['role_id']) || !isset($data['user_id'])) {
            throw new RoleException('请检查参数是否正确');
        }

        if(!self::where('role_id', $data['role_id'])->where('user_id', $data['user_id'])->find()) {
            return parent::create($data, $field, $replace);
        }

    }
}