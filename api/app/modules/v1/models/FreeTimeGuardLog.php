<?php

namespace app\models;

/**
* FreeTimeGuardLog 守护免费通话记录
*/
class FreeTimeGuardLog extends ModelBase
{
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