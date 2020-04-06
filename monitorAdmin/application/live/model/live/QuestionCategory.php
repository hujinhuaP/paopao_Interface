<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 问题分类表
 */
class QuestionCategory extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'question_category_create_time';
    protected $updateTime = 'question_category_update_time';
}
