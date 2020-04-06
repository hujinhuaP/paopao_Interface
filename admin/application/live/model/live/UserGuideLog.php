<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 诱导记录
 */
class UserGuideLog extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'guide_create_time';
    protected $updateTime = 'guide_update_time';
}