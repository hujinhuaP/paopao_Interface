<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 用户
 */
class UserGuideVideo extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


    public function user()
    {
        return $this->belongsTo('user','anchor_user_id','user_id',[],'inner')->setEagerlyType(0);
    }

}
