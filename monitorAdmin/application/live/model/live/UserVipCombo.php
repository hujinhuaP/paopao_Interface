<?php

namespace app\live\model\live;


use app\live\model\LiveModel as Model;

/**
 * VIP套餐
 */
class UserVipCombo extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_vip_combo_create_time';
    protected $updateTime = 'user_vip_combo_update_time';
}