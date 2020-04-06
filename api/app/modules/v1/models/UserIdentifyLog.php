<?php 

namespace app\models;

/**
* UserIdentifyLog 用户违规记录表
*/
class UserIdentifyLog extends ModelBase
{

    public $user_identify_id;
    public $user_identify_user_id;
    public $user_identify_confidence;
    public $user_identify_chat_log_id;
    public $user_identify_image_url;
    public $user_identify_check_text;
    public $user_identify_times;
    public $user_identify_create_time;
    public $user_identify_update_time;

	public function beforeCreate()
    {
		$this->user_identify_create_time = time();
        $this->user_identify_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_identify_update_time = time();
    }


}