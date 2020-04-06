<?php

namespace app\live\model\live;


use app\live\model\LiveModel as Model;

/**
 * 用户购买记录
 */
class UserWechatLog extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'wechat_log_create_time';
    protected $updateTime = 'wechat_log_update_time';
}