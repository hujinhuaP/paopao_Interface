<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 定时推送
 */
class CrontabPush extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'crontab_create_time';
    protected $updateTime = 'crontab_update_time';
}
