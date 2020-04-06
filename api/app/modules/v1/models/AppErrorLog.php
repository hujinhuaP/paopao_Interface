<?php 

namespace app\models;

/**
* AppErrorLog APP错误日志表
*/
class AppErrorLog extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->app_error_log_create_time = time();
		$this->app_error_log_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->app_error_log_update_time = time();
    }
}