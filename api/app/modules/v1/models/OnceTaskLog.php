<?php 

namespace app\models;

/**
* OnceTaskLog 一次性任务记录
*/
class OnceTaskLog extends ModelBase
{


    public function beforeCreate()
    {
		$this->once_task_log_create_time = time();
		$this->once_task_log_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->once_task_log_update_time = time();
    }
}