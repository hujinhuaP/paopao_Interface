<?php 

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
* UserInviteRewardLog 用户邀请奖励表
*/
class UserInviteRewardLog extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_invite_reward_log_create_time';
    protected $updateTime = 'user_invite_reward_log_update_time';

    public function inviteUser()
    {
        return $this->belongsTo('user','parent_user_id','user_id',[],'inner')->setEagerlyType(0);
    }

    public function user()
    {
        return $this->belongsTo('user','user_id','user_id',[],'inner')->setEagerlyType(0);
    }
}