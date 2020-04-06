<?php

namespace app\models;

use app\services\AnchorStatService;
use app\services\UserTaskService;
use Phalcon\Cli\Task;
use think\Url;

/**
 * TaskConfig 任务列表
 */
class TaskConfig extends ModelBase
{

    public $task_flg;
    public $task_finish_times;
    public $task_type;

    /** @var string 类型 一次性 */
    const TASK_TYPE_ONCE = 'once';
    /** @var string 类型 每日 */
    const TASK_TYPE_DAILY = 'daily';
    /** @var string 类型 每日 */
    const TASK_TYPE_ANCHOR_DAILY = 'anchor_daily';
    /** @var string 每日动态点赞、评论 */
    const TASK_DAILY_POSTS_LIKE_COMMENT = 'task_daily_posts_like_comment';
    /** @var string 每日动态打赏 */
    const TASK_DAILY_POSTS_SEND_GIFT = 'task_daily_posts_send_gift';
    /** @var string 每日消费金币数 */
    const TASK_DAILY_PAY_COIN = 'task_daily_pay_coin';
    /** @var string 每日任务单次通话超过10分钟 */
    const TASK_DAILY_VIDEO_CHAT_10_MIN = 'task_daily_video_chat_10_min';
    /** @var string 每日购买VIP */
    const TASK_DAILY_BUY_VIP = 'task_daily_buy_vip';
    /** @var string 视频通话单次超过5分钟 */
    const TASK_ONCE_VIDEO_CHAT_5_MIN = 'task_once_video_chat_5_min';
    /** @var string 守护主播人数 */
    const TASK_ONCE_GUARD_ANCHOR = 'task_once_guard_anchor';
    /** @var string 关注主播人数 */
    const TASK_ONCE_FOLLOW_ANCHOR = 'task_once_follow_anchor';
    /** @var string 首次充值 */
    const TASK_ONCE_RECHARGE = 'task_once_recharge';
    /** @var string 累计送礼 */
    const TASK_ONCE_SEND_GIFT_COIN = 'task_once_send_gift_coin';
    /** @var string 累计视频通话时长 */
    const TASK_ONCE_VIDEO_CHAT_MIN = 'task_once_video_chat_min';
    /** @var string 累计获得守护值 */
    const TASK_ONCE_GUARD_COIN = 'task_once_guard_coin';
    /** @var string 累计邀请用户数 */
    const TASK_ONCE_INVITE_USER = 'task_once_invite_user';
    /** @var string 分享APP */
    const TASK_ONCE_SHARE_APP = 'task_once_share_app';
    /** @var string 主播动态点赞评论 */
    const TASK_ANCHOR_DAILY_POSTS_LIKE_COMMENT = 'task_anchor_daily_posts_like_comment';
    /** @var string 主播发布动态 */
    const TASK_ANCHOR_DAILY_ADD_POSTS = 'task_anchor_daily_add_posts';
    /** @var string 主播累计在线时长 */
    const TASK_ANCHOR_DAILY_ONLINE_HOUR = 'task_anchor_daily_online_hour';
    /** @var string 累计视频通话时长 */
    const TASK_ANCHOR_DAILY_VIDEO_CHAT_MIN = 'task_anchor_daily_video_chat_min';
    /** @var string 单次通话时长超过过10分钟 */
    const TASK_ANCHOR_DAILY_VIDEO_CHAT_10_MIN = 'task_anchor_daily_video_chat_10_min';
    /** @var string 礼物佣金收益超过-1档 */
    const TASK_ANCHOR_DAILY_GIFT_DOT_OVER = 'task_anchor_daily_gift_dot_over';
    /** @var string 礼物佣金收益超过-2档 */
    const TASK_ANCHOR_DAILY_GIFT_DOT_OVER_2 = 'task_anchor_daily_gift_dot_over_2';

    public function beforeCreate()
    {
        $this->task_create_time = time();
        $this->task_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->task_update_time = time();
    }


    public function getTaskFinishDone($nUserId, $task_flg = '')
    {
        $checkFlg = $this->task_flg;
        if ( $task_flg ) {
            $checkFlg = $task_flg;
        }
        // 从缓存中判断 如果缓存中没有 则再判断
        $oUserTaskService = new UserTaskService($checkFlg, $this->task_type);

        if ( $oUserTaskService->is_exists($nUserId) === TRUE ) {
            return TRUE;
        }

        switch ( $checkFlg ) {
            case TaskConfig::TASK_DAILY_POSTS_LIKE_COMMENT:
                // 每日社区点赞、评论
                $taskFinished = $this->checkDailyPostsLikeComment($nUserId);
                break;
            case TaskConfig::TASK_DAILY_POSTS_SEND_GIFT:
                // 每日动态送礼
                $taskFinished = $this->checkDailyPostsSendGift($nUserId);
                break;
            case TaskConfig::TASK_DAILY_PAY_COIN:
                // 每日消耗金币
                $taskFinished = $this->checkDailyPayCoin($nUserId);
                break;
            case TaskConfig::TASK_DAILY_VIDEO_CHAT_10_MIN:
                // 视频通话单次10分钟
                $taskFinished = $this->checkDailyVideoChatSingle10Min($nUserId);
                break;
            case TaskConfig::TASK_DAILY_BUY_VIP:
                // 购买一次会员
                $taskFinished = $this->checkDailyBuyVip($nUserId);
                break;
            case TaskConfig::TASK_ONCE_VIDEO_CHAT_5_MIN:
                // 视频通话单次超过5分钟
                $taskFinished = $this->checkOnceVideoChat5Min($nUserId);
                break;
            case TaskConfig::TASK_ONCE_GUARD_ANCHOR:
                // 守护主播
                $taskFinished = $this->checkOnceGuardAnchor($nUserId);
                break;
            case TaskConfig::TASK_ONCE_FOLLOW_ANCHOR:
                // 关注主播
                $taskFinished = $this->checkOnceFollowAnchor($nUserId);
                break;
            case TaskConfig::TASK_ONCE_RECHARGE:
                // 首次充值
                $taskFinished = $this->checkOnceRecharge($nUserId);
                break;
            case TaskConfig::TASK_ONCE_SEND_GIFT_COIN:
                // 累计送礼金币
                $taskFinished = $this->checkOnceSendGiftCoin($nUserId);
                break;
            case TaskConfig::TASK_ONCE_VIDEO_CHAT_MIN:
                // 累计视频聊天时长
                $taskFinished = $this->checkOnceVideoChatMin($nUserId);
                break;
            case TaskConfig::TASK_ONCE_GUARD_COIN:
                // 累计购买守护值
                $taskFinished = $this->checkOnceGuardCoin($nUserId);
                break;
            case TaskConfig::TASK_ONCE_INVITE_USER:
                // 累计购买守护值
                $taskFinished = $this->checkOnceInviteUser($nUserId);
                break;
            case TaskConfig::TASK_ONCE_SHARE_APP:
                // 累计购买守护值
                $taskFinished = $this->checkOnceShareApp($nUserId);
                break;
            case TaskConfig::TASK_ANCHOR_DAILY_POSTS_LIKE_COMMENT:
                // 主播每日社区点赞、评论
                $taskFinished = $this->checkDailyPostsLikeComment($nUserId);
                break;
            case TaskConfig::TASK_ANCHOR_DAILY_ADD_POSTS:
                // 主播每日发布动态
                $taskFinished = $this->checkDailyPostsAdd($nUserId);
                break;
            case TaskConfig::TASK_ANCHOR_DAILY_ONLINE_HOUR:
                // 主播累计在线小时
                $taskFinished = $this->checkAnchorOnlineHour($nUserId);
                break;
            case TaskConfig::TASK_ANCHOR_DAILY_VIDEO_CHAT_MIN:
                // 主播累计通话时长
                $taskFinished = $this->checkAnchorDailyVideoChatMin($nUserId);
                break;
            case TaskConfig::TASK_ANCHOR_DAILY_VIDEO_CHAT_10_MIN:
                // 主播单次视频超过10分钟
                $taskFinished = $this->checkAnchorDailyVideoChatSingle10Min($nUserId);
                break;
            case TaskConfig::TASK_ANCHOR_DAILY_GIFT_DOT_OVER:
                // 主播累计礼物收益500
                $taskFinished = $this->checkAnchorDailyGiftDotOver($nUserId);
                break;
            case TaskConfig::TASK_ANCHOR_DAILY_GIFT_DOT_OVER_2:
                // 主播累计礼物收益500
                $taskFinished = $this->checkAnchorDailyGiftDotOver($nUserId);
                break;

            default:
                $taskFinished = FALSE;
        }
        if ( $taskFinished === TRUE ) {
            $oUserTaskService->save($nUserId);
        }

        return $taskFinished;
    }


    /**
     * @param $nUserId
     * 一次性分享app
     */
    public function checkOnceShareApp($nUserId)
    {
        $taskFinishTimes = $this->task_finish_times;

        $oUser = User::findFirst($nUserId);
        if ( $oUser->user_share_times >= $taskFinishTimes ) {
            return TRUE;
        }
        return $oUser->user_share_times;
    }


    /**
     * @param $nUserId
     * 判断每日动态点赞评论是否达标
     */
    public function checkDailyPostsLikeComment($nUserId)
    {
        $taskFinishTimes              = $this->task_finish_times;
        $currentDate                  = date('Y-m-d');
        $oShortPostsCommentReplyCount = ShortPostsCommentReply::count([
            'user_id = :user_id: AND create_time >= :create_date:',
            'bind' => [
                'user_id'     => $nUserId,
                'create_date' => strtotime($currentDate)
            ]
        ]);
        if ( $oShortPostsCommentReplyCount >= $taskFinishTimes ) {
            return TRUE;
        }
        $oShortPostsLikeCount = ShortPostsLike::count([
            'user_id = :user_id: AND create_time >= :create_date:',
            'bind' => [
                'user_id'     => $nUserId,
                'create_date' => strtotime($currentDate)
            ]
        ]);
        if ( $oShortPostsCommentReplyCount + $oShortPostsLikeCount >= $taskFinishTimes ) {
            return TRUE;
        }
        return $oShortPostsCommentReplyCount + $oShortPostsLikeCount;

    }

    /**
     * @param $nUserId
     * 判断每日动态送礼是否达标
     */
    public function checkDailyPostsSendGift($nUserId)
    {
        $taskFinishTimes      = $this->task_finish_times;
        $currentDate          = date('Y-m-d');
        $oShortPostsGiftCount = ShortPostsGift::count([
            'user_id = :user_id: AND  create_time >= :create_date:',
            'bind' => [
                'user_id'     => $nUserId,
                'create_date' => strtotime($currentDate)
            ]
        ]);
        if ( $oShortPostsGiftCount >= $taskFinishTimes ) {
            return TRUE;
        }
        return $oShortPostsGiftCount;
    }

    /**
     * @param $nUserId
     * 判断每日消费金币是否达标
     */
    public function checkDailyPayCoin($nUserId)
    {
        $taskFinishTimes = $this->task_finish_times;
        $currentDate     = date('Y-m-d');

        $payCoin = UserFinanceLog::sum([
            'column'     => 'consume',
            'conditions' => 'user_id = :user_id: AND create_time >= :create_date: AND user_amount_type = :user_amount_type: AND consume < 0 AND consume_category_id != :category_id_budan:',
            'bind'       => [
                'user_id'           => $nUserId,
                'create_date'       => strtotime($currentDate),
                'user_amount_type'  => UserFinanceLog::AMOUNT_COIN,
                'category_id_budan' => UserConsumeCategory::BUDAN_COIN,
            ]
        ]);
        if ( abs($payCoin) >= $taskFinishTimes ) {
            return TRUE;
        }
        return abs($payCoin);

    }

    /**
     * @param $nUserId
     * 单次通话超过10分钟
     */
    public function checkDailyVideoChatSingle10Min($nUserId)
    {
        $taskFinishTimes          = $this->task_finish_times;
        $currentDate              = date('Y-m-d');
        $oUserPrivateChatLogCount = UserPrivateChatLog::count([
            'chat_log_user_id = :chat_log_user_id: AND update_time >= :update_time: AND status = 6 AND duration >= 600',
            'bind' => [
                'chat_log_user_id' => $nUserId,
                'update_time'      => strtotime($currentDate),
            ]
        ]);
        if ( $oUserPrivateChatLogCount >= $taskFinishTimes ) {
            return TRUE;
        }
        return $oUserPrivateChatLogCount;
    }

    /**
     * @param $nUserId
     * 每日购买VIP
     */
    public function checkDailyBuyVip($nUserId)
    {
        $taskFinishTimes    = $this->task_finish_times;
        $currentDate        = date('Y-m-d');
        $oUserVipOrderCount = UserVipOrder::count([
            'user_id = :user_id: AND user_vip_order_status = "Y" AND user_vip_order_update_time >= :update_time:',
            'bind' => [
                'user_id'     => $nUserId,
                'update_time' => strtotime($currentDate),
            ]
        ]);
        if ( $oUserVipOrderCount >= $taskFinishTimes ) {
            return TRUE;
        }
        return $oUserVipOrderCount;
    }

    /**
     * @param $nUserId
     * 视频通话单次10分钟
     */
    public function checkOnceVideoChat5Min($nUserId)
    {
        $taskFinishTimes = $this->task_finish_times;

        $oUserPrivateChatLogCount = UserPrivateChatLog::count([
            'chat_log_user_id = :chat_log_user_id: AND status = 6 AND duration >= 300',
            'bind' => [
                'chat_log_user_id' => $nUserId,
            ]
        ]);
        if ( $oUserPrivateChatLogCount >= $taskFinishTimes ) {
            return TRUE;
        }
        return $oUserPrivateChatLogCount;
    }

    /**
     * @param $nUserId
     * 守护主播人数
     */
    public function checkOnceGuardAnchor($nUserId)
    {
        $taskFinishTimes = $this->task_finish_times;

        $oUserGuardCount = UserGuard::count([
            'user_id = :user_id:',
            'bind' => [
                'user_id' => $nUserId
            ]
        ]);
        if ( $oUserGuardCount >= $taskFinishTimes ) {
            return TRUE;
        }
        return $oUserGuardCount;
    }

    /**
     * @param $nUserId
     * 关注主播人数
     */
    public function checkOnceFollowAnchor($nUserId)
    {
        $taskFinishTimes = $this->task_finish_times;

        $oUserFollowCount = UserFollow::count([
            'user_id = :user_id:',
            'bind' => [
                'user_id' => $nUserId
            ]
        ]);
        if ( $oUserFollowCount >= $taskFinishTimes ) {
            return TRUE;
        }
        return $oUserFollowCount;
    }


    /**
     * @param $nUserId
     * 充值次数
     */
    public function checkOnceRecharge($nUserId)
    {
        $taskFinishTimes = $this->task_finish_times;

        $oUserRechargeOrderCount = UserRechargeOrder::count([
            'user_id = :user_id: AND user_recharge_order_status = "Y"',
            'bind' => [
                'user_id' => $nUserId
            ]
        ]);
        if ( $oUserRechargeOrderCount >= $taskFinishTimes ) {
            return TRUE;
        }
        return $oUserRechargeOrderCount;
    }


    /**
     * @param $nUserId
     * 送礼金币
     */
    public function checkOnceSendGiftCoin($nUserId)
    {
        $taskFinishTimes = $this->task_finish_times;

        $oUserGiftLogSum = UserGiftLog::sum([
            'column'     => 'consume_coin + consume_free_coin',
            'conditions' => 'user_id = :user_id:',
            'bind'       => [
                'user_id' => $nUserId
            ]
        ]);
        if ( $oUserGiftLogSum >= $taskFinishTimes ) {
            return TRUE;
        }

        $oShortPostsGiftSum = ShortPostsGift::sum([
            'column'     => 'send_coin',
            'conditions' => 'user_id = :user_id:',
            'bind'       => [
                'user_id' => $nUserId
            ]
        ]);
        if ( $oUserGiftLogSum + $oShortPostsGiftSum >= $taskFinishTimes ) {
            return TRUE;
        }
        return $oUserGiftLogSum + $oShortPostsGiftSum;

    }

    /**
     * @param $nUserId
     * 累计视频通话时长
     */
    public function checkOnceVideoChatMin($nUserId)
    {
        $taskFinishTimes          = $this->task_finish_times;
        $oUserPrivateChatLogCount = UserPrivateChatLog::sum([
            'column'     => 'duration',
            'conditions' => 'chat_log_user_id = :chat_log_user_id: AND status = 6',
            'bind'       => [
                'chat_log_user_id' => $nUserId,
            ]
        ]);
        if ( intval($oUserPrivateChatLogCount / 60) >= $taskFinishTimes ) {
            return TRUE;
        }
        return intval($oUserPrivateChatLogCount / 60);
    }


    /**
     * @param $nUserId
     * 累计花费获得的守护值
     */
    public function checkOnceGuardCoin($nUserId)
    {
        $taskFinishTimes = $this->task_finish_times;

        $oUserGuardSum = UserGuard::sum([
            'column'     => 'total_coin',
            'conditions' => 'user_id = :user_id:',
            'bind'       => [
                'user_id' => $nUserId
            ]
        ]);
        if ( $oUserGuardSum >= $taskFinishTimes ) {
            return TRUE;
        }
        return $oUserGuardSum;

    }

    /**
     * @param $nUserId
     * 累计邀请用户数
     */
    public function checkOnceInviteUser($nUserId)
    {
        $taskFinishTimes = $this->task_finish_times;
        $oUser           = User::findFirst($nUserId);
        if ( $oUser->user_invite_effective_total >= $taskFinishTimes ) {
            return TRUE;
        }
        return $oUser->user_invite_effective_total;
    }

    /**
     * @param $nUserId
     * 判断每日动态添加
     */
    public function checkDailyPostsAdd($nUserId)
    {
        $taskFinishTimes  = $this->task_finish_times;
        $currentDate      = date('Y-m-d');
        $oShortPostsCount = ShortPosts::count([
            'short_posts_user_id = :short_posts_user_id: AND short_posts_create_time >= :short_posts_create_time:',
            'bind' => [
                'short_posts_user_id'     => $nUserId,
                'short_posts_create_time' => strtotime($currentDate)
            ]
        ]);
        if ( $oShortPostsCount >= $taskFinishTimes ) {
            return TRUE;
        }

        return $oShortPostsCount;

    }

    /**
     * @param $nUserId
     * 累计在线
     */
    public function checkAnchorOnlineHour($nUserId)
    {
        $taskFinishTimes    = $this->task_finish_times;
        $oAnchorStatService = new AnchorStatService($nUserId);
        $anchorStat         = $oAnchorStatService->getData();

        // 在线秒钟
        $onlineDuration = $anchorStat[AnchorStatService::ONLINE_DURATION];

        $lastLoginTime = $anchorStat[AnchorStatService::TIME_LOGIN];

        $lastLogoutTime = $anchorStat[AnchorStatService::TIME_LOGOUT];

        if(!$lastLoginTime){
            // 上线时间不存在 则需要判断当前是否在线
            $oUser = User::findFirst($nUserId);
            if($oUser->user_online_status == User::USER_ONLINE_STATUS_ONLINE){
                // 在线的话 则上线时间为 0点
                $lastLoginTime = strtotime(date('Y-m-d'));
            }
        }
        if(!$lastLogoutTime){
            // 下线时间不在的话 则为当前时间
            $lastLogoutTime = time();
        }
        if (  $lastLoginTime > $lastLogoutTime ) {
            // 最后上线时间大于 最后下线时间  证明当前在线 则需要将在线时间加上 这段时间
            $onlineDuration += $anchorStat[AnchorStatService::TIME_LOGIN] - $anchorStat[AnchorStatService::TIME_LOGOUT];
        }

        $hours = intval($onlineDuration / 3600);

        if ( $hours >= $taskFinishTimes ) {
            return TRUE;
        }

        return $hours;

    }

    /**
     * @param $nUserId
     * 主播单次通话超过10分钟
     */
    public function checkAnchorDailyVideoChatSingle10Min($nUserId)
    {
        $taskFinishTimes          = $this->task_finish_times;
        $currentDate              = date('Y-m-d');
        $oUserPrivateChatLogCount = UserPrivateChatLog::count([
            'chat_log_anchor_user_id = :chat_log_anchor_user_id: AND update_time >= :update_time: AND status = 6 AND duration >= 600',
            'bind' => [
                'chat_log_anchor_user_id' => $nUserId,
                'update_time'             => strtotime($currentDate),
            ]
        ]);
        if ( $oUserPrivateChatLogCount >= $taskFinishTimes ) {
            return TRUE;
        }
        return $oUserPrivateChatLogCount;
    }


    /**
     * @param $nUserId
     * 累计视频通话时长
     */
    public function checkAnchorDailyVideoChatMin($nUserId)
    {
        $taskFinishTimes          = $this->task_finish_times;
        $oUserPrivateChatLogCount = UserPrivateChatLog::sum([
            'column'     => 'duration',
            'conditions' => 'chat_log_anchor_user_id = :chat_log_anchor_user_id: AND status = 6 AND create_time >= :create_time:',
            'bind'       => [
                'chat_log_anchor_user_id' => $nUserId,
                'create_time' => strtotime('today')
            ]
        ]);
        if ( intval($oUserPrivateChatLogCount / 60) >= $taskFinishTimes ) {
            return TRUE;
        }
        return intval($oUserPrivateChatLogCount / 60);
    }

    /**
     * @param $nUserId
     * 累计礼物收益超过500
     */
    public function checkAnchorDailyGiftDotOver($nUserId)
    {
        $taskFinishTimes = $this->task_finish_times;
        $currentDate     = date('Y-m-d');
        $oUserFinanceLogCount = UserFinanceLog::sum([
            'column'     => 'consume',
            'conditions' => 'user_id = :user_id: AND consume_category_id in( :consume_category_id:) AND update_time >= :update_time:',
            'bind'       => [
                'user_id'             => $nUserId,
                'consume_category_id' => sprintf('%s,%s', UserConsumeCategory::RECEIVE_GIFT_COIN, UserConsumeCategory::POSTS_GIFT_INCOME),
                'update_time'         => strtotime($currentDate),
            ]
        ]);
        if ( $oUserFinanceLogCount >= $taskFinishTimes ) {
            return TRUE;
        }
        return intval($oUserFinanceLogCount);
    }


    public function anchorDailyInfo()
    {

    }

}