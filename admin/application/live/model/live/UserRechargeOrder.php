<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 用户充值订单表
 */
class UserRechargeOrder extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_recharge_order_create_time';
    protected $updateTime = 'user_recharge_order_update_time';
}