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
    const CHAT_MATCH_PRICE                   = 'chat_match_price';
    // 主播邀请用户首次充值比例
    const INVITE_ANCHOR_FIRST_RECHARGE_RADIO = 'invite:recharge:anchor:radio:first';
    // 注册赠送免费匹配时长
    const REGISTER_FREE_MATCH_TIMES     = 'register_free_match_times';
    //邀请注册奖励"现金"
    const INVITE_REGISTER_CASH = 'invite:register:cash';
    //邀请用户充值奖励"现金"比例
    const INVITE_RECHARGE_RADIO_CASH = 'invite:recharge:radio:cash';
    //邀请用户购买VIP奖励"现金"比例
    const INVITE_VIP_RADIO_CASH = 'invite:vip:radio:cash';
    //邀请用户提现奖励"现金"比例
    const INVITE_WITHDRAW_RADIO_CASH = 'invite:withdraw:radio:cash';
    //用户匹配单个主播间隔时间
    const USER_MATCH_SINGLE_ANCHOR_INTERVAL = 'user_match_single_anchor_interval';
    //用户匹配单个主播间隔时间
    const GUIDE_MSG_FLG = 'guide_msg_flg';
    //分享获得免费时长开关
    const FIRST_SHARE_REWARD_FLG = 'first_share_reward_flg';
    //分享获得免费时长
    const FIRST_SHARE_REWARD_MATCH_TIMES = 'first_share_reward_match_times';
    //第一次分享获得免费时长有效期（从注册时间开始算起）
    const FIRST_SHARE_REWARD_EXPIRE_HOUR = 'first_share_reward_expire_hour';
    //  用户一键打招呼 是否开启
    const USER_SAY_HI_FLG   = 'user_say_hi_flg';
    // 主播批量打招呼的间隔时间（s）
    const ANCHOR_BATCH_SAY_HI_INTERVAL = 'anchor_batch_say_hi_interval';
    // 主播批量打招呼单次用户数
    const ANCHOR_BATCH_SAY_HI_NUM = 'anchor_batch_say_hi_num';
    // 主播打招呼总间隔时间
    const ANCHOR_SAY_HI_TOTAL_INTERVAL = 'anchor_say_hi_total_interval';
    // 主播打招呼单位时间内用户数
    const ANCHOR_SAY_HI_TOTAL_NUM = 'anchor_say_hi_total_num';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'kv_create_time';
    protected $updateTime = 'kv_update_time';
    // 是否开启普通用户诱导
    const ANCHOR_REPLY_NORMAL_USER = 'anchor_reply_normal_user';
    // 守护最小支付金额
    const GUARD_MIN_COIN = 'guard_min_coin';
    // 守护每次抢 需要超出多少金币
    const GUARD_INTERVAL_COIN = 'guard_interval_coin';
    // 守护 主播可以获得分成比例
    const GUARD_DOT_RADIO = 'guard_dot_radio';
    // 1经验获取多少多少经验
    const COIN_TO_EXP = 'coin:to:exp';
    // 用户达到多少级能有上线滚屏
    const SCROLL_LEVEL_ONLINE = 'scroll_level_online';
    // 用户达到多少级能隐藏排行榜上昵称 头像
    const HIDE_RANK_LEVEL = 'hide_rank_level';
    // 用户评论等级
    const POSTS_COMMENT_LEVEL = 'posts:comment_level';
    // 发帖是否需要VIP
    const POSTS_ADD_VIP_FLG = 'posts:add_vip_flg';
    // 发帖是否需要审核
    const POSTS_NEW_CHECK = 'posts:new_check';
    // 活动提醒是否开启
    const ACTIVITY_NOTICE_FLG = 'activity:notice_flg';
    // 活动显示H5地址
    const ACTIVITY_SHOW_URL = 'activity:show_url';
    // 申请主播引导图片
    const BECOME_ANCHOR_GUIDE_IMAGE = 'become_anchor_guide_image';
    // 邀请用户引导图片
    const INVITE_USER_GUIDE_IMAGE = 'invite_user_guide_image';
    // 任务每日获得金币最大数
    const TASK_DAILY_COIN_MAX = 'task_daily_coin_max';
    // 用户昵称修改间隔时间（天）
    const USER_NICKNAME_CHANGE_INTERVAL_DAY     = 'user_nickname_change_interval_day';
    // 微信最低价格
    const WECHAT_MIN_PRICE = 'wechat_min_price';
    // 微信最高价格
    const WECHAT_MAX_PRICE = 'wechat_max_price';
    // 微信价格步长
    const WECHAT_INTERVAL_PRICE = 'wechat_interval_price';
    // 微信修改间隔时间
    const WECHAT_CHANGE_INTERVAL_DAY = 'wechat_change_interval_day';
    // 是否开启亲密度 达到显示微信
    const INTIMATE_WECHAT_SHOW = 'intimate_wechat_show';
    // 首次审核通过赠送魅力值
    const WECHAT_FIRST_CHECK_ANCHOR_EXP = 'wechat_first_check_anchor_exp';
    // 1金币可得x亲密度
    const COIN_TO_INTIMATE           = 'coin_to_intimate';
    // 1收益转换为经验
    const DOT_TO_ANCHOR_EXP    = 'dot_to_anchor_exp';
    // 动态最低价格
    const POSTS_MIN_PRICE = 'posts_min_price';
    // 动态最高价格
    const POSTS_MAX_PRICE = 'posts_max_price';
    // 动态价格步长
    const POSTS_INTERVAL_PRICE = 'posts_interval_price';
    // 是否开启免费时长弹窗
    const FREE_TIME_SHOW = 'free_time_show';
    // 离线主播状态自动变更
    const  ANCHOR_OFFLINE_MODIFY = 'anchor_offline_modify';
    // 派单超时时间
    const  DISPATCH_OVER_TIME = 'dispatch_over_time';

    // 默认砸蛋奖励类型
    const  CONSOLATION_CATEGORY = 'consolation_category';
    // 默认砸蛋奖励价值
    const  CONSOLATION_VALUE = 'consolation_value';
    // 默认砸蛋奖励名称
    const  CONSOLATION_NAME = 'consolation_name';
    // 默认砸蛋奖励图标
    const  CONSOLATION_IMAGE = 'consolation_image';
    // 砸蛋锤子价格
    const  EGGS_HAMMER_COIN = 'eggs_hammer_coin';

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