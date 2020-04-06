<?php

namespace app\models;

/**
 * ShortPostsMessage 动态消息
 */
class ShortPostsMessage extends ModelBase
{

    /** @var string 评论回复 */
    const MESSAGE_TYPE_REPLY = 'reply';
    /** @var string 评论 */
    const MESSAGE_TYPE_COMMENT = 'comment';
    /** @var string 礼物 */
    const MESSAGE_TYPE_GIFT = 'gift';

    /**
     * @param int $nUserId
     * @return bool
     * 全部已读
     */
    public static function readAll(int $nUserId)
    {
        $updateSql  = "update short_posts_message set user_is_read = 'Y' WHERE user_id = $nUserId AND user_is_read = 'N'";
        $model      = new ShortPostsMessage();
        $connection = $model->getWriteConnection();
        $connection->execute($updateSql);
        return TRUE;
    }

    /**
     * @param array $readMessageId
     * 将数组中的消息全部设为已读
     */
    public static function readMessage(array $readMessageId)
    {
        if ( empty($readMessageId) ) {
            return TRUE;
        }
        $ids        = implode(',', $readMessageId);
        $updateSql  = "update short_posts_message set user_is_read = 'Y' WHERE id in ($ids)";
        $model      = new ShortPostsMessage();
        $connection = $model->getWriteConnection();
        $connection->execute($updateSql);
        return TRUE;
    }

    /**
     * @param int $nUserId
     * @return array
     * 获取用户 动态未读消息数 以及最后一条动态消息
     */
    public static function getLastMessageAndUnread(int $nUserId)
    {
        $lastShortPostsMessage = ShortPostsMessage::findFirst([
            "user_id = :user_id:",
            'bind'  => [
                'user_id' => $nUserId
            ],
            'order' => 'create_time desc'
        ]);
        $unreadCount           = 0;
        if ( $lastShortPostsMessage && $lastShortPostsMessage->user_is_read == 'N' ) {
            $unreadCount = ShortPostsMessage::count([
                "user_id = :user_id: AND user_is_read = 'N'",
                'bind' => [
                    'user_id' => $nUserId
                ]
            ]);
        }
        $last_message_content = '';
        if ( $lastShortPostsMessage ) {
            switch ( $lastShortPostsMessage->message_type ) {
                case 'like':
                    $last_message_content = '您有一条点赞消息';
                    break;
                case 'gift':
                    $last_message_content = '您收到一件礼物';
                    break;
                case 'reply':
                    $last_message_content = '您的评论收到一条回复';
                    break;
                case 'comment':
                    $last_message_content = '您的动态收到一条评论';
                    break;
                case 'posts_delete':
                    $last_message_content = '您收到一条系统消息';
                    break;
                case 'reply_delete':
                    $last_message_content = '您收到一条系统消息';
                    break;
                default:
                    $last_message_content = '您收到一条消息';
            }
        }

        return [
            'unread_count'         => $unreadCount,
            'last_message'         => $lastShortPostsMessage ? $lastShortPostsMessage->toArray() : [],
            'last_message_content' => $last_message_content
        ];
    }

    public function beforeCreate()
    {
        $this->create_time = time();
        $this->update_time = time();
    }

    public function afterCreate()
    {
        // 发送IM 消息
        $timServer = self::getTimServer();
        $timServer->setUid($this->user_id);
        $timServer->sendPostsMsg();
    }

    public function beforeUpdate()
    {
        $this->update_time = time();
    }
}