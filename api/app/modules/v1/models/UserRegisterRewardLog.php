<?php 

namespace app\models;

/**
* UserRegisterRewardLog 用户注册奖励表
*/
class UserRegisterRewardLog extends ModelBase
{

    public function beforeCreate()
    {
		$this->user_register_reward_log_update_time = time();
		$this->user_register_reward_log_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_register_reward_log_update_time = time();
    }
}