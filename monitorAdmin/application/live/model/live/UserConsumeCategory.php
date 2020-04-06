<?php 

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
* UserConsumeCategory 用户流水分类表
*/
class UserConsumeCategory extends Model
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
	/** @var int 补单金币 */
	const BUDAN_COIN        = 11;
	/** @var int 补单收益 */
	const BUDAN_DOT         = 12;
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
}