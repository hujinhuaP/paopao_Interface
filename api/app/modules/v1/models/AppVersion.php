<?php 

namespace app\models;

/**
* AppVersion APP版本配置表
*/
class AppVersion extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->app_version_create_time = time();
		$this->app_version_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->app_version_update_time = time();
    }
}