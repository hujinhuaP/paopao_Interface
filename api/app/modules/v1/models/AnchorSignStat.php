<?php

namespace app\models;

/**
 * AnchorSignStat 签约主播统计
 */
class AnchorSignStat extends ModelBase
{

    /** @var string 签约时间内被呼叫次数 */
    const TYPE_CALLED = 'affect_called_times';
    /** @var string 签约时间内通话成功数 */
    const TYPE_CALL = 'affect_call_times';
    /** @var string 签约时间内收到消息用户总数 */
    const TYPE_RECEIVE_USER_MSG = 'affect_receive_user_count';
    /** @var string 签约时间内回复消息用户总数 */
    const TYPE_REPLY_USER_MSG = 'affect_reply_user_count';

    /**
     * @param \app\models\User $oUser
     * @param \app\models\Anchor $oAnchor
     * @param $type
     * 添加统计数据
     * @param int $number
     */
    public static function signAnchorStatAdd($oUser, $oAnchor, $type, $number = 1)
    {
        if ( $oAnchor->anchor_is_sign == 'N' ) {
            return FALSE;
        }
        list($oAnchorSignStat, $anchor_sign_live_start_timestamp, $anchor_sign_live_end_timestamp) = self::getSignTime($oUser, $oAnchor);

        if ( time() >= $anchor_sign_live_start_timestamp && time() <= $anchor_sign_live_end_timestamp ) {
            $oAnchorSignStat->$type += $number;
            $oAnchorSignStat->save();
        }
    }

    protected static function getSignTime($oUser, $oAnchor)
    {
        $stat_time = strtotime(date('Y-m-d 12:00:00'));
        if ( time() < $stat_time ) {
            // 当前时间小于当天12点 则统计为昨天的数据
            $stat_time = strtotime(date('Y-m-d 12:00:00')) - 3600 * 24;
        }
        $oAnchorSignStat = AnchorSignStat::findFirst([
            'user_id = :user_id: AND stat_date = :stat_date:',
            'bind' => [
                'user_id'   => $oUser->user_id,
                'stat_date' => strtotime(date('Y-m-d',$stat_time))
            ]
        ]);
        if ( !$oAnchorSignStat ) {
            //记录不存在
            $oAnchorSignStat                              = new AnchorSignStat();
            $oAnchorSignStat->user_id                     = $oUser->user_id;
            $oAnchorSignStat->stat_date                   = strtotime(date('Y-m-d', $stat_time));
            $oAnchorSignStat->group_id                    = $oUser->user_group_id;
            $oAnchorSignStat->anchor_sign_live_start_time = $oAnchor->anchor_sign_live_start_time;
            $oAnchorSignStat->anchor_sign_live_end_time   = $oAnchor->anchor_sign_live_end_time;
            $oAnchorSignStat->save();
        }
        // 签约时间的开始时间是跨天前
        $anchor_sign_live_start_timestamp = strtotime(date('Y-m-d')) + $oAnchorSignStat->anchor_sign_live_start_time * 3600;
        if ( $oAnchorSignStat->anchor_sign_live_start_time < 12 ) {
            // 签约时间的开始时间是跨天后  即明天0点 加上小时数
            $anchor_sign_live_start_timestamp = strtotime(date('Y-m-d')) + 3600 * 24 + $oAnchorSignStat->anchor_sign_live_start_time * 3600;
        }

        // 签约时间的结束时间是跨天前
        $anchor_sign_live_end_timestamp = strtotime(date('Y-m-d')) + $oAnchorSignStat->anchor_sign_live_end_time * 3600;
        if ( $oAnchorSignStat->anchor_sign_live_end_time < 12 ) {
            // 签约时间的结束时间是跨天后  即明天0点 加上小时数
            $anchor_sign_live_end_timestamp = strtotime(date('Y-m-d')) + 3600 * 24 + $oAnchorSignStat->anchor_sign_live_end_time * 3600;
        }
        return [
            $oAnchorSignStat,
            $anchor_sign_live_start_timestamp,
            $anchor_sign_live_end_timestamp
        ];
    }


    /**
     * @param $oUser
     * @param $nUserId
     * 判断接受消息的主播 是否是签约主播以及是否在签约时间内发送人第一次发送给主播
     */
    public static function signAnchorStatAddReceiveUserCount($oUser, $nUserId)
    {
        $oAnchor = Anchor::findFirst([
            'user_id = :user_id:',
            'bind' => [ 'user_id' => $oUser->user_id ]
        ]);
        if ( $oAnchor->anchor_is_sign == 'N' ) {
            return FALSE;
        }
        list($oAnchorSignStat, $anchor_sign_live_start_timestamp, $anchor_sign_live_end_timestamp) = self::getSignTime($oUser, $oAnchor);
        if ( time() < $anchor_sign_live_start_timestamp || time() > $anchor_sign_live_end_timestamp ) {
            return FALSE;
        }
        // 在区间内
        $intervalChatCount = UserChat::count([
                'user_chat_receiv_user_id = :user_chat_receiv_user_id: AND user_chat_send_user_id = :user_chat_send_user_id: AND user_chat_create_time >= :start_time: AND user_chat_create_time <= :end_time: ',
                'bind' => [
                    'user_chat_receiv_user_id' => $oUser->user_id,
                    'user_chat_send_user_id'   => $nUserId,
                    'start_time'               => $anchor_sign_live_start_timestamp,
                    'end_time'                 => $anchor_sign_live_end_timestamp,
                ]
            ]
        );
        if($intervalChatCount == 1){
            //只有一条 表示就是当前添加的这个条是第一个条 则需要加1
            $oAnchorSignStat->affect_receive_user_count += 1;
            $oAnchorSignStat->save();
            return true;
        }
        return false;
    }


    /**
     * @param $oUser
     * @param $nUserId
     * 判断接受消息的主播 是否是签约主播以及是否在签约时间内发送人第一次发送给主播
     */
    public static function signAnchorStatAddReplyUserCount($oUser, $nUserId)
    {
        $oAnchor = Anchor::findFirst([
            'user_id = :user_id:',
            'bind' => [ 'user_id' => $oUser->user_id ]
        ]);
        if ( $oAnchor->anchor_is_sign == 'N' ) {
            return FALSE;
        }
        list($oAnchorSignStat, $anchor_sign_live_start_timestamp, $anchor_sign_live_end_timestamp) = self::getSignTime($oUser, $oAnchor);
        if ( time() < $anchor_sign_live_start_timestamp || time() > $anchor_sign_live_end_timestamp ) {
            return FALSE;
        }
        // 在区间内 用户发的第一条的时间
        $intervalUserSendFirstMsg = UserChat::findFirst([
                'user_chat_receiv_user_id = :user_chat_receiv_user_id: AND user_chat_send_user_id = :user_chat_send_user_id: AND user_chat_create_time >= :start_time: AND user_chat_create_time <= :end_time: ',
                'bind' => [
                    'user_chat_receiv_user_id' => $oUser->user_id,
                    'user_chat_send_user_id'   => $nUserId,
                    'start_time'               => $anchor_sign_live_start_timestamp,
                    'end_time'                 => $anchor_sign_live_end_timestamp,
                ],
                'order' => 'user_chat_create_time ASC'
            ]
        );
        if(!$intervalUserSendFirstMsg){
            // 接收消息的此用户 在签约时间内 还没有发消息给主播
            return false;
        }
        $intervalUserSendFirstMsgTime = $intervalUserSendFirstMsg->user_chat_create_time;
        if(time() >= $intervalUserSendFirstMsgTime){
            //当前时间 大于用户在签约时间区间内发给主播的第一条消息 证明是主播的有效回复
            $intervalChatCount = UserChat::count([
                    'user_chat_receiv_user_id = :user_chat_receiv_user_id: AND user_chat_send_user_id = :user_chat_send_user_id: AND user_chat_create_time >= :start_time: AND user_chat_create_time <= :end_time: ',
                    'bind' => [
                        'user_chat_receiv_user_id' => $nUserId,
                        'user_chat_send_user_id'   => $oUser->user_id,
                        'start_time'               => $intervalUserSendFirstMsgTime,
                        'end_time'                 => $anchor_sign_live_end_timestamp,
                    ]
                ]
            );
            if($intervalChatCount == 1){
                //只有一条 表示就是当前添加的这个条是主播在签约时间内并且在用户回复主播后的第一个条 则需要加1
                $oAnchorSignStat->affect_reply_user_count += 1;
                $oAnchorSignStat->save();
                return true;
            }
        }
        return false;
    }

    public function beforeCreate()
    {
        $this->update_time = time();
        $this->create_time = time();
    }

    public function beforeUpdate()
    {
        $this->update_time = time();
    }
}