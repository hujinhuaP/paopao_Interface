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
 * MatchCenterUserAnchorService 匹配大厅用户匹配过的主播列表 （或主播匹配过的用户）
 */
class MatchCenterUserAnchorService extends RedisService
{

    public function __construct($nUserId)
    {
        parent::__construct();
        $this->_key = 'match_center_user_anchor:'.$nUserId;
        $this->_ttl = 3600 * 24 * 2;
    }

    /**
     * save 保存
     *
     * @return bool
     */
    public function save($nAnchorId =0)
    {
        $bool = $this->redis->zAdd($this->_key, time(), $nAnchorId);
        if($bool){
            $this->redis->expire($this->_key,$this->_ttl);
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
        return $this->redis->del($this->_key);
    }

    /**
     * delete_item 删除个数
     *
     * @return number
     */
    public function delete_item($start,$end)
    {
        return $this->redis->zRemRangeByScore($this->_key,$start,$end);
    }

    /**
     * getData 获取数据
     *
     * @param int $startSecond  获取多少秒内的数据
     * @return mixed
     */
    public function getData($startSecond = 60)
    {
        return $this->redis->zRangeByScore($this->_key, time() - $startSecond, time() + 2);
    }

}