<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 机器人语言库
 */
class IsrobotTalk extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'isrobot_talk_create_time';
    protected $updateTime = 'isrobot_talk_update_time';
}