<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 公会表
 */
class Group extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public function groupAdmin()
    {
        return $this->hasMany('group_admin','id','group_id');
    }


    public function createInviteCode() {
        $invite_code = createNoncestr(8);
        $existData          = $this->where("invite_code", $invite_code)->find();
        if(!$existData){
            return $invite_code;
        }
        $this->createInviteCode();
    }
}
