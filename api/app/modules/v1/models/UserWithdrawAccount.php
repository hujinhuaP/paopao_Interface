<?php 

namespace app\models;

/**
* UserWithdrawAccount 用户提现账号表
*/
class UserWithdrawAccount extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->user_withdraw_account_create_time = time();
        $this->user_withdraw_account_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_withdraw_account_update_time = time();
    }

}