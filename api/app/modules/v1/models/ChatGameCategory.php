<?php 

namespace app\models;

/**
* ChatGameCategory 聊天游戏分类表
*/
class ChatGameCategory extends ModelBase
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