<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 诱导语言库
 */
class GuideMsgList extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
}