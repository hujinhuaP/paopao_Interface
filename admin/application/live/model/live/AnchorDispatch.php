<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 派单主播
 */
class AnchorDispatch extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'anchor_dispatch_create_time';
    protected $updateTime = 'anchor_dispatch_update_time';

    public function user()
    {
        return $this->belongsTo('user','anchor_dispatch_user_id','user_id',[],'inner')->setEagerlyType(0);
    }

    public function anchor()
    {
        return $this->belongsTo('anchor','anchor_dispatch_user_id','user_id',[],'inner')->setEagerlyType(0);
    }

}
