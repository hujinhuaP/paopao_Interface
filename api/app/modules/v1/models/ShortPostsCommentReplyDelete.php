<?php 

namespace app\models;

/**
* ShortPostsCommentReplyDelete 动态评论回复删除表
*/
class ShortPostsCommentReplyDelete extends ModelBase
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