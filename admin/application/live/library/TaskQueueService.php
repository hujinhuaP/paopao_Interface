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

namespace app\live\library;

use app\live\library\Redis;

/**
* TaskQueueService
*/
class TaskQueueService
{

	public function __construct()
	{
		$this->_key = 'task:mq';
		$this->redis = new Redis();
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
	 * @param  mixed $data
	 * @return int
	 */
	public function enQueue($data)
	{
		return $this->redis->lpush($this->_key, serialize(['data'=>$data, 'time'=>time()]));
	}
}