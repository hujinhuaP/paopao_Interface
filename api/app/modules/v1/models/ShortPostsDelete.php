<?php 

namespace app\models;

/**
* ShortPostsDelete 动态删除表
*/
class ShortPostsDelete extends ModelBase
{

	public function beforeCreate()
    {
		$this->short_posts_create_time = time();
		$this->short_posts_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->short_posts_update_time = time();
    }
}