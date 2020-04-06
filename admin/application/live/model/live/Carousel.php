<?php
namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 图片轮播
 */
class Carousel extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'carousel_create_time';
    protected $updateTime = 'carousel_update_time';
}