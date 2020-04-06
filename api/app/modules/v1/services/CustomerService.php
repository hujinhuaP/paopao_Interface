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
* CustomerService 客服
*/
class CustomerService extends RedisService
{


	public function __construct()
	{
		parent::__construct();
		$this->_key        = 'customer_service_list';
	}

    /**
     * save 保存
     *
     * @param $userId
     * @param $value
     * @return bool
     */
	public function save($userId,$value = '')
	{
       return $this->redis->hSet($this->_key,$userId,$value);
	}

	/**
	 * delete 删除
	 * 
	 * @return bool
	 */
	public function delete($userId = 0)
	{
		return $this->redis->hDel($this->_key,$userId);
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