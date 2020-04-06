<?php 

namespace app\models;

/**
* UserChatDialog 用户聊天对话ID
*/
class UserChatDialog extends ModelBase
{
	
	public function beforeCreate()
    {
        $this->user_chat_dialog_update_time = time();
		$this->user_chat_dialog_create_time = time();
    }

    public function beforeUpdate()
    {
    }

    /**
     * getChatRoomId 获取聊天房间ID，组成规则为【大的用户_小的用户ID】
     * 
     * @param  int $nUserId1
     * @param  int $nUserId2
     * @return string
     */
    public static function getChatRoomId($nUserId1, $nUserId2)
    {
    	if ($nUserId1 > $nUserId2) {
    		return $nUserId1 .'_'. $nUserId2;
    	}
    	return $nUserId2 .'_'. $nUserId1;
    }

}