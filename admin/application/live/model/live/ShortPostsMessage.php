<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 动态消息
 */
class ShortPostsMessage extends Model
{
    /** @var string 评论回复 */
    const MESSAGE_TYPE_REPLY   = 'reply';
    /** @var string 评论 */
    const MESSAGE_TYPE_COMMENT = 'comment';
    /** @var string 礼物 */
    const MESSAGE_TYPE_GIFT = 'gift';
    /** @var string 动态删除 */
    const MESSAGE_TYPE_POSTS_DELETE = 'posts_delete';
    /** @var string 评论删除 */
    const MESSAGE_TYPE_REPLY_DELETE = 'reply_delete';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


}
