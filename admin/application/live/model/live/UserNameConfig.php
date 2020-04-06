<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 用户等级
 */
class UserNameConfig extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_name_config_create_time';
    protected $updateTime = 'user_name_config_update_time';

}
