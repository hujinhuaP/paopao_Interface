<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 视频回复
 */
class UserVideoReply extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';


    public function user()
    {
        return $this->belongsTo('user','user_id','user_id',[],'inner')->setEagerlyType(0);
    }

}
