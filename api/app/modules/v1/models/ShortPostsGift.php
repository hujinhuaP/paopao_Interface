<?php 

namespace app\models;

/**
* ShortPostsGift 动态礼物表
*/
class ShortPostsGift extends ModelBase
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