<?php
namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 关键字
 */
class Banword extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'banword_create_time';
    protected $updateTime = 'banword_update_time';
}