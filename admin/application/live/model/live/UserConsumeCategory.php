<?php 

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
* UserConsumeCategory 用户流水分类表
*/
class UserConsumeCategory extends Model
{
    /** @var int 会员充值 */
    const RECHARGE_VIP = 1;
    /** @var int 金币充值 */
    const RECHARGE_COIN = 2;
    /** @var int 计时收费 */
    const TIME_COIN = 3;
    /** @var int 门票收费 */
    const FARE_COIN = 4;
    /** @var int 赠送礼物 */
    const SEND_GIFT_COIN = 5;
    /** @var int 收取礼物 */
    const RECEIVE_GIFT_COIN = 6;
    /** @var int 签到金币 */
    const SIGNIN_COIN = 7;
    /** @var int 邀请用户充值获取金币 */
    const INVITE_USER_COIN = 8;
    /**@type int 提现 */
    const CATEGORY_WITHDRAW = 9;
    /** @var int 等级宝箱金币 */
    const LEVEL_CHEST_COIN = 10;
    /** @var int 补单金币 */
    const BUDAN_COIN = 11;
    /** @var int 补单收益 */
    const BUDAN_DOT = 12;
    /** @var int 游戏下注 */
    const GAME_BET = 13;
    /** @var int 下注成功赢钱 */
    const GAME_WIN = 14;
    /** @var int 游戏提前结束退还下注 */
    const GAME_OVER = 15;
    /** @var int 兑换游戏币 */
    const GAME_EXCHANGE = 16;
    /** @var int 一对一私聊 */
    const PRIVATE_CHAT = 17;
    /** @var int 回放收费 */
    const PLAYBACK_FEE = 18;
    /** @var int 邀请注册奖励 */
    const INVITE_REGISTER_REWARD = 19;
    /** @var int 注册奖励 */
    const REGISTER_REWARD = 20;
    /** @var int 主播邀请用户充值获取佣金 */
    const INVITE_USER_DOT = 21;
    /** @var int 私密视频收费 */
    const VIDEO_PAY = 22;
    /** @var int 私聊收费 */
    const SEND_CHAT_PAY = 23;
    /** @var int 诱导视频支付 */
    const GUIDE_VIDEO_PAY = 24;
    /** @var int 邀请主播提现奖励 */
    const INVITE_WITHDRAW_REWARD = 25;
    /** @var int 聊天游戏付费 */
    const CHAT_GAME_PAY = 26;
    /** @var int 聊天游戏收益 */
    const CHAT_GAME_INCOME = 27;
    /** @var int 主播动态收益 */
    const ANCHOR_POSTS_INCOME = 28;
    /** @var int 动态打赏 */
    const POSTS_GIFT_PAY = 29;
    /** @var int 摄影师动态收益 */
    const PHOTOGRAPHER_POSTS_INCOME = 30;
    /** @var int 微信支付 */
    const WECHAT_PAY = 31;
    /** @var int 微信主播收益 */
    const WECHAT_INCOME = 32;
    /** @var int 微信摄影师收益 */
    const PHOTOGRAPHER_WECHAT_INCOME = 33;
    /** @var int "现金"兑换金币 */
    const CASH_EXCHANGE = 34;
    /** @var int 守护贡献消耗 */
    const GUARD_PAY = 35;
    /** @var int 守护分成 */
    const GUARD_GET = 36;
    /** @var int VIP用户充值赠送 */
    const VIP_RECHARGE_REWARD = 37;
    /** @var int 动态礼物收益 */
    const POSTS_GIFT_INCOME = 38;
    /** @var int 用户等级奖励 */
    const USER_LEVEL_REWARD = 39;
    /** @var int 每日任务收益 */
    const DAILY_TASK = 40;
    /** @var int 一次性任务收益 */
    const ONCE_TASK = 41;
    /** @var int 主播每日收益 */
    const ANCHOR_DAILY_TASK_TASK = 42;
    /** 动态付费 */
    const POSTS_PAY = 43;
    /** 每日视频时长额外奖励 */
    const DAILY_VIDEO_CHAT_REWARD = 44;

}