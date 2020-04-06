<?php

namespace app\helper;

/*
 +------------------------------------------------------------------------+
 | C Live                                                                 |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 C Live Team (https://xxxxxxxxxx.com)           |
 +------------------------------------------------------------------------+
 | This source file is subject to the ...                                 |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

abstract class IIMService
{
    const JOIN                   = 'join';
    const JOIN_ROOM              = 'join_room';
    const LEAVE                  = 'leave';
    const LEAVE_ROOM             = 'leave_room';
    const KILL_ONLINE            = 'user_kill_online';
    const ROOM_USERS             = 'room_users';
    const SEND_GIFT              = 'send_gift';
    const SEND_ROOM_CHAT         = 'send_room_chat';
    const SEND_BARRAGE           = 'send_barrage';
    const SEND_LIKE              = 'send_like';
    const SEND_CHAT              = 'send_chat';
    const SEND_ANCHOR            = 'send_anchor';
    const SEND_LIVE_END          = 'live_end';
    const PROHIBIT_TALK          = 'prohibit_talk';
    const ROOM_ADMIN             = 'room_admin';
    const KICK                   = "kick";
    const KILL_LIVE              = "kill_live";
    const SYSTEM                 = "system";
    const PRIVATE_CHAT           = "private_chat";
    const ACCEPT_PRIVATE_CHAT    = "accept_private_chat";
    const REFUSE_PRIVATE_CHAT    = "refuse_private_chat";
    const CANCEL_PRIVATE_CHAT    = "cancel_private_chat";
    const HANG_UP_PRIVATE_CHAT   = "hang_up_private_chat";
    const VAGUE_CHAT             = "vague_chat";
    const ADD_FOLLOW             = "add_follow";
    const CHAT_FORBIDDEN         = "chat_forbidden";
    const MATCH_SUCCESS          = "match_success";
    const MATCH_SUCCESS_USER     = "match_success_user";
    const USER_COIN_NOT_ENOUGH   = "user_coin_not_enough";
    const VIDEO_CHAT_PAY_SUCCESS = "video_chat_pay_success";
    const VIP_PAY_SUCCESS        = "vip_pay_success";
    const RECHARGE_SUCCESS       = "recharge_success";
    const SEND_SNATCH            = "send_snatch";
    const START_SNATCH           = "start_snatch";
    const SCROLL_MSG             = "scroll_msg";
    const OFFLINE_ALL            = "offline_all";
    const CHAT_GAME              = "chat_game";
    const POSTS_MESSAGE          = "posts_message";
    const INVITE_CHAT_GAME       = "invite_chat_game";
    const CLOSE_CHAT_STATUS      = "close_chat_status";
    const NOTIFICATION_MSG       = "notification_msg";
    const INTIMATE_LEVEL_UP      = "intimate_level_up";
    const BREAK_SPECIAL_EGG_GIFT = "break_special_egg_gift";
    const UPDATE_ROOM            = "update_room";
    const CANCEL_ADMIN           = "cancel_admin";
    const ADD_ADMIN              = "add_admin";
    const CANCEL_PROHIBIT_TALK   = "cancel_prohibit_talk";
    const ADD_ROOM_SEAT          = "add_room_seat";
    const LEAVE_ROOM_SEAT        = "leave_room_seat";
    const ROOM_SEAT_VOICE_FLG    = "room_seat_voice_flg";
    const RESET_HEART_VALUE      = 'reset_heart_value';
    const ROOM_START_VIDEO_CHAT  = 'room_start_video_chat';
    const SEND_VOICE_ROOM_CHAT   = 'send_voice_room_chat';
    const UPDATE_NOTICE          = 'update_notice';

    /** @var array websocket返回的信息 */
    protected static $aMsg
        = [
            self::JOIN                   => 'join',
            self::JOIN_ROOM              => '加入语聊房',
            self::LEAVE                  => 'leave',
            self::LEAVE_ROOM             => '离开语聊房',
            self::KILL_ONLINE            => '用户退出',
            self::SEND_GIFT              => '发送礼物',
            self::ROOM_USERS             => '房间用户',
            self::SEND_ROOM_CHAT         => '房间聊天',
            self::SEND_BARRAGE           => '弹幕',
            self::SEND_CHAT              => '私信',
            self::SEND_ANCHOR            => '主播信息',
            self::SEND_LIVE_END          => '直播结束',
            self::PROHIBIT_TALK          => '你已被禁言',
            self::ROOM_ADMIN             => '房管',
            self::KICK                   => '你已被踢出房间',
            self::KILL_LIVE              => '主播禁播(停播)',
            self::SEND_LIKE              => '点赞主播',
            self::SYSTEM                 => '系统消息',
            self::PRIVATE_CHAT           => '私聊消息',
            self::ACCEPT_PRIVATE_CHAT    => '接受私聊',
            self::REFUSE_PRIVATE_CHAT    => '拒绝私聊',
            self::CANCEL_PRIVATE_CHAT    => '取消私聊',
            self::HANG_UP_PRIVATE_CHAT   => '挂断私聊',
            self::VAGUE_CHAT             => '模糊/取消模糊',
            self::ADD_FOLLOW             => '关注主播',
            self::CHAT_FORBIDDEN         => '私聊主播违规，被禁止',
            self::MATCH_SUCCESS          => '匹配成功',
            self::MATCH_SUCCESS_USER     => '用户匹配成功',
            self::USER_COIN_NOT_ENOUGH   => '用户余额不足',
            self::VIDEO_CHAT_PAY_SUCCESS => '视频聊天付费成功',
            self::VIP_PAY_SUCCESS        => 'VIP付费成功',
            self::RECHARGE_SUCCESS       => '充值成功',
            self::SEND_SNATCH            => '用户发起抢播',
            self::START_SNATCH           => '用户开始抢播',
            self::SCROLL_MSG             => '飘屏消息',
            self::OFFLINE_ALL            => '全局下线',
            self::CHAT_GAME              => '聊天游戏',
            self::POSTS_MESSAGE          => '动态消息',
            self::INVITE_CHAT_GAME       => '邀请聊天游戏',
            self::CLOSE_CHAT_STATUS      => '关闭聊天状态',
            self::NOTIFICATION_MSG       => '系统公告',
            self::INTIMATE_LEVEL_UP      => '亲密等级提升',
            self::BREAK_SPECIAL_EGG_GIFT => '砸蛋稀有礼物',
            self::UPDATE_ROOM            => '房间更新',
            self::CANCEL_ADMIN           => '取消管理员',
            self::ADD_ADMIN              => '添加管理员',
            self::CANCEL_PROHIBIT_TALK   => '取消禁言',
            self::ADD_ROOM_SEAT          => '房间上麦',
            self::LEAVE_ROOM_SEAT        => '房间下麦',
            self::ROOM_SEAT_VOICE_FLG    => '房间麦序声音变化',
            self::RESET_HEART_VALUE      => '清空甜心值',
            self::ROOM_START_VIDEO_CHAT  => '从语聊房带去1v1视频',
            self::SEND_VOICE_ROOM_CHAT   => '语聊房房间消息',
            self::UPDATE_NOTICE          => '公告修改',
        ];
}