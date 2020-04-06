<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 房间关注用户服务                                                       |
 | 记录房间直播时间段内主播被用户关注的数量                               |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\services;

use Exception;
use Phalcon\DI\FactoryDefault as Di;

/**
* RoomFollowService 房间关注用户服务
*/
class RoomFollowService extends RedisService
{

	/** @var int 直播日志ID */
	protected $live_log_id;

	public function __construct($sLiveLogId)
	{
		parent::__construct();
		$this->live_log_id = $sLiveLogId;
		$this->_key        = 'room:follow:'.$sLiveLogId;
		$this->_ttl         = 60*60*24*2;
	}

	/**
	 * save 保存
	 * 
	 * @return bool
	 */
	public function save($nUserId)
	{
		$bool = $this->redis->zAdd($this->_key, time(), $nUserId);
		if ($bool) {
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
		throw new Exception(static::class." Can not getData");
	}

	/**
	 * getUserTotal 获取用户总数
	 * 
	 * @return int
	 */
	public function getUserTotal()
	{
		return $this->redis->zCard($this->_key);
	}

}