<?php

namespace app\admin\model\api;


/**
 * 公会收益统计表
 */
class GroupIncomeStat extends ApiModel
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $updateTime = 'update_time';
    protected $createTime = false;

    public function group()
    {
        return $this->belongsTo('group','group_id','id',[],'inner')->setEagerlyType(0);
    }

    public function user()
    {
        return $this->belongsTo('user','user_id','user_id',[],'left')->setEagerlyType(0);
    }

    public function userAccount()
    {
        return $this->belongsTo('user_account','user_id','user_id',[],'left')->setEagerlyType(0);
    }
}
