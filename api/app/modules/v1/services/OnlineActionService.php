<?php

namespace app\services;

use Exception;
use Phalcon\DI\FactoryDefault as Di;

/**
 * OnlineActionService 在线操作记录（在线状态）(在线时长)
 *
 * 用户请求一次接口 则记录一次活动时间  存入 hash 里面  hash里存入 操作方法 操作时间
 *      如果活动时间延迟10分钟 则表示用户离线   每分钟从在线人列表中 查询 是否活动时间延迟10分钟
 */
class OnlineActionService extends RedisService
{

    /**
     * UserOnlineService constructor.
     * @param $type  string  anchor or user
     */
    public function __construct($nUserId)
    {
        parent::__construct();
        $this->_key         = sprintf("online:action:%s",$nUserId);
        $this->_ttl         = 60 * 60 * 24 * 2;
    }

    /**
     * save 保存
     *
     * @return bool
     * 保存的时候 需要删除 当前小时的上线过 且离线的数据
     */
    public function save($data)
    {
        $bool = $this->redis->hMSet($this->_key, $data);
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
     * getData 获取数据
     *
     * @return mixed
     */
    public function getData()
    {
        return '';
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