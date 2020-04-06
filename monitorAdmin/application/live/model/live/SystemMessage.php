<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 系统消息
 */
class SystemMessage extends Model
{
	/** @var string 普通消息类型 */
    const TYPE_GENERAL       = 'general';
    /** @var string 关注消息类型 */
    const TYPE_FOLLOW        = 'follow';
    /** @var string 提现消息类型 */
    const TYPE_WITHDRAW      = 'withdraw';
    /** @var string 实名认证消息类型 */
    const TYPE_CERTIFICATION = 'certification';
    
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'system_message_create_time';
    protected $updateTime = 'system_message_update_time';
}