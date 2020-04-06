<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 系统配置
 */
class Kv extends Model
{
    /** @var string 充值比例 */
    const KEY_RECHARGE_RATIO = 'recharge:ratio';
    /** @var string 主播房间公告 */
    const KEY_NOTICE_ANCHOR_ROOM = 'notice:anchor:room';
    /** @var string 用户房间公告 */
    const KEY_NOTICE_USER_ROOM = 'notice:user:room';
    /** @var string 机器人是否关闭 */
    const KEY_ISROBOT_IS_CLOSE = 'isrobot:is:close';
    /** @var string 直播免费时间 */
    const KEY_LIVE_FREE_TIME = 'live:free:time';
    /** @var string 直播计时收费上限 */
    const KEY_LIVE_TIME_FEE = 'live:time:fee';
    /** @var string 直播门票收费上限 */
    const KEY_LIVE_FARE_FEE = 'live:fare:fee';
    /** @var string 直播回放生成时间 */
    const KEY_LIVE_RECORD_CREATE_TIME = 'live:record:create:time';
    /** @var string 金币兑换成收益的比例，取值(0,100) */
    const KEY_COIN_TO_DOT_RATIO = 'coin:to:dot:ratio';
    /** @var string 收益兑换钱的比例，取值(0,100) */
    const KEY_DOT_TO_MONEY_RATIO = 'dot:to:money:ratio';
    /** @var string 弹幕价格 */
    const KEY_BARRAGE_FEE = 'barrage:fee';
    /** @var string 系统公告 */
    const KEY_NOTICE_SYSTEM = 'notice:system';
    /** @var string 一级邀请比例，取值(0,100) */
    const KEY_INVITE_RATIO_1 = 'invite:ratio:1';
    /** @var string 二级邀请比例，取值(0,100) */
    const KEY_INVITE_RATIO_2 = 'invite:ratio:2';
    /** @var string 三级邀请比例，取值(0,100) */
    const KEY_INVITE_RATIO_3 = 'invite:ratio:3';
    /** @var string 禁言时长 */
    const KEY_ROOM_PROHIBIT_TALK = 'room:prohibit:talk';
    /** @var string 踢出房间时长 */
    const KEY_ROOM_KICK = 'room:kick';
    /** @var string 金币名称 */
    const KEY_COIN_NAME = 'coin:name';
    /** @var string 收益名称 */
    const KEY_DOT_NAME = 'dot:name';
    /** @var string 客服联系方式 */
    const KEY_CONTACT_US = 'contact:us';
    /** @var string 用户等级宝箱金币 */
    const KEY_LEVEL_CHEST_COIN = 'user:level:chest:coin';
    /** @var string 停播时长 */
    const KEY_LIVE_STOP_TIME = 'live:stop:time';
    /** @var string 游戏阀值 */
    const KEY_GAME_THRESHOLD = 'game:threshold';
    /*游戏对接 秘钥*/
    const KEY_API_SECRET = 'api_secret';
    //私聊价格（最大）
    const PRIVATE_PRICE_MAX = 'private_price_max';
    //私聊价格（最小）
    const PRIVATE_PRICE_MIN = 'private_price_min';
    //冻结提现
    const FREEZE_WITHDRAWALS = 'freeze_withdrawals';
    //冻结充值
    const FREEZE_RECHARGE = 'freeze_recharge';
    //虚拟币偏差阀值
    const COIN_THRESHOLD = 'coin_threshold';
    //管理员手机号
    const ADMIN_PHONE = 'admin_phone';
    //苹果上线
    const APPLE_ONLINE = 'apple_online';
    //是否开启邀请注册奖励  1为开启
    const INVITE_REGISTER_FLG = 'invite:register:flg';
    //邀请注册奖励金币数
    const INVITE_REGISTER_COIN = 'invite:register:coin';
    //是否开启邀请充值奖励  1为开启
    const INVITE_RECHARGE_FLG = 'invite:recharge:flg';
    //邀请充值奖励比例
    const INVITE_RECHARGE_RADIO = 'invite:recharge:radio';
    //是否开启主播邀请充值奖励  1为开启
    const INVITE_ANCHOR_RECHARGE_FLG = 'invite:recharge:anchor:flg';
    //主播邀请充值奖励比例
    const INVITE_ANCHOR_RECHARGE_RADIO = 'invite:recharge:anchor:radio';
    //是否开启注册奖励  1为开启
    const REGISTER_REWARD_FLG = 'register:reward:flg';
    //注册奖励金币数
    const REGISTER_REWARD_COIN = 'register:reward:coin';
    // 金币送礼获得收益比例
    const COIN_TO_DOT_RATIO_GIFT = 'coin:to:dot:ratio:gift';
    // 金币市场收费获得收益比例
    const COIN_TO_DOT_RATIO_TIME = 'coin:to:dot:ratio:time';
    // 私密视频最低价格
    const CHARGE_VIDEO_PRICE_MIN = 'charge_video:price:min';
    // 私密视频最高价格
    const CHARGE_VIDEO_PRICE_MAX = 'charge_video:price:max';
    // 聊天免费条数
    const CHAT_FREE_COUNT = 'chat:free:count';
    // 视频获得收益比例
    const COIN_TO_DOT_RATIO_VIDEO = 'coin:to:dot:ratio:video';
    // 聊天获得收益比例
    const COIN_TO_DOT_RATIO_CHAT = 'coin:to:dot:ratio:chat';
    // VIP观看视频折扣
    const  VIP_VIDEO_DISCOUNT = 'vip_video_discount';
    // 私聊价格
    const  CHAT_PRICE = 'chat:price';
    // 新用户匹配诱导视频播放长度
    const  NEW_USER_VIDEO_PLAY_TIME = 'new_user_video_play_time';
    //主播邀请主播提现奖励比例
    const INVITE_ANCHOR_WITHDRAW_RADIO = 'invite:anchor:withdraw:radio';
    // 匹配大厅 房间id
    const MATCH_CENTER_ROOM_ID = 'match_center_room_id';
    // 匹配价格
    const CHAT_MATCH_PRICE     = 'chat_match_price';
    // 注册赠送免费匹配时长
    const REGISTER_FREE_MATCH_TIMES     = 'register_free_match_times';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'kv_create_time';
    protected $updateTime = 'kv_update_time';

    /**
     * getValue 获取一个配置信息
     *
     * @param  string $sKey
     * @return string
     */
    public static function getValue($sKey, $sValue = '')
    {
        return static::where('kv_key', $sKey)->value('kv_value', $sValue);
    }

    /**
     * many 获取多个配置信息
     *
     * @param  array $aKey
     * @return array
     */
    public static function many(array $aKey)
    {
        return static::where('kv_key', 'in', $aKey)->column('kv_value', 'kv_key');
    }
}