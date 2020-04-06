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
 * UserLoginService 用户的登录信息记录
 * 利用redis的位运算 判断留存 连续登陆数据
 */
class UserLoginService extends RedisService
{

    private $_key_prefix;

    public function __construct()
    {
        parent::__construct();
        $this->_key_prefix = 'user_Login:';
        $this->_key        = $this->_key_prefix . date('Ymd');
    }

    /**
     * save 保存
     *
     * @return bool
     */
    public function save($userId = 0)
    {
        $bool = $this->redis->setBit($this->_key, $userId, 1);
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
        $bool = $this->redis->setBit($this->_key, $userId, 0);
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

    /**
     * @param $endDate
     * @param string $type
     * 获取多天留存
     *
     */
    public function getData2($endDate, $dateCount = 2)
    {
        $startTimestamp = strtotime($endDate);
        $keyArr         = [
            $this->_getDataKey(date('Ymd', strtotime($endDate)))
        ];

        $startDate = $startTimestamp - ($dateCount - 1) * 86400;
        $tmpStamp  = $startTimestamp;
        for ( $i = $dateCount; $i > 1; $i-- ) {
            $dateItem = date('Ymd', $tmpStamp - 86400);
            $keyArr[] = $this->_getDataKey($dateItem);
            $tmpStamp -= 86400;
        }
        $newKey = sprintf('%s%sto%s', $this->_key_prefix, $startDate, $endDate);
        switch ( $dateCount ) {
            case 3:
                $this->redis->bitOp('and', $newKey, $keyArr[0], $keyArr[1], $keyArr[2]);
                break;
            case 7:
                $this->redis->bitOp('and', $newKey, $keyArr[0], $keyArr[1], $keyArr[2], $keyArr[3], $keyArr[4], $keyArr[5], $keyArr[6]);
                break;
            case 30:
                $this->redis->bitOp('and', $newKey, $keyArr[0], $keyArr[1], $keyArr[2], $keyArr[3], $keyArr[4], $keyArr[5], $keyArr[6], $keyArr[7], $keyArr[8], $keyArr[9], $keyArr[10], $keyArr[11], $keyArr[12], $keyArr[13], $keyArr[14], $keyArr[15], $keyArr[16], $keyArr[17], $keyArr[18], $keyArr[19], $keyArr[20], $keyArr[21], $keyArr[22], $keyArr[23], $keyArr[24], $keyArr[25], $keyArr[26], $keyArr[27], $keyArr[28], $keyArr[29]);
                break;
            case 2:
            default:
                $this->redis->bitOp('and', $newKey, $keyArr[0], $keyArr[1]);
        }
        return $this->redis->bitCount($newKey);
    }

    private function _getDataKey($date)
    {
        return sprintf('%s%s', $this->_key_prefix, $date);
    }

}