<?php

namespace app\admin\model\api;

use think\Model;
use think\Session;

class UserConsumeCategory extends ApiModel
{
    /** @var int 会员充值 */
    const RECHARGE_VIP      = 1;
    /** @var int 金币充值 */
    const RECHARGE_COIN     = 2;
    /** @var int 计时收费 */
    const TIME_COIN         = 3;
    /** @var int 门票收费 */
    const FARE_COIN         = 4;
    /** @var int 赠送礼物 */
    const SEND_GIFT_COIN    = 5;
    /** @var int 收取礼物 */
    const RECEIVE_GIFT_COIN = 6;
    /** @var int 签到金币 */
    const SIGNIN_COIN       = 7;
    /** @var int 邀请用户充值获取金币 */
    const INVITE_USER_COIN  = 8;
    /**@type int 提现 */
    const CATEGORY_WITHDRAW = 9;
    /** @var int 等级宝箱金币 */
    const LEVEL_CHEST_COIN  = 10;
    /** @var int 补单金币 */
    const BUDAN_COIN        = 11;
    /** @var int 补单收益 */
    const BUDAN_DOT         = 12;
    /** @var int 游戏下注 */
    const GAME_BET          = 13;
    /** @var int 下注成功赢钱 */
    const GAME_WIN          = 14;
    /** @var int 游戏提前结束退还下注 */
    const GAME_OVER         = 15;
    /** @var int 兑换游戏币 */
    const GAME_EXCHANGE = 16;
    /** @var int 一对一私聊 */
    const PRIVATE_CHAT = 17;
    /** @var int 回放收费 */
    const PLAYBACK_FEE = 18;
    /** @var int 每日登陆奖励 */
    const LOGIN_REWARD = 19;
    /** @var int 代理商操作提现 */
    const AGENT_WITHDRAW = 20;
    /** @var int 任务奖励-送礼 */
    const REWARD_COIN_SEND_GIFT = 21;
    /** @var int 任务奖励-看播 */
    const REWARD_COIN_WATCH_LIVE = 22;
    /** @var int 任务奖励-分享 */
    const REWARD_COIN_SHARE = 23;


}
