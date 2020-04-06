<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 代理商流水
 */
class AgentWaterLog extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';



    public function sourceAgent()
    {
        return $this->belongsTo('agent', 'source_agent_id', 'id', [], 'inner')->setEagerlyType(0);
    }

    public function User()
    {
        return $this->belongsTo('user', 'source_user_id', 'user_id', [], 'inner')->setEagerlyType(0);
    }
}
