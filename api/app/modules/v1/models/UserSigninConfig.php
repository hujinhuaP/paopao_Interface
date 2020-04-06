<?php

namespace app\models;

/**
 * UserSigninConfig 用户签到奖励配置表
 */
class UserSigninConfig extends ModelBase
{
    public function beforeCreate()
    {
        $this->user_signin_config_update_time = time();
        $this->user_signin_config_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_signin_config_update_time = time();
    }
}