<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 用户渠道修改记录
 */
class UserAgentLog extends Model
{
    // 开启自动写入时间戳字段
    /** 类型 管理员操作 */
    const TYPE_ADMIN = 'admin';

    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_agent_log_create_time';
    protected $updateTime = 'user_agent_log_update_time';
}
