<?php 

namespace app\models;

/**
* UserBudan 用户补单
*/
class UserBudan extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->user_budan_create_time = time();
		$this->user_budan_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_budan_update_time = time();
    }
}