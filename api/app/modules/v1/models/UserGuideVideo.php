<?php 

namespace app\models;

/**
* UserGuideVideo 用户诱导视频
*/
class UserGuideVideo extends ModelBase
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