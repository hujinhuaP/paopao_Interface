<?php


namespace app\services;

/**
 * GuardFreeTimeService  守护使用免费时长记录
 */
class GuardFreeTimeService extends RedisService
{

    private $_userId;
    private $_anchorUserId;

    public function __construct($nUserId, $nAnchorUserId)
    {
        parent::__construct();
        $this->_userId       = $nUserId;
        $this->_anchorUserId = $nAnchorUserId;
        $this->_key          = sprintf('guard_free_time:%s:%s:%s', date('Ymd'), $nAnchorUserId, $nUserId);
        $this->_ttl          = 60 * 60 * 24;
    }

    /**
     * save 保存
     *
     * @param int $nTime
     * @return float
     */
    public function save($nTime = 1)
    {
        $nTime = $this->redis->incrBy($this->_key, $nTime);
        if ( $nTime ) {
            $this->_ttl = strtotime(date('Y-m-d',time())) + 3600 * 24 - time();
            $this->redis->expire($this->_key, $this->_ttl);
        }
        return $nTime;
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
     * getData 获取数据
     *
     * @return mixed
     */
    public function getData()
    {
        return intval($this->redis->get($this->_key));
    }
}