<?php 

namespace app\models;

/**
* UserShareRewardLog 用户分享奖励记录表
*/
class UserShareRewardLog extends ModelBase
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