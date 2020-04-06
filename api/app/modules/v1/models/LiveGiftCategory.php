<?php 

namespace app\models;

/**
* LiveGiftCategory 礼物分类
*/
class LiveGiftCategory extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->live_gift_category_create_time   = time();
		$this->live_gift_category_update_time   = time();
    }

    public function beforeUpdate()
    {
        $this->live_gift_category_update_time = time();
    }
}