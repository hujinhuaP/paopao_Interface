<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 主播分类
 */
class AnchorCategory extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'anchor_category_create_time';
    protected $updateTime = 'anchor_category_update_time';
}
