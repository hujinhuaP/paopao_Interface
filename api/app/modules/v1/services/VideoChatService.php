<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 视频聊天开始                                                           |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\services;

/**
* VideoChatService
*/
class VideoChatService extends RedisService
{

	public function __construct()
	{
		parent::__construct();
		// 用来定时付费
        $this->_key        = 'chat:user:chat_id';

	}

    /**
     * save 保存
     *
     * @param $str
     * @param string $time
     * @return bool
     */
	public function save($str,$time = 0)
	{
	    if(!$time){
            $time = time();
        }
		$bool = $this->redis->zAdd($this->_key, $time, $str);
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
     * @param string $member
     * @return mixed
     */
	public function getData($member = '')
	{
        return $this->redis->zScore($this->_key,$member);
	}

	/**
	 * delItem 删除指定聊天
	 * 
	 * @param  string $str
	 */
	public function delItem($str)
	{
		return $this->redis->zrem($this->_key, $str);
	}

    /**
     * @param $anchorUserToUser  string 主播id:用户id
     * @param $coin int 用户增加消费金币
     * @param $dot float 主播增加收益
     */
    public function addGiftData($anchorUserToUser,$coin, $dot)
    {
        $key = sprintf('chatGift:'.$anchorUserToUser);
        $this->redis->hIncrByFloat($key,'coin',$coin);
        $this->redis->hIncrByFloat($key,'dot',$dot);
        return;
	}

    public function getGiftData($anchorUserToUser)
    {
        $key = sprintf('chatGift:'.$anchorUserToUser);
        // 取数据后1小时 删除
        $this->redis->expire($key,3600);
        return $this->redis->hGetAll($key);
	}

    public function deleteGiftData($anchorUserToUser)
    {
        $key = sprintf('chatGift:'.$anchorUserToUser);
        return $this->redis->delete($key);
	}


    /**
     * @param $anchorUserToUser  string 主播id:用户id
     * @param $coin int 用户增加消费金币
     * @param $dot float 主播增加收益
     */
    public function addChatGameData($anchorUserToUser,$coin, $dot)
    {
        $key = sprintf('chatChatGame:'.$anchorUserToUser);
        $this->redis->hIncrByFloat($key,'coin',$coin);
        $this->redis->hIncrByFloat($key,'dot',$dot);
        return;
    }

    public function getChatGameData($anchorUserToUser)
    {
        $key = sprintf('chatChatGame:'.$anchorUserToUser);
        // 取数据后1小时 删除
        $this->redis->expire($key,3600);
        return $this->redis->hGetAll($key);
    }

    public function deleteChatGameData($anchorUserToUser)
    {
        $key = sprintf('chatChatGame:'.$anchorUserToUser);
        return $this->redis->delete($key);
    }
}