<?php 

namespace app\models;

/**
* ShortPostsCollect 动态收藏表
*/
class ShortPostsCollect extends ModelBase
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