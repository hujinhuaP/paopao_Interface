<?php 

namespace app\models;

/**
* LiveGift 礼物
*/
class LiveGift extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->live_gift_create_time = time();
		$this->live_gift_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->live_gift_update_time = time();
    }
}