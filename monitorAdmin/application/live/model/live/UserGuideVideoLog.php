<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 用户
 */
class UserGuideVideoLog extends Model
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

    public function userGuideVideo()
    {
        return $this->belongsTo('user_guide_video','guide_video_id','id',[],'inner')->setEagerlyType(0);
    }
}
