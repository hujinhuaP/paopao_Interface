<?php 

namespace app\models;

/**
* ShortPostsCommentReply 动态评论回复表
*/
class ShortPostsCommentReply extends ModelBase
{

    public $reply_id = 0;
    public $user_id = 0;
    public $at_user_id = 0;
    public $at_user_nickname = '';
    public $short_posts_id = 0;
    public $create_time;
    public $update_time;
    public $reply_content = '';
    public $comment_id = 0;
    public $check_admin_id = 0;
    public $reply_status;
    public $reply_check_remark = '';
    public $is_comment = 'N';
    public $reply_check_time;
    public $is_auto_refuse = 'N';

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