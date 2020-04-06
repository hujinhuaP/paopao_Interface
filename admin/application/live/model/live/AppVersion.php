<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * APP版本
 */
class AppVersion extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'app_version_create_time';
    protected $updateTime = 'app_version_update_time';
}
