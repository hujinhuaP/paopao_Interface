<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 动态
 */
class ShortPostsDelete extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'short_posts_create_time';
    protected $updateTime = 'short_posts_update_time';


}
