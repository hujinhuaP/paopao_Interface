<?php

namespace app\tasks;

use Phalcon\Exception;
use RedisException;

/**
 * UserTask 用户操作
 */
class UserTask extends MainTask
{

    /**
     *  修改用户昵称后 的同步信息
     */
    public function changeNicknameAction($params)
    {
        $userId = $params['user_id'] ?? '';
        if ( !$userId ) {
            exit('error user_id empty');
        }
        $sql = 'SELECT user_id,user_nickname FROM user WHERE user_id=:user_id LIMIT 1';

        $oResult = $this->db->query($sql, [
            'user_id' => $userId,
        ]);
        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oUser = $oResult->fetch();
        if ( !$oUser ) {
            exit('error user_id error');
        }

        $newNickname = $oUser['user_nickname'];
        // 修改社区数据库中的数据

        // 评论显示昵称
        $shortPostsCommentSql = "UPDATE short_posts_comment set show_reply_user_nickname = :nickname WHERE show_reply_user_id = :user_id";
        $this->db->execute($shortPostsCommentSql, [
            'user_id'  => $userId,
            'nickname' => $newNickname
        ]);

        // 评论@昵称
        $shortPostsComment2Sql = "UPDATE short_posts_comment set show_reply_at_user_nickname = :nickname WHERE show_reply_at_user_id = :user_id";
        $this->db->execute($shortPostsComment2Sql, [
            'user_id'  => $userId,
            'nickname' => $newNickname
        ]);

        // 评论回复@昵称
        $shortPostsCommentReplySql = "UPDATE short_posts_comment_reply set at_user_nickname = :nickname WHERE at_user_id = :user_id";
        $this->db->execute($shortPostsCommentReplySql, [
            'user_id'  => $userId,
            'nickname' => $newNickname
        ]);

    }

}