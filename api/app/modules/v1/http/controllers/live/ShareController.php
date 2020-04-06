<?php
/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 |直播分享控制器                                                          |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\live;

use app\models\AnchorLiveLog;
use app\models\UserGiftLog;
use Exception;
use app\models\User;
use app\models\Anchor;
use app\models\UserVideo;
use app\helper\ResponseError;
use app\http\controllers\ControllerBase;

/**
 * ShareController 直播分享
 */
class ShareController extends ControllerBase
{

    public function videoAction()
    {
        $video_id = $this->getParams('video_id', 'int', 0);
        if ( empty($video_id) ) {
            $this->error(10002);
        }
        $video = UserVideo::findFirst([
            'id=:id:',
            'bind' => [ 'id' => $video_id ]
        ]);
        if ( !$video ) {
            $this->error(10050);
        }
        if($video->watch_type == UserVideo::WATCH_TYPE_CHARGE) {
            $this->error(10050,'请下载app观看此视频哦');
        }
        $anchor               = Anchor::findFirst("user_id={$video->user_id}");
        $user                 = User::findFirst("user_id={$video->user_id}");
        $row                  = [];
        $row['user_nickname'] = $user->user_nickname;
        $row['user_avatar']   = $user->user_avatar;
        $row['user_level']    = $user->user_level;
        $row['video_cover']   = $video->cover;
        $row['play_url']      = $video->play_url;
        $row['like_num']      = $video->like_num;
        $row['reply_num']     = $video->reply_num;
        $row['title']         = $video->title;
        $builder              = $this->modelsManager->createBuilder()->from([ 'v' => UserVideo::class ])
            ->join(User::class, 'u.user_id=v.user_id', 'u')
            ->columns("u.user_avatar,u.user_level,title,user_nickname,like_num,play_url,reply_num,cover");
        $hot_row              = $this->page($builder->where("hot_time > 0 AND watch_type = :watch_type:",['watch_type' => UserVideo::WATCH_TYPE_FREE])->orderby("like_num desc"), 1, 8);
        $my_row               = $this->page($builder->where("v.user_id={$video->user_id} AND watch_type = :watch_type:",['watch_type' => UserVideo::WATCH_TYPE_FREE])->orderby("create_time desc"), 1, 8);
        $this->success([
            'user'      => $row,
            'hot_video' => $hot_row['items'],
            'my_video'  => $my_row['items']
        ]);
    }

    public function shareuserAction()
    {
        $user_id = $this->getParams('user_id', 'int');
        if ( empty($user_id) ) {
            $this->error(10002);
        }
        $user = User::findFirst([
            'user_id=:user_id:',
            'bind' => [ 'user_id' => $user_id ]
        ]);
        if ( !$user ) {
            $this->error(10002);
        }

        $data      = UserVideo::query()->columns("sum(like_num) as total")->where("user_id={$user_id}")->execute()->toArray();
        $liveNum      = $data[0]['total'] ? $data[0]['total'] : 0;
        $gift_img  = $this->getLiftByUid($user_id, 1, 1, 3);
        $user_data = [
            'id'                   => $user->user_id,
            'avatar'               => $user->user_avatar,
            'level'                => $user->user_level,
            'nickname'             => $user->user_nickname,
            'user_sex'             => $user->user_sex,
            'follow_total'         => $user->user_follow_total,
            'fans_total'           => $user->user_fans_total,
            'user_intro'           => $user->user_intro,
            'user_birth'           => $user->user_birth,
            'like_num'             => $liveNum,
            'user_home_town'       => $user->user_home_town,
            'user_hobby'           => $user->user_hobby,
            'user_profession'      => $user->user_profession,
            'user_constellation'   => $user->user_constellation,
            'user_emotional_state' => $user->user_emotional_state,
            'user_register_time'   => $user->user_register_time,
            'gift_img'             => empty($gift_img) ? [] : $gift_img,
            'total_gift'           => $this->getTotalGift($user_id),
        ];
        $builder   = $this->modelsManager->createBuilder()->from([ 'v' => UserVideo::class ])->join(User::class, 'u.user_id=v.user_id', 'u')->columns("v.id,u.user_avatar,u.user_level,title,user_nickname,like_num,play_url,cover")->where("u.user_id={$user_id}")->orderby("create_time desc");
        $row       = $this->page($builder, 1, 8);
        $this->success([
            'user'  => $user_data,
            'video' => $row['items']
        ]);
    }

    public function sharePrivateChatAction()
    {

        $user_id = $this->getParams('user_id');
        if ( empty($user_id) ) {
            $this->error(10002);
        }
        $user = User::findFirst([
            'user_id = :user_id:',
            'bind' => [ 'user_id' => $user_id ]
        ]);
        if ( !$user ) {
            $this->error(10002);
        }
        $anchor           = Anchor::findFirst("user_id={$user_id}");
        $return           = [
            'user_nickname'        => $user->user_nickname,
            'user_avatar'          => $user->user_avatar,
            'user_level'           => $user->user_level,
            'user_id'              => $user->user_id,
            'user_birth'           => $user->user_birth,
            'user_constellation'   => $user->user_constellation,
            'user_emotional_state' => $user->user_emotional_state,
            'user_hobby'           => $user->user_hobby,
            'anchor_local'         => $anchor->anchor_private_local,
            'user_video'           => $user->user_video,
            'user_video_cover'     => $user->user_video_cover,
            'user_home_town'       => $user->user_home_town,
            'user_profession'      => $user->user_profession,
            'user_sex'             => $user->user_sex,
            'anchor_private_local' => $anchor->anchor_private_local,
        ];
        $builder          = $this->modelsManager->createBuilder()->from([ 'a' => Anchor::class ])
            ->join(User::class, 'u.user_id=a.user_id', 'u')
            ->where("a.anchor_chat_status > 0 and a.user_id <> {$user_id} and user_video != '' and anchor_private_forbidden = 0 ")
            ->columns('u.user_nickname,u.user_avatar,u.user_level,u.user_id,u.user_sex,user_hobby,anchor_chat_status,user_video_cover,
            user_video,user_sex,anchor_private_local,anchor_private_local as anchor_local');
        $row              = $this->page($builder, 1, 8);
        $return['anchor'] = $row['items'];
        $this->success($return);
    }

    //获取用户收礼总数
    public function getTotalGift($nUserId)
    {
        $data = UserGiftLog::query()->columns("sum(live_gift_number) as total")->where("anchor_user_id={$nUserId}")->execute()->toArray();
        return $data[0]['total'] ? $data[0]['total'] : 0;
    }

    public function getAnchorGiftAction()
    {
        $page     = $this->getParams('page', 'int', 1);
        $pagesize = $this->getParams('pagesize', 'int', 10);
        $user_id  = $this->getParams('user_id');
        if ( empty($user_id) ) {
            $this->error(10002);
        }
        $data = $this->getLiftByUid($user_id, 2, $page, $pagesize);
        $this->success($data);
    }

    public function getLiftByUid($user_id, $type, $page, $pagesize)
    {
        try {
            $builder = $this->modelsManager->createBuilder()->from([ 'a' => UserGiftLog::class ])
                ->columns('live_gift_logo,sum(live_gift_number) as total,live_gift_name,live_gift_coin')
                ->where("anchor_user_id={$user_id}")->groupBy([ 'live_gift_name' ])->orderby("total desc");
            $row     = $this->page($builder, $page, $pagesize);
        } catch ( Exception $e ) {
            $this->error(ResponseError::FAIL, sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage()));
        }
        if ( $type == 1 ) {
            $data = array_column($row['items'], 'live_gift_logo');
            return $data;
        }
        return $row;
    }

}