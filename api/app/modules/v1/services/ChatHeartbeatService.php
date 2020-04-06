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
class ChatHeartbeatService extends RedisService
{

	public function __construct($type)
	{
		parent::__construct();
		if($type == 1){
            $this->_key        = 'chat:anchor:heartbeat';
        }else{
            $this->_key        = 'chat:user:heartbeat';
        }

	}

	/**
	 * save 保存
	 * 
	 * @return bool
	 */
	public function save($str)
	{
		$bool = $this->redis->zAdd($this->_key, time(), $str);
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