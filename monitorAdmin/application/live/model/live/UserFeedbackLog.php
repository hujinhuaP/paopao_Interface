<?php 

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
* UserFeedbackLog 用户反馈表
*/
class UserFeedbackLog extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_feedback_log_create_time';
    protected $updateTime = 'user_feedback_log_update_time';
}