<?php 

namespace app\models;

/**
* UserGuideVideoLog 用户进入诱导视频日志
*/
class UserGuideVideoLog extends ModelBase
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