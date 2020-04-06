<?php

namespace app\admin\model\api;



/**
 * 用户系统消息对话表
 */
class UserSystemMessageDialog extends apiModel
{
	/** @var string 消息类型 */
    const TYPE_SYSTEM = 'system';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_system_message_dialog_create_time';
    protected $updateTime = 'user_system_message_dialog_update_time';
}