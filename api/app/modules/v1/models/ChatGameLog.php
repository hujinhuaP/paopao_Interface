<?php 

namespace app\models;

/**
* ChatGameLog 聊天游戏记录表
*/
class ChatGameLog extends ModelBase
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