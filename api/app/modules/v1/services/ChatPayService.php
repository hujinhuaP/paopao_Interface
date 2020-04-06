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
* ChatPayService 通话支付服务
*/
class ChatPayService extends RedisService
{

	public function __construct($sChatLogId)
	{
		parent::__construct();
		$this->_key        = 'chat:pay:'.$sChatLogId;
        $this->_ttl = 86400;
	}

	/**
	 * save 保存
	 * 
	 * @return bool
	 */
	public function save($nUserId=0)
	{
		$bool = $this->redis->incr($this->_key);
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
	    return $this->redis->get($this->_key);
	}


}