<?php 

namespace app\models;

/**
* UserVipCombo 用户VIP充值套餐表
*/
class UserVipCombo extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->user_vip_combo_update_time = time();
        $this->user_vip_combo_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_vip_combo_update_time = time();
    }

}