<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 充值每日统计
 */
class DailyRechargeStat extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'recharge_stat_create_time';
    protected $updateTime = FALSE;

}
