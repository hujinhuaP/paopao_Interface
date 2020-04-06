<?php
namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 等级奖励记录
 */
class LevelRewardLog extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'level_reward_log_create_time';
    protected $updateTime = 'level_reward_log_update_time';

    public function user()
    {
        return $this->belongsTo('user','level_reward_log_user_id','user_id',[],'inner')->setEagerlyType(0);
    }
}