<?php 

namespace app\models;

/**
* VideoCategory 分类
*/
class VideoCategory extends ModelBase
{
	public function beforeCreate()
    {
		$this->create_time = time();
    }

    public function beforeUpdate()
    {
        $this->update_time = time();
    }

}