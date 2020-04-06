<?php 

namespace app\models;

/**
* UseAgentLog 操作用户渠道记录表
*/
class UserAgentLog extends ModelBase
{

    /** 充值 */
    const TYPE_RECHARGE = 'recharge';

    public function beforeCreate()
    {
		$this->user_agent_log_update_time = time();
        $this->user_agent_log_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_agent_log_update_time = time();
    }

}