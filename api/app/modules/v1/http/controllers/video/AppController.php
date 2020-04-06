<?php

namespace app\http\controllers\video;

use app\helper\ResponseError;
use app\models\Anchor;
use app\models\AppList;
use app\models\Kv;
use app\models\Area;
use app\models\ShortPosts;
use app\models\User;
use app\models\UserAccount;
use app\models\UserFollow;
use app\http\controllers\ControllerBase;
use app\models\UserVideo;
use app\models\UserVideoLike;
use app\models\UserVideoPay;
use app\models\UserVideoReply;
use app\models\VideoCategory;
use app\models\VideoMusic;
use Exception;

/**
 * AppController 视频
 */
class AppController extends ControllerBase
{
    use \app\services\UserService;

    /**
     * 小视频列表  / 用户的视频列表
     * location 3 为 最新
     * location 4 为 我关注的用户发布的小视频
     */
    public function listAction($nUserId = 0)
    {
        $type      = $this->getParams('type', 'string');
        $user_id   = $this->getParams('user_id', 'int');
        $nPage     = $this->getParams('page', 'int', 1);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $lat       = $this->getParams('lat', 'string', '0.000000');
        $lng       = $this->getParams('lng', 'string', '0.000000');
        $city      = $this->getParams('city', 'string', '');
        $location  = $this->getParams('location', 'int', 1);
        $row       = [];
        try {
            $columns = "v.id,u.user_id,u.user_nickname,u.user_avatar,u.user_level,v.title,v.cover,v.duration,v.watch_num,vc.name as category_name,
            v.like_num,v.reply_num,v.create_time,vc.id as category_id,v.watch_type,v.watch_price";
            $orderby = "v.id desc";
            $where   = "1=1";

            $builder    = $this->modelsManager->createBuilder()->from([ 'v' => UserVideo::class ])
                ->join(User::class, 'u.user_id=v.user_id', 'u')
                ->join(VideoCategory::class, 'vc.id = v.type', 'vc')->columns($columns);
            $watch_type = '';
            $isPublish  = $this->isPublish($nUserId, AppList::EXAMINE_ANCHOR);
            switch ( $location ) {
                case 1:
                    // 热门
                    $orderby = "hot_time desc,like_num desc,v.id desc";
                    $orderby = "rand()";
                    $where   .= " and v.is_show = 1 AND hot_time > 0";
                    if ( $isPublish ) {
                        $appInfo = $this->getAppInfo('qq');
                        $where   = 'v.video_is_examine = ' . $appInfo['id'];
                    }
                    $watch_type = UserVideo::WATCH_TYPE_FREE;
                    break;
                case 2:
                    // 自己的id
                    $user_id = $nUserId;
//                    $where   = " v.check_status = 'Y'";
                    break;
                case 3:
                    // 最新的  按照时间排序  分类的
                    $orderby = 'v.id desc';
                    $orderby = "rand()";
                    $where   .= " and v.is_show = 1";
                    if ( $isPublish ) {
                        $appInfo = $this->getAppInfo('qq');
                        $where   = 'v.video_is_examine = ' . $appInfo['id'];
                    }
                    break;
                case 4:
                    // 关注的  关注人发布的
                    $orderby = 'v.id desc';
                    $where   .= " and v.is_show = 1";
                    if ( $isPublish ) {
                        $appInfo = $this->getAppInfo('qq');
                        $where   .= ' AND v.video_is_examine = ' . $appInfo['id'];
                    }
                    $builder->join(UserFollow::class, 'u.user_id=uf.to_user_id AND uf.user_id = ' . $nUserId, 'uf');
                    $watch_type = UserVideo::WATCH_TYPE_FREE;
                    break;
                case 0:
                default:
                    // 分类 或者其他人的
                    $orderby = "hot_time desc,v.id desc";
                    if ( $user_id ) {
                        $where .= " AND v.check_status = 'Y'";
                    }
                    if($type){
                        $orderby = 'rand()';
                    }
                    if ( $isPublish ) {
                        $appInfo = $this->getAppInfo('qq');
                        $where   = 'v.video_is_examine = ' . $appInfo['id'];
                    }
            }
            $builder->where($where)->orderby($orderby);
            if ( $type ) {
                $builder->andWhere('v.type = :type: AND v.check_status = "Y" AND v.is_show = 1', [ 'type' => intval($type) ]);

            }
            if ( $user_id ) {
                $builder->andWhere('v.user_id = :user_id:', [ 'user_id' => intval($user_id) ]);
            }
            if ( $city ) {
                $builder->andWhere("v.city like :city:", [ 'city' => "%{$city}%" ]);
            }
            if ( $watch_type ) {
                $builder->andWhere("v.watch_type like :watch_type:", [ 'watch_type' => $watch_type ]);
            }
            $row = $this->page($builder, $nPage, $nPagesize);
        } catch ( Exception $e ) {
            $this->error(ResponseError::FAIL, sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage()));
        }
        $this->success($row);
    }

    /**
     * indexAction 导航列表
     *
     * @param  int $nUserId
     */
    public function indexAction($nUserId = 0)
    {
        $row['video_category'] = VideoCategory::find([
            'status="Y" order by sort asc',
            'columns' => 'id,name,logo',
        ]);
        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/video/app/videoDetail
     * @api {get} /video/app/videoDetail 小视频-视频详情
     * @apiName video-videoDetail
     * @apiGroup Video
     * @apiDescription 视频详情
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} id 视频id
     * @apiParam (正常请求){String} pay 是否支付
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} id 视频id
     * @apiParam (debug){String} pay 是否支付
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {number} d.video_id   视频id
     * @apiSuccess {number} d.user_id  用户id
     * @apiSuccess {String} d.user_avatar  用户头像
     * @apiSuccess {String} d.user_nickname  用户昵称
     * @apiSuccess {String} d.title  标题
     * @apiSuccess {String} d.share_url  分享地址
     * @apiSuccess {number} d.like_num  点赞人数
     * @apiSuccess {number} d.reply_num  回复人数
     * @apiSuccess {number} d.share_num  分享数量
     * @apiSuccess {String} d.category_name  类型名称
     * @apiSuccess {String} d.play_url  播放地址
     * @apiSuccess {String} d.is_like  是否点赞了
     * @apiSuccess {String} d.is_follow  是否关注了
     * @apiSuccess {String} d.user_is_anchor   用户是否为主播
     * @apiSuccess {number} d.anchor_guard_id  主播守护id
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *    {
     *        "c": 0,
     *        "m": "请求成功",
     *        "d": {
     *                "video_id": "327",
     *                "user_id": "258",
     *                "user_avatar": "http://tvax4.sinaimg.cn/crop.0.0.40.40.180/007dqVi7ly8ftm2u9xgx9j3014014a9t.jpg",
     *                "user_nickname": "考虑考虑",
     *                "title": "考虑考虑",
     *                "share_url": "http://dev.h5.sxypaopao.com/sharevideo?video_id=327",
     *                "like_num": "0",
     *                "reply_num": "0",
     *                "share_num": "0",
     *                "category_name": "生活",
     *                "play_url": "http://lebolive-1255651273.cos.ap-guangzhou.myqcloud.com/video/2018/12/07/1544150924134.mp4",
     *                "is_like": false,
     *                "is_follow": false,
     *                "user_is_anchor": "N",
     *                "anchor_guard_id": "0"
     *        },
     *        "t": "1544173176"
     *    }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function videoDetailAction($nUserId = 0)
    {
        $id       = $this->getParams('id', 'int');
        $payVideo = $this->getParams('pay', 'int');
        if ( !$id ) {
            $this->error(10002);
        }
        try {
            $video = UserVideo::findFirst([
                "id=:id:",
                'bind' => [ 'id' => $id ]
            ]);
            if ( !$video ) {
                $this->error(10002);
            }
            $user = User::findFirst($nUserId);

            if ( $payVideo ) {
                if ( $user->user_is_superadmin == 'S' ) {
                    $this->error(10002, '该账号无此权限哦');
                }
                // 用户确认支付
                $flg = UserVideoPay::addPay($video, $nUserId);
                if ( $flg !== TRUE ) {
                    $this->error($flg['c'], $flg['m']);
                }
                $video = UserVideo::findFirst([
                    "id=:id:",
                    'bind' => [ 'id' => $id ]
                ]);
            } else {
                if ( $video->checkVideoAuth($user) === FALSE ) {
//                    $vipVideoPayDiscount = 50;
                    $vipVideoPayDiscount = intval(Kv::get(Kv::VIP_VIDEO_DISCOUNT));
                    $vipPrice            = intval($video->watch_price * $vipVideoPayDiscount / 100, 2);
                    $showDiscount        = $vipVideoPayDiscount / 10;
                    if ( $user->user_member_expire_time > time() ) {
                        $this->error(ResponseError::SHOULD_VIP_PAY_VIDEO, sprintf(ResponseError::getError(ResponseError::SHOULD_VIP_PAY_VIDEO), $vipPrice));
                    } else {
                        $this->error(ResponseError::SHOULD_PAY_VIDEO, sprintf(ResponseError::getError(ResponseError::SHOULD_PAY_VIDEO), $video->watch_price, $vipPrice));
                    }
                }
            }

            $category          = VideoCategory::findFirst([
                'id=:id:',
                'bind' => [ 'id' => $video->type ]
            ]);
            $bool              = UserVideoLike::findFirst([
                "video_id=:video_id: and user_id = :user_id:",
                'bind' => [
                    'video_id' => $id,
                    'user_id'  => $nUserId
                ]
            ]);
            $follow            = UserFollow::findFirst([
                "user_id = :user_id: and to_user_id = :to_user_id:",
                'bind' => [
                    'user_id'    => $nUserId,
                    'to_user_id' => $video->user_id
                ]
            ]);
            $anchor_guard_id   = '0';
            $anchor_chat_price = '0';
            $oAnchor           = Anchor::findFirst([
                'user_id = :user_id:',
                'bind' => [ 'user_id' => $video->user_id ]
            ]);
            $videoUser         = User::findFirst($video->user_id);
            if ( $oAnchor ) {
                $anchor_guard_id   = $oAnchor->anchor_guard_id;
                $anchor_chat_price = $oAnchor->anchor_chat_price;
            }
            $return = [
                'video_id'          => $id,
                'user_id'           => $video->user_id,
                'user_avatar'       => $videoUser->user_avatar,
                'user_level'        => $videoUser->user_level,
                'user_nickname'     => $videoUser->user_nickname,
                'title'             => $video->title,
                'share_url'         => $video->watch_type == UserVideo::WATCH_TYPE_FREE ? APP_WEB_URL . '/sharevideo?video_id=' . $video->id : '',
                'like_num'          => $video->like_num,
                'reply_num'         => $video->reply_num,
                'share_num'         => $video->share_num,
                'category_name'     => $category->name,
                'play_url'          => $video->play_url,
                'is_like'           => $bool ? TRUE : FALSE,
                'is_follow'         => $follow ? TRUE : FALSE,
                'user_is_anchor'    => $videoUser->user_is_anchor,
                'anchor_guard_id'   => $anchor_guard_id,
                'anchor_chat_price' => $anchor_chat_price,
            ];
            if ( $nUserId != $video->user_id ) {
                $video->watch_num += 1;
                $video->save();
            }
        } catch ( Exception $e ) {
            $this->error(ResponseError::FAIL, sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage()));
        }
        $this->success($return);
    }

    /**
     * 获取同一类型的所有的视频
     */
    public function getVideoByTypeAction($nUserId = 0)
    {
        $type = $this->getParams('type', 'int');
        if ( !$type ) {
            $this->error(10002);
        }
        try {
            $model = new UserVideo();
            $data  = $model->getIdByType($type);
        } catch ( Exception $e ) {
            $this->error(ResponseError::FAIL, sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage()));
        }
        $this->success($data);
    }

    /**
     * 视频回复列表
     */
    public function replyListAction($nUserId = 0)
    {
        $video_id  = $this->getParams('video_id');
        $nPage     = $this->getParams('page', 'int', 1);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $row       = [];
        $row       = [
            'items'     => [],
            'page'      => 1,
            'pagesize'  => 20,
            'pagetotal' => 1,
            'total'     => 0,
            'prev'      => 1,
            'next'      => 1,
        ];
        $this->success($row);
        exit();

        try {
            $columns = "u.user_id,u.user_nickname,u.user_avatar,u.user_level,ifnull(u2.user_id,0) as f_user_id,
            ifnull(u2.user_nickname,'') as f_user_nickname,vr.create_time,vr.content";
            $builder = $this->modelsManager->createBuilder()->from([ 'vr' => UserVideoReply::class ])
                ->join(User::class, 'u.user_id=vr.user_id', 'u')
                ->leftJoin(User::class, 'u2.user_id=vr.follow_user_id', 'u2')
                ->columns($columns)->where("vr.video_id = :video_id:  AND u.user_is_deny_speak = 'N'", [ 'video_id' => $video_id ])->orderBy("vr.id desc");
            $row     = $this->page($builder, $nPage, $nPagesize);
        } catch ( Exception $e ) {
            $this->error(ResponseError::FAIL, sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage()));
        }
        $this->success($row);
    }


    /**
     * 发表评论
     */
    public function addReplyAction($nUserId = 0)
    {
        $video_id  = $this->request->getPost('video_id');
        $content   = $this->request->getPost('content');
        $f_user_id = $this->request->getPost('f_user_id');
        $this->error(ResponseError::OPERATE_FAILED, '该功能暂不开放');
        $oUser = User::findFirst($nUserId);
        if ( $oUser->user_is_deny_speak == 'Y' ) {
            $this->error(ResponseError::USER_PROHIBIT_TALK, ResponseError::getError(ResponseError::USER_PROHIBIT_TALK));
        }

        /*判断参数的正确性*/
        if ( !$video_id || !$content ) {
            $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'video_id|content'));
        }
        if ( $this->banword($content) ) {
            $this->error(ResponseError::BANWORD, sprintf('%s[%s]', ResponseError::getError(ResponseError::BANWORD), ResponseError::BANWORD));
        }
        $video = UserVideo::findFirst([
            'id = :bind_param:',
            'bind' => [ 'bind_param' => $video_id ]
        ]);
        if ( !$video ) {
            $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'video_id'));
        }
        if ( $f_user_id ) {
            $f_user = User::findFirst([
                "user_id = :bind_param:",
                'bind' => [ 'bind_param' => $f_user_id ]
            ]);
            if ( !$f_user ) {
                $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'f_user_id'));
            }
        }
        $reply                 = new UserVideoReply();
        $reply->user_id        = $nUserId;
        $reply->video_id       = $video_id;
        $reply->follow_user_id = $f_user_id;
        $reply->content        = $content;
        if ( !$reply->create() ) {
            $this->error(ResponseError::OPERATE_FAILED, sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $reply->getMessages()));
        }
        $this->success();
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/video/app/addVideo
     * @api {post} /video/app/addVideo 发布小视频
     * @apiName Profile-addVideo
     * @apiGroup Profile
     * @apiDescription 发布小视频
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String}  type 分类 默认为1
     * @apiParam (正常请求){String}  title 标题
     * @apiParam (正常请求){String}  cover 封面
     * @apiParam (正常请求){String}  play_url 播放地址
     * @apiParam (正常请求){String}  city 地址
     * @apiParam (正常请求){String}  lat 经度
     * @apiParam (正常请求){String}  lng 纬度
     * @apiParam (正常请求){String}  duration 时长
     * @apiParam (正常请求){String}  watch_type 观看类型  free 免费 charge 收费
     * @apiParam (正常请求){String}  watch_price 价格
     * @apiParam (正常请求){String}  add_posts 是否同步发布动态  Y 为是 N 为否
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String}  type 分类 默认为1
     * @apiParam (debug){String}  title 标题
     * @apiParam (debug){String}  cover 封面
     * @apiParam (debug){String}  play_url 播放地址
     * @apiParam (debug){String}  city 地址
     * @apiParam (debug){String}  lat 经度
     * @apiParam (debug){String}  lng 纬度
     * @apiParam (debug){String}  duration 时长
     * @apiParam (debug){String}  watch_type 观看类型  free 免费 charge 收费
     * @apiParam (debug){String}  watch_price 价格
     * @apiParam (debug){String}  add_posts 是否同步发布动态  Y 为是 N 为否
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function addVideoAction($nUserId = 0)
    {
        $type        = $this->request->getPost('type');
        $title       = $this->request->getPost('title', 'string', '什么都没留下~');
        $cover       = $this->request->getPost('cover');
        $play_url    = $this->request->getPost('play_url');
        $music_id    = $this->request->getPost('music_id');
        $city        = $this->request->getPost('city');
        $lat         = $this->request->getPost('lat');
        $lng         = $this->request->getPost('lng');
        $duration    = $this->request->getPost('duration');
        $watch_type  = $this->request->getPost('watch_type', 'string', 'free');
        $watch_price = $this->request->getPost('watch_price');
        $addPosts    = $this->request->getPost('add_posts', 'string', 'N');

        if ( !$type || !$cover || !$play_url ) {
            $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'type|cover|play_url'));
        }
        if ( !in_array($watch_type, [
            UserVideo::WATCH_TYPE_CHARGE,
            UserVideo::WATCH_TYPE_FREE
        ]) ) {
            $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'watch_type'));
        }
        if ( $watch_type == UserVideo::WATCH_TYPE_CHARGE ) {
            $max_watch_price = 100;
            $min_watch_price = 20;
            if ( $watch_price > $max_watch_price || $watch_price < $min_watch_price ) {
                $this->error(ResponseError::PARAM_ERROR, "视频价格必须在 $min_watch_price 到 $max_watch_price 之间");
            }
        }

        if ( !VideoCategory::findFirst([
            'id = :bind_param:',
            'bind' => [ 'bind_param' => $type ]
        ]) ) {
            $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'type'));
        }
        if ( $music_id ) {
            if ( !VideoMusic::findFirst([
                'id = :bind_param:',
                'bind' => [ 'bind_param' => $music_id ]
            ]) ) {
                $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'music_id'));
            }
        }
        $video              = new UserVideo();
        $video->user_id     = $nUserId;
        $video->music_id    = $music_id;
        $video->type        = $type;
        $video->title       = $title;
        $video->cover       = $cover;
        $video->play_url    = $play_url;
        $video->lat         = $lat;
        $video->lng         = $lng;
        $video->city        = $city;
        $video->duration    = $duration;
        $video->watch_type  = $watch_type;
        $video->watch_price = $watch_price;
        $video->is_show     = 0;
        if ( !$video->create() ) {
            $this->error(ResponseError::OPERATE_FAILED, sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $video->getMessages()));
        }

        $oUser = User::findFirst($nUserId);
        if ( $addPosts == 'Y' && $watch_type == UserVideo::WATCH_TYPE_FREE ) {
            // 如果是免费的 则可以设置为一条动态
            $oUser = User::findFirst($nUserId);
            if($oUser->user_is_anchor == 'N'){
                $oUserAccount = UserAccount::findFirst($nUserId);
                $user_phone = $oUserAccount->user_phone;
            }else{
                $user_phone = 'this is flg';
            }
            // 普通用户 必须是VIP 并且绑定手机号 才能发帖
            if( $oUser->user_is_anchor == 'Y' || ($oUser->user_is_anchor == 'N' && $oUser->user_member_expire_time > time() && $user_phone) ){
                    $oShortPosts                       = new ShortPosts();
                    $oShortPosts->short_posts_word     = $title;
                    $oShortPosts->short_posts_user_id  = $nUserId;
                    $oShortPosts->short_posts_position = $city;
                    $oShortPosts->short_posts_type     = 'video';
                    $oShortPosts->short_posts_images   = $cover;
                    $oShortPosts->short_posts_video    = $play_url;
                    $oShortPosts->short_posts_status   = 'C';
                    $oShortPosts->save();
            }
        }

        $return['share_url'] = APP_WEB_URL . '/sharevideo?video_id=' . $video->id;
        $return['video_id']  = $video->id;
        $this->success($return);
    }

    /**
     * 音乐列表
     */
    public function musicListAction($nUserId = 0)
    {
        $search    = $this->getParams('search');
        $nPage     = $this->getParams('page', 'int', 1);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $row       = [];
        try {
            $columns = "id,name,duration,author,cover,url";
            $builder = $this->modelsManager->createBuilder()->from(VideoMusic::class)->columns($columns);
            if ( $search ) {
                $builder->andWhere('name like :like:', [ 'like' => "%$search%" ]);
            }
            $row = $this->page($builder, $nPage, $nPagesize);
        } catch ( Exception $e ) {
            $this->error(ResponseError::FAIL, sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage()));
        }
        $this->success($row);
    }

    /**
     * 点赞视频
     */
    public function videoLikeAction($nUserId = 0)
    {
        $video_id = $this->request->getPost('video_id');
        /*判断参数的正确性*/
        if ( !$video_id ) {
            $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'video_id'));
        }
        $video = UserVideo::findFirst([
            'id = :bind_param:',
            'bind' => [ 'bind_param' => $video_id ]
        ]);
        if ( !$video ) {
            $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'video_id'));
        }
        $like           = new UserVideoLike();
        $like->user_id  = $nUserId;
        $like->video_id = $video_id;
        if ( !$like->create() ) {
            $this->error(ResponseError::OPERATE_FAILED, sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $like->getMessages()));
        }
        $this->success();
    }

    /**
     * 点赞视频
     */
    public function cancelVideoLikeAction($nUserId = 0)
    {
        $video_id = $this->request->getPost('video_id');
        /*判断参数的正确性*/
        if ( !$video_id ) {
            $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'video_id'));
        }
        $video = UserVideo::findFirst([
            'id = :bind_param:',
            'bind' => [ 'bind_param' => $video_id ]
        ]);
        if ( !$video ) {
            $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'video_id'));
        }
        $like = UserVideoLike::findFirst("video_id={$video_id} and user_id ={$nUserId}");
        if ( !$like ) {
            $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'video_id'));
        }
        if ( !$like->delete() ) {
            $this->error(ResponseError::OPERATE_FAILED, sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $like->getMessages()));
        }
        $this->success();
    }

    /**
     * 删除视频
     */
    public function deleteVideoAction($nUserId = 0)
    {
        $video_id = $this->request->getPost('video_id');
        /*判断参数的正确性*/
        if ( !$video_id ) {
            $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'video_id'));
        }
        $video = UserVideo::findFirst([
            'id = :bind_param:',
            'bind' => [ 'bind_param' => $video_id ]
        ]);
        if ( !$video ) {
            $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'video_id'));
        }
        if ( !$video->delete() ) {
            $this->error(ResponseError::OPERATE_FAILED, sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $video->getMessages()));
        }
        $this->success();
    }

    /**
     * 分享成功视频
     *
     * @param int $nUserId
     */
    public function shareVideoSuccessAction($nUserId)
    {
        $video_id = $this->getParams('video_id');
        if ( !$video_id ) {
            $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'video_id'));
        }
        $video = UserVideo::findFirst([
            'id = :bind_param:',
            'bind' => [ 'bind_param' => $video_id ]
        ]);
        if ( !$video ) {
            $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'video_id'));
        }
        $video->share_num += 1;
        if ( !$video->save() ) {
            $this->error(ResponseError::OPERATE_FAILED, sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $video->getMessages()));
        }
        $this->success();
    }

    private function getSignUrl($live_log)
    {
        $key  = $this->config->live->playbackAuthKey;
        $url  = $live_log->anchor_live_log_playback_url;
        $data = explode('/', explode('.com/', $url)[1]);
        $dir  = "/{$data[0]}/{$data[1]}/";
        $t    = dechex(time() + 10);
        $us   = $live_log->user_id;
        $sign = md5($key . $dir . $t . $us);
        $url  .= "?t={$t}&us={$us}&sign={$sign}";
        return $url;
    }

    public function getCityAction()
    {
        $data = $this->redis->get('city_list');
        if ( empty($data) ) {
            $data = Area::query()->columns('areaname as city')->where("level = 2")->orderBy("id asc")->execute()->toArray();
            if ( !empty($data) ) {
                $data = json_encode($data);
                $this->redis->set('city_list', $data);
            }
        }
        $list   = array_column(json_decode($data), 'city');
        $return = [ 'city' => $list ];
        $this->success($return);
    }

    /**
     * 私密视频设置价格区间
     */
    public function ChargePriceSettingListAction($nUserId = 0)
    {
        $minPrice = intval(intval(Kv::get(Kv::CHARGE_VIDEO_PRICE_MIN)) / 10) * 10;
        $maxPrice = intval(intval(Kv::get(Kv::CHARGE_VIDEO_PRICE_MAX)) / 10) * 10;

        $list = [];
        for ( $price = $minPrice; $price <= $maxPrice; $price += 10 ) {
            $list[] = [
                'price' => $price
            ];
        }
        $this->success($list);
    }


    /**
     * 关注人发布的小视频列表
     */
    public function followAction()
    {

    }

}
