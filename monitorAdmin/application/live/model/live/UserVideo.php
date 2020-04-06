<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 用户
 */
class UserVideo extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_create_time';


    public function user()
    {
        return $this->belongsTo('user','user_id','user_id',[],'inner')->setEagerlyType(0);
    }

    public function category()
    {
        return $this->belongsTo('video_category','type','id',[],'inner')->setEagerlyType(0);
    }
}
