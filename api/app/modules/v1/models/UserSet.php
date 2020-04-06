<?php 

namespace app\models;

/**
* UserSet 用户设置表
*/
class UserSet extends ModelBase
{
    public         $user_id;
    public         $user_get_stranger_msg_flg = 'Y';
    public         $user_get_call_flg = 'Y';
    public         $user_set_create_time;
    public         $user_set_update_time;


	public function beforeCreate()
    {
        $this->user_set_update_time = time();
        $this->user_set_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_set_update_time = time();
    }



}