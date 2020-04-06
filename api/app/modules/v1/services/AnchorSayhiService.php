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
* AnchorSayhiService 主播給用戶打招呼记录
*/
class AnchorSayhiService extends RedisService
{


	public function __construct($sAnchorId)
	{
		parent::__construct();
		$this->_key        = 'anchor:sayhi:'.$sAnchorId;
		$this->_ttl         = 60*60*24*2;
	}

	/**
	 * save 保存
	 * 
	 * @return bool
	 */
	public function save($nUserId)
	{
		$bool = $this->redis->sAdd($this->_key,$nUserId);
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

    public function delete_item($nUserId)
    {
        return $this->redis->sRem($this->_key,$nUserId);
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

}