<?php

namespace app\admin\model\api;

use think\Model;
use think\Session;

class AgentFinanceLog extends ApiModel
{
    const TYPE_LIVE = 'live';
    const TYPE_CHAT = 'chat';
    const TYPE_WITHDRAW = 'withdraw';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


    public function Anchor() {
        return $this->belongsTo('user', 'user_id', 'user_id', [], 'left')->setEagerlyType(0);
    }
}
