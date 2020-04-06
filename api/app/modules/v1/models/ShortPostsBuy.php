<?php 

namespace app\models;

/**
* ShortPostsBuy 动态购买表
*/
class ShortPostsBuy extends ModelBase
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