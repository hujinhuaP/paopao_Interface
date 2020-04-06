<?php 

namespace app\models;

/**
* UserRechargeCombo 用户充值套餐表
*/
class UserRechargeCombo extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->user_recharge_combo_update_time = time();
        $this->user_recharge_combo_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_recharge_combo_update_time = time();
    }

}