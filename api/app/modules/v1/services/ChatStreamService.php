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
* ChatStreamService 视频聊天流记录
*/
class ChatStreamService extends RedisService
{


	public function __construct($streamId)
	{
		parent::__construct();
		$this->_key        = 'chat:stream:'.$streamId;
	}

    /**
     * save 保存
     *
     * @return bool
     */
	public function save($arr)
	{
       return $this->redis->hMSet($this->_key,$arr);
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