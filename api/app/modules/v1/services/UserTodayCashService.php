<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2018 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 用户发消息                          |
 +------------------------------------------------------------------------+
 +------------------------------------------------------------------------+
 */

namespace app\services;

use Exception;
use Phalcon\DI\FactoryDefault as Di;

/**
* UserTodayCashService 用户今日收益 “现金” 记录
*/
class UserTodayCashService extends RedisService
{


	public function __construct($nUserId)
	{
		parent::__construct();
		$this->_key        = 'user:today:cash:'. date('Y-m-d') . ':'.$nUserId;
		$this->_ttl         = 60*60*24*2;
	}

    /**
     * save 保存
     *
     * @param string $nUserId
     * @return bool
     */
	public function save($value)
	{
		$bool = $this->redis->incrByFloat($this->_key,$value);
		if ($bool > 0) {
		    $ttl = strtotime(date('Y-m-d').' +1 day') - time();
			$this->redis->expire($this->_key, $ttl);
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
		return (float)$this->redis->get($this->_key);
	}

}