<?php 

namespace app\models;

/**
* UserRechargeOrder 用户充值套餐订单表
*/
class UserRechargeOrder extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->user_recharge_order_update_time = time();
        $this->user_recharge_order_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_recharge_order_update_time = time();
    }

}