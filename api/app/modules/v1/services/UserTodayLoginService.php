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
* UserTodayLoginService 用户设备今日登录
*/
class UserTodayLoginService extends RedisService
{


	public function __construct()
	{
		parent::__construct();
		$this->_key        = 'user:device:'. date('Ymd');
		$this->_ttl         = 60*60*24*2;
	}

    /**
     * save 保存
     *
     * @param string $nUserId
     * @return bool
     */
	public function save($nUserId  = '')
	{
		$bool = $this->redis->sAdd($this->_key,$nUserId);
		if ($bool > 0) {
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

	/**
	 * getData 获取数据
	 * 
	 * @return mixed
	 */
	public function getData()
	{
		return $this->redis->get($this->_key);
	}

}