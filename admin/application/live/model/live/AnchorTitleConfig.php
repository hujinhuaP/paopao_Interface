<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 主播称号配置
 */
class AnchorTitleConfig extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'anchor_title_create_time';
    protected $updateTime = 'anchor_title_update_time';
}
