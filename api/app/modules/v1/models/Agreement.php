<?php 

namespace app\models;

/**
* Agreement 平台协议表
*/
class Agreement extends ModelBase
{

	public function beforeCreate()
    {
		$this->agreement_create_time = time();
		$this->agreement_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->agreement_update_time = time();
    }
}