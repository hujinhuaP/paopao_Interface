<?php 

namespace app\models;

/**
* UserWithdrawAccountCategory 用户提现账号分类表
*/
class UserWithdrawAccountCategory extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->user_withdraw_account_category_create_time = time();
        $this->user_withdraw_account_category_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_withdraw_account_category_update_time = time();
    }

}