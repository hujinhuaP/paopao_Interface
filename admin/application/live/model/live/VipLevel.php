<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * VIP等级
 */
class VipLevel extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'vip_level_create_time';
    protected $updateTime = 'vip_level_update_time';
}
