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
* UserLastGuideService 用户最后一次诱导取的话术记录
 * 保留10分钟，第一次诱导和第二次诱导类型不能相同
*/
class UserLastGuideService extends RedisService
{


	public function __construct($location = 'index',$nUserId=0)
	{
		parent::__construct();
		$this->_key         = sprintf('user:guide:%s:%s',$location,$nUserId);
		$this->_ttl         = 600;
	}

    /**
     * save 保存
     *
     * @param array $saveArr
     * @return bool
     */
	public function save($saveArr = [])
	{
		$bool = $this->redis->hMSet($this->_key,$saveArr);
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
		return $this->redis->hGetAll($this->_key);
	}

}