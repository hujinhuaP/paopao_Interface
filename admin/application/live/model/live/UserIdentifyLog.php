<?php 

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
* UserIdentifyLog 涉黄审核记录
*/
class UserIdentifyLog extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'user_identify_create_time';
    protected $updateTime = 'user_identify_update_time';



    public function user()
    {
        return $this->belongsTo('user','user_identify_user_id','user_id',[],'inner')->setEagerlyType(0);
    }

}