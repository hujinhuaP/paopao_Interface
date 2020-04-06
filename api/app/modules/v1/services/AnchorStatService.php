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
* AnchorStatService 主播统计
*/
class AnchorStatService extends RedisService
{
    // 聊天时长收益
    const TIME_INCOME = 'time_income';
    // 礼物收益
    const GIFT_INCOME = 'gift_income';
    // 视频收益
    const VIDEO_INCOME = 'video_income';
    // 文字收益
    const WORD_INCOME = 'word_income';
    // 匹配时长秒
    const MATCH_DURATION = 'match_duration';
    // 匹配次数
    const MATCH_TIMES = 'match_times';
    // 第一次匹配并充值人数
    const MATCH_RECHARGE_COUNT = 'match_recharge_count';
    // 点播时长秒
    const NORMAL_CHAT_DURATION = 'normal_chat_duration';
    // 点播成功次数
    const NORMAL_CHAT_TIMES = 'normal_chat_times';
    // 有效点播呼叫次数
    const NORMAL_CHAT_CALL_TIMES = 'normal_chat_call_times';
    // 每日在线总时长
    const ONLINE_DURATION = 'online_duration';
    // 最近上线时间
    const TIME_LOGIN = 'login_time';
    // 最近下线时间
    const TIME_LOGOUT = 'logout_time';
    // 诱导消息发送次数
    const GUIDE_MSG_TIMES = 'guide_msg_times';
    // 诱导用户数
    const GUIDE_USER_COUNT = 'guide_user_count';
    // 视频游戏收入
    const CHAT_GAME_INCOME = 'chat_game_income';
    // 守护收益
    const GUARD_INCOME = 'guard_income';
    // 动态收益
    const POSTS_INCOME = 'posts_income';
    // 邀请充值收益奖励
    const INVITE_RECHARGE_INCOME = 'invite_recharge_income';
    // 微信收益奖励
    const WECHAT_INCOME = 'wechat_income';

    private $_guide_user_key;

	public function __construct($sAnchorId)
	{
		parent::__construct();
		$this->_key        = sprintf('anchor:stat:%s:%s',date('Ymd'),$sAnchorId);
		$this->_guide_user_key        = sprintf('anchor:stat:guide_user_list:%s:%s',date('Ymd'),$sAnchorId);
		$this->_ttl         = 86400 * 7;
	}

	/**
	 * save 保存
	 * 
	 * @return bool
	 */
	public function save($key,$value = 0,$isIncr = true)
	{
	    if($key == self::GUIDE_USER_COUNT){
	        // 如果是人数 需要去重
            $flg = $this->redis->sAdd($this->_guide_user_key,$value);
            if( !$flg ){
                $this->redis->expire($this->_guide_user_key,86400 * 2);
                return FALSE;
            }
            $value = 1;
        }
	    if($isIncr){
	        if(in_array($key,[self::MATCH_DURATION,self::NORMAL_CHAT_DURATION]) && $value < 10){
	            // 聊天时长小于5秒的不算
	            return FALSE;
            }
            $bool = $this->redis->hIncrByFloat($this->_key,$key,sprintf('%.4f',$value));
        }else{
            $bool = $this->redis->hSet($this->_key,$key,$value);
        }
		$this->redis->hSet($this->_key,'update_time',time());
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

    public function delete_item($key)
    {
        return $this->redis->hSet($this->_key,$key,0);
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