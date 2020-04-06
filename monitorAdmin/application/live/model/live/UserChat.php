<?php

namespace app\live\model\live;


use app\live\model\LiveModel as Model;

/**
 * 聊天记录
 */
class UserChat extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


    public function sendUser()
    {
        return $this->belongsTo('user','user_chat_send_user_id','user_id',[],'inner')->setEagerlyType(0);
    }

    public function getUser()
    {
        return $this->belongsTo('user','user_chat_receiv_user_id','user_id',[],'inner')->setEagerlyType(0);
    }

}