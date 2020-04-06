<?php

namespace app\models;

/**
 * Photographer 摄影师
 */
class Photographer extends ModelBase
{

    /** @var string 分成比例类型  动态 */
    const RATIO_POSTS = 'posts';
    /** @var string 分成比例类型  微信 */
    const RATIO_WECHAT = 'wechat';
    /** @var string 分成比例类型  微信 */
    const RATIO_GIFT = 'gift';
    /**
     * 获取摄影师的分成比例
     */
    public function getCoinToDotRatio($oPhotographerUser, $type = self::RATIO_POSTS)
    {
        $postsRatio    = Kv::get(Kv::COIN_TO_DOT_RATIO_PHOTOGRAPHER_POSTS);
        $wechatRatio    = Kv::get(Kv::COIN_TO_DOT_RATIO_PHOTOGRAPHER_WECHAT);
        $giftRatio    = Kv::get(Kv::COIN_TO_DOT_RATIO_PHOTOGRAPHER_GIFT);
        switch ( $type ) {
            case self::RATIO_POSTS:
                $result = $postsRatio;
                break;
            case self::RATIO_WECHAT:
                $result = $wechatRatio;
                break;
            case self::RATIO_GIFT:
                $result = $giftRatio;
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

}