<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 主播心跳时间                                                           |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\services;

/**
* AnchorHeartbeatService
*/
class AnchorHeartbeatService extends RedisService
{

	public function __construct()
	{
		parent::__construct();
		$this->_key        = 'anchor:heartbeat';
	}

	/**
	 * save 保存
	 * 
	 * @return bool
	 */
	public function save($nAnchorUserId)
	{
		$bool = $this->redis->zAdd($this->_key, time(), $nAnchorUserId);
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
		throw new Exception(static::class." Can not getData");
	}

	/**
	 * delUser 删除用户
	 * 
	 * @param  int $nAnchorUserId
	 * @return true
	 */
	public function delUser($nAnchorUserId)
	{
		return $this->redis->zrem($this->_key, $nAnchorUserId);
	}
}