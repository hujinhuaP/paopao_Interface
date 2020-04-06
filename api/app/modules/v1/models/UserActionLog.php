<?php

namespace app\models;


/**
 * UserActionLog 用户操作记录表
 */
class UserActionLog extends ModelBase
{

    public $user_id;
    public $change_nickname_time;
    public $action_create_time;
    public $action_update_time;

    public function beforeCreate()
    {
        $this->action_create_time = time();
        $this->action_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->action_update_time = time();
    }

}