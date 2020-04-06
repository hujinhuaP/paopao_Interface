<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | VIP免费观看视频                                      |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\services;

/**
* VipTodayVideoService
*/
class VipTodayVideoService extends RedisService
{

	public function __construct($nUserId)
	{
		parent::__construct();
		$this->_userId = $nUserId;
		$this->_key    = 'vip:video:'. date('Y-m-d') . ':' . $nUserId;
		$this->_ttl    = strtotime(date('Y-m-d',strtotime('+1 day'))) - time();
	}

	/**
	 * save 保存
	 * 
	 * @param  float $nExp
	 * @return float 
	 */
	public function save($videoId)
	{
        $flg = $this->redis->sAdd($this->_key,$videoId);
        $this->redis->expire($this->_key,$this->_ttl);
		return $flg;
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
		return $this->redis->sMembers($this->_key);
	}

    /**
     * delete 删除
     *
     * @return bool
     */
    public function delete_item($video_id)
    {
        return $this->redis->sRem($this->_key,$video_id);
    }
}