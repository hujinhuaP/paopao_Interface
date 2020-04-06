<?php

namespace app\admin\model\api;


/**
 * 代理商每日统计
 */
class AgentDailyStat extends ApiModel
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';



    public function agent()
    {
        return $this->belongsTo('agent','agent_id','id',[],'inner')->setEagerlyType(0);
    }
}
