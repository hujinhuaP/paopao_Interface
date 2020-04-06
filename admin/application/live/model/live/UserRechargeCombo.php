<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 充值套餐
 */
class UserRechargeCombo extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_recharge_combo_create_time';
    protected $updateTime = 'user_recharge_combo_update_time';
}