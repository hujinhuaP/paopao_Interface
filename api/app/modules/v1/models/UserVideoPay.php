<?php

namespace app\models;

/**
 * UserVideoPay 是否购买记录
 */
class UserVideoPay extends ModelBase
{

    const PAY_TYPE_NORMAL = 'N';
    const PAY_TYPE_VIP    = 'V';
    public $id;
    public $user_id;
    public $video_id;
    public $create_time;
    public $pay_price;
    public $pay_type;

    /**
     * @param $video
     * @param $nUserId
     * 用户支付视频
     */
    public static function addPay($video, $nUserId)
    {

        if ( $video->watch_type == UserVideo::WATCH_TYPE_FREE || $video->watch_price == 0 ) {
            return TRUE;
        }
        $oUserVideoPay = UserVideoPay::findFirst([
            'video_id = :video_id: AND user_id = :user_id:',
            'bind' =>
                [
                    'video_id' => $video->id,
                    'user_id'  => $nUserId
                ],
        ]);
        if ( $oUserVideoPay ) {
            return TRUE;
        }

        return UserFinanceLog::addVideoPay($video, $nUserId);
    }

    public function beforeCreate()
    {
        $this->create_time = time();
    }

}