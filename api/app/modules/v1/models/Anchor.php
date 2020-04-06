<?php

namespace app\models;

/**
 * Anchor 主播
 */
class Anchor extends ModelBase
{
    /** @var int 免费直播 */
    const LIVE_PAY_FREE = 0;
    /** @var int VIP直播 */
    const LIVE_PAY_VIP = 1;
    /** @var int 直播门票付费 */
    const LIVE_PAY_FARE = 2;
    /** @var int 直播计时付费 */
    const LIVE_PAY_TIME = 3;
    /** @var int 聊天在线类型  没开启 */
    const CHAT_STATUS_OFF = 0;
    /** @var int 聊天在线类型  离线 */
    const CHAT_STATUS_OFFLINE = 1;
    /** @var int 聊天在线类型  在线-空闲 */
    const CHAT_STATUS_FREE = 3;
    /** @var string 分成比例类型  礼物 */
    const RATIO_GIFT = 'gift';
    /** @var string 分成比例类型  时长 */
    const RATIO_TIME = 'time';
    /** @var string 分成比例类型  视频 */
    const RATIO_VIDEO = 'video';
    /** @var string 分成比例类型  文字聊天 */
    const RATIO_CHAT = 'chat';
    /** @var string 分成比例类型  视频聊天游戏 */
    const RATIO_CHATGAME = 'chatgame';
    /** @var string 分成比例类型  守护购买 */
    const RATIO_GUARD = 'guard';
    /** @var string 分成比例类型  动态 */
    const RATIO_POSTS = 'posts';
    /** @var string 分成比例类型  微信 */
    const RATIO_WECHAT = 'wechat';

    /** @var string 第100个新主播的缓存key */
    const NEW_100_ANCHOR_ID_KEY = 'new_100_anchor_id';

    /**
     * 获取主播的分成比例
     * 有公会 按照公会配置的比例获取奖励
     * 没有公会 按照系统配置低的比例获取奖励
     */
    public function getCoinToDotRatio( $oAnchorUser, $type = self::RATIO_GIFT )
    {
        $giftRatio     = Kv::get(Kv::COIN_TO_DOT_RATIO_GIFT);
        $timeRatio     = Kv::get(Kv::COIN_TO_DOT_RATIO_TIME);
        $videoRatio    = Kv::get(Kv::COIN_TO_DOT_RATIO_VIDEO);
        $chatRatio     = Kv::get(Kv::COIN_TO_DOT_RATIO_CHAT);
        $chatgameRatio = Kv::get(Kv::COIN_TO_DOT_RATIO_CHATGAME);
        $guardRatio    = Kv::get(Kv::GUARD_DOT_RADIO);
        $postsRatio    = Kv::get(Kv::COIN_TO_DOT_RATIO_ANCHOR_POSTS);
        $wechatRatio   = Kv::get(Kv::COIN_TO_DOT_RATIO_ANCHOR_WECHAT);
        if ( $oAnchorUser->user_group_id ) {
            $oGroup = Group::findFirst($oAnchorUser->user_group_id);
            if ( $oGroup ) {
                $giftRatio   = $oGroup->divid_gift_precent;
                $timeRatio   = $oGroup->divid_time_precent;
                $videoRatio  = $oGroup->divid_video_precent;
                $chatRatio   = $oGroup->divid_chat_precent;
                $postsRatio  = $oGroup->divid_posts_precent;
                $wechatRatio = $oGroup->divid_wechat_precent;
            }
        }
        switch ( $type ) {
            case self::RATIO_GIFT:
                $result = $giftRatio;
                break;
            case self::RATIO_TIME:
                $result = $timeRatio;
                break;
            case self::RATIO_VIDEO:
                $result = $videoRatio;
                break;
            case self::RATIO_CHAT:
                $result = $chatRatio;
                break;
            case self::RATIO_CHATGAME:
                $result = $chatgameRatio;
                break;
            case self::RATIO_GUARD:
                $result = $guardRatio;
                break;
            case self::RATIO_POSTS:
                $result = $postsRatio;
                break;
            case self::RATIO_WECHAT:
                $result = $wechatRatio;
                break;
            default:
                $result = 0;
        }
        if ( $result > 100 || $result < 0 ) {
//            配置错误 设置为不给分成
            $result = 0;
        }

        // 金币兑佣金 需要和 RMB兑金币的比例对等
        $result = $result / 10;

        return $result;
    }

    public function beforeCreate()
    {
        $this->anchor_create_time = time();
        $this->anchor_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->anchor_update_time = time();
    }

    /**
     * 获取随机的在线主播
     * @param  $num 随机的数量
     **/
    public function getRandAnchor( $num )
    {
        $data = Anchor::query()
            ->columns('anchor_id')
            ->where("anchor_chat_status > 0 and anchor_private_forbidden = 0")
            ->limit(10000)
            ->execute()
            ->toarray();
        if ( !empty($data) ) {
            $ids          = array_column($data, 'anchor_id');
            $list         = [];
            $total_num    = count($ids);
            $rand_ids_key = array_rand($ids, $total_num >= $num ? $num : $total_num);
            if ( is_array($rand_ids_key) ) {
                foreach ( $rand_ids_key as $item ) {
                    $list[] = $ids[ $item ];
                }
                $ids = implode(',', $list);
            } else {
                $ids = $ids[ $rand_ids_key ];
            }

        } else {
            return [ 'anchor' => [] ];
        }
        $anchor = Anchor::query()
            ->columns('app\models\User.user_id,app\models\User.user_avatar,app\models\User.user_level,app\models\User.user_video_cover,app\models\User.user_sex')
            ->join('app\models\User', 'app\models\User.user_id = app\models\Anchor.user_id')
            ->where("app\models\Anchor.anchor_id in ({$ids})")
            ->execute()
            ->toArray();
        return [ 'anchor' => $anchor ];
    }

    /**
     * 获取红人主播
     * @param  $num 随机的数量
     **/
    public function getHotMan()
    {
        $anchor = Anchor::query()
            ->columns('u.user_id,u.user_avatar,u.user_level,u.user_nickname,u.user_sex,anchor_video_cover as user_video_cover,anchor_video as user_video')
            ->join('app\models\User', 'u.user_id = app\models\Anchor.user_id', 'u')
            ->where('app\models\Anchor.anchor_hot_man > 0')
            ->orderBy('app\models\Anchor.anchor_hot_man desc')
            ->limit(20)
            ->execute()
            ->toArray();
        return $anchor;
    }

    /**
     * 获取接通率
     */
    public function getConnectionRate()
    {
        if ( $this->anchor_connection_rate_flg == 'custom' ) {
            return $this->anchor_connection_custom_rate;
        }
        return sprintf('%.2f', $this->anchor_called_count == 0 ? 100 : $this->anchor_chat_count / $this->anchor_called_count * 100);
    }
}