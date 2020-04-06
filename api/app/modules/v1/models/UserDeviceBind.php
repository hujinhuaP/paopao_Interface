<?php 

namespace app\models;

/**
* UserDeviceBind 用户设备绑定表
*/
class UserDeviceBind extends ModelBase
{

    /** @var string 绑定类型 用户  */
    const BIND_TYPE_USER = 'user';
    /** @var string 绑定类型 渠道  */
    const BIND_TYPE_AGENT  = 'agent';
    /** @var string 绑定类型 没有绑定  */
    const BIND_TYPE_UNBIND = 'unbind';

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