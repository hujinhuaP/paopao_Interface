<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * APP错误日志
 */
class AppErrorLog extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'app_error_log_create_time';
    protected $updateTime = 'app_error_log_update_time';
}
