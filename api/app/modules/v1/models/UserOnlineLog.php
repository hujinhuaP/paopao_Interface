<?php 

namespace app\models;

/**
* UserOnlineLog 用户上下线记录
*/
class UserOnlineLog extends ModelBase
{
    
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