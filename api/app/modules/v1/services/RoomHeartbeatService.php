<?php 


namespace app\services;

/**
* RoomHeartbeatService
 * 房间心跳
*/
class RoomHeartbeatService extends RedisService
{
	public function __construct()
	{
		parent::__construct();
        $this->_key        = 'room:heartbeat';

	}

	/**
	 * save 保存
	 * 
	 * @return bool
	 */
	public function save($str)
	{
		$bool = $this->redis->zAdd($this->_key, time(), $str);
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
		throw new Exception(static::class." Can not getData");
	}

	/**
	 * delItem 删除项目
	 * 
	 * @param  string $str
	 * @return true
	 */
	public function delItem($str)
	{
		return $this->redis->zRem($this->_key, $str);
	}
}