<?php 

namespace app\models;

/**
* UserInviteRewardLog 用户邀请奖励表
*/
class UserInviteRewardLog extends ModelBase
{
	/** @var string 充值类型 */
	const TYPE_RECHARGE = 'recharge';
    const TYPE_REGISTER = 'register';
    const TYPE_VIP = 'vip';
    const TYPE_WITHDRAW = 'withdraw';

    public function beforeCreate()
    {
		$this->user_invite_reward_log_update_time = time();
		$this->user_invite_reward_log_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_invite_reward_log_update_time = time();
    }
}