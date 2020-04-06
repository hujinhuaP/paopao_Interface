<?php 

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
* UserInviteRelationship 用户邀请关系表
*/
class UserInviteRelationship extends Model
{
	// 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_invite_relationship_create_time';
    protected $updateTime = 'user_invite_relationship_update_time';
}