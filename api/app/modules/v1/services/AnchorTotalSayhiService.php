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
* AnchorTotalSayhiService 主播給用戶打招呼记录(每小时最多10次，时间以最后一个计时)
 * 每个主播有个hash key 存储 打招呼次数 以及最后一次的时间  key的超时时间为每次记录的1天后
*/
class AnchorTotalSayhiService extends RedisService
{


    /**
     * hash key 打招呼次数
     */
    const KEY_TIMES = 'times';
    /**
     * hash key 最后一次的时间
     */
    const KEY_LAST_TIME = 'last_time';

    public function __construct($sAnchorId)
	{
		parent::__construct();
		$this->_key        = 'anchor:totalsayhi:'.$sAnchorId;
		$this->_ttl         = 60*60*24;
	}

	/**
	 * save 保存
	 * 
	 * @return bool
	 */
	public function save($nUserId)
	{
	    $this->redis->hIncrBy($this->_key,self::KEY_TIMES,1);
	    $this->redis->hSet($this->_key,self::KEY_LAST_TIME,time());
        $this->redis->expire($this->_key,$this->_ttl);
		return true;
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
		return $this->redis->hGetAll($this->_key);
	}

}