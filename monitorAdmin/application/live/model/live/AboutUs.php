<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 关于我们表
 */
class AboutUs extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'about_us_create_time';
    protected $updateTime = 'about_us_update_time';
}
