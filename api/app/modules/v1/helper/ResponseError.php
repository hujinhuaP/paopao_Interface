<?php

namespace app\helper;

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 错误码工具类                                                           |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

/**
 * ResponseError 错误码
 */
class ResponseError
{
    /** @var int 成功错误码 */
    const SUCCESS = 0;

    /** @var int 错误错误码 */
    const FAIL                            = 10000;
    const ACCESS_TOKEN_INVALID            = 10001;
    const PARAM_ERROR                     = 10002;
    const NETWORK_ERROR                   = 10003;
    const OPERATE_FAILED                  = 10004;
    const LIVE_START_ERROR                = 10005;
    const LIVE_END_ERROR                  = 10006;
    const USER_FORBID                     = 10007;
    const ANCHOR_FORBID                   = 10008;
    const ANCHOR_NOT_EXISTS               = 10009;
    const USER_NOT_EXISTS                 = 10010;
    const USER_CERTIFICATION_FAIL         = 10011;
    const USER_NOT_CERTIFICATION          = 10012;
    const USER_CERTIFICATION_CHECK        = 10013;
    const USER_IS_CERTIFICATION           = 10014;
    const VERIFY_CODE_ERROR               = 10015;
    const VERIFY_CODE_NOT_EXPIRE          = 10016;
    const ACCOUNT_EXISTS                  = 10017;
    const ACCOUNT_NOT_EXISTS              = 10018;
    const ACCOUNT_ERROR                   = 10019;
    const PASSWORD_ERROR                  = 10020;
    const ACCOUNT_OR_PASSWORD_ERROR       = 10021;
    const LOGIN_ERROR                     = 10022;
    const VERIFY_CODE_EXPIRE              = 10023;
    const USER_COIN_NOT_ENOUGH            = 10024;
    const COMBO_NOT_EXISTS                = 10025;
    const USER_VIP_EXPIRE                 = 10026;
    const ANCHOR_FORBID_EXPIRE            = 10027;
    const LIVE_TRAILER_END                = 10028;
    const GIFT_NOT_EXISTS                 = 10029;
    const IS_FOLLOW                       = 10030;
    const CANCEL_FOLLOW                   = 10031;
    const INVITE_CODE_ERROR               = 10032;
    const INVITE_USER_EXISTS              = 10033;
    const NOT_IS_ROOM_ADMIN               = 10034;
    const SIGNED                          = 10035;
    const USER_NOT_BIND_PHONE             = 10036;
    const PASSWORD_FORMAT_ERROR           = 10037;
    const RECEIVED                        = 10038;
    const USER_HAS_BIND_PHONE             = 10039;
    const USER_PROHIBIT_TALK              = 10040;
    const USER_KICK                       = 10041;
    const USERNAME_EXISTS                 = 10042;
    const LIVE_FEE_ERROR                  = 10043;
    const BANWORD                         = 10044;
    const TYPE_ERROR                      = 10045;
    const NOT_ONLINE                      = 10046;
    const DO_NOT_DISTURB                  = 10047;
    const IS_BUSYING                      = 10048;
    const IS_NOT_ANCHOR                   = 10049;
    const VIDEO_NOT_EXIST                 = 10050;
    const IN_BLACK                        = 10051;
    const HAS_BEEN_BLACKED                = 10052;
    const FREEZE_WITHDRAWALS              = 10053;
    const FREEZE_RECHARGE                 = 10054;
    const APPLE_BUY_ERROR                 = 10055;
    const STATUS_ERROR                    = 10056;
    const CHAT_FORBIDDEN                  = 10057;
    const PRICE_ERROR                     = 10058;
    const FORBIDDEN_SAY_HI                = 10059;
    const FORBIDDEN_SAY_HI_USER           = 10060;
    const FORBIDDEN_ANCHOR_CALL_ANCHOR    = 10061;
    const FORBIDDEN_TOTAL_SAY_HI          = 10062;
    const ANCHOR_CAN_NOT_CHAT_TO_USER     = 10063;
    const USER_CERTIFICATION_FORBID       = 10064;
    const WITHDRAW_ADD_DAY_ERROR          = 10065;
    const MATCH_USER_NOT_EXISTS           = 10066;
    const LOGIN_INFO_ERROR                = 10067;
    const SHOULD_PAY_VIDEO                = 10068;
    const SHOULD_VIP_PAY_VIDEO            = 10069;
    const USER_CHAT_SHOULD_PAY            = 10070;
    const USER_CERTIFICATION_IMAGES_COUNT = 10071;
    const ANCHOR_VIDEO_CHECK              = 10072;
    const ANCHOR_IMAGE_CHECK              = 10073;
    const ANCHOR_EXISTS_CALLED            = 10074;
    const ANCHOR_FORBID_LOGIN             = 10075;
    const NOT_VIP                         = 10076;
    const NOT_IN_CHAT                     = 10077;
    const NOT_SNATCH_CHAT                 = 10078;
    const CHECKED_CANNOT_UPDATE           = 10079;
    const IS_IN_GUARD                     = 10080;
    const PAY_LESS_THAN                   = 10081;
    const SNATCH_OVERTIME                 = 10082;
    const FORBIDDEN_USER_CALL_USER        = 10083;
    const FORBIDDEN_MESSAGE               = 10084;
    const LEVEL_FORBIDDEN                 = 10085;
    const POSTS_NOT_ANCHOR                = 10086;
    const POSTS_DELETE                    = 10087;
    const ANCHOR_CAN_NOT_CHAT_TO_ANCHOR   = 10088;
    const USER_CAN_NOT_CHAT_TO_USER       = 10089;
    const USER_SAY_HI_CLOSE               = 10090;
    const FORBIDDEN_ACTION_INTERVAL       = 10091;
    const LEVEL_REWARD_FORBIDDEN          = 10092;
    const LEVEL_REWARD_HAS_GET            = 10093;
    const TASK_IS_CLOSE                   = 10094;
    const TASK_HAS_GET                    = 10095;
    const TASK_ERROR                      = 10096;
    const TASK_NOT_FINISH                 = 10097;
    const NICKNAME_LENGTH                 = 10099;
    const CHANGE_NICKNAME_FORBIDDEN       = 10100;
    const WECHAT_BEING_CHECK              = 10101;
    const WECHAT_CHANGE_FORBIDDEN         = 10102;
    const NOT_GET_STRANGER_MSG            = 10103;
    const POSTS_ADD_RULE                  = 10104;
    const NOT_CHAT_BETWEEN_EACH_OTHER     = 10105;
    const EGG_HAMMER_NOT_ENOUGH           = 10106;
    const ROOM_NO_ADMIN                   = 10107;
    const USER_NOT_IN_ROOM                = 10108;
    const USER_IN_ROOM_BLACK              = 10109;
    const ROOM_NOT_OWNER                  = 10110;
    const USER_IS_SUPER_ADMIN             = 10111;
    const CAN_NOT_REMOVE_ADMIN_ON_HOST    = 10112;
    const USER_IS_ROOM_OWNER              = 10113;
    const ROOM_ADMIN_CAN_NOT_REMOVE       = 10114;
    const ROOM_HOST_EXISTS                = 10115;
    const NOT_SEAT                        = 10116;
    const ROOM_NOT_HOST                   = 10117;
    const USER_ROOM_CHAT_FORBIDDEN                   = 10118;

    /** @var array 错误码提示信息 */
    protected static $aError
        = [
            self::SUCCESS              => '请求成功',
            self::FAIL                 => '请求失败',
            self::PARAM_ERROR          => '参数错误',
            self::NETWORK_ERROR        => '网络错误',
            self::ACCESS_TOKEN_INVALID => '登录状态失效',
            self::OPERATE_FAILED       => '操作失败',
            self::BANWORD              => '包含被禁用的关键字',

            self::LIVE_START_ERROR => '直播已开始',
            self::LIVE_END_ERROR   => '直播已结束',
            self::LIVE_TRAILER_END => '直播试看已结束',

            self::GIFT_NOT_EXISTS => '礼物不存在',

            self::ANCHOR_FORBID        => '主播已被禁播',
            self::ANCHOR_FORBID_EXPIRE => '停播期时间',
            self::ANCHOR_NOT_EXISTS    => '主播不存在',

            self::USER_FORBID                     => '您已被禁止登录，如有疑问请联系客服',
            self::USER_NOT_EXISTS                 => '用户不存在',
            self::USER_CERTIFICATION_FAIL         => '实名认证失败',
            self::USER_COIN_NOT_ENOUGH            => '用户余额不足',
            self::USER_VIP_EXPIRE                 => '用户会员已过期',
            self::USER_CERTIFICATION_CHECK        => '实名认证正在审核',
            self::USER_NOT_CERTIFICATION          => '没有实名认证',
            self::USER_IS_CERTIFICATION           => '已实名认证',
            self::VERIFY_CODE_ERROR               => '验证码错误',
            self::VERIFY_CODE_EXPIRE              => '验证码已过期',
            self::VERIFY_CODE_NOT_EXPIRE          => '验证码还没有过期',
            self::ACCOUNT_EXISTS                  => '账号已存在',
            self::ACCOUNT_NOT_EXISTS              => '账号不存在',
            self::ACCOUNT_ERROR                   => '账号错误',
            self::PASSWORD_ERROR                  => '密码错误',
            self::PASSWORD_FORMAT_ERROR           => '密码长度为6-16位',
            self::ACCOUNT_OR_PASSWORD_ERROR       => '账号或密码错误',
            self::LOGIN_ERROR                     => '登录失败',
            self::COMBO_NOT_EXISTS                => '套餐不存在',
            self::IS_FOLLOW                       => '已关注',
            self::CANCEL_FOLLOW                   => '已取消关注',
            self::INVITE_CODE_ERROR               => '邀请码不存在',
            self::INVITE_USER_EXISTS              => '你的上级邀请用户已存在',
            self::NOT_IS_ROOM_ADMIN               => '你不是该房间的管理员',
            self::SIGNED                          => '您已签到，不用重复签到',
            self::USER_NOT_BIND_PHONE             => '未绑定手机号码',
            self::USER_HAS_BIND_PHONE             => '手机号已被绑定',
            self::RECEIVED                        => '已领取',
            self::USER_PROHIBIT_TALK              => '你已被禁言',
            self::USER_KICK                       => '你已被踢出直播间',
            self::USERNAME_EXISTS                 => '用户名已存在',
            self::LIVE_FEE_ERROR                  => '直播金币不能超过%d',
            self::TYPE_ERROR                      => '轮播图类型错误',
            self::NOT_ONLINE                      => '对方不在线',
            self::DO_NOT_DISTURB                  => '主播正在直播',
            self::IS_BUSYING                      => '对方忙碌中',
            self::IS_NOT_ANCHOR                   => '还不是主播，不能开启',
            self::VIDEO_NOT_EXIST                 => '小视频不存在',
            self::IN_BLACK                        => '你把对方拉黑',
            self::HAS_BEEN_BLACKED                => '对方把你拉黑',
            self::FREEZE_WITHDRAWALS              => '提现被禁止',
            self::FREEZE_RECHARGE                 => '充值被禁止',
            self::APPLE_BUY_ERROR                 => '內购购买失败',
            self::STATUS_ERROR                    => '已取消',
            self::CHAT_FORBIDDEN                  => '主播已被禁止私聊',
            self::PRICE_ERROR                     => '私聊价格错误',
            self::FORBIDDEN_SAY_HI                => '您今天已经给TA打过招呼了，请等待用户回复',
            self::FORBIDDEN_SAY_HI_USER           => '您今天已经打过招呼了，请等待主播回复',
            self::FORBIDDEN_ANCHOR_CALL_ANCHOR    => '小仙女，暂不支持主播接通主播哦',
            self::FORBIDDEN_TOTAL_SAY_HI          => '每打%d次打招呼,需要休息%d分钟才能继续操作哦。您还需要等待%d分钟',
            self::ANCHOR_CAN_NOT_CHAT_TO_USER     => '不能主动和用户聊天哦',
            self::USER_CERTIFICATION_FORBID       => '您已被禁止认证',
            self::WITHDRAW_ADD_DAY_ERROR          => '请在周日0点到24点发起提现操作',
            self::MATCH_USER_NOT_EXISTS           => '抢单失败，该用户已取消或被其他主播抢走',
            self::LOGIN_INFO_ERROR                => '登录失败，请重试，如有疑问请联系管理人员 18926074941',
            self::SHOULD_PAY_VIDEO                => '此视频需要打赏给主播%d金币，VIP用户仅需%d金币。打赏后将永久观看，是否确认打赏？',
            self::SHOULD_VIP_PAY_VIDEO            => '此视频需要打赏给主播%d金币，打赏后将永久观看。是否确认打赏？',
            self::USER_CHAT_SHOULD_PAY            => '如继续聊天将以（%d金币/条）扣除或购买泡泡VIP可以和主播无限文字畅聊哦',
            self::USER_CERTIFICATION_IMAGES_COUNT => '用户图片审核需要为%d张图片',
            self::ANCHOR_VIDEO_CHECK              => '视频正在审核中',
            self::ANCHOR_IMAGE_CHECK              => '图片正在审核中',
            self::ANCHOR_EXISTS_CALLED            => '主播已接通，请稍等',
            self::ANCHOR_FORBID_LOGIN             => '您已被取消主播账号权限，如需申诉或有疑问请联系客服 TTbaby02',
            self::NOT_VIP                         => '您不是VIP请购买VIP',
            self::NOT_IN_CHAT                     => '主播不在聊天，请直接邀请',
            self::NOT_SNATCH_CHAT                 => '您的守护对象与用户通话时长还不足一分钟，请您一分钟后重新尝试。',
            self::CHECKED_CANNOT_UPDATE           => '审核中，暂时无法修改',
            self::IS_IN_GUARD                     => '您已经是守护了',
            self::PAY_LESS_THAN                   => '支付金额不能小于%d',
            self::SNATCH_OVERTIME                 => '本次抢聊请求已超时，请保证网络通畅，重新发起抢聊',
            self::FORBIDDEN_USER_CALL_USER        => '您不能呼叫用户哦',
            self::FORBIDDEN_MESSAGE               => '短信请求过于频繁，请稍后重试',
            self::LEVEL_FORBIDDEN                 => '等级暂未达到开放此功能',
            self::POSTS_NOT_ANCHOR                => '对方不是主播，无法打赏用户',
            self::POSTS_DELETE                    => '原内容已删除',
            self::ANCHOR_CAN_NOT_CHAT_TO_ANCHOR   => '主播无法和主播聊天哦',
            self::USER_CAN_NOT_CHAT_TO_USER       => '用户无法和用户聊天哦',
            self::USER_SAY_HI_CLOSE               => '直接去找心仪的Ta聊天吧',
            self::FORBIDDEN_ACTION_INTERVAL       => '需要休息%s才能继续操作哦',
            self::LEVEL_REWARD_FORBIDDEN          => '该等级没有奖励，请快块升级吧',
            self::LEVEL_REWARD_HAS_GET            => '该奖励已领取过',
            self::TASK_IS_CLOSE                   => '该任务不存在或者已下线',
            self::TASK_HAS_GET                    => '该奖励已经领取',
            self::TASK_ERROR                      => '任务配置错误，请联系客服',
            self::TASK_NOT_FINISH                 => '任务没有达成，请继续加油',
            self::NICKNAME_LENGTH                 => '昵称长度不能超过10个字符哦',
            self::CHANGE_NICKNAME_FORBIDDEN       => '距离上次修改不足%d天，暂时无法修改昵称',
            self::WECHAT_BEING_CHECK              => '微信正在审核中',
            self::WECHAT_CHANGE_FORBIDDEN         => '距离上次修改不足%d天，暂时无法修改',
            self::NOT_GET_STRANGER_MSG            => '对方已开启陌生人免打扰',
            self::POSTS_ADD_RULE                  => '发布动态，请先成为认证用户',
            self::NOT_CHAT_BETWEEN_EACH_OTHER     => '暂不支持与对方聊天哦',
            self::EGG_HAMMER_NOT_ENOUGH           => '锤子数量不够',
            self::ROOM_NO_ADMIN                   => '您不是房间管理员，无权操作',
            self::USER_NOT_IN_ROOM                => '用户不在当前房间',
            self::USER_IN_ROOM_BLACK              => '您已被房间拉黑',
            self::ROOM_NOT_OWNER                  => '您不是房主，无权操作',
            self::USER_IS_SUPER_ADMIN             => '对方是超级管理员，您无法对其进行操作',
            self::CAN_NOT_REMOVE_ADMIN_ON_HOST    => '对方正在主持位，请下替换主持',
            self::USER_IS_ROOM_OWNER              => '对方是房主，您无法对其进行操作',
            self::ROOM_ADMIN_CAN_NOT_REMOVE       => '对方是管理员，请先取消管理员',
            self::ROOM_HOST_EXISTS                => '主持位有人',
            self::NOT_SEAT                        => '当前没有麦序了',
            self::ROOM_NOT_HOST                   => '您不是主持，无权操作',
            self::USER_ROOM_CHAT_FORBIDDEN        => '您已被禁言',
        ];

    /**
     * getError 获取错误信息
     *
     * @param int $nCode
     * @return string
     */
    public static function getError( ... $aParam )
    {
        if ( !isset(static::$aError[ $aParam[0] ]) ) {
            throw new \Phalcon\Exception("Unknown error code");
        }
        if ( count($aParam) == 1 ) {
            return static::$aError[ $aParam[0] ];
        }
        $aParam[0] = static::$aError[ $aParam[0] ];

        return call_user_func_array('sprintf', $aParam);
    }
}