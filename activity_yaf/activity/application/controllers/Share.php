<?php

/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class ShareController extends BaseController
{
    private $_key = 'hzjkb24';

    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/activity/index/index/index/name/root 的时候, 你就会发现不同
     */
    public function postsAction($posts = 0)
    {
        $tFlg = $this->getRequest()->get("t");
        $kFlg = $this->getRequest()->get("k");
//        if ( !$kFlg || !$tFlg || !$posts ) {
//            echo 404;
//            return FALSE;
//        }
//
//        $tCheck = md5(sprintf('%s-%s-%s', $posts, $tFlg, $this->_key));
//        if ( $tCheck != $tFlg ) {
//            echo 404;
//            return FALSE;
//        }
        $posts       = intval($posts);
        $postsSql    = <<<DSQL
SELECT u.user_nickname,u.user_avatar,u.user_level,u.user_birth,p.short_posts_word,p.short_posts_create_time,p.short_posts_watch_num,
p.short_posts_like_num,p.short_posts_gift_num,p.short_posts_position,p.short_posts_type,p.short_posts_images,p.short_posts_video,
u.user_sex,p.short_posts_comment_num,u.user_member_expire_time,u.user_level
FROM short_posts as p inner join `user` as u on p.short_posts_user_id = u.user_id WHERE p.short_posts_id = $posts
DSQL;
        $postsResult = $this->db->fetchRow($postsSql);
        if ( !$postsResult ) {
            echo 404;
            return FALSE;
        }

        $postsResult['user_is_member'] = $postsResult['user_member_expire_time'] > time() ? 'Y' : 'N';
        $postsResult['user_age']       = $this->_birthday($postsResult['user_birth']);


        // 获取用户等级对应的颜色
        $levelSql       = <<<LSQL
select level_value,level_extra FROM level_config where level_type = 'user' order by level_value
LSQL;
        $levelResult    = $this->db->fetchAll($levelSql);
        $levelConfigArr = array_column($levelResult, 'level_extra', 'level_value');

        $postsResult['user_level_color'] = $levelConfigArr[$postsResult['user_level']] ?? '';

        $commentSql    = <<<CSQL
SELECT c.comment_id,u.user_id,u.user_avatar,u.user_nickname,u.user_member_expire_time,c.create_time,c.comment_content,
c.at_user_id,c.at_user_nickname,u.user_level,u.user_sex,u.user_is_anchor,u.user_sex,u.user_birth,
c.comment_like_num,c.show_reply_user_id,c.show_reply_content,c.show_reply_user_nickname,c.reply_num
FROM short_posts_comment as c inner join `user` as u on c.user_id = u.user_id
where c.comment_status = "Y" AND c.short_posts_id= $posts
order by c.comment_like_num desc
limit 5
CSQL;
        $commentResult = $this->db->fetchAll($commentSql);
        foreach ( $commentResult as $commentItem ) {
            $commentItem['user_is_member']   = $commentItem['user_member_expire_time'] > time() ? 'Y' : 'N';
            $commentItem['comment_like_num'] = intval($commentItem['comment_like_num']);
            $commentItem['reply_num']        = intval($commentItem['reply_num']);
            $commentItem['user_age']         = $this->_birthday($commentItem['user_birth']);
            $commentItem['user_level_color'] = $levelConfigArr[$commentItem['user_level']] ?? '';
        }

        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        $this->getView()->assign("postsResult", $postsResult);
        $this->getView()->assign("commentResult", $commentResult);
        return TRUE;
    }


    private function _birthday($birthday)
    {
        $age = strtotime($birthday);
        if ( !$birthday || $age === FALSE ) {
            return 22;
        }
        list($y1, $m1, $d1) = explode("-", date("Y-m-d", $age));
        $now = strtotime("now");
        list($y2, $m2, $d2) = explode("-", date("Y-m-d", $now));
        $age = $y2 - $y1;
        if ( (int)($m2 . $d2) < (int)($m1 . $d1) )
            $age -= 1;
        return $age;
    }

}
