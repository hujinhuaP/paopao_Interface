<?php 

namespace app\models;

/**
* LoginErrorLog 登录异常记录表
*/
class LoginErrorLog extends ModelBase
{

    const LOGIN_TYPE_PHONE = 'phone';
    const LOGIN_TYPE_QQ    = 'qq';
    const LOGIN_TYPE_WX    = 'wx';
    const LOGIN_TYPE_WB    = 'wb';

    public function beforeCreate()
    {
		$this->update_time = time();
        $this->create_time = time();
    }

    public function beforeUpdate()
    {
        $this->update_time = time();
    }

}