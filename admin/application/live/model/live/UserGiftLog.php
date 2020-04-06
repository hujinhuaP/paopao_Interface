<?php
namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * UserGiftLog 用户礼物记录
 */
class UserGiftLog extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_gift_log_create_time';
    protected $updateTime = 'user_gift_log_update_time';
}