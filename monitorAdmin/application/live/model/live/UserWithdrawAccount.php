<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 用户提现账号表
 */
class UserWithdrawAccount extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_withdraw_account_create_time';
    protected $updateTime = 'user_withdraw_account_update_time';
}