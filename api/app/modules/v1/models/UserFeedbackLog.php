<?php 

namespace app\models;

/**
* UserFeedbackLog 用户意见反馈表
*/
class UserFeedbackLog extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->user_feedback_log_create_time = time();
		$this->user_feedback_log_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_feedback_log_update_time = time();
    }
}