<?php 

namespace app\models;

/**
* UserWithdrawLog 用户提现记录表
*/
class UserWithdrawLog extends ModelBase
{

    public $user_withdraw_log_check_status;
    const DOT_TYPE_CASH = 'cash';

    public function beforeCreate()
    {
		$this->user_withdraw_log_create_time = time();
        $this->user_withdraw_log_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_withdraw_log_update_time = time();
    }

}