<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2018 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 用戶給主播打招呼记录                          |
 +------------------------------------------------------------------------+
 +------------------------------------------------------------------------+
 */

namespace app\services;

use Exception;
use Phalcon\DI\FactoryDefault as Di;

/**
* AnchorBatchSayhiService 主播给用户批量打招呼记录
*/
class AnchorBatchSayhiService extends RedisService
{


	public function __construct($sUserId)
	{
		parent::__construct();
		$this->_key        = 'anchor:sayhi:batch:'.$sUserId;
		$this->_ttl         = 3600 * 24;
	}

    /**
     * save 保存
     *
     * @param $value
     * @return bool
     */
	public function save($value)
	{
		$bool = $this->redis->set($this->_key,$value);
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