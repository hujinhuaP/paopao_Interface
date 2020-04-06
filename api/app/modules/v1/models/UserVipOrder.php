<?php 

namespace app\models;

/**
* UserVipOrder 用户VIP充值订单表
*/
class UserVipOrder extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->user_vip_order_update_time = time();
        $this->user_vip_order_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_vip_order_update_time = time();
    }

}