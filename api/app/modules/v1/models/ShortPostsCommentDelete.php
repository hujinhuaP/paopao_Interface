<?php 

namespace app\models;

/**
* ShortPostsCommentDelete 动态评论删除表
*/
class ShortPostsCommentDelete extends ModelBase
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