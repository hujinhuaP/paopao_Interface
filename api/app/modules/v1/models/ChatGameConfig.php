<?php 

namespace app\models;

/**
* ChatGameConfig 聊天游戏配置表
*/
class ChatGameConfig extends ModelBase
{

	public function beforeCreate()
    {
		$this->chat_game_create_time = time();
		$this->chat_game_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->chat_game_update_time = time();
    }
}