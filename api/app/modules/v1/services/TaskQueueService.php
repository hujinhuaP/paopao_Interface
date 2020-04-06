<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 任务队列服务                                                           |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\services;

/**
* TaskQueueService
*/
class TaskQueueService extends RedisService
{

	public function __construct()
	{
		parent::__construct();
		$this->_key = 'task:mq';
	}

	public function save($value='')
	{}

	public function getData()
	{}

	public function delete()
	{}

	/**
	 * deQueue 出队
	 * 
	 * @return array
	 */
	public function deQueue()
	{
		return unserialize($this->redis->brpop($this->_key));
	}

	/**
	 * enQueue 入队
	 * 
	 * @param  array $data
	 * @return int
	 */
	public function enQueue($data)
	{
		return $this->redis->lpush($this->_key, serialize(['data'=>$data, 'time'=>time()]));
	}
}