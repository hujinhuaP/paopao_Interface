<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 主播
 */
class Anchor extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'anchor_create_time';
    protected $updateTime = 'anchor_update_time';


    public function user()
    {
        return $this->belongsTo('user','user_id','user_id',[],'inner')->setEagerlyType(0);
    }
}
