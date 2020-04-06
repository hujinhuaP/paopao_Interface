<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;
use app\live\library\Redis;

/**
 * 分段统计数据
 */
class IntervalStatLog extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


    public function getOnline($type = 'user'){
        $oRedis = new Redis();
        $key         = 'online:'.$type;
        $key_offline = sprintf('offline:%s:%s', $type, date('YmdH'));
        $onlineCount = $oRedis->sCard($key);
        $offlineCount = $oRedis->sCard($key_offline);
        return intval($onlineCount) + intval($offlineCount);
    }

}
