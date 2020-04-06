<?php 

namespace app\models;

/**
* ShortPostsCommentLike 动态评论点赞表
*/
class ShortPostsCommentLike extends ModelBase
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