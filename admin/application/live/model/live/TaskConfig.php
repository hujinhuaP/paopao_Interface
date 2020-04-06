<?php
namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 任务列表
 */
class TaskConfig extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'task_create_time';
    protected $updateTime = 'task_update_time';
}