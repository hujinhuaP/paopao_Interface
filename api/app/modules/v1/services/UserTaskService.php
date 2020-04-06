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

use app\models\TaskConfig;
use Exception;
use Phalcon\DI\FactoryDefault as Di;

/**
 * UserTaskService 用户完成任务记录
 */
class UserTaskService extends RedisService
{


    public function __construct($taskFlg, $taskType = '')
    {
        parent::__construct();

        if ( $taskType == TaskConfig::TASK_TYPE_DAILY ) {
            $this->_key = sprintf('task_finish:daily_user:%s:%s', date('Ymd'), $taskFlg);
        } else if ( $taskType == TaskConfig::TASK_TYPE_ANCHOR_DAILY ) {
            $this->_key = sprintf('task_finish:daily_anchor:%s:%s', date('Ymd'), $taskFlg);
        } else {
            $this->_key = sprintf('task_finish:once_user:%s', $taskFlg);
        }

        $this->_ttl = 86400;
    }

    /**
     * save 保存
     *
     * @return bool
     */
    public function save($nUserId)
    {
        $bool = $this->redis->sAdd($this->_key, $nUserId);
        if ( $bool > 0 ) {
            $this->redis->expire($this->_key, $this->_ttl);
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

    public function delete_item($nUserId)
    {
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

    public function is_exists($nUserId)
    {
        return $this->redis->sIsMember($this->_key, $nUserId);
    }

}