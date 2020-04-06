<?php

namespace app\live\model\live;


use app\live\model\LiveModel as Model;

/**
 * VIP套餐订单
 */
class UserVipOrder extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_vip_order_create_time';
    protected $updateTime = 'user_vip_order_update_time';


    public function user()
    {
        return $this->belongsTo('user','user_id','user_id',[],'inner')->setEagerlyType(0);
    }
}