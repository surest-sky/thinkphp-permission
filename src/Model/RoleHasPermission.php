<?php
/**
 * Created by PhpStorm.
 * User: chenf
 * Date: 19-5-23
 * Time: 下午2:28
 */

namespace Surest\Model;

use think\model\Pivot;

class RoleHasPermission extends Pivot
{
    protected $table = 'role_has_permission';
}