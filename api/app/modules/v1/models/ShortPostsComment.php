<?php 

namespace app\models;

/**
* ShortPostsComment 动态评论表
*/
class ShortPostsComment extends ModelBase
{

    public $comment_id = 0;
    public $user_id = 0;
    public $at_user_id = 0;
    public $at_user_nickname = '';
    public $short_posts_id = 0;
    public $show_reply_user_id = 0;
    public $show_reply_content = '';
    public $show_reply_user_nickname = '';
    public $show_reply_at_user_id = 0;
    public $show_reply_at_user_nickname = '';
    public $comment_content = '';
    public $show_reply_id = 0;
    public $check_admin_id = 0;
    public $comment_status;
    public $comment_check_remark = '';
    public $comment_like_num = 0;
    public $reply_num = 0;
    public $create_time;
    public $update_time;
    public $comment_check_time;

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