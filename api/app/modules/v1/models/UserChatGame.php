<?php 

namespace app\models;

/**
* UserChatGame 用户聊天游戏配置表
*/
class UserChatGame extends ModelBase
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