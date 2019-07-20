<?php
/**
 * Created by PhpStorm.
 * User: chenf
 * Date: 19-5-27
 * Time: 下午4:37
 */

namespace Surest\Model;

use think\Model;

class BaseModel extends Model
{
    protected $autoWriteTimestamp = "timestamp";
}