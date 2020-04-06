<?php

namespace app\models;

/**
 * UserSignin 用户签到表
 */
class UserSignin extends ModelBase
{

    public $user_signin_id;
    public $user_id;
    public $user_signin_last_date;
    public $user_signin_total        = 0;
    public $user_signin_serial_total = 0;
    public $user_signin_coin_total   = 0;
    public $user_signin_exp_total    = 0;
    public $user_signin_create_time;
    public $user_signin_update_time;

    public function beforeCreate()
    {
        $this->user_signin_create_time = time();
        $this->user_signin_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_signin_update_time = time();
    }
}