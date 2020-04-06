<?php 

namespace app\models;

use Phalcon\Di;

/**
* UserSnatchLog 用户抢聊记录表
*/
class UserSnatchLog extends ModelBase
{
    public $id ;
    public $user_id = 0;
    public $anchor_user_id = 0;
    public $snatched_user_id = 0;
    public $chat_log_id = 0;
    public $snatched_chat_log_id = 0;
    public $status = 0;
    public $chat_type;
    public $create_time;
    public $update_time;

	public function beforeCreate()
    {
		$this->create_time = time();
    }

    public function beforeUpdate()
    {
        $this->create_time = time();
        $this->update_time = time();
    }

}