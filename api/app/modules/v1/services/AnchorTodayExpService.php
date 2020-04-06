<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 用户今天经验记录服务                                                   |
 | 负责用户记录今天获取的经验                                             |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\services;

/**
* AnchorTodayExpService
*/
class AnchorTodayExpService extends RedisService
{

	/** @var int 直播日志ID */
	protected $_userId;

	/** @var string 获取经验方式 */
	protected $_mode;

	public function __construct($nUserId, $sMode)
	{
		parent::__construct();
		$this->_userId = $nUserId;
		$this->_mode   = $sMode;
		$this->_key    = 'anchor:today:exp:'.date('Ymd').':'.$nUserId.':'.$sMode;
		$this->_ttl    = 60*60*24;
	}

	/**
	 * save 保存
	 * 
	 * @param  float $nExp
	 * @return float 
	 */
	public function save($nExp)
	{
		$nExp = $this->redis->incrByFloat($this->_key, $nExp);

		if ($nExp) {
			$this->redis->expire($this->_key, $this->_ttl);
		}
		return $nExp;
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