<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 主播等级升级方式
 */
class AnchorLevelUpgrade extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'anchor_level_upgrade_create_time';
    protected $updateTime = 'anchor_level_upgrade_update_time';
}