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
 * IntimateService 亲密值
 */
class IntimateService extends RedisService
{

    private $anchor_id;
    private $user_id;
    private $_save_key = '';
    private $_key_all;
    private $_item_key;


    public function __construct($anchorId = 0, $nUserId = 0)
    {
        parent::__construct();
        $this->anchor_id = $anchorId;
        $this->user_id   = $nUserId;
        $this->_save_key = sprintf('%s-%s', $anchorId, $nUserId);
        $this->_key      = 'intimate_week:' . date('o-W');
//        $this->_key_all = 'intimate_anchor_user';
        $this->_item_key = sprintf('intimate_item:%s-%s', $anchorId, $nUserId);

    }

    /**
     * save 保存
     *
     * @return bool
     */
    public function save($nValue = 0, $level = 1, $level_name = '', $total_value = 0)
    {
        // 保存到周榜
        $bool = $this->redis->zIncrBy($this->_key, $nValue, $this->_save_key);
        // 保存到总榜
//        $bool = $this->redis->zIncrBy($this->_key_all, $nValue, $this->_save_key);
        $this->redis->hMSet($this->_item_key, [
            'level'       => $level,
            'level_name'  => $level_name,
            'total_value' => $total_value
        ]);
        $this->redis->expire($this->_item_key, 86400);

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
    public function delete_item()
    {
        return $this->redis->zRem($this->_key, $this->_save_key);
    }

    /**
     * getData 获取数据 排行榜前10
     *
     * @return mixed
     */
    public function getData($number = 10)
    {
        return $this->redis->zRevRange($this->_key, 0, $number, TRUE);
    }


    /**
     * 从缓存中取已经统计好的数据
     */
    public function getCacheData()
    {
        $cacheKey = 'intimateRank';
        return $this->redis->get($cacheKey);
    }


    public function saveCacheData($data)
    {
        $cacheKey = 'intimateRank';
        return $this->redis->set($cacheKey, $data, 60);
    }

    /**
     * @return array
     * 获取单个信息
     */
    public function getInfo()
    {
        return $this->redis->hGetAll($this->_item_key);
    }

    public function saveItem($arr)
    {
        $this->redis->hMSet($this->_item_key, $arr);
        $this->redis->expire($this->_item_key, 86400);
    }
}