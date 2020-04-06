<?php 

namespace app\models;

/**
* UserRechargeActionLog 用户充值·VIP行为记录表
*/
class UserRechargeActionLog extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->update_time = time();
        $this->create_time = time();
    }

    public function beforeUpdate()
    {
        $this->update_time = time();
    }

}