<?php 

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
* UserLevelUpgrade 用户等级升级方式表
*/
class UserLevelUpgrade extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_level_upgrade_create_time';
    protected $updateTime = 'user_level_upgrade_update_time';
}