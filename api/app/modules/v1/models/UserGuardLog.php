<?php

namespace app\models;

/**
 * UserGuardLog 用户的守护记录表
 */
class UserGuardLog extends ModelBase
{
    public $id;
    public $log_number;
    public $user_id;
    public $anchor_user_id;
    public $total_coin;
    public $consume_coin;
    public $current_level;
    public $current_level_name;
    public $create_time;
    public $update_time;

    public function beforeCreate()
    {
        $this->log_number = date('YmdHis') . '000000' . mt_rand(10, 99) . mt_rand(100, 999);
        $this->update_time = time();
        $this->create_time = time();
    }

    public function beforeUpdate()
    {
        $this->update_time = time();
    }

}