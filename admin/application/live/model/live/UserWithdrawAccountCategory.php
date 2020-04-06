<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 用户提现账号分类表
 */
class UserWithdrawAccountCategory extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_withdraw_account_category_create_time';
    protected $updateTime = 'user_withdraw_account_category_update_time';
}