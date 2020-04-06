<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 用户实名认证
 */
class UserCertification extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_certification_create_time';
    protected $updateTime = 'user_certification_update_time';


    public function User()
    {
        return $this->belongsTo('user','user_id','user_id',[],'inner')->setEagerlyType(0);
    }
}
