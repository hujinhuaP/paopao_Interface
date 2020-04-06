<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 问题表
 */
class Question extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'question_create_time';
    protected $updateTime = 'question_update_time';
}
