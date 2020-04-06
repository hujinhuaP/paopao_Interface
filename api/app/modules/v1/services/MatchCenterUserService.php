<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 匹配大厅用户列表                          |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\services;

use Exception;

/**
 * MatchCenterUserService 匹配大厅用户列表
 */
class MatchCenterUserService extends RedisService
{

    private $_key_all = 'match_center_user_list_all';

    private $_key_time = 'match_center_user_list_time';

    public function __construct()
    {
        parent::__construct();
        $this->_key = 'match_center_user_list';
        $this->_ttl = 60 * 60 * 24 * 2;
    }

    /**
     * save 保存
     *
     * @return bool
     */
    public function save($nUserInfoStr,$isAll = FALSE)
    {
        if($isAll){
            $bool = $this->redis->zAdd($this->_key, time(), $nUserInfoStr);
            $bool = $this->redis->zAdd($this->_key_all, time(), $nUserInfoStr);
        }else{
            $bool = $this->redis->zAdd($this->_key, time(), $nUserInfoStr);
        }
        return $bool;
    }

    /**
     * delete 删除
     *
     * @return bool
     */
    public function delete()
    {
        $this->redis->del($this->_key_all);
        return $this->redis->del($this->_key);
    }

    /**
     * delete_item 删除个数
     *
     * @return number
     */
    public function delete_item($nUserInfoStr)
    {
        $delete_hot = $this->redis->zRem($this->_key_all, $nUserInfoStr);
        $delete_all = $this->redis->zRem($this->_key, $nUserInfoStr);
        return $delete_hot + $delete_all;
    }

    /**
     * getData 获取数据 20秒内
     *
     * @return mixed
     */
    public function getData($isAll = FALSE)
    {
        if($isAll){
            return $this->redis->zRangeByScore($this->_key_all, time() - 8, time() + 2);
        }
        return $this->redis->zRangeByScore($this->_key, time() - 20, time() + 2);
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

    /**
     * 记录用户进入匹配大厅时间
     */
    public function setEnterTime($nUserId)
    {
        return $this->redis->hSet($this->_key_time,$nUserId,time());
    }

    /**
     * 获取用户进入匹配大厅时间
     */
    public function getEnterTime($nUserId)
    {
        return $this->redis->hGet($this->_key_time,$nUserId);
    }

    /**
     * 删除用户进入匹配大厅时间
     */
    public function deleteEnterTime($nUserId)
    {
        return $this->redis->hDel($this->_key_time,$nUserId);
    }

}