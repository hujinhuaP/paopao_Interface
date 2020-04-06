<?php

namespace app\live\model\live;


use app\live\model\LiveModel as Model;

/**
 * 用户
 */
class UserMatchLog extends Model
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

    public function anchor()
    {
        return $this->belongsTo('user','anchor_user_id','user_id',[],'left')->setEagerlyType(0);
    }

    public function userPrivateChatLog()
    {
        return $this->belongsTo('user_private_chat_log','chat_log_id','id',[],'left')->setEagerlyType(0);
    }
}