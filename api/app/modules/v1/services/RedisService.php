<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | Redis服务基类                                                          |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\services;

use Phalcon\DI\FactoryDefault as Di;

/**
* RedisService
*/
abstract class RedisService
{
	/** @var int redis key的生存时间 */
	protected $_ttl=0;

	/** @var string redis的key */
	protected $_key;

	/** @var mixed redis的数据 */
	protected $_data;

	/** @var \Redis */
	public $redis;

	/**
	 * __construct 构造函数
	 */
	public function __construct()
	{
		$di = Di::getDefault();
		$this->redis = $di['redis'];
	}

	/**
	 * getKey 获取key
	 * 
	 * @return string
	 */
	public function getKey()
	{
		return $this->_key;
	}

	/**
	 * getData 获取数据
	 * 
	 * @return mixed
	 */
	abstract public function getData();

	/**
	 * save 保存
	 * 
	 * @param  mixed $value
	 * @return bool
	 */
	abstract public function save($value);

	/**
	 * delete 删除
	 * 
	 * @return bool
	 */
	abstract public function delete();
}