<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 公会管理员表
 */
class GroupAdmin extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function group()
    {
        return $this->belongsTo('group','group_id','id',[],'inner')->setEagerlyType(0);
    }
}
