<?php 

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
* UserInvite 用户邀请表
*/
class UserInvite extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_invite_create_time';
    protected $updateTime = 'user_invite_update_time';
}