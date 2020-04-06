<?php

/*
 +------------------------------------------------------------------------+
 +------------------------------------------------------------------------+
 | 主播时长排行榜                          |
 +------------------------------------------------------------------------+
 */

namespace app\services;

use Exception;

/**
 * ActivityAnchorService 活动 主播时长排行榜
 */
class ActivityAnchorService extends RedisService
{

    protected $_item_key;
    public function __construct()
    {
        parent::__construct();
        $this->_key = 'activity:anchor_chat_time:all';
        $this->_item_key = 'activity:anchor_chat_time:'.date('Ymd');
        $this->_ttl = 3600 * 24 * 2;
    }

    /**
     * save 保存
     *
     * @return bool
     */
    public function save($nAnchorId =0,$duration=0)
    {
        $startTime = strtotime('2019-02-04');
        $endTime = strtotime('2019-02-18');
        if( time() < $startTime || time() > $endTime || $duration < 10 ){
            return false;
        }
        // 添加总数据 以及排行榜
        $bool = $this->redis->zIncrBy($this->_key, $duration, $nAnchorId);
        // 添加主播今日数据
        $bool2 = $this->redis->incrBy($this->_item_key . ':'.$nAnchorId,$duration);
        return $bool;
    }


    /**
     * delete 删除
     *
     * @return bool
     */
    public function delete()
    {
        return $this->redis->del($this->_key);
    }


    /**
     * getData  获取前十数据
     * @return mixed
     */
    public function getData()
    {
        return $this->redis->zRevRange($this->_key, 0, 9,TRUE);
    }

}