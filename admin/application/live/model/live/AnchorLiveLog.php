<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 主播直播记录
 */
class AnchorLiveLog extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'anchor_live_log_create_time';
    protected $updateTime = 'anchor_live_log_update_time';
}
