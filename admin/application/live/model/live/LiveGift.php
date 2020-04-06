<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 礼物表
 */
class LiveGift extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'live_gift_create_time';
    protected $updateTime = 'live_gift_update_time';
}
