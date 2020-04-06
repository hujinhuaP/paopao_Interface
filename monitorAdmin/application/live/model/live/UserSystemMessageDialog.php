<?php

namespace app\live\model\live;


use app\live\model\LiveModel as Model;

/**
 * 用户系统消息对话表
 */
class UserSystemMessageDialog extends Model
{
	/** @var string 消息类型 */
    const TYPE_SYSTEM = 'system';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_system_message_dialog_create_time';
    protected $updateTime = 'user_system_message_dialog_update_time';
}