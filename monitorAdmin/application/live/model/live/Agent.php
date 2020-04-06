<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 代理商
 */
class Agent extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


    public function createInviteCode() {
        $invite_code = createNoncestr(10);
        $existData          = $this->where("invite_code", $invite_code)->find();
        if(!$existData){
            return $invite_code;
        }
        $this->createInviteCode();
    }


    public function inviteAgent()
    {
        return $this->belongsTo('agent','first_leader','id',[],'left')->setEagerlyType(0);
    }
}
