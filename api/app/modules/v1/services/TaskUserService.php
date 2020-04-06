<?php

/*
 +------------------------------------------------------------------------+
 +------------------------------------------------------------------------+
 | 主播时长排行榜                          |
 +------------------------------------------------------------------------+
 */

namespace app\services;

use app\models\Kv;
use Exception;

/**
 * TaskUserService 记录今日用户任务获得金币数
 */
class TaskUserService extends RedisService
{


    public function __construct()
    {
        parent::__construct();
        $this->_key = sprintf('task_reward_coin:%s',date('Y-m-d'));
        $this->_ttl = 3600 * 24 * 2;
    }

    /**
     * save 保存
     *
     * @return bool
     */
    public function save($nUserId =0,$coin=0)
    {
        $bool = $this->redis->hIncrBy($this->_key, $nUserId, $coin);
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
     * @return mixed
     */
    public function getData($nUserId= 0)
    {
        return $this->redis->hGet($this->_key,$nUserId);
    }

    /**
     * @param int $nUserId
     * @param $nSerialSigninCoin
     * @return mixed
     * 获取实际可增加的金币
     */
    public function getExistsCoin(int $nUserId, $totalCoin) {
        $todayCoin = $this->save($nUserId,$totalCoin);
        // 添加后 判断超出了多少
        $task_daily_coin_max = Kv::get(Kv::TASK_DAILY_COIN_MAX);
        if($todayCoin > $task_daily_coin_max){
            // 已经大于最大值了 则实际可以添加的为 当前的 减去最大值 为多添加的  $todayCoin - $task_daily_coin_max
            return max(0,$totalCoin + $task_daily_coin_max - $todayCoin);
        }
        return $totalCoin;

    }


}