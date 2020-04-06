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
 * ChatTimCheckService 聊天中的TIM断线问题
 */
class ChatTimCheckService extends RedisService
{

    public function __construct()
    {
        parent::__construct();
        $this->_key = 'chat_tim_offline';
    }

    /**
     * save 保存
     *
     * @return bool
     */
    public function save($str)
    {
        $bool = $this->redis->zAdd($this->_key, time(), $str);
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
    public function delete_item($str)
    {
        return $this->redis->zRem($this->_key, $str);
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