<?php 

namespace app\models;

/**
* ShortPostsLike 动态点赞表
*/
class ShortPostsLike extends ModelBase
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