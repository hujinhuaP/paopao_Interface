<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 聊天中的TIM断线问题                          |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\services;

use Exception;

/**
 * DeviceRetainService 设备的留存
 */
class DeviceRetainService extends RedisService
{

    private $_key_prefix;

    public function __construct($timesFlg,$dateFlg)
    {
        parent::__construct();
        $this->_key_prefix = 'device_retain';
        $this->_key        = sprintf('%s:%s:%s',$this->_key_prefix ,$timesFlg,$dateFlg);
    }

    /**
     * save 保存
     *
     * @return bool
     */
    public function save($userId = 0)
    {
        $bool = $this->redis->sAdd($this->_key, $userId);
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
     * delete_item 删除个数
     *
     * @return number
     */
    public function delete_item($userId)
    {
        $bool = $this->redis->sRem($this->_key, $userId);
        return $bool;
    }

    /**
     * getData 获取数据 20秒内
     *
     * @return mixed
     */
    public function getData()
    {
    }

}