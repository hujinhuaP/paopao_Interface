<?php
namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 每日任务记录
 */
class AnchorDailyTaskLog extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'anchor_daily_task_log_create_time';
    protected $updateTime = 'anchor_daily_task_log_update_time';

    public function user()
    {
        return $this->belongsTo('user','anchor_daily_task_log_user_id','user_id',[],'inner')->setEagerlyType(0);
    }
}