<?php 

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
* UserLevel 用户等级表
*/
class UserLevel extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_level_create_time';
    protected $updateTime = 'user_level_update_time';
}