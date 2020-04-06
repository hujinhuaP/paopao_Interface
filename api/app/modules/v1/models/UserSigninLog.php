<?php 

namespace app\models;

/**
* UserSigninLog 用户签到日志表
*/
class UserSigninLog extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->user_signin_log_create_time = time();
		$this->user_signin_log_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_signin_log_update_time = time();
    }
}