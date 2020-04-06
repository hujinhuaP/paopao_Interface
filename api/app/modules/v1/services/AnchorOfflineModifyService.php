<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2018 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 主播給用戶打招呼记录                          |
 +------------------------------------------------------------------------+
 +------------------------------------------------------------------------+
 */

namespace app\services;

use Exception;
use Phalcon\DI\FactoryDefault as Di;

/**
 * AnchorOfflineModifyService 主播离线后 操作服务
 */
class AnchorOfflineModifyService extends RedisService
{

    const TYPE_ONLINE  = 'online';
    const TYPE_ON_CHAT = 'on_chat';
    const TYPE_OFFLINE = 'offline';

    public function __construct($type = 'online')
    {
        parent::__construct();
        switch ( $type ) {
            case self::TYPE_ONLINE:
                $this->_key = 'offline_anchor:online';
                break;
            case self::TYPE_ON_CHAT:
                $this->_key = 'offline_anchor:on_chat';
                break;
            case self::TYPE_OFFLINE:
            default:
                $this->_key = 'offline_anchor:offline';
        }

        $this->_ttl = 60 * 60 * 24 * 2;
    }

    public function save($nUserId)
    {
        $bool = $this->redis->zAdd($this->_key, time(), $nUserId);
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

    public function delete_item($nUserId)
    {
        return $this->redis->zDelete($this->_key, $nUserId);
    }

    public function delete_all($nUserId)
    {
        $this->redis->zDelete('offline_anchor:online', $nUserId);
        $this->redis->zDelete('offline_anchor:on_chat', $nUserId);
        $this->redis->zDelete('offline_anchor:offline', $nUserId);
        return TRUE;
    }

    /**
     * getData 获取数据
     *
     * @return mixed
     */
    public function getData()
    {
        return '';
    }

    public function getItem($nUserId)
    {
        return $this->redis->zScore($this->_key,$nUserId);
    }

    /**
     * getUserTotal 获取用户总数
     *
     * @return int
     */
    public function getUserTotal()
    {
        return $this->redis->zCard($this->_key);
    }

}