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
 * ActivityUserService 活动 用户消费排行榜
 * 同时记录 用户消费周榜
 */
class ActivityUserService extends RedisService
{

    protected $_item_key;

    private $_key_pay;

    public function __construct()
    {
        parent::__construct();
        $this->_key = 'activity:user_coin:all';
        $this->_ttl = 3600 * 24 * 2;

        $this->_key_pay = 'ranking:user:'.date('o-W');
    }

    /**
     * save 保存
     *
     * @return bool
     */
    public function save($nUserId =0,$coin=0)
    {
        // 添加消费周榜
        $bool = $this->redis->zIncrBy($this->_key_pay, $coin, $nUserId);

        $startTime = strtotime('2019-02-04');
        $endTime = strtotime('2019-02-18');
        if( time() < $startTime || time() > $endTime ){
            return false;
        }
        // 添加总数据 以及排行榜
        $bool = $this->redis->zIncrBy($this->_key, $coin, $nUserId);
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

    /**
     * 从缓存中取已经统计好的数据
     */
    public function getCacheData()
    {
        $cacheKey = 'ranking:user:0tmp';
        return $this->redis->get($cacheKey);
    }

    /**
     * 从缓存中取已经统计好的数据  （周榜）
     */
    public function saveCacheData($data)
    {
        $cacheKey = 'ranking:user:0tmp';
        return $this->redis->set($cacheKey,$data,60);
    }


    /**
     * getData 获取周榜数据 排行榜前10
     *
     * @return mixed
     */
    public function getPayData($number = 10)
    {
        return $this->redis->zRevRange($this->_key_pay,0,$number,TRUE);
    }

}