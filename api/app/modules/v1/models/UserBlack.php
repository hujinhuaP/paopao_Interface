<?php 

namespace app\models;

/**
* UserBlack 用户黑名单表
*/
class UserBlack extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->create_time = time();
    }

}