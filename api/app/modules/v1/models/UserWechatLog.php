<?php 

namespace app\models;

/**
* UserWechatLog 用户微信购买记录表
*/
class UserWechatLog extends ModelBase
{

	public function beforeCreate()
    {
		$this->wechat_log_create_time = time();
		$this->wechat_log_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->wechat_log_update_time = time();
    }
}