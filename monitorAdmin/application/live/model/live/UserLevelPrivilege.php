<?php 

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
* UserLevelPrivilege 用户等级特权
*/
class UserLevelPrivilege extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_level_privilege_create_time';
    protected $updateTime = 'user_level_privilege_update_time';
}