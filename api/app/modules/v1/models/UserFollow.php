<?php 

namespace app\models;

/**
* UserFollow 用户关注表
*/
class UserFollow extends ModelBase
{

	public function beforeCreate()
    {
		$this->user_follow_update_time = time();
        $this->user_follow_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_follow_update_time = time();
    }

}