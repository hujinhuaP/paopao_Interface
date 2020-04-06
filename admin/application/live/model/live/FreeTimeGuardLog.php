<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 守护免费通话记录
 */
class FreeTimeGuardLog extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    /**
     * @return \think\model\relation\BelongsTo
     * 用户
     */
    public function user()
    {
        return $this->belongsTo('user','user_id','user_id',[],'inner')->setEagerlyType(0);
    }


    /**
     * @return \think\model\relation\BelongsTo
     * 主播
     */
    public function anchorUser()
    {
        return $this->belongsTo('user','anchor_user_id','user_id',[],'inner')->setEagerlyType(0);
    }
}
