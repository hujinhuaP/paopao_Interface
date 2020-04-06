<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 派单记录
 */
class DispatchChat extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'dispatch_chat_create_time';
    protected $updateTime = 'dispatch_chat_update_time';

    public function user()
    {
        return $this->belongsTo('user','dispatch_chat_user_id','user_id',[],'inner')->setEagerlyType(0);
    }

    public function anchorUser()
    {
        return $this->belongsTo('user','dispatch_chat_anchor_user_id','user_id',[],'inner')->setEagerlyType(0);
    }

}
