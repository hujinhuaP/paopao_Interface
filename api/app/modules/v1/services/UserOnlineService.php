<?php

namespace app\services;

use Exception;
use Phalcon\DI\FactoryDefault as Di;

/**
 * UserOnlineService 用户用户集合
 *
 */
class UserOnlineService extends RedisService
{
    private $_key_offline;


    /**
     * UserOnlineService constructor.
     * @param $type  string  anchor or user
     */
    public function __construct($type)
    {
        parent::__construct();
        $this->_key         = 'online:' . $type;
        $this->_key_offline = sprintf('offline:%s:%s', $type, date('YmdH'));
        $this->_ttl         = 60 * 60 * 24 * 2;
    }

    /**
     * save 保存
     *
     * @return bool
     * 保存的时候 需要删除 当前小时的上线过 且离线的数据
     */
    public function save($nUserId)
    {
        $bool = $this->redis->sAdd($this->_key, $nUserId);
        $this->redis->sRem($this->_key_offline, $nUserId);
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
     * @param $nUserId
     * @return int
     * 下线删除时 需要添加到 当前小时 上线过 且离线的数据
     */
    public function delete_item($nUserId)
    {
        $this->redis->sAdd($this->_key_offline, $nUserId);
        return $this->redis->sRem($this->_key, $nUserId);
    }

    /**
     * getData 获取数据
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->redis->sMembers($this->_key);
    }

    /**
     * getUserTotal 获取用户总数
     *
     * @return int
     */
    public function getUserTotal()
    {
        return $this->redis->sCard($this->_key);
    }

}