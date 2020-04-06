<?php

namespace app\admin\model\api;


/**
 * 签约主播统计
 */
class AnchorSignStat extends ApiModel
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public function user()
    {
        return $this->belongsTo('user','user_id','user_id',[],'inner')->setEagerlyType(0);
    }

    public function group()
    {
        return $this->belongsTo('group','group_id','id',[],'left')->setEagerlyType(0);
    }
}
