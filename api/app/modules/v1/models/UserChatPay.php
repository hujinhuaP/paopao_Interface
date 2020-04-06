<?php 

namespace app\models;

/**
* UserLivePay 用户直播支付记录表
*/
class UserChatPay extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->create_time = time();
		$this->update_time = time();
    }

    public function beforeUpdate()
    {
        $this->update_time = time();
    }
}