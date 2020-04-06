<?php
namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 一次性任务记录
 */
class OnceTaskLog extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'once_task_log_create_time';
    protected $updateTime = 'once_task_log_update_time';

    public function user()
    {
        return $this->belongsTo('user','once_task_log_user_id','user_id',[],'inner')->setEagerlyType(0);
    }
}