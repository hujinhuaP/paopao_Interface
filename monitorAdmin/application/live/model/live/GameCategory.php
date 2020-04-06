<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 游戏分类
 */
class GameCategory extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'game_category_create_time';
    protected $updateTime = 'game_category_update_time';
}