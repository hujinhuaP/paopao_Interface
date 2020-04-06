<?php 

namespace app\models;

/**
* DispatchChat 派单记录
*/
class DispatchChat extends ModelBase
{

    public $dispatch_chat_id;
    public $dispatch_chat_user_id;
    public $dispatch_chat_anchor_user_id;
    public $dispatch_chat_wait_duration;
    public $dispatch_chat_status;
    public $dispatch_chat_chat_id;
    public $dispatch_chat_price;
    public $dispatch_chat_create_time;
    public $dispatch_chat_update_time;

	public function beforeCreate()
    {
		$this->dispatch_chat_create_time = time();
		$this->dispatch_chat_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->dispatch_chat_update_time = time();
    }
}