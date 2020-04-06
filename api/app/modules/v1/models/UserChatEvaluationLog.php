<?php

namespace app\models;

/**
 * UserChatEvaluationLog 用户聊天评价
 */
class UserChatEvaluationLog extends ModelBase
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