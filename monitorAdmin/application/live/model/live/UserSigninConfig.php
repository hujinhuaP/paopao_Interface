<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 用户签到配置表
 */
class UserSigninConfig extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_signin_config_create_time';
    protected $updateTime = 'user_signin_config_update_time';
}