<?php 

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
* UserHammer 用户砸蛋数据
*/
class UserHammer extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
}