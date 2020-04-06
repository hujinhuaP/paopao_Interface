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
* UserVideoChatService 用户视频通话状态
 * 正在通话中 有数据  退出通话则删除
*/
class UserVideoChatService extends RedisService
{


	public function __construct()
	{
		parent::__construct();
		$this->_key        = 'user:video_chat';
	}

    /**
     * save 保存
     *
     * @param $value
     * @return bool
     */
	public function save($sUserId)
	{
		$bool = $this->redis->sAdd($this->_key,$sUserId);
		return $bool;
	}

	/**
	 * delete 删除
	 * 
	 * @return bool
	 */
	public function delete($sUserId=0)
	{
		return $this->redis->sRem($this->_key,$sUserId);
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