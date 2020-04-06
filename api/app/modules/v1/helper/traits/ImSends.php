<?php 

namespace app\helper\traits;

trait ImSends
{
	/**
     * sendRoomOnlineUserSignal 发送房间用户信息
     * 
     * @param  array  $aData
	 * @return bool
     */
    public function sendRoomOnlineUserSignal($aData=[])
    {
    	return $this->notify(static::ROOM_USERS, '', $aData);
    }

	/**
	 * sendKillSignal 发送退出信号
	 * 
	 * @param  array  $sMsg
	 * @return bool
	 */
	public function sendKillSignal($sMsg='', $aData=[])
	{
    	return $this->notify(static::KILL_ONLINE, $sMsg, $aData);
	}

	/**
	 * sendGiftSignal 发送礼物信号
	 * 
	 * @param  string $sMsg
	 * @param  int    $nRoomId
	 * @param  array  $aData
	 * @return bool
	 */
	public function sendGiftSignal($aData=[])
	{
		return $this->notify(static::SEND_GIFT, '', $aData);
	}

	/**
	 * sendRoomChatSignal 发送房间聊天信号
	 * 
	 * @param  array   $aData   
	 * @return bool
	 */
	public function sendRoomChatSignal($aData=[])
	{
        return $this->notify(static::SEND_ROOM_CHAT, '', array_merge(['fuser'=>$this->_user],$aData));
	}

	/**
	 * sendBarrageSignal 发送弹幕信号
	 * 
	 * @param  array   $aData   
	 * @return bool
	 */
	public function sendBarrageSignal($aData=[])
	{
		return $this->notify(static::SEND_BARRAGE, '', $aData);
	}

	/**
	 * sendChatSignal 发送聊天信号
	 * 
	 * @param  array   $aData   
	 * @return bool
	 */
	public function sendChatSignal($aData=[])
	{
		return $this->notify(static::SEND_CHAT, '', $aData);
	}

	/**
	 * sendAnchorSignal 发送主播信息信号
	 * 
	 * @param  array  $aData 
	 * @return bool
	 */
	public function sendAnchorSignal($aData=[])
	{
		return $this->notify(static::SEND_ANCHOR, '', $aData);
	}

	/**
	 * sendLiveEndSignal 发送结束直播信号
	 * 
	 * @return bool
	 */
	public function sendLiveEndSignal($aData=[])
	{
		return $this->notify(static::SEND_LIVE_END, '', $aData);
	}

	/**
	 * sendRoomAdminSignal 发送房管信息信号
	 * 
	 * @param  array  $aData 
	 * @return bool
	 */
	public function sendRoomAdminSignal($aData=[])
	{
		return $this->notify(static::ROOM_ADMIN, '', $aData);
	}

	/**
	 * sendKillLiveSignal 发送禁播(停播)信号
	 * 
	 * @param  array  $aData 
	 * @return bool
	 */
	public function sendKillLiveSignal($sMsg='')
	{
		return $this->notify(static::KILL_LIVE, $sMsg);
	}

	/**
	 * sendKickSignal 发送踢出房间信号
	 * 
	 * @param  array  $aData 
	 * @return bool
	 */
	public function sendKickSignal($aData=[])
	{
		return $this->notify(static::KICK, '', $aData);
	}

	/**
	 * sendProhibitTalkSignal 发送禁言信息信号
	 * 
	 * @param  array  $aData 
	 * @return bool
	 */
	public function sendProhibitTalkSignal($aData=[])
	{
		return $this->notify(static::PROHIBIT_TALK, '', $aData);
	}

	/**
	 * sendLikeSignal 发送点赞信号
	 * 
	 * @param  array  $aData 
	 * @return bool
	 */
	public function sendLikeSignal($aData=[])
	{
		return $this->notify(static::SEND_LIKE, '', $aData);
	}

	/**
	 * sendJoinSignal 发送加入房间信号
	 * 
	 * @return bool
	 */
	public function sendJoinSignal($batch = FALSE,$data = [])
	{
	    if($batch){
            return $this->notify_batch(static::JOIN, '', ['fuser'=>$this->_user, 'liveonlines'=>0]);
        }
        // 语聊房
        return $this->notify(static::JOIN_ROOM, '', array_merge(['fuser'=>$this->_user],$data));
	}

	/**
	 * sendLeaveSignal 发送离开房间信号
	 * 
	 * @return bool
	 */
	public function sendLeaveSignal($batch = FALSE,$data = [])
	{
        if($batch){
            return $this->notify_batch(static::LEAVE, '', ['fuser'=>$this->_user, 'liveonlines'=>0]);
        }
        // 语聊房
        return $this->notify(static::LEAVE_ROOM, '',array_merge(['fuser'=>$this->_user],$data));
	}

	/**
	 * sendUserLevelUpSignal 发送用户等级升级信号
	 * 
	 * @param  \app\models\User $oUser
	 * @return bool
	 */
	public function sendUserLevelUpSignal(\app\models\User $oUser)
	{
		$sMsg = sprintf('恭喜%s升级至%s级', $oUser->user_nickname, $oUser->user_level);
		return $this->notify(static::SYSTEM, $sMsg);
	}

    /**
     * sendPrivateChat 发送一对一私聊信息
     * @return bool
     */
    public function sendPrivateChat($aData=[])
    {
        return $this->notify(static::PRIVATE_CHAT, '',$aData);
    }

    /**
     * acceptPrivateChat 接受一对一私聊信息
     * @return bool
     */
    public function acceptPrivateChat($aData=[])
    {
        return $this->notify(static::ACCEPT_PRIVATE_CHAT,'', $aData);
    }

    /**
     * refusePrivateChat 拒绝一对一私聊信息
     * @return bool
     */
    public function refusePrivateChat($aData=[])
    {
        return $this->notify(static::REFUSE_PRIVATE_CHAT, '',$aData);
    }

    /**
     * refusePrivateChat 取消一对一私聊信息
     * @return bool
     */
    public function cancelPrivateChat($aData=[])
    {
        return $this->notify(static::CANCEL_PRIVATE_CHAT, '',$aData);
    }

    /**
     * refusePrivateChat 取消一对一私聊信息
     * @return bool
     */
    public function hangUpPrivateChat($aData=[])
    {
        return $this->notify(static::HANG_UP_PRIVATE_CHAT, '',$aData);
    }

	/**
	 * refusePrivateChat 取消一对一私聊信息
	 * @return bool
	 */
	public function vagueChat($aData=[])
	{
		return $this->notify(static::VAGUE_CHAT, '',$aData);
	}

    /**
     * sendFollowAdd 取消一对一私聊信息
     * @return bool
     */
    public function sendFollowAdd($aData=[])
    {
        return $this->notify(static::ADD_FOLLOW, '',$aData);
    }

    /**
     * sendFollowAdd 取消一对一私聊信息
     * @return bool
     */
    public function chatForbidden($aData=[])
    {
        return $this->notify(static::CHAT_FORBIDDEN, '',$aData);
    }

    /**
     * matchSuccessPrivateChat 匹配成功 发送给用户 主播的信息
     * @return bool
     */
    public function matchSuccessPrivateChat($aData=[])
    {
        return $this->notify(static::MATCH_SUCCESS, '',$aData);
    }

    /**
     * userMatchSuccessPrivateChat 匹配成功 后 用户接收到推送 将此推送发送给主播
     * @return bool
     */
    public function userMatchSuccessPrivateChat($aData=[])
    {
        return $this->notify(static::MATCH_SUCCESS_USER, '',$aData);
    }

    /**
     * userCoinNotEnough 取消一对一私聊信息
     * @return bool
     */
    public function userCoinNotEnough($aData=[])
    {
        return $this->notify(static::USER_COIN_NOT_ENOUGH, '',$aData);
    }

    /**
     * videoChatPaySuccess 视频聊天付费成功
     * @return bool
     */
    public function videoChatPaySuccess($aData=[])
    {
        return $this->notify(static::VIDEO_CHAT_PAY_SUCCESS, '',$aData);
    }

    /**
     * vipPaySuccess VIP购买 或者续费成功
     * @return bool
     */
    public function vipPaySuccess($aData=[])
    {
        return $this->notify(static::VIP_PAY_SUCCESS, '',$aData);
    }

    /**
     * rechargeSuccess 充值成功
     * @return bool
     */
    public function rechargeSuccess($aData=[])
    {
        return $this->notify(static::RECHARGE_SUCCESS, '',$aData);
    }

    /**
     * sendChatSignal 发送聊天信号
     *
     * @param  array   $aData
     * @return bool
     */
    public function sendChatSignalBatch($aData=[])
    {
        return $this->notify_batch(static::SEND_ROOM_CHAT, '', $aData);
    }

    public function sendGiftSignalBatch($aData=[]){
        return $this->notify_batch(static::SEND_GIFT, '', $aData);
    }

    /**
     * @param array $aData
     * @return mixed
     * 用户发起抢播
     */
    public function sendSnatchChatBatch($aData=[])
    {
        return $this->notify_batch(static::SEND_SNATCH, '', $aData);
    }

    /**
     * @param array $aData
     * @return mixed
     * 用户发起抢播
     */
    public function startSnatchChatBatch($aData=[])
    {
        return $this->notify_batch(static::START_SNATCH, '', $aData);
    }

    /**
     * sendChatSignal 发送飘屏聊天信号
     *
     * @param  array   $aData
     * @return bool
     */
    public function sendScrollMsg($aData=[])
    {
        return $this->notify(static::SCROLL_MSG, '', $aData);
    }


    /**
     * @param array $aData
     * @return mixed
     * 全局下线
     */
    public function sendOfflineAll($aData=[])
    {
        return $this->notify(static::OFFLINE_ALL, '', $aData);
    }

    /**
     * sendChatGameMsg 发送聊天游戏信号
     *
     * @param  array   $aData
     * @return bool
     */
    public function sendChatGameMsg($aData=[])
    {
        return $this->notify(static::CHAT_GAME, '', $aData);
    }

    /**
     * sendPostsMsg 发送动态消息信号
     *
     * @param  array   $aData
     * @return bool
     */
    public function sendPostsMsg($aData=[])
    {
        return $this->notify(static::POSTS_MESSAGE, '', $aData);
    }

    /**
     * sendInviteChatGameMsg 发送邀请聊天游戏信号
     *
     * @param  array   $aData
     * @return bool
     */
    public function sendInviteChatGameMsg($aData=[])
    {
        return $this->notify(static::CHAT_GAME, '', $aData);
    }

    /**
     * sendCloseChatStatusMsg 发送关闭聊天状态信号
     *
     * @param  array   $aData
     * @return bool
     */
    public function sendCloseChatStatusMsg($aData=[])
    {
        return $this->notify(static::CLOSE_CHAT_STATUS, '', $aData);
    }

    /**
     * sendNotificationMsg 发送后台公告信号
     *
     * @param  array   $aData
     * @return bool
     */
    public function sendNotificationMsg($aData=[])
    {
        return $this->notify(static::NOTIFICATION_MSG, '', $aData);
    }

    /**
     * sendNotificationMsgBatch 发送后台公告信号
     *
     * @param  array   $aData
     * @return bool
     */
    public function sendNotificationMsgBatch($aData=[])
    {
        return $this->notify_batch(static::NOTIFICATION_MSG, '', $aData);
    }

    /**
     * @param array $aData
     * @return mixed
     * 双方亲密等级提升
     */
    public function sendIntimateLevelUpBatch($aData=[])
    {
        return $this->notify_batch(static::INTIMATE_LEVEL_UP, '', $aData);
    }


    /**
     * sendEggSpecialGiftSignal 发送砸蛋稀有礼物信号
     *
     * @param  string $sMsg
     * @param  int    $nRoomId
     * @param  array  $aData
     * @return bool
     */
    public function sendEggSpecialGiftSignal($aData=[])
    {
        return $this->notify(static::BREAK_SPECIAL_EGG_GIFT, '', array_merge([ 'fuser' =>$this->_user],$aData));
    }


    /**
     * @param array $data
     * @return mixed
     * 修改房间信息
     */
    public function sendUpdateRoomSignal($data = [])
    {
        return $this->notify(static::UPDATE_ROOM, '', $data);
    }

    /**
     * sendCancelAdminSignal 发送取消管理员信息信号
     *
     * @param  array  $aData
     * @return bool
     */
    public function sendCancelAdminSignal($aData=[])
    {
        return $this->notify(static::CANCEL_ADMIN, '', $aData);
    }


    /**
     * sendAddAdminSignal 发送添加管理员信息信号
     *
     * @param  array  $aData
     * @return bool
     */
    public function sendAddAdminSignal($aData=[])
    {
        return $this->notify(static::ADD_ADMIN, '', $aData);
    }

    /**
     * sendProhibitTalkSignal 发送禁言信息信号
     *
     * @param  array  $aData
     * @return bool
     */
    public function sendCancelProhibitTalkSignal($aData=[])
    {
        return $this->notify(static::CANCEL_PROHIBIT_TALK, '', $aData);
    }


    /**
     * sendEnterSeatSignal 用户上麦
     *
     * @param  array  $aData
     * @return bool
     */
    public function sendEnterSeatSignal($data = [])
    {
        return $this->notify(static::ADD_ROOM_SEAT, '', array_merge(['fuser'=>$this->_user],$data));
    }

    /**
     * sendLeaveSeatSignal 用户下麦
     *
     * @param  array  $aData
     * @return bool
     */
    public function sendLeaveSeatSignal($data = [])
    {
        return $this->notify(static::LEAVE_ROOM_SEAT, '', array_merge(['fuser'=>$this->_user],$data));
    }

    /**
     * sendLeaveWaitSeatSignal 用户删除上麦排队
     *
     * @param  array  $aData
     * @return bool
     */
    public function sendRoomSeatVoiceFlgSignal($data = [])
    {
        return $this->notify(static::ROOM_SEAT_VOICE_FLG, '', $data);
    }

    /**
     * sendCountDownSignal 麦序倒计时
     *
     * @param  array  $aData
     * @return bool
     */
    public function sendResetHeartValueSignal($data = [])
    {
        return $this->notify(static::RESET_HEART_VALUE, '', $data);
    }

    public function sendRoomStartVideoChatSignal($data = [])
    {
        return $this->notify(static::ROOM_START_VIDEO_CHAT, '', $data);
    }

    /**
     * sendRoomChatSignal 发送房间聊天信号
     *
     * @param  array   $aData
     * @return bool
     */
    public function sendVoiceRoomChatSignal($aData=[])
    {
        return $this->notify(static::SEND_VOICE_ROOM_CHAT, '', array_merge(['fuser'=>$this->_user],$aData));
    }

    /**
     * @param array $data
     * @return mixed
     * 修改公告
     */
    public function sendNoticeSignal($data = [])
    {
        return $this->notify(static::UPDATE_NOTICE, '', $data);
    }

}