<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2018 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 主播统计                          |
 +------------------------------------------------------------------------+
 +------------------------------------------------------------------------+
 */

namespace app\services;

use Exception;
use Phalcon\DI\FactoryDefault as Di;

/**
* AppListService appList
*/
class AppListService extends RedisService
{

	public function __construct($appFlg='tianmi')
	{
		parent::__construct();
		$this->_key        = sprintf('app_list:%s',$appFlg);
		$this->_ttl         = 86400 * 7;
	}

	/**
	 * save 保存
	 * 
	 * @return bool
	 */
	public function save($value = '')
	{
        $bool = $this->redis->hMSet($this->_key,$value);
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