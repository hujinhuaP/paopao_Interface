<?php 

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
* UserEggLog 砸蛋记录
*/
class UserEggLog extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_egg_log_create_time';
    protected $updateTime = false;
}