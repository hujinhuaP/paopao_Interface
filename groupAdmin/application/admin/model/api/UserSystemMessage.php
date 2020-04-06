<?php

namespace app\admin\model\api;



/**
 * 用户系统消息记录表
 */
class UserSystemMessage extends apiModel
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_system_message_create_time';
    protected $updateTime = 'user_system_message_update_time';
}