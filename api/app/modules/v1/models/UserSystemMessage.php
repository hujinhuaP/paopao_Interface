<?php 

namespace app\models;

/**
* UserSystemMessage 用户系统消息记录表
*/
class UserSystemMessage extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->user_system_message_create_time = time();
		$this->user_system_message_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_system_message_update_time = time();
    }
}