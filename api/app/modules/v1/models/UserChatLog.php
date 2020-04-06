<?php 

namespace app\models;

/**
* UserChatLog 用户聊天对话ID
*/
class UserChatLog extends ModelBase
{
    
    public function beforeCreate()
    {
        $this->user_chat_log_update_time = time();
        $this->user_chat_log_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_chat_log_update_time = time();
    }
}