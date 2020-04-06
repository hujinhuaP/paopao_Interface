<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 用户
 */
class User extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_create_time';
    protected $updateTime = 'user_update_time';

    public function group()
    {
        return $this->belongsTo('group','user_group_id','id',[],'left')->setEagerlyType(0);
    }

    public function userAccount()
    {
        return $this->hasOne('user_account','user_id','user_id',[],'inner')->setEagerlyType(0);
    }
}
