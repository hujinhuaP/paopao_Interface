<?php
/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 主播控制器                                                             |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\live;

use app\helper\JiGuangApi;
use app\helper\ResponseError;
use app\http\controllers\ControllerBase;
use app\models\Agent;
use app\models\Anchor;
use app\models\AnchorDispatch;
use app\models\AnchorImage;
use app\models\AnchorSignStat;
use app\models\AnchorTitleConfig;
use app\models\AppList;
use app\models\Banword;
use app\models\FreeTimeGuardLog;
use app\models\Group;
use app\models\Kv;
use app\models\LevelConfig;
use app\models\Room;
use app\models\User;
use app\models\UserAccount;
use app\models\UserBlack;
use app\models\UserBudan;
use app\models\UserCertification;
use app\models\UserChat;
use app\models\UserChatDialog;
use app\models\UserChatPay;
use app\models\UserConsumeCategory;
use app\models\UserFinanceLog;
use app\models\UserFollow;
use app\models\UserGuard;
use app\models\UserIntimateLog;
use app\models\UserMatchLog;
use app\models\UserNameConfig;
use app\models\UserPrivateChatDialog;
use app\models\UserPrivateChatLog;
use app\models\UserProfileSetting;
use app\models\UserRechargeActionLog;
use app\models\UserSet;
use app\models\UserSnatchLog;
use app\models\VipLevel;
use app\services\ActivityAnchorService;
use app\services\ActivityUserService;
use app\services\AnchorBatchSayhiService;
use app\services\AnchorHeartbeatService;
use app\services\AnchorOfflineModifyService;
use app\services\AnchorSayhiService;
use app\services\AnchorStatService;
use app\services\AnchorTodayDotService;
use app\services\ChatHeartbeatService;
use app\services\ChatPayService;
use app\services\ChatStreamService;
use app\services\ChatTimCheckService;
use app\services\CustomerService;
use app\services\GuardFreeTimeService;
use app\services\IntimateService;
use app\services\MatchCenterUserAnchorService;
use app\services\TaskQueueService;
use app\services\UserVideoChatService;
use app\services\VideoChatService;
use Exception;

/**
 * AnchorController 主播
 */
class AnchorController extends ControllerBase
{

    use \app\services\UserService;
    use \app\services\SystemMessageService;


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/getGuideAnchorUserId
     * @api {get} /live/anchor/getGuideAnchorUserId 获取诱导主播id
     * @apiName anchor-getGuideAnchorUserId
     * @apiGroup Index
     * @apiDescription 获取诱导主播id
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.anchor_user_id 主播id
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *                 "anchor_user_id": "318"
     *         },
     *         "t": "1544170824"
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function getGuideAnchorUserIdAction( $nUserId = 0 )
    {
        // 文字诱导
        try {
            $oUser   = User::findFirst($nUserId);
            $oAnchor = Anchor::findFirst([
                '(anchor_hot_time > 0 or anchor_is_newhot = "Y") AND anchor_is_examine = 0',
                'order' => 'anchor_chat_status desc,rand()'
            ]);
            if ( !$oAnchor ) {
                $this->success([
                    'anchor_user_id' => '0'
                ]);
            }
            $row = [
                'anchor_user_id' => $oAnchor->user_id
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);

    }


    /**
     * getLiveInfoAction 获取开始直播信息
     *
     * @param int $nUserId
     */
    public function getLiveInfoAction( $nUserId = 0 )
    {

        try {

            $oUser = User::findFirst($nUserId);
            if ( $oUser->user_is_certification == 'N' ) {
                $oUserCertification = UserCertification::findFirst([
                    'user_id=:user_id: order by user_certification_id desc',
                    'bind' => [
                        'user_id' => $nUserId,
                    ],
                ]);
                if ( !$oUserCertification ) {
                    // 没有认证
                    throw new Exception(ResponseError::getError(ResponseError::USER_NOT_CERTIFICATION), ResponseError::USER_NOT_CERTIFICATION);
                } else if ( $oUserCertification->user_certification_status == 'N' ) {
                    // 认证失败
                    throw new Exception(sprintf('%s (%s)', ResponseError::getError(ResponseError::USER_CERTIFICATION_FAIL), $oUserCertification->user_certification_result), ResponseError::USER_CERTIFICATION_FAIL);
                } else if ( $oUserCertification->user_certification_status == 'C' ) {
                    // 正在核对
                    throw new Exception(ResponseError::getError(ResponseError::USER_CERTIFICATION_CHECK), ResponseError::USER_CERTIFICATION_CHECK);
                }
            }
            $oAnchor = Anchor::findFirst([
                'user_id=:user_id:',
                'bind' => [
                    'user_id' => $nUserId,
                ],
            ]);
            if ( $oAnchor == FALSE ) {
                throw new Exception(ResponseError::getError(ResponseError::ANCHOR_NOT_EXISTS), ResponseError::ANCHOR_NOT_EXISTS);
            }
            // 是否永久禁播
            if ( $oAnchor->anchor_is_forbid == 'Y' ) {
                throw new Exception(ResponseError::getError(ResponseError::ANCHOR_FORBID), ResponseError::ANCHOR_FORBID);
                // 停播时间是否过期
            } else if ( time() < $oAnchor->anchor_forbid_time ) {
                throw new Exception(sprintf('%s %s', ResponseError::getError(ResponseError::ANCHOR_FORBID_EXPIRE), date('Y-m-d H:i:s', $oAnchor->anchor_forbid_time)), ResponseError::ANCHOR_FORBID_EXPIRE);
            }
            $sMicrotime           = sprintf('%.10f', microtime(1));
            $aTime                = explode('.', $sMicrotime);
            $sAnchorLiveLogNumber = date('YmdHis', $aTime[0]) . $aTime[1];
            $this->cookies->set('_ANCHOR_LIVE_LOG_NUMBER', $sAnchorLiveLogNumber, time() + 60 * 60 * 24);
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }

    /**
     * heartbeatAction 心跳
     *
     * @param int $nUserId
     */
    public function heartbeatAction( $nUserId = 0 )
    {
        $oAnchorHeartbeatService = new AnchorHeartbeatService();
        $oAnchorHeartbeatService->save($nUserId);
        $this->success();
    }

    /**
     * 以下是增加代码
     * 2018-01-03
     */
    //获取私聊主播
    public function getAnchorAction()
    {
        $num   = $this->getParams('num', 'int', 1);
        $model = new Anchor();
        $res   = $model->getRandAnchor($num);
        $this->success($res);
    }

    //获取红人主播  （推荐 或者 诱导用户充值）
    public function getHotManAction()
    {
        $model = new Anchor();
        $data  = $model->getHotMan();
        $this->success($data);
    }

    //获取快聊主播

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/getPrivateChat
     * @api {get} /live/anchor/getPrivateChat 001-190909首页主播
     * @apiName getPrivateChat
     * @apiGroup Index
     * @apiDescription 首页主播  审核状态中，不展示离线的用户  添加主播标签搜索 年龄搜索
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} local  城市
     * @apiParam (正常请求) {String="index(首页)","all(所有)","hot(热门)","follow(关注)","new(新人)","guide(诱导)","nearby(附近)"} type  类型
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} local  城市
     * @apiParam (debug) {String="index(首页)","all(所有)","hot(热门)","follow(关注)","new(新人)","guide(诱导)","nearby(附近)"} type  类型
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items  内容
     * @apiSuccess {String} d.items.user_nickname  昵称
     * @apiSuccess {String} d.items.user_avatar  头像
     * @apiSuccess {String} d.items.user_level  等级
     * @apiSuccess {String} d.items.user_intro  简介
     * @apiSuccess {String} d.items.user_profession  职业
     * @apiSuccess {number} d.items.anchor_chat_status  聊天状态
     * @apiSuccess {number} d.items.user_id  用户id
     * @apiSuccess {number} d.items.user_sex 性别
     * @apiSuccess {number} d.items.user_birth 生日
     * @apiSuccess {String} d.items.user_video_cover  视频封面
     * @apiSuccess {String} d.items.user_video   视频地址
     * @apiSuccess {number} d.items.is_follow  是否关注  0为 未关注 其他为已关注
     * @apiSuccess {String} d.items.anchor_tip
     * @apiSuccess {String} d.items.anchor_character
     * @apiSuccess {String} d.items.anchor_good_topic
     * @apiSuccess {String} d.items.anchor_dress
     * @apiSuccess {String} d.items.anchor_stature
     * @apiSuccess {String} d.items.anchor_emotional_state
     * @apiSuccess {number} d.items.price
     * @apiSuccess {String} d.items.anchor_local
     * @apiSuccess {String} d.items.anchor_title
     * @apiSuccess {String} d.items.share_url
     * @apiSuccess {String} d.items.anchor_guard_id  主播守护id
     * @apiSuccess {String} d.items.today_guard_free_times  当天守护剩余可免费聊天时长
     * @apiSuccess {String} d.items.anchor_level  主播等级
     * @apiSuccess {String} d.items.anchor_title_number  主播称号值
     * @apiSuccess {String} d.items.anchor_title_name  主播称号名称
     * @apiSuccess {String} d.items.anchor_custom_title  自定义称号
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *             "items": [{
     *                 "user_nickname": "118啦咯",
     *                 "user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/09\/1533807443523.png",
     *                 "user_level": "1",
     *                 "user_intro": "徒孙气质红哦你好给力了",
     *                 "user_profession": "工程师",
     *                 "anchor_chat_status": "3",
     *                 "user_id": "166",
     *                 "user_sex": "2",
     *                 "anchor_guard_id": "2",
     *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/08\/1533694327924.png"
     *         }, {
     *                 "user_nickname": "泡泡08271736169",
     *                 "user_avatar": "http:\/\/lebolive-1255651273.file.myqcloud.com\/avatar.jpg",
     *                 "user_level": "1",
     *                 "user_intro": "",
     *                 "user_profession": "",
     *                 "anchor_chat_status": "1",
     *                 "user_id": "230",
     *                 "user_sex": "2",
     *                 "anchor_guard_id": "2",
     *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/27\/1970dd6937d27862b745642fabbc6af5"
     *         }, {
     *                 "user_nickname": "神秘",
     *                 "user_avatar": "http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/Q0j4TwGTfTKkYqhRB1yzqQsZh7DwA0IvFAX2Ks9DCFcdXn5KUrquT2qpouY7k1qbfTpmDBiaxIz37Zaic4rKFqHg\/132",
     *                 "user_level": "1",
     *                 "user_intro": "",
     *                 "user_profession": "",
     *                 "anchor_chat_status": "0",
     *                 "user_id": "170",
     *                 "user_sex": "1",
     *                 "anchor_guard_id": "2",
     *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/06\/1533540159917.png"
     *         }],
     *         "page": 1,
     *         "pagesize": 50,
     *         "pagetotal": 1,
     *         "total": 3,
     *         "prev": 1,
     *         "next": 1
     *         },
     *         "t": 1535600607
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function getPrivateChatAction( $nUserId = 0 )
    {
        $page      = $this->getParams('page', 'int', 1);
        $pagesize  = $this->getParams('pagesize', 'int', 50);
        $type      = $this->getParams('type', 'string', 'index');
        $localCity = $this->getParams('local', 'string');
//        $where    = " u.user_video != '' and a.anchor_private_forbidden = 0 AND a.anchor_chat_status > 0";

        try {
            $oUser = User::findFirst($nUserId);
            if ( $type == 'guide' ) {
                // 视频诱导
                if ( $oUser->user_invite_agent_id ) {
                    // 渠道判断诱导
                    $oAgent = Agent::findFirst($oUser->user_invite_agent_id);
                    if ( $oAgent->video_guide_flg == 'N' ) {
                        $this->success([]);
                    }
                    if ( $oAgent->video_guide_flg == 'S' ) {
                        $guideTimestampStart = strtotime(date('Y-m-d ')) + $oAgent->video_guide_hour_start * 3600;
                        $guideTimestampEnd   = strtotime(date('Y-m-d ')) + $oAgent->video_guide_hour_end * 3600;
                        if ( $oAgent->video_guide_hour_start > $oAgent->video_guide_hour_end ) {
                            // ps   23点到7点
                            $todayGuideTimestampStart = $guideTimestampStart;
                            if ( time() < $todayGuideTimestampStart ) {
                                $guideTimestampStart -= 24 * 3600;
                            } else {
                                $guideTimestampEnd += 24 * 3600;
                            }
                        }
                        // 判断当前是否不在时间范围内
                        $this->getDump([
                            'time'  => date('Y-m-d H:i:s'),
                            'start' => date('Y-m-d H:i:s', $guideTimestampStart),
                            'end'   => date('Y-m-d H:i:s', $guideTimestampEnd),
                        ]);
                        // 判断当前是否不在时间范围内
                        if ( time() < $guideTimestampStart || time() > $guideTimestampEnd ) {
                            $this->success([]);
                        }
                    }
                }
            }
            $exameFlg = $this->isPublish($nUserId, AppList::EXAMINE_ANCHOR);
            $where    = " a.anchor_video != '' and a.anchor_private_forbidden = 0";
            $order    = "anchor_chat_status desc,anchor_is_sign,rand()";
//            if ( $type != 'all' && $this->isPublish($nUserId, AppList::PUBLISH_HIDE_OFFLINE_ANCHOR) ) {
//                // 不在审核状态  且 该uid 不是审核账号   看到的都是在线的用户
//                $where .= ' AND a.anchor_chat_status > 1';
//            }
            switch ( $type ) {
                case 'nearby':
                    // 取同城在线数据 不足15个 用活跃补全。  取同城不在线数据 不足15个用其他补齐
                    $page     = 1;
                    $pagesize = 15;
                    $where    .= " AND anchor_chat_status > 1 and a.anchor_is_show_index = 'Y'";
                    if ( $localCity ) {
                        $order = "if(anchor_private_local='{$localCity}',0,1),anchor_chat_status desc,anchor_is_sign,rand()";
                    }
                    break;
                case 'guide':
                    $where .= ' AND (a.anchor_hot_time > 0 or a.anchor_is_newhot = "Y")';
                    break;
                case 'all':
                    if ( !$exameFlg ) {
                        $where .= ' AND a.anchor_chat_status > 1';
                    }
                    break;
                case 'new':
//                $startAnchorTime = strtotime('-20 day');
//                $where           .= " AND anchor_create_time >= $startAnchorTime";
                    // 获取最近成为主播的第100位
                    $page              = 1;
                    $pagesize          = 100;
                    $new_100_anchor_id = $this->redis->get(Anchor::NEW_100_ANCHOR_ID_KEY);
                    if ( !$new_100_anchor_id ) {
                        // 如果缓存中没有 那么则需要取第100个
                        $oAnchor           = Anchor::findFirst([
                            "anchor_is_show_index = 'Y'",
                            'order' => 'anchor_id desc',
                            'limit' => [
                                1,
                                99
                            ]
                        ]);
                        $new_100_anchor_id = 0;
                        if ( $oAnchor ) {
                            $new_100_anchor_id = $oAnchor->anchor_id;
                        }
                        $this->redis->set(Anchor::NEW_100_ANCHOR_ID_KEY, $new_100_anchor_id);
                        $this->redis->expire(Anchor::NEW_100_ANCHOR_ID_KEY, 60 * 60);
                    }
                    $where .= " AND anchor_is_show_index = 'Y' AND anchor_id >= $new_100_anchor_id";
                    $order = 'anchor_chat_status desc,anchor_is_newhot,rand()';
                    break;
                case 'hot':
                    $where .= ' AND a.anchor_hot_time > 0';
                    break;
                case 'follow':
                    $where .= ' AND uf.user_follow_id is not null';
                    break;
                case 'index':
                default:
                    $where .= " and a.anchor_is_show_index = 'Y'";
            }
            if ( $exameFlg ) {
                $row              = $this->getExamineAnchorList($nUserId, $where, $type);
                $row['pagetotal'] = 1;
            } else {
                $where          .= ' AND anchor_is_examine = 0';
                $shareUrlPrefix = APP_WEB_URL . '/sharePrivateChat?user_id=';
                $colimns        = "u.user_birth,u.user_nickname,u.user_avatar,u.user_level,user_intro,user_profession,anchor_chat_status,u.user_id,u.user_sex,
            a.anchor_video_cover as user_video_cover,anchor_video as user_video,ifnull(uf.user_follow_id,0) as is_follow,
            anchor_tip,anchor_character,anchor_good_topic,anchor_dress,anchor_stature,
            anchor_emotional_state,anchor_chat_price price,anchor_private_local anchor_local,anchor_private_title anchor_title,
            concat('$shareUrlPrefix',u.user_id) as share_url,a.anchor_guard_id,0 as today_guard_free_times,a.anchor_level,a.anchor_title_id,anchor_custom_title";
                $builder        = $this->modelsManager->createBuilder()->from([ 'a' => Anchor::class ])
                    ->join(User::class, 'u.user_id=a.user_id', 'u')
                    ->leftJoin(UserFollow::class, 'u.user_id = uf.to_user_id AND uf.user_id = ' . $nUserId, 'uf')
                    ->where($where)->columns($colimns)->orderby($order);

                $row = $this->page($builder, $page, $pagesize);

                $userIdS = [];

                foreach ( $row['items'] as &$item ) {
                    $userIdS[] = $item['user_id'];
                    if ( $item['anchor_guard_id'] == $nUserId ) {
                        $item['today_guard_free_times'] = (string)UserGuard::getTodayFreeTimes($item['user_id'], $nUserId);
                    }
                    $anchorTitleInfo             = AnchorTitleConfig::getInfo($item['anchor_title_id']);
                    $item['anchor_title_number'] = $anchorTitleInfo['number'];
                    $item['anchor_title_name']   = $anchorTitleInfo['name'];
                    unset($item['anchor_title_id']);

                }

                $this->getDump([
                    'old'        => count($userIdS),
                    'old_unique' => count(array_unique($userIdS)),
                ]);

                if ( $type == 'nearby' ) {
                    $moreData = $this->getNearbyAnchor($localCity, $nUserId);
                    foreach ( $moreData['items'] as $moreItem ) {
                        if ( $moreItem['anchor_guard_id'] == $nUserId ) {
                            $moreItem['today_guard_free_times'] = (string)UserGuard::getTodayFreeTimes($moreItem['user_id'], $nUserId);
                        }
                        $anchorTitleInfo             = AnchorTitleConfig::getInfo($item['anchor_title_id']);
                        $item['anchor_title_number'] = $anchorTitleInfo['number'];
                        $item['anchor_title_name']   = $anchorTitleInfo['name'];
                        unset($item['anchor_title_id']);
                        $row['items'][] = $moreItem;
                    }
                }

                if ( Kv::get(Kv::ANCHOR_OFFLINE_MODIFY) == 'Y' ) {

                    $onlineData  = [];
                    $chatData    = [];
                    $offlineData = [];
                    foreach ( $row['items'] as &$item ) {
//                    echo $item['user_id'] . "-----";
                        switch ( $item['anchor_chat_status'] ) {
                            case '0':
                            case '1':
                                // 不在线  取假状态显示
                                $oAnchorOfflineModifyService = new AnchorOfflineModifyService(AnchorOfflineModifyService::TYPE_OFFLINE);
                                $exist                       = $oAnchorOfflineModifyService->getItem($item['user_id']);
                                if ( $exist ) {
                                    $item['anchor_chat_status'] = '1';
                                    $offlineData[]              = $item;
                                    break;
//                                echo "flg 1 ---------";
                                } else {
                                    // 判断当前是否为 假聊天
                                    $oAnchorOfflineModifyServiceOnChat = new AnchorOfflineModifyService(AnchorOfflineModifyService::TYPE_ON_CHAT);
                                    $exist                             = $oAnchorOfflineModifyServiceOnChat->getItem($item['user_id']);
                                    if ( $exist ) {
                                        $item['anchor_chat_status'] = '2';
                                        $chatData[]                 = $item;
                                        break;
//                                    echo "flg 2 ---------";
                                    } else {
                                        // 判断当前是否为假在线
                                        $oAnchorOfflineModifyServiceOnline = new AnchorOfflineModifyService(AnchorOfflineModifyService::TYPE_ONLINE);
                                        $exist                             = $oAnchorOfflineModifyServiceOnline->getItem($item['user_id']);
                                        if ( $exist ) {
                                            $item['anchor_chat_status'] = '3';
                                            $onlineData[]               = $item;
                                            break;
//                                        echo "flg 3 ---------";
                                        } else {
                                            //  都没取到 则存入在线内
//                                        if ( $item['anchor_chat_status'] == '1' ) {
                                            $item['anchor_chat_status'] = rand(2, 3);
                                            if ( $item['anchor_chat_status'] == 2 ) {
                                                $oAnchorOfflineModifyServiceOnChat->save($item['user_id']);
                                            } else {
                                                $oAnchorOfflineModifyService->save($item['user_id']);
                                            }
                                            $onlineData[] = $item;
                                            break;
//                                            echo "flg 3 ---------";
//                                        }
                                        }
                                    }
                                }
                                break;
                            case '2':
                                $chatData[] = $item;
                                break;
                            case '3':
                                $onlineData[] = $item;
                                break;
                        }
//                    echo "anchor_chat_status :{$item['anchor_chat_status']} <br />";
                    }
                    $row['items'] = array_merge($onlineData, $chatData, $offlineData);
//                var_dump(count($onlineData),count($chatData),count($offlineData),count($row['items']));die;
                    $this->getDump([
                        'new'        => count($row['items']),
                        'new_unique' => count(array_unique(array_column($row['items'], 'user_id'))),
                    ]);

                }

            }
        } catch ( Exception $e ) {
            $this->error(ResponseError::FAIL, sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage()));
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/getSearchRecommend
     * @api {get} /live/anchor/getSearchRecommend 获取搜索推荐主播
     * @apiName anchor-getSearchRecommend
     * @apiGroup Index
     * @apiDescription 获取搜索推荐主播 获取5个热门推荐，5个新人推荐
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {String} d.anchor_character 性格标签
     * @apiSuccess {String} d.anchor_dress 爱穿标签
     * @apiSuccess {String} d.anchor_stature 身材标签
     * @apiSuccess {object[]} d.hot_anchor  热门主播推荐
     * @apiSuccess {String} d.hot_anchor.user_nickname
     * @apiSuccess {String} d.hot_anchor.user_avatar
     * @apiSuccess {String} d.hot_anchor.user_level
     * @apiSuccess {number} d.hot_anchor.user_id
     * @apiSuccess {number} d.hot_anchor.is_follow
     * @apiSuccess {object[]} d.new_anchor   新人主播推荐
     * @apiSuccess {String} d.new_anchor.user_nickname
     * @apiSuccess {String} d.new_anchor.user_avatar
     * @apiSuccess {String} d.new_anchor.user_level
     * @apiSuccess {number} d.new_anchor.user_id
     * @apiSuccess {number} d.new_anchor.is_follow
     * @apiSuccess {object[]} d.user   用户数据
     * @apiSuccess {String} d.user.user_nickname
     * @apiSuccess {String} d.user.user_avatar
     * @apiSuccess {String} d.user.user_level
     * @apiSuccess {number} d.user.user_id
     * @apiSuccess {number} d.user.is_follow
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *         {
     *             "c": 0,
     *             "m": "请求成功",
     *             "d": {
     *                  "anchor_character": "温柔,可爱,热情,开放,幽默,好动,可亲,文静",
     *                  "anchor_dress": "丝袜,护士装,女仆装,空姐装,超短裙,露背装,透视装,抹胸装,热裤,蕾丝,渔网袜,豹纹",
     *                  "anchor_stature": "S型,丰满,高挑,苗条,大长腿,波霸,蜜桃臀,水蛇腰",
     *                 "hot_anchor": [{
     *                      "user_nickname": "冯千瑞",
     *                      "user_avatar": "http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/Q0j4TwGTfTKp70DaNwXvRIW0AD6UBQGfGRF80xwb8Kdx6xM1H9iagTT5QKjic3GYRpzVynLiaBibhURmib96yhIQTAg\/132",
     *                      "user_level: "1",
     *                      "user_id": "255",
     *                      "is_follow": "0",
     *                 }],
     *                  "new_anchor": [{
     *                      "user_nickname": "冯千瑞",
     *                      "user_avatar": "http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/Q0j4TwGTfTKp70DaNwXvRIW0AD6UBQGfGRF80xwb8Kdx6xM1H9iagTT5QKjic3GYRpzVynLiaBibhURmib96yhIQTAg\/132",
     *                      "user_level: "1",
     *                      "user_id": "255",
     *                      "is_follow": "0",
     *                 }],
     *                  "user": [{
     *                      "user_nickname": "Dawn11151739029",
     *                    "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/9012BAEA9B36E6AE8846D0EFE9C05A13\/100",
     *                      "user_level: "1",
     *                    "user_id": "308",
     *                    "is_follow": "0"
     *                  }]
     *             },
     *             "t": "1545619030"
     *         }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function getSearchRecommendAction( $nUserId = 0 )
    {

        try {
            $oUser = User::findFirst($nUserId);
            if ( $oUser->user_is_anchor == 'N' ) {
                $userData = [];
                // 先取5个热门  再取10个新人热推  遍历新人热推 取5个不在取出的热门中的主播
                $where      = 'a.anchor_hot_time > 0';
                $columns    = "u.user_nickname,u.user_avatar,u.user_level,u.user_id,ifnull(uf.user_follow_id,0) as is_follow";
                $builder    = $this->modelsManager->createBuilder()->from([ 'a' => Anchor::class ])
                    ->join(User::class, 'u.user_id=a.user_id', 'u')
                    ->leftJoin(UserFollow::class, 'u.user_id = uf.to_user_id AND uf.user_id = ' . intval($nUserId), 'uf')
                    ->where($where)->columns($columns)->orderby('rand()');
                $hotRow     = $this->page($builder, 1, 5);
                $hotData    = $hotRow['items'];
                $hotUserIds = array_column($hotData, 'user_id');

                // 新人热推
                $where   = 'a.anchor_is_newhot = "Y"';
                $columns = "u.user_nickname,u.user_avatar,u.user_level,u.user_id,ifnull(uf.user_follow_id,0) as is_follow";
                $builder = $this->modelsManager->createBuilder()->from([ 'a' => Anchor::class ])
                    ->join(User::class, 'u.user_id=a.user_id', 'u')
                    ->leftJoin(UserFollow::class, 'u.user_id = uf.to_user_id AND uf.user_id = ' . intval($nUserId), 'uf')
                    ->where($where)->columns($columns)->orderby('rand()');
                $newRow  = $this->page($builder, 1, 10);
                $newArr  = $newRow['items'];
                $newData = [];
                foreach ( $newArr as $newItem ) {
                    if ( in_array($newItem['user_id'], $hotUserIds) ) {
                        continue;
                    }
                    $newData[] = $newItem;
                }
            } else {
                $hotData  = [];
                $newData  = [];
                $where    = 'a.invitee_id = ' . intval($nUserId);
                $columns  = "u.user_nickname,u.user_avatar,u.user_level,u.user_id,ifnull(uf.user_follow_id,0) as is_follow";
                $builder  = $this->modelsManager->createBuilder()
                    ->from([ 'a' => UserPrivateChatDialog::class ])
                    ->join(User::class, 'u.user_id=a.inviter_id', 'u')
                    ->leftJoin(UserFollow::class, 'u.user_id = uf.to_user_id AND uf.user_id = ' . intval($nUserId), 'uf')
                    ->where($where)->columns($columns)
                    ->orderby('a.update_time desc');
                $userRow  = $this->page($builder, 1, 5);
                $userData = $userRow['items'];
            }


            // 获取性格标签，爱穿，身材
            $oUserProfileSetting = UserProfileSetting::find([
                "profile_key in ('anchor_character','anchor_dress','anchor_stature')",
                'columns' => 'profile_key,profile_select'
            ])->toArray();
            $row                 = array_column($oUserProfileSetting, 'profile_select', 'profile_key');
            $row['hot_anchor']   = $hotData;
            $row['new_anchor']   = $newData;
            $row['user']         = $userData;

        } catch ( Exception $e ) {
            $this->error(ResponseError::FAIL, sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage()));
        }
        $this->success($row);
    }


    /**
     * 获取附近主播
     */
    private function getNearbyAnchor( $localCity = '', $nUserId = '' )
    {
        $where          = " a.anchor_video != '' and a.anchor_private_forbidden = 0 AND anchor_chat_status = 1 and a.anchor_is_show_index = 'Y'";
        $shareUrlPrefix = APP_WEB_URL . '/sharePrivateChat?user_id=';
        $colimns        = "u.user_birth,u.user_nickname,u.user_avatar,u.user_level,user_intro,user_profession,anchor_chat_status,u.user_id,u.user_sex,
            a.anchor_video_cover as user_video_cover,anchor_video as user_video,ifnull(uf.user_follow_id,0) as is_follow,
            anchor_tip,anchor_character,anchor_good_topic,anchor_dress,anchor_stature,
            anchor_emotional_state,anchor_chat_price price,anchor_private_local anchor_local,anchor_private_title anchor_title,
            concat('$shareUrlPrefix',u.user_id) as share_url,a.anchor_guard_id,0 as today_guard_free_times,a.anchor_level,a.anchor_title_id,anchor_custom_title";
        $builder        = $this->modelsManager->createBuilder()->from([ 'a' => Anchor::class ])
            ->join(User::class, 'u.user_id=a.user_id', 'u')
            ->leftJoin(UserFollow::class, 'u.user_id = uf.to_user_id AND uf.user_id = ' . $nUserId, 'uf')
            ->where($where)->columns($colimns)->orderby("if(anchor_private_local='{$localCity}',0,1)");
        return $this->page($builder, 1, 15);
    }


    /**
     * 获取审核主播信息
     */
    private function getExamineAnchorList( $nUserid, $where, $type = 'all' )
    {
        $appInfo = $this->getAppInfo('qq');
        $where   .= ' AND anchor_is_examine = ' . $appInfo['id'];
//        $cacheKey       = 'examine_anchor:' . $type . ':' . $appInfo['app_flg'];
//        $examine_anchor = $this->redis->get($cacheKey);
//        if ( $examine_anchor ) {
//            return json_decode($examine_anchor, TRUE);
//        } else {
        $where          .= ' AND a.anchor_is_examine > 0';
        $shareUrlPrefix = APP_WEB_URL . '/sharePrivateChat?user_id=';
        $colimns        = "u.user_birth,u.user_nickname,u.user_avatar,u.user_level,user_intro,user_profession,anchor_chat_status,u.user_id,u.user_sex,
            a.anchor_video_cover as user_video_cover,anchor_video as user_video,ifnull(uf.user_follow_id,0) as is_follow,
            anchor_tip,anchor_character,anchor_good_topic,anchor_dress,anchor_stature,
            anchor_emotional_state,anchor_chat_price price,anchor_private_local anchor_local,anchor_private_title anchor_title,
            concat('$shareUrlPrefix',u.user_id) as share_url,a.anchor_guard_id,0 as today_guard_free_times,a.anchor_title_id,anchor_custom_title,a.anchor_level";
        $builder        = $this->modelsManager->createBuilder()->from([ 'a' => Anchor::class ])
            ->join(User::class, 'u.user_id=a.user_id', 'u')
            ->leftJoin(UserFollow::class, 'u.user_id = uf.to_user_id AND uf.user_id = ' . $nUserid, 'uf')
            ->where($where)->columns($colimns);

        $this->getDump($builder->getPhql());
        $row = $this->page($builder, 1, 100);

        $data = $row['items'];
        // 空闲主播
        $freeAnchor = [];
        // 忙碌主播
        $chatAnchor = [];
        // 离线主播
        $offlineAnchor = [];

        foreach ( $data as $item ) {
            $rand = rand(0, 2);
            if ( $rand == 0 && count($freeAnchor) >= 10 ) {
                $rand = rand(1, 2);
                if ( $rand == 1 && count($chatAnchor) >= 4 ) {
                    $rand = 2;
                }
            } else if ( $rand == 1 && count($chatAnchor) >= 4 ) {
                $rand = 2;
            }
            switch ( $rand ) {
                case 0:
                    $item['anchor_chat_status'] = '3';
                    $freeAnchor[]               = $item;
                    break;
                case 1:
                    $item['anchor_chat_status'] = '2';
                    $chatAnchor[]               = $item;
                    break;
                case 2:
                    $item['anchor_chat_status'] = '1';
                    $offlineAnchor[]            = $item;
                    break;
            }
            $anchorTitleInfo             = AnchorTitleConfig::getInfo($item['anchor_title_id']);
            $item['anchor_title_number'] = $anchorTitleInfo['number'];
            $item['anchor_title_name']   = $anchorTitleInfo['name'];
            unset($item['anchor_title_id']);
        }
        $row['items'] = array_merge($freeAnchor, $chatAnchor, $offlineAnchor);
//            $this->redis->set($cacheKey, json_encode($row), 600);
//        }
        return $row;

    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/getAnchorInfo
     * @api {get} /live/anchor/getAnchorInfo 获取主播个人主页信息
     * @apiName getAnchorInfo
     * @apiGroup Anchor
     * @apiDescription 获取主播个人主页信息
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} user_id 主播用户id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} user_id 主播用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {number} d.user_nickname   昵称
     * @apiSuccess {String} d.user_avatar  头像
     * @apiSuccess {String} d.user_level  用户等级
     * @apiSuccess {number} d.user_sex 性别
     * @apiSuccess {number} d.anchor_ranking  排名
     * @apiSuccess {number} d.user_id  用户id
     * @apiSuccess {String} d.is_follow 是否关注
     * @apiSuccess {String} d.user_intro  简介
     * @apiSuccess {String} d.user_constellation  用户星座
     * @apiSuccess {String} d.user_emotional_state 用户情感
     * @apiSuccess {String} d.coin_name  虚拟币名称
     * @apiSuccess {String} d.user_hobby  用户爱好
     * @apiSuccess {String} d.user_profession 用户职业
     * @apiSuccess {number} d.price  价格
     * @apiSuccess {String} d.anchor_local  地址
     * @apiSuccess {number} d.anchor_title 标题
     * @apiSuccess {String} d.user_video  视频
     * @apiSuccess {String} d.user_video_cover 视频封面
     * @apiSuccess {String} d.anchor_video_check_status  视频审核状态
     * @apiSuccess {String} d.anchor_image_check_status   图片审核状态
     * @apiSuccess {object} d.anchor_info  主播信息
     * @apiSuccess {String} d.anchor_info.anchor_guard_id  守护id
     * @apiSuccess {String} d.anchor_info.anchor_tip  标签
     * @apiSuccess {String} d.anchor_info.anchor_character  个性
     * @apiSuccess {String} d.anchor_info.anchor_good_topic 擅长
     * @apiSuccess {String} d.anchor_info.anchor_dress  爱穿
     * @apiSuccess {String} d.anchor_info.anchor_stature 身材
     * @apiSuccess {String} d.anchor_info.anchor_images  图片
     * @apiSuccess {String} d.anchor_info.anchor_check_img 审核图片
     * @apiSuccess {String} d.anchor_info.anchor_emotional_state  主播情感
     * @apiSuccess {number} d.anchor_info.anchor_connection_rate  接通率
     * @apiSuccess {String} d.app_share_url
     * @apiSuccess {number} d.no_income_free_time  剩余不计算收益的免费时长数
     * @apiSuccess {String='guard(守护)'} d.no_income_free_time_type  不计算收益的免费时长类型
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
    public function getAnchorInfoAction( $nUserId )
    {
        $user_id = $this->getParams('user_id');
        if ( empty($user_id) ) {
            $this->error(10002);
        }
        $user = User::findFirst([
            'user_id = :user_id:',
            'bind' => [ 'user_id' => $user_id ]
        ]);
        if ( !$user_id ) {
            $this->error(10002);
        }
        $oAnchor   = Anchor::findFirst("user_id={$user_id}");
        $follow    = UserFollow::findFirst([
            "user_id = :user_id: and to_user_id = :to_user_id:",
            'bind' => [
                'user_id'    => $nUserId,
                'to_user_id' => $user->user_id
            ]
        ]);
        $coin_name = Kv::get(Kv::KEY_COIN_NAME);
        $return    = [
            'user_nickname'             => $user->user_nickname,
            'user_avatar'               => $user->user_avatar,
            'user_level'                => $user->user_level,
            'user_sex'                  => $user->user_sex,
            'anchor_ranking'            => $oAnchor->anchor_ranking,
            'user_id'                   => $user->user_id,
            'is_follow'                 => $follow ? TRUE : FALSE,
            'user_intro'                => $user->user_intro,
            'user_constellation'        => $user->user_constellation,
            'user_emotional_state'      => $user->user_emotional_state,
            'coin_name'                 => $coin_name,
            'user_hobby'                => $user->user_hobby,
            'user_profession'           => $user->user_profession,
            'price'                     => $oAnchor->anchor_chat_price,
            'anchor_local'              => $oAnchor->anchor_private_local,
            'anchor_title'              => $oAnchor->anchor_private_title,
            'user_video'                => $oAnchor->anchor_video,
            'user_video_cover'          => $oAnchor->anchor_video_cover,
            'anchor_video_check_status' => $oAnchor->anchor_video_check_status,
            'anchor_image_check_status' => $oAnchor->anchor_image_check_status,
            'anchor_info'               => [
                'anchor_tip'             => $oAnchor->anchor_tip,
                'anchor_guard_id'        => $oAnchor->anchor_guard_id,
                'anchor_character'       => $oAnchor->anchor_character,
                'anchor_good_topic'      => $oAnchor->anchor_good_topic,
                'anchor_dress'           => $oAnchor->anchor_dress,
                'anchor_stature'         => $oAnchor->anchor_stature,
                'anchor_images'          => $oAnchor->anchor_images,
                'anchor_check_img'       => $oAnchor->anchor_check_img,
                'anchor_emotional_state' => $oAnchor->anchor_emotional_state,
                //                'anchor_connection_rate' => sprintf('%.2f', $oAnchor->anchor_called_count == 0 ? 100 : $oAnchor->anchor_chat_count / $oAnchor->anchor_called_count * 100),
                'anchor_connection_rate' => $oAnchor->getConnectionRate(),
                'anchor_dispatch_flg'    => $oAnchor->anchor_dispatch_flg,
            ],
            'app_share_url'             => APP_WEB_URL . '/sharePrivateChat?user_id=' . $user_id,
            'no_income_free_time'       => (string)UserGuard::getTodayFreeTimes($user->user_id, $nUserId),
            'no_income_free_time_type'  => UserPrivateChatLog::FREE_TIME_TYPE_GUARD
        ];
        $this->success($return);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/startPrivateChat
     * @api {get} /live/anchor/startPrivateChat 邀请一对一聊天
     * @apiName startPrivateChat
     * @apiGroup Chat
     * @apiDescription 邀请一对一聊天
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} user_id 主播用户id
     * @apiParam (正常请求){String} dialog_id 消息id（回拨时传）
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} user_id 主播用户id
     * @apiParam (debug){String} dialog_id 消息id（回拨时传）
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.push_url 腾讯云推流地址
     * @apiSuccess {string} d.chat_log 聊天id
     * @apiSuccess {Object} d.wangsu 网宿内容
     * @apiSuccess {string} d.wangsu.push_url 推流地址
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     *  邀请私聊推送
     *    {
     *        "type": "private_chat",
     *        "msg": "私聊消息",
     *        "data": {
     *                "no_income_free_time": "10",   // 剩余的不计算收益的免费时长数
     *                "no_income_free_time_type": "guard",   // 剩余的不计算收益的免费时长类型 guard ：守护
     *                "is_user_call": "Y",
     *                "chat_log": "4493",
     *                "f_user_id": "318",
     *                "f_user_nickname": "渐入佳境",
     *                "f_user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1107915107\/63F3F098E6FAC4B5C210CA2458C66BE6\/100",
     *                "f_user_level": "1",
     *                "anchor_video_url": "https:\/\/lebolive-1255651273.image.myqcloud.com\/video\/2018\/11\/27\/output-2018-11-27-17:45:40-643.mp4",
     *                "is_free_match_flg": "Y",
     *                "free_match_over_time": "20",
     *                "play_rtmp": "rtmp:\/\/tencent.play.sxypaopao.com\/live\/34574_yuyin_318_4493_2?bizid=34574&txSecret=eee9e2bd39ffd77590adb7d1b9bf600d&txTime=5C07F5FF",
     *                "play_flv": "http:\/\/tencent.play.sxypaopao.com\/live\/34574_yuyin_318_4493_2.flv?bizid=34574&txSecret=eee9e2bd39ffd77590adb7d1b9bf600d&txTime=5C07F5FF",
     *                "play_m3u8": "http:\/\/tencent.play.sxypaopao.com\/live\/34574_yuyin_318_4493_2.m3u8?bizid=34574&txSecret=eee9e2bd39ffd77590adb7d1b9bf600d&txTime=5C07F5FF",
     *                "wangsu": {
     *                    "push_url": ""
     *                }
     *        }
     *    }
     *
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {私聊_
     *       "error": "UserNotFound"
     *     }
     */
    public function startPrivateChatAction( $nUserId = 0 )
    {
        $sToUserId = $this->getParams('user_id');
        $msgId     = $this->getParams('dialog_id');

        try {
            $this->forceUpdate($nUserId);
            if ( empty($sToUserId) ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            // 主播可以邀请用户，用户可以邀请主播
            $oToUser = User::findFirst($sToUserId);
            if ( !$oToUser ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $ownerUser = User::findFirst($nUserId);
            if ( $ownerUser->user_is_superadmin == 'S' ) {
                throw new Exception('该账号暂不支持此功能哦', ResponseError::PARAM_ERROR);
            }
            if ( $ownerUser->user_is_anchor == $oToUser->user_is_anchor ) {
                // 两者角色相同 不能通话
                if ( $ownerUser->user_is_anchor == 'Y' ) {
                    throw new Exception(ResponseError::getError(ResponseError::FORBIDDEN_ANCHOR_CALL_ANCHOR), ResponseError::FORBIDDEN_ANCHOR_CALL_ANCHOR);
                } else {
                    throw new Exception(ResponseError::getError(ResponseError::FORBIDDEN_USER_CALL_USER), ResponseError::FORBIDDEN_USER_CALL_USER);
                }
            }
            $payUser          = $ownerUser;
            $anchorUserId     = $oToUser->user_id;
            $oAnchorUser      = $oToUser;
            $isUserCall       = TRUE;
            $callbackUserChat = NULL;
            $userChatExtraArr = [];
            if ( $ownerUser->user_is_anchor == 'Y' ) {
                // 自己是主播

                // 判断对方是否 接受被拨打
                $toUserSet = UserSet::findFirst($oToUser->user_id);
                if ( $toUserSet && $toUserSet->user_get_call_flg == 'N' ) {
                    throw new Exception('对方已设置不能被拨打', ResponseError::OPERATE_FAILED);
                }


                $payUser      = $oToUser;
                $anchorUserId = $ownerUser->user_id;
                $oAnchorUser  = $ownerUser;
                $isUserCall   = FALSE;

                if ( $msgId ) {
                    // 回拨
                    $oUserChat        = UserChat::findFirst($msgId);
                    $callbackUserChat = $oUserChat;
                    if ( !$oUserChat || !$oUserChat->user_chat_extra ) {
                        throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
                    }
                    $userChatExtraArr = unserialize($oUserChat->user_chat_extra);

                    $extraHasCallback = $userChatExtraArr['video_chat_has_callback'] ?? 'Y';
                    if ( $extraHasCallback == 'Y' ) {
                        //
                        throw new Exception('已经回拨过了', ResponseError::PARAM_ERROR);
                    }
                } else {
                    // 正常呼叫
                }


            } else {
                $oUserVideoChatService = new UserVideoChatService();
                $oUserVideoChatService->delete($nUserId);
            }

            // 被对方拉黑
            $oUserBlack = UserBlack::findFirst([
                'user_id = :user_id: and to_user_id = :to_user_id:',
                'bind' => [
                    'user_id'    => $sToUserId,
                    'to_user_id' => $nUserId
                ]
            ]);
            if ( $oUserBlack ) {
                throw new Exception(ResponseError::getError(ResponseError::HAS_BEEN_BLACKED), ResponseError::HAS_BEEN_BLACKED);
            }

            $oAnchor = Anchor::findFirst([
                'user_id = :user_id:',
                'bind' => [ 'user_id' => $anchorUserId ]
            ]);
            if ( $oAnchor->anchor_private_forbidden == 1 ) {
                // 主播被禁播
                throw new Exception(ResponseError::getError(ResponseError::CHAT_FORBIDDEN), ResponseError::CHAT_FORBIDDEN);
            }

            if ( $oToUser->user_online_status == 'Offline' ) {
                // 判断对方是否在线
                if ( $isUserCall && !in_array($oAnchor->anchor_chat_status, [
                        0,
                        1
                    ]) ) {
                    // 用户拨打 发现对方主播不在线
                    $oAnchor->anchor_chat_status = 1;
                    $oAnchor->save();
                }
                throw new Exception(ResponseError::getError(ResponseError::NOT_ONLINE), ResponseError::NOT_ONLINE);
            }

            if ( $isUserCall ) {
                // 用户拨打 判断主播状态
                if ( $oAnchor->anchor_chat_status == 0 ) {
                    throw new Exception(ResponseError::getError(ResponseError::NOT_ONLINE), ResponseError::NOT_ONLINE);
                }
                if ( $oAnchor->anchor_chat_status == 1 ) {
                    throw new Exception(ResponseError::getError(ResponseError::NOT_ONLINE), ResponseError::NOT_ONLINE);
                }

                // 添加主播的版本判断
                $user_version = $this->redis->hGet('user_app_version', $sToUserId);
                if ( $user_version ) {
                    if ( $this->checkVersionMatch($user_version) == FALSE ) {
                        throw new Exception('主播APP版本过低，暂时不能和您聊天，请换个小姐姐或通知她更新版本哦', ResponseError::PARAM_ERROR);
                    }
                }
            }
            // 修改用户状态
            $oUserVideoChatService = new UserVideoChatService();
            $flg                   = $oUserVideoChatService->save($payUser->user_id);
            if ( !$flg ) {
                //   正在聊天
                throw new Exception(ResponseError::getError(ResponseError::IS_BUSYING), ResponseError::IS_BUSYING);
            }

            // 判断是否为守护 并且当日还剩余守护免费时长
//            $hssGuardFreeTimes = 0;
            $isGuardUser = 'N';
//            if ( $oAnchor->anchor_guard_id == $payUser->user_id ) {
//                $hssGuardFreeTimes = UserGuard::getTodayFreeTimes($oAnchor->user_id, $payUser->user_id);
//                $isGuardUser       = 'Y';
//            }
            $hssGuardFreeTimes = UserGuard::getTodayFreeTimes($oAnchor->user_id, $payUser->user_id);
            if ( $hssGuardFreeTimes > 0 ) {
                $isGuardUser = 'Y';
            }

            if ( $hssGuardFreeTimes == 0 && $isUserCall && $payUser->user_coin + $payUser->user_free_coin < $oAnchor->anchor_chat_price ) {
                // 如果是用户拨打则需要判断是否有钱
                throw new Exception(ResponseError::getError(ResponseError::USER_COIN_NOT_ENOUGH), ResponseError::USER_COIN_NOT_ENOUGH);
            } elseif ( !$isUserCall && !$msgId && $payUser->user_coin + $payUser->user_free_coin < $oAnchor->anchor_chat_price ) {
                // 如果是主播拨打 且不是回拨 择需要判断用户是否有钱
                throw new Exception(ResponseError::getError(ResponseError::USER_COIN_NOT_ENOUGH), ResponseError::USER_COIN_NOT_ENOUGH);
            }


            $oUserPrivateChatLog                          = new UserPrivateChatLog();
            $oUserPrivateChatLog->is_user_call            = $isUserCall ? 'Y' : 'N';
            $oUserPrivateChatLog->chat_log_user_id        = $payUser->user_id;
            $oUserPrivateChatLog->chat_log_anchor_user_id = $oAnchor->user_id;
            $oUserPrivateChatLog->inviter_id              = $nUserId;
            $oUserPrivateChatLog->invitee_id              = $sToUserId;
            $oUserPrivateChatLog->free_times_type         = $hssGuardFreeTimes > 0 ? UserPrivateChatLog::FREE_TIME_TYPE_GUARD : UserPrivateChatLog::FREE_TIME_TYPE_EMPTY;
            $oUserPrivateChatDialog                       = new UserPrivateChatDialog();
            $dialog_id                                    = $oUserPrivateChatDialog->getDialogId($nUserId, $sToUserId);
            if ( $isUserCall && $oAnchor->anchor_chat_status == 2 ) {
                // 判断主播是否真正在聊天 或真正有人在邀请她
                // 判断是否有在通话的记录 或者是否有状态为0的并且在90秒内的
                $existChat = UserPrivateChatLog::findFirst([
                    'chat_log_anchor_user_id = :anchor_id: AND (status = 4 or (status = 0 AND create_time > :create_time:))',
                    'bind' => [
                        'anchor_id'   => $sToUserId,
                        'create_time' => time() - 90
                    ]
                ]);
                if ( $existChat ) {
                    $oUserPrivateChatLog->addData($nUserId, $sToUserId, 3, $dialog_id);
                    throw new Exception(ResponseError::getError(ResponseError::IS_BUSYING), ResponseError::IS_BUSYING);
                }
            }
            $oAnchor->anchor_chat_status = 2;
            $oAnchor->save();
            $dialog         = UserPrivateChatDialog::findFirst($dialog_id);
            $dialog->status = 1;
            $dialog->save();

            // 如果主播是签约主播 且当前是签约时段
            if ( $oAnchor->anchor_is_sign == 'Y' ) {
                AnchorSignStat::signAnchorStatAdd($oAnchorUser, $oAnchor, AnchorSignStat::TYPE_CALLED);
            }

            $addDataReturn = $oUserPrivateChatLog->addData($nUserId, $sToUserId, 0, $dialog_id, UserPrivateChatLog::CHAT_TYPE_NORMAL, 'N', TRUE);
            $chat_log      = $addDataReturn['id'];
            $chatPushData  = $addDataReturn['push'];

            $this->liveServer->setStreamName($nUserId . '_' . $chat_log . '_2');
            //TIM发送信号
            $push_url     = $this->liveServer->pushUrl();
            $aPushMessage = [
                'is_say_hi'                => $chatPushData['dialog']['is_say_hi'],
                'is_guard_user'            => $isGuardUser,
                'no_income_free_time'      => $hssGuardFreeTimes,
                'no_income_free_time_type' => $oUserPrivateChatLog->free_times_type,
                'is_user_call'             => $isUserCall ? 'Y' : 'N',
                'chat_log'                 => $chat_log,
                'f_user_id'                => $nUserId,
                'f_user_nickname'          => $ownerUser->user_nickname,
                'f_user_avatar'            => $ownerUser->user_avatar,
                'f_user_level'             => $ownerUser->user_level,
                'anchor_video_url'         => $oAnchor->anchor_video,
                'price'                    => $payUser->getVip1V1VideoPrice($oAnchor->anchor_chat_price),
                'is_free_match_flg'        => 'N',
                'free_match_over_time'     => 0,
                'play_rtmp'                => $this->liveServer->playUrl('rtmp'),
                'play_flv'                 => $this->liveServer->playUrl('flv'),
                'play_m3u8'                => $this->liveServer->playUrl('m3u8'),
                'wangsu'                   => [
                    'push_url' => ''
                ]
            ];
            $appInfo      = $this->getAppInfo('qq', $oToUser->user_app_flg ? $oToUser->user_app_flg : 'tianmi');
            $jPush        = new JiGuangApi($appInfo['jpush_app_key'], $appInfo['jpush_master_secret'], NULL, APP_ENV == 'dev' ? FALSE : TRUE);
            $res          = $jPush->push([ 'alias' => [ "{$sToUserId}" ] ], '视频消息', "【{$ownerUser->user_nickname}】邀请您进行快聊", [
                'type'    => 'private_chat',
                'chat_id' => $chat_log
            ]);
            $this->timServer->setUid($sToUserId);
            $this->timServer->sendPrivateChat($aPushMessage);
            $this->log->info($nUserId . '邀请 ' . $sToUserId . '推送:' . json_encode($aPushMessage));


            // 如果传入了 dialog_id 则需要将 记录改为已回拨
            if ( $msgId && $callbackUserChat ) {
                $userChatExtraArr['video_chat_has_callback'] = 'Y';
                $callbackUserChat->user_chat_extra           = serialize($userChatExtraArr);
                $callbackUserChat->save();
            }

            $return = [
                'is_guard_user'            => $isGuardUser,
                'no_income_free_time'      => $hssGuardFreeTimes,
                'no_income_free_time_type' => $oUserPrivateChatLog->free_times_type,
                'push_url'                 => $push_url['push_url'],
                'chat_log'                 => $chat_log,
                'price'                    => $payUser->getVip1V1VideoPrice($oAnchor->anchor_chat_price),
                'wangsu'                   => [
                    'push_url' => ''
                ]
            ];
            //1.6.1版本存入邀请用户的app版本号
            $this->redis->hSet('user_app_version', $nUserId, $this->getParams('app_version'));

            $anchorList = $this->modelsManager->createBuilder()
                ->from([ 'a' => Anchor::class ])
                ->join(User::class, 'u.user_id = a.user_id', 'u')
                ->columns('u.user_id,u.user_nickname,u.user_avatar')
                ->where('a.anchor_hot_time > 0 AND a.anchor_chat_status = 3')
                ->orderBy('rand()')
                ->limit(20, 0)->getQuery()->execute()->toArray();

            // 推送到语聊房  谁谁谁被谁谁谁带走了
            $this->timServer->setUid('');
            $this->timServer->setRid(Room::B_CHAT_ID);
            $this->timServer->sendRoomStartVideoChatSignal([
                'room_id'              => Room::B_CHAT_ID,
                'anchor_user_id'       => $anchorUserId,
                'anchor_user_nickname' => $oAnchorUser->user_nickname,
                'user_id'              => $payUser->user_id,
                'user_nickname'        => $payUser->user_nickname,
                'anchor_list'          => $anchorList
            ]);


        } catch ( \Phalcon\Db\Exception $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
            // 删除用户状态
            if ( $oUserPrivateChatLog ) {
                $oUserVideoChatService = new UserVideoChatService();
                $oUserVideoChatService->delete($oUserPrivateChatLog->chat_log_user_id);
            }

        } catch ( \PDOException $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
            // 删除用户状态
            if ( $oUserPrivateChatLog ) {
                $oUserVideoChatService = new UserVideoChatService();
                $oUserVideoChatService->delete($oUserPrivateChatLog->chat_log_user_id);
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
            // 删除用户状态
            if ( $oUserPrivateChatLog ) {
                $oUserVideoChatService = new UserVideoChatService();
                $oUserVideoChatService->delete($oUserPrivateChatLog->chat_log_user_id);
            }
        }

        $this->success($return);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/acceptPrivateChat
     * @api {get} /live/anchor/acceptPrivateChat 接受一对一私聊
     * @apiName acceptPrivateChat
     * @apiGroup Chat
     * @apiDescription 接受一对一私聊
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} chat_log 聊天id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} chat_log 聊天id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.push_url 腾讯云推流地址
     * @apiSuccess {string} d.chat_log 聊天id
     * @apiSuccess {string} d.live_key 房间权限key
     * @apiSuccess {Object} d.wangsu 网宿内容
     * @apiSuccess {string} d.wangsu.push_url 推流地址
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
    public function acceptPrivateChatAction( $nUserId )
    {
        $this->forceUpdate($nUserId);
        $chat_log_id = $this->getParams('chat_log');

        try {
            if ( empty($chat_log_id) ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $oUserPrivateChatLog = UserPrivateChatLog::findFirst([
                'id=:id:',
                'bind' => [ 'id' => $chat_log_id ]
            ]);
            if ( !$oUserPrivateChatLog || $oUserPrivateChatLog->invitee_id != $nUserId ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            if ( $oUserPrivateChatLog->status != 0 ) {
                throw new Exception(ResponseError::getError(ResponseError::STATUS_ERROR), ResponseError::STATUS_ERROR);
            }
            //添加操作锁 防止用户同时在请求取消邀请
            $flg = $this->redis->sAdd('changeChat', $chat_log_id);
            if ( $flg == 0 ) {
                throw new Exception('对方已取消', ResponseError::PARAM_ERROR);
            }
            $anchorId = $oUserPrivateChatLog->chat_log_anchor_user_id;
            $oAnchor  = Anchor::findFirst([
                "user_id = :user_id:",
                'bind' => [ 'user_id' => $anchorId ]
            ]);
            if ( $oUserPrivateChatLog->is_user_call == 'N' ) {
                // 如果是主播回拨 用户需要判断余额
                $oUser = User::findFirst($nUserId);
                if ( $oUser->user_free_coin + $oUser->user_coin < $oAnchor->anchor_chat_price ) {
                    $result = $this->httpRequest(sprintf('%s/v1/live/anchor/refusePrivateChat?%s', $this->config->application->api_url, http_build_query([
                        'uid'         => $nUserId,
                        'debug'       => 1,
                        'chat_log'    => $oUserPrivateChatLog->id,
                        'cli_api_key' => $this->config->application->cli_api_key,
                    ])));
                    $this->redis->sRem('changeChat', $chat_log_id);
                    throw new Exception(ResponseError::getError(ResponseError::USER_COIN_NOT_ENOUGH), ResponseError::USER_COIN_NOT_ENOUGH);
                }
            }

            $createTime                       = time() + 1;
            $oUserPrivateChatLog->status      = 0;
            $oUserPrivateChatLog->create_time = $createTime;
            $oUserPrivateChatLog->save();


            // 如果主播是签约主播 且当前是签约时段
            if ( $oAnchor->anchor_is_sign == 'Y' ) {
                $oAnchorUser = User::findFirst($oAnchor->user_id);
                AnchorSignStat::signAnchorStatAdd($oAnchorUser, $oAnchor, AnchorSignStat::TYPE_CALL);
            }

            //主播接受一对一私聊 则增加有效呼叫次数 以及有效聊天次数
            $oAnchor->anchor_called_count       += 1;
            $oAnchor->anchor_chat_count         += 1;
            $oAnchor->anchor_today_called_times += 1;
            $oAnchor->save();

            // 主播每日统计
            $oAnchorStatService = new AnchorStatService($oAnchor->user_id);
            $oAnchorStatService->save(AnchorStatService::NORMAL_CHAT_CALL_TIMES, 1);

            $this->liveServer->setStreamName($nUserId . '_' . $chat_log_id . '_2');
            //TIM发送信号
            $push_url     = $this->liveServer->pushUrl();
            $aPushMessage = [
                'chat_log'     => $oUserPrivateChatLog->id,
                'is_user_call' => $oUserPrivateChatLog->is_user_call,
                'play_rtmp'    => $this->liveServer->playUrl('rtmp'),
                'play_flv'     => $this->liveServer->playUrl('flv'),
                'play_m3u8'    => $this->liveServer->playUrl('m3u8'),
            ];
            if ( $oUserPrivateChatLog->is_user_call == 'Y' ) {
                $this->timServer->setUid($oUserPrivateChatLog->chat_log_user_id);
            } else {
                $this->timServer->setUid($oUserPrivateChatLog->chat_log_anchor_user_id);
            }
            $this->timServer->acceptPrivateChat($aPushMessage);

            $return = [
                'push_url' => $push_url['push_url'],
                'chat_log' => $chat_log_id,
                'live_key' => $this->timServer->genPrivateMapKey($nUserId, $chat_log_id),
            ];
            // 1.6.1版本 如果是小于该版本 需要在此扣费
            $user_version = $this->redis->hGet('user_app_version', $oUserPrivateChatLog->chat_log_user_id);
            if ( !$this->checkVersionMatch($user_version) ) {
                //版本小于1.6.1
                // 此处第一次付费
                $this->httpRequest(sprintf('%s/v1/live/anchor/privateChatMinuteNew?%s', $this->config->application->api_url, http_build_query([
                    'uid'         => $oUserPrivateChatLog->chat_log_user_id,
                    'debug'       => 1,
                    'chat_log'    => $oUserPrivateChatLog->id,
                    'cli_api_key' => $this->config->application->cli_api_key,
                ])));
                // 记录聊天开始 可以开始扣费 (延迟1秒)
                $oVideoChatService = new VideoChatService();
                $videoChatStr      = sprintf('%s:%s', $oUserPrivateChatLog->chat_log_user_id, $oUserPrivateChatLog->id);
                $oVideoChatService->save($videoChatStr, date('s', $createTime));
                $oUserPrivateChatLog->status = 4;
                $oUserPrivateChatLog->save();
            }
            $this->redis->sRem('changeChat', $chat_log_id);

        } catch ( \PDOException $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($return);
    }

    //拒绝一对一聊天
    public function refusePrivateChatAction( $nUserId )
    {
        if ( empty($nUserId) ) {
            $nUserId = $this->getParams('user_id');
        }
        $chat_log_id = $this->getParams('chat_log');
        if ( empty($chat_log_id) ) {
            $this->error(10002);
        }
        $oUserPrivateChatLog = UserPrivateChatLog::findFirst([
            'id=:id:',
            'bind' => [ 'id' => $chat_log_id ]
        ]);
        if ( !$oUserPrivateChatLog ) {
            $this->error(10002);
        }
        if ( $oUserPrivateChatLog->invitee_id != $nUserId ) {
            $this->error(10002);
        }
        $oUserPrivateChatLog->status   = 2;
        $oUserPrivateChatLog->duration = time() - $oUserPrivateChatLog->create_time;
        $oUserPrivateChatLog->save();

        // 删除用户状态
        $oUserVideoChatService = new UserVideoChatService();
        $oUserVideoChatService->delete($oUserPrivateChatLog->chat_log_user_id);

        $inviter_anchor = Anchor::findFirst([
            'user_id = :user_id:',
            'bind' => [ 'user_id' => $oUserPrivateChatLog->chat_log_user_id ]
        ]);
        if ( $inviter_anchor && $inviter_anchor->anchor_chat_status == 2 ) {
            $inviter_anchor->anchor_chat_status = 3;
            $inviter_anchor->save();
        }
        $invitee_anchor                     = Anchor::findFirst([
            'user_id = :user_id:',
            'bind' => [ 'user_id' => $oUserPrivateChatLog->chat_log_anchor_user_id ]
        ]);
        $invitee_anchor->anchor_chat_status = 3;

        if ( $invitee_anchor ) {
            // 被邀请的是主播 则需要统计
            //主播拒绝一对一私聊 则增加有效呼叫次数 不加有效聊天次数
            $invitee_anchor->anchor_called_count       += 1;
            $invitee_anchor->anchor_today_called_times += 1;
            $invitee_anchor->save();

            // 主播每日统计
            $oAnchorStatService = new AnchorStatService($invitee_anchor->user_id);
            $oAnchorStatService->save(AnchorStatService::NORMAL_CHAT_CALL_TIMES, 1);
        }

        $dialog         = UserPrivateChatDialog::findFirst($oUserPrivateChatLog->dialog_id);
        $dialog->status = 0;
        $dialog->save();
        $this->timServer->setUid($oUserPrivateChatLog->inviter_id);
        $this->timServer->refusePrivateChat([
            'chat_log' => $oUserPrivateChatLog->id
        ]);
        $this->delChatHeartbeat($chat_log_id);
        $this->success();
    }

    //取消一对一私聊

    /**
     * type 为1 主动取消  2是时长已达最长
     */
    public function cancelPrivateChatAction( $nUserId = 0 )
    {
        $chat_log_id = $this->getParams('chat_log');
        $type        = $this->getParams('type');
        if ( empty($chat_log_id) || !in_array($type, [
                '1',
                '2'
            ]) ) {
            $this->error(10002);
        }
        $oUserPrivateChatLog = UserPrivateChatLog::findFirst([
            'id=:id:',
            'bind' => [ 'id' => $chat_log_id ]
        ]);
        if ( !$oUserPrivateChatLog ) {
            $this->error(10002);
        }
        if ( $oUserPrivateChatLog->status == 1 || $oUserPrivateChatLog->status == 5 ) {
            $this->success();
        }
        if ( $oUserPrivateChatLog->inviter_id != $nUserId ) {
            $this->error(10002);
        }
        if ( $oUserPrivateChatLog->status != 0 ) {
            if ( in_array($oUserPrivateChatLog->status, [
                6,
                7
            ]) ) {
                $this->success();
            }
            $this->error(ResponseError::ANCHOR_EXISTS_CALLED, '通话中无需取消');
        }
        //添加操作锁 防止用户同时在请求取消邀请
        $flg = $this->redis->sAdd('changeChat', $chat_log_id);
        if ( $flg == 0 ) {
            $this->error(ResponseError::ANCHOR_EXISTS_CALLED, ResponseError::getError(ResponseError::ANCHOR_EXISTS_CALLED));
        }
        if ( $type == 1 ) {
            $status = 1;
        } else {
            $status = 5;
        }

        // 删除用户状态
        $oUserVideoChatService = new UserVideoChatService();
        $oUserVideoChatService->delete($oUserPrivateChatLog->chat_log_user_id);

        $inviter_anchor = Anchor::findFirst([
            'user_id = :user_id:',
            'bind' => [ 'user_id' => $oUserPrivateChatLog->chat_log_user_id ]
        ]);
        if ( $inviter_anchor && $inviter_anchor->anchor_chat_status == 2 ) {
            $inviter_anchor->anchor_chat_status = 3;
            $inviter_anchor->save();
        }
        $invitee_anchor                     = Anchor::findFirst([
            'user_id = :user_id:',
            'bind' => [ 'user_id' => $oUserPrivateChatLog->chat_log_anchor_user_id ]
        ]);
        $invitee_anchor->anchor_chat_status = 3;
        $invitee_anchor->save();
        $oUserPrivateChatLog->status   = $status;
        $oUserPrivateChatLog->duration = time() - $oUserPrivateChatLog->create_time;
        $oUserPrivateChatLog->save();
        $dialog         = UserPrivateChatDialog::findFirst($oUserPrivateChatLog->dialog_id);
        $dialog->status = 0;
        if ( $nUserId == $dialog->inviter_id ) {
            $dialog->invitee_unread += 1;
        } else {
            $dialog->inviter_unread += 1;
        }
        $dialog->save();
        $this->timServer->setUid($oUserPrivateChatLog->invitee_id);
        $this->timServer->cancelPrivateChat([
            'chat_log' => $oUserPrivateChatLog->id
        ]);
        $this->delChatHeartbeat($chat_log_id);

        // 被邀请的是主播  主播需要统计
        if ( $invitee_anchor ) {
            $invitee_anchor = Anchor::findFirst([
                'user_id = :user_id:',
                'bind' => [ 'user_id' => $oUserPrivateChatLog->chat_log_anchor_user_id ]
            ]);
            // 如果被邀请人是签约主播 并且在签约时间内 如果挂断时候在6秒以及6秒一下 则去掉邀请记录数减一
            if ( $invitee_anchor->anchor_is_sign == 'Y' ) {
                $oAnchorUser = User::findFirst($invitee_anchor->user_id);
                AnchorSignStat::signAnchorStatAdd($oAnchorUser, $invitee_anchor, AnchorSignStat::TYPE_CALLED, -1);
            }
            $this->redis->sRem('changeChat', $chat_log_id);

            // 用户取消邀请一对一私聊  需要判断是否超过5秒 如果超过 则记录一次 有效呼叫次数  不记录有效聊天次数、
            if ( time() - $oUserPrivateChatLog->create_time > 5 ) {
                $invitee_anchor->anchor_called_count += 1;
                // 今日有效呼叫次数
                $invitee_anchor->anchor_today_called_times += 1;
                $invitee_anchor->save();
                // 主播每日统计
                $oAnchorStatService = new AnchorStatService($oUserPrivateChatLog->chat_log_anchor_user_id);
                $oAnchorStatService->save(AnchorStatService::NORMAL_CHAT_CALL_TIMES, 1);
            }
        }


        $this->success();
    }

    /**
     * 一对一私聊
     * privateChatMinuteAction 计时付费
     *
     * 此接口只负责用户扣钱
     * 主播收钱在挂断接口判断
     * 用户流水也在挂断接口统计
     * @param int $nUserId
     *
     * 接口为后台长跑脚本请求
     */
    public function privateChatMinuteNewAction( $nUserId = 0 )
    {
        $chat_log_id = $this->getParams('chat_log', 'int', 0);
        try {
            $oUserPrivateChatLog = UserPrivateChatLog::findFirst([
                'id=:id:',
                'bind' => [ 'id' => $chat_log_id ]
            ]);
            if ( !$oUserPrivateChatLog ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $dialog = UserPrivateChatDialog::findFirst($oUserPrivateChatLog->dialog_id);
            if ( $dialog->status == 0 ) {
                $this->success([ 'msg' => '聊天还没开始，不需要付费' ]);
            }
            if ( $oUserPrivateChatLog->status != 4 ) {
                $this->log->info(sprintf('聊天支付修改聊天id【%d】状态;原状态 【%d】', $oUserPrivateChatLog->id, $oUserPrivateChatLog->status));
                $oUserPrivateChatLog->status = 4;
                $oUserPrivateChatLog->save();
            }
            $payUser      = User::findFirst($oUserPrivateChatLog->chat_log_user_id);
            $anchorUserId = $oUserPrivateChatLog->chat_log_user_id == $oUserPrivateChatLog->chat_log_anchor_user_id ? $oUserPrivateChatLog->chat_log_user_id : $oUserPrivateChatLog->chat_log_anchor_user_id;
            $oUser        = $payUser;
            $oAnchorUser  = User::findFirst($anchorUserId);

            if ( $oUserPrivateChatLog->timepay_count == 0 && $oUserPrivateChatLog->create_time > time() - 10 ) {
                // 发送系统公告
                $userNoticeMsg   = Kv::get(Kv::KEY_NOTICE_USER_ROOM);
                $anchorNoticeMsg = Kv::get(Kv::KEY_NOTICE_ANCHOR_ROOM);
                file_get_contents(sprintf('%s/im/sendNotifyRoom?%s', $this->config->application->api_url, http_build_query([
                    'user_id' => $oUserPrivateChatLog->chat_log_user_id,
                    'content' => $userNoticeMsg,
                ])));
                file_get_contents(sprintf('%s/im/sendNotifyRoom?%s', $this->config->application->api_url, http_build_query([
                    'user_id' => $oUserPrivateChatLog->chat_log_anchor_user_id,
                    'content' => $anchorNoticeMsg,
                ])));

                // 将接收方 未读消息减一
                $oUserChatDialog = UserChatDialog::findFirst([
                    'user_id=:user_id: and to_user_id=:to_user_id:',
                    'bind' => [
                        'user_id'    => $oUserPrivateChatLog->invitee_id,
                        'to_user_id' => $oUserPrivateChatLog->inviter_id,
                    ]
                ]);
                if ( $oUserChatDialog ) {
                    $oUserChatDialog->user_chat_unread -= 1;
                    if ( $oUserChatDialog->user_chat_unread >= 0 ) {
                        $oUserChatDialog->save();
                    }
                }
            }

            if ( $oUser->user_is_superadmin == 'Y' or $oUser->user_is_superadmin == 'C' ) {
                $row['user'] = [
                    'user_coin' => $oUser->user_coin + $oUser->user_free_coin,
                    'msg'       => '超管不用扣费'
                ];
                $this->success($row);
            }

            // 60秒内不重复收费
            $oUserChatPay = UserChatPay::findFirst([
                'user_id=:user_id: AND anchor_user_id=:anchor_user_id: AND update_time >=:update_time: AND chat_log_id=:chat_log_id: ORDER BY update_time DESC',
                'bind' => [
                    'user_id'        => $payUser->user_id,
                    'anchor_user_id' => $oAnchorUser->user_id,
                    'chat_log_id'    => $chat_log_id,
                    'update_time'    => time() - 40,
                ]
            ]);
            if ( $oUserChatPay ) {
                $row['user'] = [
                    'user_coin' => $oUser->user_coin + $oUser->user_free_coin,
                    'msg'       => '已扣过费用'
                ];
                $this->success($row);
            }
            // Start a transaction
            $this->db->begin();
            $oAnchor = Anchor::findFirst("user_id = $anchorUserId");
            // 每分钟金币
            // 用户最多1分钟免费时长
            $hasFreeTimes      = min($oUser->user_free_match_time, 1);
            $hssGuardFreeTimes = 0;
            switch ( $oUserPrivateChatLog->chat_type ) {
                case UserPrivateChatLog::CHAT_TYPE_MATCH:
                    $chat_fee = intval(Kv::get(Kv::CHAT_MATCH_PRICE));
                    break;
                case UserPrivateChatLog::CHAT_TYPE_DISPATCH:
                    $oAnchorDispatch = AnchorDispatch::findFirst([
                        'anchor_dispatch_user_id = :anchor_dispatch_user_id:',
                        'bind' => [
                            'anchor_dispatch_user_id' => $anchorUserId
                        ]
                    ]);
                    if ( $oAnchorDispatch ) {
                        $chat_fee = $oAnchorDispatch->anchor_dispatch_price;
                    } else {
                        $chat_fee = intval(Kv::get(Kv::CHAT_MATCH_PRICE));
                    }
                    break;
                case UserPrivateChatLog::CHAT_TYPE_NORMAL:
                    $hasFreeTimes = 0;
                    // 判断当前用户是否为主播 当前的守护
                    if ( $oAnchor->anchor_guard_id == $oUserPrivateChatLog->chat_log_user_id ) {
                        $hssGuardFreeTimes = UserGuard::getTodayFreeTimes($oAnchor->user_id, $oUserPrivateChatLog->chat_log_user_id);
                    }
                default:
                    $chat_fee = $oAnchor->anchor_chat_price;
                    // VIP 折扣
                    if ( $payUser->user_member_expire_time > time() ) {
                        $vipInfo  = VipLevel::getVipInfo($payUser->user_vip_level);
                        $chat_fee = sprintf('%.2f', $chat_fee * $vipInfo->vip_level_video_chat_discount / 10);
                    }
            }

            if ( $hssGuardFreeTimes ) {
                // 有守护 免费时长  使用
                $oGuardFreeTimeService = new GuardFreeTimeService($nUserId, $anchorUserId);
                $flg                   = $oGuardFreeTimeService->save();
                $this->log->info('update to:' . $flg);
                $oUserChatPay = UserChatPay::findFirst([
                    'chat_log_id =' . $chat_log_id
                ]);
                if ( $oUserChatPay ) {
                    $oUserChatPay->free_times_no_income += 1;
                    $oUserChatPay->pay_times            += 1;
                } else {
                    $oUserChatPay                       = new UserChatPay();
                    $oUserChatPay->user_id              = $nUserId;
                    $oUserChatPay->anchor_user_id       = $anchorUserId;
                    $oUserChatPay->chat_log_id          = $chat_log_id;
                    $oUserChatPay->group_id             = $oAnchorUser->user_group_id;
                    $oUserChatPay->pay_times            = 1;
                    $oUserChatPay->free_times_no_income = 1;
                }
                if ( $oUserChatPay->save() === FALSE ) {
                    $this->db->rollback();
                    throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserChatPay->getMessages())), ResponseError::OPERATE_FAILED);
                }

            } else {

                // 操作用户的数据
                $nCoin            = $chat_fee;
                $consumeFreeCoin  = 0;
                $consumeCoin      = 0;
                $consumeFreeTimes = 0;

                if ( $hasFreeTimes > 0 ) {
                    $consumeFreeTimes = 1;
                } else {
                    if ( $oUser->user_free_coin <= 0 ) {
                        // 直接扣充值币
                        $consumeCoin = $nCoin;

                    } else if ( $oUser->user_free_coin < $nCoin ) {
                        //扣一部分充值币 扣光赠送币
                        $consumeFreeCoin = $oUser->user_free_coin;
                        $consumeCoin     = $nCoin - $oUser->user_free_coin;
                    } else {
                        $consumeFreeCoin = $nCoin;
                    }
                }
                $exp       = ($consumeFreeCoin + $consumeCoin) * intval(Kv::get(Kv::COIN_TO_EXP));
                $userLevel = User::getUserLevel($oUser->user_exp + $exp);

//                $sql = 'update `user` set user_free_coin = user_free_coin - :consume_free_coin,user_consume_free_total = user_consume_free_total + :consume_free_coin
//,user_coin = user_coin - :consume_coin,user_consume_total = user_consume_total + :consume_coin,user_free_match_time = user_free_match_time - :user_free_match_time
//,user_exp = user_exp + :exp,user_level = :user_level
//where user_id = :user_id AND user_free_coin >= :consume_free_coin AND user_coin >= :consume_coin AND user_free_match_time >= :user_free_match_time';
//                $this->db->execute($sql, [
//                    'consume_free_coin'    => $consumeFreeCoin,
//                    'consume_coin'         => $consumeCoin,
//                    'user_id'              => $payUser->user_id,
//                    'user_free_match_time' => $consumeFreeTimes,
//                    'exp'                  => $exp,
//                    'user_level'           => $userLevel
//                ]);

                // 免费时长 直接扣光 然后如果 免费时长大于1 记录
                $sql = 'update `user` set user_free_coin = user_free_coin - :consume_free_coin,user_consume_free_total = user_consume_free_total + :consume_free_coin
,user_coin = user_coin - :consume_coin,user_consume_total = user_consume_total + :consume_coin,user_free_match_time = user_free_match_time - :user_free_match_time
,user_exp = user_exp + :exp,user_level = :user_level
where user_id = :user_id AND user_free_coin >= :consume_free_coin AND user_coin >= :consume_coin AND user_free_match_time >= :user_free_match_time';
                $this->db->execute($sql, [
                    'consume_free_coin'    => $consumeFreeCoin,
                    'consume_coin'         => $consumeCoin,
                    'user_id'              => $payUser->user_id,
                    'user_free_match_time' => $consumeFreeTimes,
                    'exp'                  => $exp,
                    'user_level'           => $userLevel
                ]);
                if ( $this->db->affectedRows() <= 0 ) {
                    // 赠送币 不够钱
                    $this->db->rollback();
                    $this->timServer->setUid($payUser->user_id);
                    $this->timServer->userCoinNotEnough([
                        'user_coin' => $oUser->user_coin + $oUser->user_free_coin
                    ]);
                    throw new Exception(ResponseError::getError(ResponseError::USER_COIN_NOT_ENOUGH), ResponseError::USER_COIN_NOT_ENOUGH);
//                throw new Exception(json_encode([
//                    'consume_free_coin' => $consumeFreeCoin,
//                    'consume_coin'      => $consumeCoin,
//                    'user_id'           => $nUserId,
//                ]), ResponseError::USER_COIN_NOT_ENOUGH);
                }
                if ( $oUser->user_free_match_time > 1 && $oUserPrivateChatLog->chat_type == UserPrivateChatLog::CHAT_TYPE_MATCH ) {
                    $this->log->info(sprintf("user [%s] has match min 【%s】", $oUser->user_id, $oUser->user_free_match_time));
                }
                $oUserChatPay = UserChatPay::findFirst([
                    'chat_log_id =' . $chat_log_id
                ]);

                $isFirstPay = TRUE;
                if ( $oUserChatPay ) {
                    $isFirstPay = FALSE;
                    // 存在记录  则在原有记录上添加
                    $oUserChatPay->chat_total_fee += $nCoin;
                    $oUserChatPay->chat_fee       += $consumeCoin;
                    $oUserChatPay->chat_free_fee  += $consumeFreeCoin;
                    $oUserChatPay->pay_times      += 1;
                    $oUserChatPay->free_times     += $consumeFreeTimes;
                } else {
                    // 记录私聊支付
                    $oUserChatPay                 = new UserChatPay();
                    $oUserChatPay->user_id        = $nUserId;
                    $oUserChatPay->anchor_user_id = $anchorUserId;
                    $oUserChatPay->chat_log_id    = $chat_log_id;
                    $oUserChatPay->chat_total_fee = $nCoin;
                    $oUserChatPay->chat_fee       = $consumeCoin;
                    $oUserChatPay->chat_free_fee  = $consumeFreeCoin;
                    $oUserChatPay->group_id       = $oAnchorUser->user_group_id;
                    $oUserChatPay->pay_times      = 1;
                    $oUserChatPay->free_times     = $consumeFreeTimes;
                }
                if ( $oUserChatPay->save() === FALSE ) {
                    $this->db->rollback();
                    throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserChatPay->getMessages())), ResponseError::OPERATE_FAILED);
                }


                $payCoin = $consumeCoin + $consumeFreeCoin;
                if ( $payCoin ) {
                    // 活动消费统计
                    $oActivityUserService = new ActivityUserService();
                    $oActivityUserService->save($payUser->user_id, $payCoin);
                }

            }
            // 记录此条通话的付费次数  加1
            $oChatPayService = new ChatPayService($chat_log_id);
            $oChatPayService->save();

            $this->db->commit();

            if ( $oUserPrivateChatLog->status != 4 ) {
                $this->log->info(sprintf('聊天支付修改聊天id【%d】状态;原状态 【%d】', $oUserPrivateChatLog->id, $oUserPrivateChatLog->status));
                $oUserPrivateChatLog->status = 4;
                $oUserPrivateChatLog->save();
            }
            $oUser       = User::findFirst($oUser->user_id);
            $row['user'] = [
                'user_coin'              => sprintf('%.2f', $oUser->user_coin + $oUser->user_free_coin),
                'guard_free_times'       => (string)max(0, $hssGuardFreeTimes - 1),
                'pay_user_old_coin'      => sprintf('%.2f', $payUser->user_coin + $payUser->user_free_coin),
                'pay_user_old_free_time' => $payUser->user_free_match_time,
                'user_free_time'         => $oUser->user_free_match_time,
            ];
            $this->timServer->setUid($payUser->user_id);
            $this->timServer->videoChatPaySuccess($row);
        } catch ( \Phalcon\Db\Exception $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( \PDOException $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * 挂断一对一私聊
     * 主播收钱
     *
     * 1. 视频接通10s内挂断，扣取用户金币，但不予主播结算
     * 2. 视频通话如果主播主动关闭，该视频通话最后一分钟，不予结算，但同样扣取用户金币。
     * 若用户主动关闭，不受该逻辑影响
     *
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/hangUpChat
     * @api {get} /live/anchor/hangUpChat 挂断聊天
     * @apiName hangUpChat
     * @apiGroup Chat
     * @apiDescription 挂断聊天
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} chat_log 聊天id
     * @apiParam (正常请求){String='manual(手动),auto(自动)'} hang_up_type 挂断类型
     * @apiParam (正常请求){String} detail 挂断原因
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} chat_log 聊天id
     * @apiParam (debug){String='manual(手动),auto(自动)'} hang_up_type 挂断类型
     * @apiParam (debug){String} detail 挂断原因
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object} d.gift  礼物收益
     * @apiSuccess {number} d.gift.coin  礼物消费金币
     * @apiSuccess {number} d.gift.dot  礼物收益
     * @apiSuccess {object} d.chat_game  聊天游戏收益
     * @apiSuccess {number} d.chat_game.coin  聊天游戏金币
     * @apiSuccess {number} d.chat_game.dot  聊天游戏收益
     * @apiSuccess {object} d.total  总收益
     * @apiSuccess {number} d.total.coin  聊天游戏金币
     * @apiSuccess {number} d.total.dot  聊天游戏收益
     * @apiSuccess {number} d.free_times  使用免费时长
     * @apiSuccess {number} d.no_income_free_time  使用的不计算收益的免费时长数
     * @apiSuccess {String='guard(守护)'} d.no_income_free_time_type  使用的不计算收益的免费时长类型
     * @apiSuccess {number} d.amount  时间收益
     * @apiSuccess {number} d.coin_amount  时间消费
     * @apiSuccess {number} d.duration  时长
     * @apiSuccess {String} d.is_follow  是否关注
     * @apiSuccess {String} d.snatch_status  抢聊状态  Y 成功，N 失败，C 等待中
     * @apiSuccess {number} d.exp  获得经验
     * @apiSuccess {number} d.intimate_value  获取亲密度
     * @apiSuccess {number} d.anchor_exp  主播经验（魅力值）
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *          "c": 0,
     *          "m": "请求成功",
     *          "d": {
     *                  "gift": {
     *                      "coin": "30",
     *                      "dot": "1.2"
     *              },
     *                  "chat_game": {
     *                      "coin": "30",
     *                      "dot": "1.2"
     *              },
     *                  "total": {
     *                      "coin": "30",
     *                      "dot": "1.2"
     *              },
     *              "free_times": "10",
     *              "amount": "0.8000",
     *              "coin_amount": 20,
     *              "duration": 50,
     *              "is_follow": "Y",
     *              "snatch_status": "Y",
     *              "exp": "100",
     *              "intimate_value": "100",
     *              "anchor_exp": "100"
     *          },
     *          "t": 1536662604
     *      }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     * */
    public function hangUpChatAction( $nUserId = 0 )
    {
        $type        = $this->getParams('type', 'int', 0);
        $chat_log_id = $this->getParams('chat_log');
        $hangUpType  = $this->getParams('hang_up_type', 'string', 'manual');
        $detail      = $this->getParams('detail', 'string', '- ');
        $netError    = $this->getParams('net_error', 'string', '');
//        $this->log->info('hangUp:'.json_encode($this->getParams()));
        $this->log->info("挂断开始：chat log : {$chat_log_id}; 用户id：{$nUserId}; type: {$type}; 类型{$hangUpType}; detail:{$detail}\n");

        try {
            if ( !in_array($hangUpType, [
                'manual',
                'auto'
            ]) ) {
                $hangUpType = 'manual';
            }
            if ( empty($chat_log_id) ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $oUserPrivateChatLog = UserPrivateChatLog::findFirst([
                'id=:id:',
                'bind' => [ 'id' => $chat_log_id ]
            ]);
            if ( !in_array($nUserId, [
                $oUserPrivateChatLog->chat_log_user_id,
                $oUserPrivateChatLog->chat_log_anchor_user_id,
                $oUserPrivateChatLog->snatch_user_id,
            ]) ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $flg = $this->redis->sAdd('hangUpChat', $chat_log_id);
            if ( $flg == 0 ) {
                throw new Exception('正在结算数据', ResponseError::PARAM_ERROR);
            }
            $anchorUserId = $oUserPrivateChatLog->chat_log_anchor_user_id;
            $snatchStatus = 'N';
            if ( $nUserId == $oUserPrivateChatLog->snatch_user_id ) {
                $snatchStatus = 'Y';
                $nUserId      = $oUserPrivateChatLog->chat_log_user_id;
            } else if ( $oUserPrivateChatLog->snatch_user_id ) {
                // 判断抢聊状态 是否为Y
                $oUserSnatchLog = UserSnatchLog::findFirst([
                    'snatched_chat_log_id = :snatched_chat_log_id:',
                    'bind' => [
                        'snatched_chat_log_id' => $chat_log_id
                    ]
                ]);
                if ( $oUserSnatchLog->status == 'C' && $oUserSnatchLog->create_time < time() - 8 ) {
                    // 抢聊状态为N 且 创建时长 据现在超过8秒 即抢聊用户没有请求第二次确认抢聊
                    $oUserSnatchLog->status = 'N';
                    $oUserSnatchLog->save();
                }
                $snatchStatus = $oUserSnatchLog->status;
            }

            if ( !$oUserPrivateChatLog || ($oUserPrivateChatLog->status != 4 && $oUserPrivateChatLog->status != 6) ) {
                //判断一下 用户是否付过费 如果没有付过费 则是错误请求
                $oUserChatPay = UserChatPay::findFirst([
                    'chat_log_id=:chat_log_id:',
                    'bind' => [ 'chat_log_id' => $chat_log_id ]
                ]);
                $oUser        = User::findFirst($oUserPrivateChatLog->chat_log_user_id);
                if ( !$oUserChatPay && !in_array($oUser->user_is_superadmin, [
                        'Y',
                        'C'
                    ]) ) {
                    $this->redis->sRem('hangUpChat', $chat_log_id);
                    $row = [
                        'no_income_free_time'      => 0,
                        'no_income_free_time_type' => UserPrivateChatLog::FREE_TIME_TYPE_GUARD,
                        'free_times'               => 0,
                        'amount'                   => 0,
                        'coin_amount'              => 0,
                        'gift'                     => [
                            'coin' => 0,
                            'dot'  => 0,
                        ],
                        'chat_game'                => [
                            'coin' => 0,
                            'dot'  => 0,
                        ],
                        'duration'                 => 0,
                        'total'                    => [
                            'coin' => 0,
                            'dot'  => 0,
                        ],
                        'exp'                      => 0,
                        'intimate_value'           => 0,
                        'anchor_exp'               => 0
                    ];
                    //将数据改为用户未进入
                    $oUserPrivateChatLog->status         = 7;
                    $oUserPrivateChatLog->hangup_user_id = $nUserId;
                    $oUserPrivateChatLog->hangup_type    = $hangUpType;
                    $oUserPrivateChatLog->detail         = '用户未进入房间';
                    $oUserPrivateChatLog->save();
                    //两个状态都要考虑
                    $dialog = UserPrivateChatDialog::findFirst($oUserPrivateChatLog->dialog_id);
                    if ( $snatchStatus == 'N' ) {
                        $oAnchor = Anchor::findFirst("user_id = {$anchorUserId}");
                        if ( $oAnchor && $oAnchor->anchor_chat_status == 2 ) {
                            $oAnchor->anchor_chat_status = 3;
                            $oAnchor->save();
                        }
                    }
                    // 删除用户状态
                    $oUserVideoChatService = new UserVideoChatService();
                    $oUserVideoChatService->delete($oUserPrivateChatLog->chat_log_user_id);

                    $dialog->status = 0;
                    $dialog->save();
                    $this->success($row);
                }
            }
            $oVideoChatService = new VideoChatService();

            //礼物收益
            $giftData = $oVideoChatService->getGiftData(sprintf("%s:%s", $anchorUserId, $oUserPrivateChatLog->chat_log_user_id));
            // 游戏收益
            $chatGameData = $oVideoChatService->getChatGameData(sprintf("%s:%s", $anchorUserId, $oUserPrivateChatLog->chat_log_user_id));
            if ( $oUserPrivateChatLog->status == 6 ) {
                $oUserChatPay = UserChatPay::findFirst([
                    'chat_log_id=:chat_log_id:',
                    'bind' => [ 'chat_log_id' => $chat_log_id ]
                ]);
                $row          = [
                    'no_income_free_time'      => $oUserChatPay->free_times_no_income,
                    'no_income_free_time_type' => UserPrivateChatLog::FREE_TIME_TYPE_GUARD,
                    'free_time'                => $oUserChatPay->free_times,
                    'amount'                   => $oUserPrivateChatLog->anchor_get_dot,
                    'coin_amount'              => $oUserChatPay->chat_free_fee + $oUserChatPay->chat_fee,
                    'gift'                     => [
                        'coin' => $giftData['coin'] ?? 0,
                        'dot'  => $giftData['dot'] ?? 0,
                    ],
                    'chat_game'                => [
                        'coin' => $chatGameData['coin'] ?? 0,
                        'dot'  => $chatGameData['dot'] ?? 0,
                    ],
                    'duration'                 => $oUserPrivateChatLog->duration,
                    'snatch_status'            => $snatchStatus,
                ];
                $row['total'] = [
                    'coin' => round($row['coin_amount'] + $row['gift']['coin'] + $row['chat_game']['coin'],2),
                    'dot'  => round($row['amount'] + $row['gift']['dot'] + $row['chat_game']['dot'], 2),
                ];

                $row['exp']            = intval($row['total']['coin'] * intval(Kv::get(Kv::COIN_TO_EXP)));
                $row['intimate_value'] = intval($row['total']['coin'] * intval(Kv::get(Kv::COIN_TO_INTIMATE)));
                $row['anchor_exp']     = intval($row['total']['dot'] * intval(Kv::get(Kv::DOT_TO_ANCHOR_EXP)));

                $this->delChatHeartbeat($chat_log_id);
                $this->redis->sRem('hangUpChat', $chat_log_id);
                // 让用户推出群聊
//                $this->timServer->setRid($ancho);
//                $this->timServer->setAccountId($oUserPrivateChatLog->chat_log_user_id);
//                $this->timServer->leaveRoom();
                $this->success($row);
            }

            $row                  = [];
            $row['snatch_status'] = $snatchStatus;
            $row['gift']          = [
                'coin' => $giftData['coin'] ?? 0,
                'dot'  => $giftData['dot'] ?? 0,
            ];
            $row['chat_game']     = [
                'coin' => $chatGameData['coin'] ?? 0,
                'dot'  => $chatGameData['dot'] ?? 0,
            ];
            /**
             * 计算收益
             * */
            $oAnchorUser  = User::findFirst($anchorUserId);
            $oAnchor      = Anchor::findFirst([
                'user_id=:user_id:',
                'bind' => [ 'user_id' => $anchorUserId ]
            ]);
            $oUserChatPay = UserChatPay::findFirst([
                'chat_log_id=:chat_log_id:',
                'bind' => [ 'chat_log_id' => $chat_log_id ]
            ]);
            if ( !$oUserChatPay ) {
                $nDot                            = 0;
                $userConsumeCoin                 = 0;
                $timepay_count                   = 0;
                $userConsumeFreeTimes            = 0;
                $trueConsumeCoin                 = 0;
                $noIncomeFreeTimes               = 0;
                $row['no_income_free_time']      = $noIncomeFreeTimes;
                $row['no_income_free_time_type'] = $oUserPrivateChatLog->free_times_type;
                $row['free_times']               = $userConsumeFreeTimes;
                $row['amount']                   = $nDot;
                $row['coin_amount']              = $trueConsumeCoin;
                $row['total']                    = [
                    'coin' => '0',
                    'dot'  => '0',
                ];
            } else {
                // 三个变量 用于计算主播收益
                // 三个变量 用于记录用户流水
                $total_anchor_chat_total_fee = $total_chat_total_fee = round($oUserChatPay->chat_total_fee, 4);
                $total_anchor_chat_free_fee  = $total_chat_free_fee = round($oUserChatPay->chat_free_fee, 4);
                $total_anchor_chat_fee       = $total_chat_fee = round($oUserChatPay->chat_fee, 4);
                $time_count                  = $timepay_count = $oUserChatPay->pay_times;
                $userConsumeCoin             = $total_chat_total_fee;
                $userConsumeFreeTimes        = $oUserChatPay->free_times;
                $noIncomeFreeTimes           = $oUserChatPay->free_times_no_income;
                $oUser                       = User::findFirst($oUserPrivateChatLog->chat_log_user_id);
                $trueConsumeCoin             = $oUserChatPay->chat_free_fee + $oUserChatPay->chat_fee;
                // 记录用户流水  判断流水是否存在了  如果存在了 就结束    去除
//                $oUserFinanceLog = UserFinanceLog::findFirst([
//                    'flow_id = :flow_id: AND user_amount_type = :user_amount_type: AND consume_category_id = :consume_category_id:',
//                    'bind' => [
//                        'flow_id'             => $chat_log_id,
//                        'user_amount_type'    => UserFinanceLog::AMOUNT_COIN,
//                        'consume_category_id' => UserConsumeCategory::PRIVATE_CHAT,
//                    ]
//                ]);
//                if ( $oUserFinanceLog ) {
//                    $oAnchorUserFinanceLog = UserFinanceLog::findFirst([
//                        'flow_id = :flow_id: AND user_amount_type = :user_amount_type: AND consume_category_id = :consume_category_id:',
//                        'bind' => [
//                            'flow_id'             => $chat_log_id,
//                            'user_amount_type'    => UserFinanceLog::AMOUNT_DOT,
//                            'consume_category_id' => UserConsumeCategory::PRIVATE_CHAT,
//                        ]
//                    ]);
//                    $nDot                  = $oAnchorUserFinanceLog ? $oAnchorUserFinanceLog->consume : 0;
//                } else {
                if ( $total_anchor_chat_free_fee != 0 || $total_anchor_chat_fee != 0 ) {
                    // 用户没有扣费
                    $oUserFinanceLog                         = new UserFinanceLog();
                    $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
                    $oUserFinanceLog->user_id                = $oUser->user_id;
                    $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin;
                    $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin + $total_chat_total_fee;
                    $oUserFinanceLog->consume_category_id    = UserConsumeCategory::PRIVATE_CHAT;
                    $oUserFinanceLog->consume                = -$total_chat_total_fee;
                    $oUserFinanceLog->remark                 = '一对一私聊计时收费';
                    $oUserFinanceLog->flow_id                = $chat_log_id;
                    $oUserFinanceLog->group_id               = $oAnchorUser->user_group_id;
                    $oUserFinanceLog->target_user_id         = $oAnchorUser->user_id;
                    $oUserFinanceLog->user_current_user_coin = $oUser->user_coin;
                    $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin + $total_chat_fee;
                    $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin;
                    $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin + $total_chat_free_fee;
                }

                if ( time() - $oUserPrivateChatLog->create_time < 10 ) {
                    // 小于10秒挂断 主播收益为0  大于10秒才计算
                    $total_anchor_chat_total_fee = 0;
                    $total_anchor_chat_free_fee  = 0;
                    $total_anchor_chat_fee       = 0;
                    $time_count                  = 0;
                } else if ( time() - $oUserPrivateChatLog->create_time < 20 && $oUserChatPay->free_times == 1 ) {
                    // 赠送的1分钟 必须通话超过20秒 不然没有收益  20190628从30秒改为20秒
                    $total_anchor_chat_total_fee = 0;
                    $total_anchor_chat_free_fee  = 0;
                    $total_anchor_chat_fee       = 0;
                    $time_count                  = 0;
                } else if ( $nUserId == $anchorUserId ) {
                    // 挂断的人是主播 扣去最后一分钟的时长费
                    $singlePrice = Kv::get(Kv::CHAT_MATCH_PRICE);
                    if ( $oUserPrivateChatLog->chat_type == UserPrivateChatLog::CHAT_TYPE_NORMAL ) {
                        $singlePrice = $oAnchor->anchor_chat_price;
                    } elseif ( $oUserPrivateChatLog->chat_type == UserPrivateChatLog::CHAT_TYPE_DISPATCH ) {
                        $oAnchorDispatch = AnchorDispatch::findFirst([
                            'anchor_dispatch_user_id = :anchor_dispatch_user_id:',
                            'bind' => [
                                'anchor_dispatch_user_id' => $anchorUserId
                            ]
                        ]);
                        if ( $oAnchorDispatch ) {
                            $singlePrice = $oAnchorDispatch->anchor_dispatch_price;
                        }
                    }
                    $total_anchor_chat_total_fee -= $singlePrice;
                    // 有免费时长情况 所有判断是否小于0
                    $total_anchor_chat_total_fee = max(0, $total_anchor_chat_total_fee);
                    $time_count                  -= 1;
                    if ( $total_anchor_chat_fee > $singlePrice ) {
                        //总充值金币数 大于单次价格 那么表示最后一次使用的 全部都是 充值币
                        $total_anchor_chat_fee -= $singlePrice;
                        $total_anchor_chat_fee = max(0, $total_anchor_chat_fee);
                    } else {
                        //总充值金币数小于单次充值币 那么 一部分用充值币 一部分用赠送币（或 全部为赠送币） 那么除去最后一分钟，使用充值币的数为0
                        $total_anchor_chat_fee = 0;
                    }
                }

                $nCoin = $total_anchor_chat_total_fee;
                // 时长金币换收益比例
                $nRatio                          = $oAnchor->getCoinToDotRatio($oAnchorUser, Anchor::RATIO_TIME);
                $nDot                            = sprintf('%.4f', $total_anchor_chat_total_fee * ($nRatio / 100));
                $getDot                          = sprintf('%.4f', $total_anchor_chat_fee * ($nRatio / 100));
                $getFreeDot                      = round($nDot - $getDot, 4);
                $row['no_income_free_time']      = $noIncomeFreeTimes;
                $row['no_income_free_time_type'] = $oUserPrivateChatLog->free_times_type;
                $row['free_times']               = $userConsumeFreeTimes;
                $row['amount']                   = $nDot;
                $row['coin_amount']              = $trueConsumeCoin;
                $row['total']                    = [
                    'coin' => round($row['coin_amount'] + $row['gift']['coin'] + $row['chat_game']['coin'],2),
                    'dot'  => round($row['amount'] + $row['gift']['dot'] + $row['chat_game']['dot'], 2),
                ];
                $row['exp']                      = intval($row['total']['coin'] * intval(Kv::get(Kv::COIN_TO_EXP)));
                $row['intimate_value']           = intval($row['total']['coin'] * intval(Kv::get(Kv::COIN_TO_INTIMATE)));
                $anchorExp                       = intval($row['total']['dot'] * intval(Kv::get(Kv::DOT_TO_ANCHOR_EXP)));
                $row['anchor_exp']               = $anchorExp;
                if ( $nCoin > 0 ) {
                    // Start a transaction
                    $this->db->begin();
                    //用户的流水
                    if ( $total_anchor_chat_free_fee != 0 || $total_anchor_chat_fee != 0 ) {
                        if ( $oUserFinanceLog->save() === FALSE ) {
                            $this->db->rollback();
                            throw new Exception(
                                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                                ResponseError::OPERATE_FAILED
                            );
                        }
                    }
                    if ( $nDot ) {
                        // 给主播充钱
                        $sql = 'update `user` set user_dot = user_dot + :total_dot,user_collect_total = user_collect_total + :get_dot
,user_collect_free_total = user_collect_free_total + :get_free_dot
 where user_id = :user_id';
                        $this->db->execute($sql, [
                            'total_dot'    => $nDot,
                            'get_dot'      => $getDot,
                            'get_free_dot' => $getFreeDot,
                            'user_id'      => $anchorUserId,
                        ]);
                        if ( $this->db->affectedRows() <= 0 ) {
                            $this->db->rollback();
                            throw new Exception(
                                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oAnchorUser->getMessages())),
                                ResponseError::OPERATE_FAILED
                            );
                        }

                        $anchorLevel = LevelConfig::getLevelInfo($oAnchor->anchor_exp + $anchorExp, LevelConfig::LEVEL_TYPE_ANCHOR);
                        // 给主播加经验(魅力值)
                        $anchorSql = 'update anchor set anchor_exp = anchor_exp + :anchor_exp,anchor_level = :anchor_level WHERE user_id = :user_id';
                        $this->db->execute($anchorSql, [
                            'anchor_exp'   => $anchorExp,
                            'anchor_level' => $anchorLevel['level'],
                            'user_id'      => $anchorUserId,
                        ]);

                        // 记录主播流水
                        $oUserFinanceLog                      = new UserFinanceLog();
                        $oUserFinanceLog->user_amount_type    = UserFinanceLog::AMOUNT_DOT;
                        $oUserFinanceLog->user_id             = $anchorUserId;
                        $oUserFinanceLog->user_current_amount = $oAnchorUser->user_dot + $nDot;
                        $oUserFinanceLog->user_last_amount    = $oAnchorUser->user_dot;
                        $oUserFinanceLog->consume_category_id = UserConsumeCategory::PRIVATE_CHAT;
                        $oUserFinanceLog->consume             = +$nDot;
                        $oUserFinanceLog->remark              = '一对一私聊计时收益';
                        $oUserFinanceLog->flow_id             = $chat_log_id;
                        $oUserFinanceLog->type                = 0;
                        $oUserFinanceLog->group_id            = $oAnchorUser->user_group_id;
                        $oUserFinanceLog->consume_source      = -$nCoin;
                        $oUserFinanceLog->extra_number        = $time_count;
                        $oUserFinanceLog->target_user_id      = $oUserPrivateChatLog->chat_log_user_id;
                        if ( $oUserFinanceLog->save() === FALSE ) {
                            $this->db->rollback();
                            throw new Exception(
                                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                                ResponseError::OPERATE_FAILED
                            );
                        }

                        if ( $oAnchorUser->user_group_id ) {
                            // 有公会的主播  需要给公会长加钱
                            $oGroup = Group::findFirst($oAnchorUser->user_group_id);
                            if ( $oGroup ) {
                                $divid_type    = $oGroup->divid_type;
                                $divid_precent = $oGroup->divid_precent;
                                if ( $divid_type == 0 ) {
                                    //主播收益分成
                                    $groupMoney = round($nDot * $divid_precent / 100, 2);
                                } else {
                                    //主播流水分成  还需要除以一个 充值比例转换值 10
                                    $groupMoney = round($nCoin * $divid_precent / 100 / 10, 2);
                                }
                                $sql = 'update `group` set money = money + :money where id = :group_id';
                                $this->db->execute($sql, [
                                    'money'    => $groupMoney,
                                    'group_id' => $oAnchorUser->user_group_id,
                                ]);
                            }
                        }
                    }
                    $this->db->commit();
                } else {
                    if ( $trueConsumeCoin != 0 ) {
                        if ( $oUserFinanceLog->save() === FALSE ) {
                            throw new Exception(
                                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                                ResponseError::OPERATE_FAILED
                            );
                        }
                    }
                }
//                }
            }
            $oInviterUser = User::findFirst($oUserPrivateChatLog->chat_log_user_id);

            $dialog                                 = UserPrivateChatDialog::findFirst($oUserPrivateChatLog->dialog_id);
            $oUserPrivateChatLog->duration          = $userConsumeCoin > 0 || $noIncomeFreeTimes > 0 || $userConsumeFreeTimes > 0 || in_array($oInviterUser->user_is_superadmin, [
                'Y',
                'C'
            ]) ? time() - $oUserPrivateChatLog->create_time : 0;
            $oUserPrivateChatLog->timepay_count     = $timepay_count;
            $oUserPrivateChatLog->user_consume_coin = $trueConsumeCoin;
            $oUserPrivateChatLog->anchor_get_dot    = $nDot;
            $oUserPrivateChatLog->hangup_user_id    = $nUserId;
            $oUserPrivateChatLog->hangup_type       = $hangUpType;
            $oUserPrivateChatLog->detail            = $detail;
            $oUserPrivateChatLog->status            = 6;
            $oUserPrivateChatLog->free_times        = $userConsumeFreeTimes + $noIncomeFreeTimes;
            $flg                                    = $oUserPrivateChatLog->save();
            $this->log->info("挂断请求完成：chat log : {$oUserPrivateChatLog->id}; duration: {$oUserPrivateChatLog->duration};  timepay_count: {$oUserPrivateChatLog->timepay_count}; user_consume_coin:{$oUserPrivateChatLog->user_consume_coin};anchor_get_dot:{$oUserPrivateChatLog->anchor_get_dot};hangup_user_id:{$oUserPrivateChatLog->hangup_user_id};save flg : {$flg} ;error: " . json_encode($oUserPrivateChatLog->getMessages()));

            // 主播今日收益 增加
            $oAnchorTodayDotService = new AnchorTodayDotService($oAnchorUser->user_id);
            $oAnchorTodayDotService->save($nDot);

            // 主播每日统计
            $oAnchorStatService = new AnchorStatService($oAnchorUser->user_id);
            $oAnchorStatService->save(AnchorStatService::TIME_INCOME, $nDot);

            $row['duration'] = $oUserPrivateChatLog->duration;


            switch ( $oUserPrivateChatLog->chat_type ) {
                case UserPrivateChatLog::CHAT_TYPE_MATCH:
                    $oAnchorStatService->save(AnchorStatService::MATCH_DURATION, $oUserPrivateChatLog->duration);
                    $oAnchorStatService->save(AnchorStatService::MATCH_TIMES, 1);
                    break;
                case UserPrivateChatLog::CHAT_TYPE_NORMAL:
                    $oAnchorStatService->save(AnchorStatService::NORMAL_CHAT_DURATION, $oUserPrivateChatLog->duration);
                    $oAnchorStatService->save(AnchorStatService::NORMAL_CHAT_TIMES, 1);
                    break;
                case UserPrivateChatLog::CHAT_TYPE_DISPATCH:
                    if ( !$oAnchorDispatch ) {
                        $oAnchorDispatch = AnchorDispatch::findFirst([
                            'anchor_dispatch_user_id = :anchor_dispatch_user_id:',
                            'bind' => [
                                'anchor_dispatch_user_id' => $anchorUserId
                            ]
                        ]);
                        if ( $oAnchorDispatch ) {
                            $oAnchorDispatch->anchor_dispatch_success_number += 1;
                            $oAnchorDispatch->anchor_dispatch_total_dot      += $nDot;
                            $oAnchorDispatch->anchor_dispatch_total_duration += $row['duration'];
                            $oAnchorDispatch->anchor_dispatch_today_duration += $row['duration'];
                            $oAnchorDispatch->save();
                        }
                    }
                    break;
            }

            // 开始# 亲密值
            if ( $nCoin > 0 ) {
                $intimateMultiple = Kv::get(Kv::COIN_TO_INTIMATE) ?? 1;
                $intimateValue    = $nCoin * $intimateMultiple;
                if ( $intimateValue > 0 ) {
                    $oUserIntimateLog                              = new UserIntimateLog();
                    $oUserIntimateLog->intimate_log_user_id        = $nUserId;
                    $oUserIntimateLog->intimate_log_anchor_user_id = $oAnchorUser->user_id;
                    $oUserIntimateLog->intimate_log_type           = UserIntimateLog::TYPE_VIDEO_CHAT;
                    $oUserIntimateLog->intimate_log_value          = $nCoin * $intimateMultiple;
                    $oUserIntimateLog->intimate_log_coin           = $nCoin;
                    $oUserIntimateLog->intimate_log_dot            = $nDot;
                    $oUserIntimateLog->save();
                }
            }
            // 结束# 亲密值


            // 活动 聊天时长活动
            $oActivityAnchorService = new ActivityAnchorService();
            $oActivityAnchorService->save($oUserPrivateChatLog->chat_log_anchor_user_id, $oUserPrivateChatLog->duration);

//            $this->timServer->setRid($this->config->application->im->global_id);
            //推送主播违规信息
            if ( $type == 1 ) {
                $this->timServer->setUid($anchorUserId);
                $this->timServer->chatForbidden();
                $this->timServer->setUid($oUserPrivateChatLog->chat_log_user_id);
                $this->timServer->chatForbidden();
            }
            $this->timServer->setUid($anchorUserId);
            // 判断关注 邀请者有没有关注被邀请者
            $inviteeIsFollow  = UserFollow::findFirst([
                'user_id=:user_id: and to_user_id=:to_user_id:',
                'bind' => [
                    'user_id'    => $anchorUserId,
                    'to_user_id' => $oUserPrivateChatLog->chat_log_user_id,
                ]
            ]);
            $row['is_follow'] = $inviteeIsFollow ? 'Y' : 'N';
            $row['chat_log']  = $oUserPrivateChatLog->id;
            $this->timServer->hangUpPrivateChat($row);
            $this->timServer->setUid($oUserPrivateChatLog->chat_log_user_id);
            // 判断关注 被邀请者有没有关注邀请者
            $inviterIsFollow  = UserFollow::findFirst([
                'user_id=:user_id: and to_user_id=:to_user_id:',
                'bind' => [
                    'user_id'    => $oUserPrivateChatLog->chat_log_user_id,
                    'to_user_id' => $anchorUserId,
                ]
            ]);
            $row['is_follow'] = $inviterIsFollow ? 'Y' : 'N';
            $this->timServer->hangUpPrivateChat($row);

            $row['is_follow'] = $nUserId == $anchorUserId ? ($inviteeIsFollow ? 'Y' : 'N') : ($inviterIsFollow ? 'Y' : 'N');
//            $doCli = $this->getParams('do_cli', 'string');
//            if($doCli == 'hzjkb24mGJ8RWOL7GLp4U7T'){
//                $this->timServer->setUid($chat_log->chat_log_anchor_user_id);
//                $this->timServer->hangUpPrivateChat($row);
//                $this->timServer->setUid($chat_log->chat_log_user_id);
//                $this->timServer->hangUpPrivateChat($row);
//            }else{
//                if ( $type == 0 ) {
//                    if ( $chat_log->chat_log_user_id == $nUserId ) {
//                        $this->timServer->setUid($chat_log->chat_log_anchor_user_id);
//                        $this->timServer->hangUpPrivateChat($row);
//                    } else {
//                        $this->timServer->setUid($chat_log->chat_log_user_id);
//                        $this->timServer->hangUpPrivateChat($row);
//                    }
//                } else {
//                    $this->timServer->setUid($chat_log->chat_log_anchor_user_id);
//                    $this->timServer->hangUpPrivateChat($row);
//                    $this->timServer->setUid($chat_log->chat_log_user_id);
//                    $this->timServer->hangUpPrivateChat($row);
//                }
//            }
            //两个状态都要考虑
            $oAnchor = Anchor::findFirst("user_id = $anchorUserId");
            if ( $oAnchor && $oAnchor->anchor_chat_status == 2 ) {
                if ( $snatchStatus == 'N' ) {
                    $oAnchor->anchor_chat_status = 3;
                }
                if ( $oAnchorUser->user_online_status == User::USER_ONLINE_STATUS_OFFLINE ) {
                    $oAnchor->anchor_chat_status = 1;
                }
                if ( $oUserPrivateChatLog->chat_type == 'match' ) {
                    $oAnchor->anchor_total_match_duration += $oUserPrivateChatLog->duration;
                    $todayDuration                        = $oUserPrivateChatLog->duration;
                    $todayTime                            = time() - strtotime(date('Y-m-d'));
                    if ( $todayDuration > $todayTime ) {
                        $todayDuration = $todayTime;
                    }
                    $oAnchor->anchor_today_match_duration += $todayDuration;
                    $oAnchor->anchor_today_match_times    += 1;
                    $oUserMatchLog                        = UserMatchLog::findFirst([
                        'chat_log_id = :chat_log_id:',
                        'bind' => [
                            'chat_log_id' => $oUserPrivateChatLog->id
                        ]
                    ]);
                    if ( $oUserMatchLog && $oUserMatchLog->user_type == UserMatchLog::USER_TYPE_NEW ) {
                        $oAnchor->anchor_today_new_match_times += 1;
                    }
                } else {
                    $oAnchor->anchor_total_normal_duration += $oUserPrivateChatLog->duration;
                    $todayDuration                         = $oUserPrivateChatLog->duration;
                    $todayTime                             = time() - strtotime(date('Y-m-d'));
                    if ( $todayDuration > $todayTime ) {
                        $todayDuration = $todayTime;
                    }
                    $oAnchor->anchor_today_normal_duration += $todayDuration;
                    $oAnchor->anchor_today_normal_times    += 1;
                }
                $oAnchor->save();
            }

            // 删除用户状态
            $oUserVideoChatService = new UserVideoChatService();
            $oUserVideoChatService->delete($oUserPrivateChatLog->chat_log_user_id);

            $dialog->status = 0;
            $dialog->save();

//            // 让用户推出群聊
//            $this->timServer->setRid($chat_log->chat_log_anchor_user_id);
//            $this->timServer->setAccountId($chat_log->chat_log_user_id);
//            $this->timServer->leaveRoom();
            $this->delChatHeartbeat($chat_log_id);


            if ( $oUserPrivateChatLog->chat_type == 'match' ) {
                // 抢单成功 将主播加入用户的 缓存数据中
                $oMatchCenterUserAnchorService = new MatchCenterUserAnchorService($oUserPrivateChatLog->chat_log_user_id);
                $oMatchCenterUserAnchorService->save($oUserPrivateChatLog->chat_log_anchor_user_id);

                // 抢单成功 将用户加入主播的 缓存数据中  并将主播之前的数据删除
                $oMatchCenterUserAnchorServiceAnchor = new MatchCenterUserAnchorService($oUserPrivateChatLog->chat_log_anchor_user_id);
                $oMatchCenterUserAnchorServiceAnchor->save($oUserPrivateChatLog->chat_log_user_id);
                $user_match_single_anchor_interval = intval(Kv::get(Kv::USER_MATCH_SINGLE_ANCHOR_INTERVAL));
                if ( $user_match_single_anchor_interval > 0 ) {
                    $oMatchCenterUserAnchorServiceAnchor->delete_item(0, time() - $user_match_single_anchor_interval);
                }

            }

            // 守护免费通话记录
            if ( $oUserPrivateChatLog->free_times_type == UserPrivateChatLog::FREE_TIME_TYPE_GUARD ) {
                // 查找守护信息
                $oUserGuard = UserGuard::findFirst([
                    'user_id = :user_id: AND anchor_user_id = :anchor_user_id:',
                    'bind' => [
                        'user_id'        => $oUserPrivateChatLog->chat_log_user_id,
                        'anchor_user_id' => $oUserPrivateChatLog->chat_log_anchor_user_id,
                    ]
                ]);
                if ( $oUserGuard ) {
                    $oFreeTimeGuardLog                      = new FreeTimeGuardLog();
                    $oFreeTimeGuardLog->user_id             = $oUserPrivateChatLog->chat_log_user_id;
                    $oFreeTimeGuardLog->anchor_user_id      = $oUserPrivateChatLog->chat_log_anchor_user_id;
                    $oFreeTimeGuardLog->free_duration       = $noIncomeFreeTimes;
                    $oFreeTimeGuardLog->guard_level         = $oUserGuard->current_level;
                    $oFreeTimeGuardLog->guard_level_name    = $oUserGuard->current_level_name;
                    $oFreeTimeGuardLog->private_chat_log_id = $oUserPrivateChatLog->id;
                    $oFreeTimeGuardLog->create_time         = $oUserPrivateChatLog->create_time;
                    $oFreeTimeGuardLog->update_time         = $oUserPrivateChatLog->update_time;
                    $oFreeTimeGuardLog->private_chat_log_id = $oUserPrivateChatLog->id;
                    $flg                                    = $oFreeTimeGuardLog->save();
                    $this->log->info('guard:' . json_encode($oFreeTimeGuardLog->toArray()));
                    $this->log->info('insert guard free time log:' . $flg . " |||| " . json_encode($oFreeTimeGuardLog->getMessages()));
                    $oUserGuard->total_use_free_time += $noIncomeFreeTimes;
                    $oUserGuard->save();
                }

            }

        } catch ( \Phalcon\Db\Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
            $this->redis->sRem('hangUpChat', $chat_log_id);
        } catch ( \PDOException $e ) {
            $this->error($e->getCode(), $e->getMessage());
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
            $this->redis->sRem('hangUpChat', $chat_log_id);
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
            $this->redis->sRem('hangUpChat', $chat_log_id);
        }
        // 删除支付的缓存
        $oVideoChatService = new VideoChatService();
        $videoChatStr      = sprintf('%s:%s', $oUserPrivateChatLog->chat_log_user_id, $oUserPrivateChatLog->id);
        $oVideoChatService->delItem($videoChatStr);
        $this->redis->sRem('hangUpChat', $chat_log_id);

        // 判断是否需要补单  时长不超过5秒 且不是用户手动挂断 给用户补钱
//        $this->log->info("budan-----: dration:{$row['duration']}; hanguptype:{$hangUpType}; chat_log_user_id: {$chat_log->chat_log_anchor_user_id}");
        if ( $row['duration'] < 10 && $oInviterUser->user_is_superadmin == 'N' && $oUserPrivateChatLog->free_times_type == $oUserPrivateChatLog::FREE_TIME_TYPE_EMPTY ) {
            if ( $hangUpType == 'manual' && $oUserPrivateChatLog->hangup_user_id == $oUserPrivateChatLog->chat_log_user_id ) {
                //  用户手动挂断不需要补单
            } else {
                $oUserPrivateChatLog = UserPrivateChatLog::findFirst($chat_log_id);
//                $this->log->info("budan-----: budanflg:{$oUserPrivateChatLog->has_budan};free_times:{$oUserPrivateChatLog->free_times};  trueConsume:{$trueConsumeCoin}");
                if ( $oUserPrivateChatLog->has_budan == 'N' ) {
                    if ( $oUserPrivateChatLog->free_times > 0 || $trueConsumeCoin > 0 ) {
                        $UserBudan  = new UserBudan();
                        $connection = $UserBudan->getWriteConnection();
                        $connection->begin();
                        $UserBudan->admin_id = 0;
                        $UserBudan->user_id  = $oUserPrivateChatLog->chat_log_user_id;
                        if ( $oUserPrivateChatLog->free_times > 0 ) {
                            $sql                          = 'update user set user_free_match_time = user_free_match_time + 1 where user_id = ' . $oUserPrivateChatLog->chat_log_user_id;
                            $UserBudan->user_budan_type   = 'free_match_time';
                            $UserBudan->user_budan_amount = 1;
                            $UserBudan->remark            = '10秒内用户程序自动挂断补单免费时长';
                            $content                      = '尊敬的用户，系统检测刚才您的通话出现异常，现给您发放了本次聊天的消耗的免费匹配时长，请等待1分钟，匹配小姐姐吧。如重复发生聊天失败问题请退出重新登录或保存首页更新二维码卸载后重新安装';
                        } else {
                            $sql                          = "update user set user_free_coin = user_free_coin + $trueConsumeCoin,user_total_free_coin = user_total_free_coin + $trueConsumeCoin where user_id = " . $oUserPrivateChatLog->chat_log_user_id;
                            $UserBudan->user_budan_amount = 'free_coin';
                            $UserBudan->user_budan_amount = $trueConsumeCoin;
                            $UserBudan->remark            = '10秒内用户程序自动挂断补单赠送金币';
                            $content                      = '尊敬的用户，系统检测刚才您的通话出现异常，现给您发放了本次聊天的消耗的金币，请等待1分钟，继续找小姐姐聊天吧。如重复发生聊天失败问题请退出重新登录或保存首页更新二维码卸载后重新安装';
                        }
                        $connection->execute($sql);
                        $this->log->info("budan:" . $sql);
                        if ( $connection->affectedRows() <= 0 ) {
                            $connection->rollback();
                        }
                        if ( !$UserBudan->save() ) {
                            $connection->rollback();
                        }
                        $oUserPrivateChatLog->has_budan = 'Y';
                        if ( !$oUserPrivateChatLog->save() ) {
                            $connection->rollback();
                        }
                        $connection->commit();

                        // 发送系统通知
                        $this->sendGeneral($oUserPrivateChatLog->chat_log_user_id, $content, '', TRUE);
                    }
                }
            }
        }


        $this->success($row);
    }

    /**
     * 获取视频聊天信息
     */
    public function getPrivateChatInfoAction( $nUserId )
    {
        $anchor = Anchor::findFirst([
            'user_id =:user_id:',
            'bind' => [ 'user_id' => $nUserId ]
        ]);

        $minPrice = Kv::get(Kv::PRIVATE_PRICE_MIN);

        // 聊天设置最大值 根据主播等级配置
        $maxPrice = LevelConfig::getAnchorMaxPrice($anchor->anchor_level);

        $maxPrice = max($maxPrice, $minPrice);

        $return = [
            'is_anchor'         => $anchor ? TRUE : FALSE,
            'coin_name'         => Kv::get(Kv::KEY_COIN_NAME),
            'private_price'     => $anchor ? $anchor->anchor_chat_price ? $anchor->anchor_chat_price : Kv::get(Kv::PRIVATE_PRICE_MIN) : Kv::get(Kv::PRIVATE_PRICE_MIN),
            'private_price_max' => (string)$maxPrice,
            'private_price_min' => (string)$minPrice,
            'chat_status'       => $anchor ? $anchor->anchor_chat_status : '0',
            'private_title'     => $anchor ? $anchor->anchor_private_title : '',
            'share_url'         => APP_WEB_URL . '/sharePrivateChat?user_id=' . $nUserId
        ];
        $this->success($return);
    }

    /**
     * 保存私聊信息
     */
    public function updateChatInfoAction( $nUserId )
    {
        $title = $this->getParams('title', 'string', '');
        $price = $this->getParams('price', 'int', 0);
        $cover = $this->getParams('cover', 'string', '');
        $video = $this->getParams('video', 'string', '');
        $local = $this->getParams('local', 'string', '');
//        $oUser   = User::findFirst([
//            'user_id=:user_id:',
//            'bind' => [ 'user_id' => $nUserId ]
//        ]);
        try {
            $oAnchor = Anchor::findFirst([
                'user_id=:user_id:',
                'bind' => [ 'user_id' => $nUserId ]
            ]);
            if ( !$oAnchor ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            if ( $title ) {
                if ( $this->banword($title, Banword::LOCATION_PROFILE) ) {
                    throw new Exception(ResponseError::getError(ResponseError::BANWORD), ResponseError::BANWORD);
                }
            }
//            $allowPriceList = [
//                20,
//                30,
//                40,
//                50
//            ];
            $minPrice = Kv::get(Kv::PRIVATE_PRICE_MIN);

            // 聊天设置最大值 根据主播等级配置
            $maxPrice = LevelConfig::getAnchorMaxPrice($oAnchor->anchor_level);

            $maxPrice = max($maxPrice, $minPrice);
            $minPrice = intval(intval(Kv::get(Kv::PRIVATE_PRICE_MIN)) / 10) * 10;
            $maxPrice = intval(intval($maxPrice) / 10) * 10;

            $allowPriceList = [];
            for ( $priceItem = $minPrice; $priceItem <= $maxPrice; $priceItem += 10 ) {
                $allowPriceList[] = $priceItem;
            }
            if ( $price && !in_array($price, $allowPriceList) ) {
                throw new Exception(sprintf('快聊价格请设置%s到%s的值', $minPrice, $maxPrice), ResponseError::PARAM_ERROR);
            }
            if ( $title && $this->banword($title, Banword::LOCATION_PROFILE) ) {
                throw new Exception(ResponseError::getError(ResponseError::BANWORD), ResponseError::BANWORD);
            }
//        if ( $title != $oAnchor->anchor_private_title ) {
//            if ( $this->isPublish($nUserId, AppList::PUBLISH_CAN_NOT_CHANGE_PROFILE) ) {
//                $this->error(ResponseError::CHECKED_CANNOT_UPDATE);
//            }
//        }
//        $oUser->user_video_cover = $cover;
//        $oUser->user_video       = $video;
//        $oUser->save();
            $oAnchor->anchor_chat_price = $price ? $price : $oAnchor->anchor_chat_price;
//        $oAnchor->anchor_chat_status   = 3;
            $oAnchor->anchor_private_title = $title ? $title : $oAnchor->anchor_private_title;
            $oAnchor->anchor_private_local = $local ? $local : $oAnchor->anchor_private_local;
            $oAnchor->save();
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }

    /**
     * 开启一对一私聊
     */
    public function updateChatStatusAction( $nUserId )
    {
        $anchor     = Anchor::findFirst([
            'user_id =:user_id:',
            'bind' => [ 'user_id' => $nUserId ]
        ]);
        $chat_price = $this->getParams('chat_price', 'string');
        $type       = $this->getParams('type', 'int', 1);
        if ( !$anchor ) {
            $this->error(10049);
        }
        try {
            if ( $type == 1 ) {
                if ( $chat_price > 0 ) {
                    $anchor->anchor_chat_price = $chat_price;
                }
                $anchor->anchor_chat_status = 3;
            } else if ( $type == 2 ) {
                if ( $chat_price > 0 ) {
                    $anchor->anchor_chat_price = $chat_price;
                }
            } else {
                $anchor->anchor_chat_status = 0;
            }
            $anchor->save();
            $this->success();
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取视频对话列表
     */
    public function getChatDialogAction( $nUserId = 0 )
    {
        $page     = $this->getParams('page', 'int', 1);
        $pagesize = $this->getParams('pagesize', 'int', 10);
        try {
            $checkUserIds = $this->getCheckUserIds();
            if ( in_array($nUserId, $checkUserIds) ) {
                $emptyData = [
                    'items'     => [],
                    'page'      => $page,
                    'pagesize'  => $pagesize,
                    'pagetotal' => 1,
                    'total'     => 0,
                    'prev'      => 1,
                    'next'      => 1,
                ];
                $this->success($emptyData);
            }
            $builder = $this->modelsManager->createBuilder()->from([ 'a' => UserPrivateChatDialog::class ])
                ->columns('a.id,a.inviter_id,u.user_avatar,u.user_level,u.user_nickname,a.invitee_id ,
                ue.user_avatar as invitee_avatar,ue.user_level as invitee_level,
                ue.user_nickname as invitee_nickname,a.update_time,u.user_member_expire_time,
                ue.user_member_expire_time as ue_member_expire_time,a.inviter_unread,a.invitee_unread,
                u.user_sex,u.user_birth,ue.user_sex as invitee_user_sex,ue.user_birth as invitee_user_birth')
                ->join(User::class, 'u.user_id = a.inviter_id', 'u')
                ->join(User::class, 'ue.user_id = a.invitee_id', 'ue')
                ->where("a.inviter_id = {$nUserId} or a.invitee_id = {$nUserId} ")
                ->orderby("update_time DESC");
            $row     = $this->page($builder, $page, $pagesize);
            foreach ( $row['items'] as &$item ) {
                $item['invitee_id_user_is_member'] = $item['user_member_expire_time'] == 0 ? 'N' : (time() > $item['user_member_expire_time'] ? 'O' : 'Y');
                $item['invitee_id_user_is_member'] = $item['ue_member_expire_time'] == 0 ? 'N' : (time() > $item['ue_member_expire_time'] ? 'O' : 'Y');
                $item['unread']                    = $nUserId == $item['inviter_id'] ? $item['inviter_unread'] : $item['invitee_unread'];
                unset($item['inviter_unread']);
                unset($item['invitee_unread']);
            }
        } catch ( Exception $e ) {
            $this->error(ResponseError::FAIL, sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage()));
        }
        $this->success($row);
    }

    /**
     * 获取视频对话详情
     */
    public function getChatDialogDetailAction( $nUserId = 0 )
    {
        $page      = $this->getParams('page', 'int', 1);
        $pagesize  = $this->getParams('pagesize', 'int', 10);
        $dialog_id = $this->getParams('dialog_id', 'int', 0);
        try {
            $oUserPrivateChatDialog = UserPrivateChatDialog::findFirst($dialog_id);
            $builder                = $this->modelsManager->createBuilder()->from([ 'a' => UserPrivateChatLog::class ])
                ->columns('a.status,a.duration,a.create_time,a.inviter_id,u.user_avatar,u.user_level,u.user_nickname,
                a.invitee_id,ue.user_avatar as invitee_avatar,ue.user_level as invitee_level,ue.user_nickname as invitee_nickname')
                ->join(User::class, 'u.user_id = a.inviter_id', 'u')
                ->join(User::class, 'ue.user_id = a.invitee_id', 'ue')
                ->where("dialog_id={$dialog_id} ")->orderby("id DESC");
            $row                    = $this->page($builder, $page, $pagesize);
            if ( $oUserPrivateChatDialog ) {
                if ( $nUserId == $oUserPrivateChatDialog->inviter_id ) {
                    $oUserPrivateChatDialog->inviter_unread = 0;
                } else {
                    $oUserPrivateChatDialog->invitee_unread = 0;
                }
                $oUserPrivateChatDialog->save();
            }
        } catch ( Exception $e ) {
            $this->error(ResponseError::FAIL, sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage()));
        }
        $this->success($row);
    }

    //模糊自己
    public function vagueChatAction( $nUserId )
    {
        $chat_log_id = $this->getParams('chat_log');
        $type        = $this->getParams('type', 'int', 0);
        if ( empty($chat_log_id) ) {
            $this->error(10002);
        }
        $chat_log = UserPrivateChatLog::findFirst([
            'id=:id:',
            'bind' => [ 'id' => $chat_log_id ]
        ]);
        if ( !$chat_log ) {
            $this->error(10002);
        }
        $row = [ 'vague' => $type ];
        $this->timServer->setRid($this->config->application->im->global_id);
        if ( $chat_log->chat_log_user_id == $nUserId ) {
            $this->timServer->setUid($chat_log->chat_log_anchor_user_id);
            $this->timServer->vagueChat($row);
        } else {
            $this->timServer->setUid($chat_log->chat_log_user_id);
            $this->timServer->vagueChat($row);
        }
        $this->success();
    }

    public function createChatRoomAction()
    {
        $nUserId = $this->getParams('user_id');
        if ( !$nUserId ) {
            $this->error(10002);
        }
        //导入账号
        $this->timServer->account_import($nUserId);
        // 腾讯消息
        $this->timServer->setRid($nUserId);
        $this->timServer->setAccountId($nUserId);
        $this->timServer->createRoom(sprintf('直播间_%s', $nUserId));;
        $this->success();
    }

    /**
     * chatHeartbeatAction 心跳
     *
     * @param int $nUserId
     */
    public function chatHeartbeatAction( $nUserId = 0 )
    {
        $type     = $this->getParams('type');
        $chat_log = $this->getParams('chat_log');
        $this->log->info($nUserId . "心跳：" . $chat_log);
        if ( empty($type) || empty($chat_log) ) {
            $this->error(10002);
        }
        $oChatLog = UserPrivateChatLog::findFirst([
            'id=:id:',
            'bind' => [ 'id' => $chat_log ]
        ]);
        if ( empty($oChatLog) ) {
            $this->error(10002);
        }
        $this->log->info('心跳状态:' . $oChatLog->status);
        $oDialog = UserPrivateChatDialog::findFirst([
            'id=:id:',
            'bind' => [ 'id' => $oChatLog->dialog_id ]
        ]);
        if ( ($oChatLog->status == 0 || $oChatLog->status == 4) && $oDialog->status == 1 ) {
            $str                   = "{$nUserId}:{$chat_log}";
            $oChatHeartbeatService = new ChatHeartbeatService($type);
            $oChatHeartbeatService->save($str);
        }


        if ( $oChatLog->status == 4 ) {
            // 需要删除 TIM掉线增加的缓存数据
            $oChatTimCheckService = new ChatTimCheckService();
            $saveData             = sprintf('%s-%s-%s', $nUserId, $oChatLog->id, $oChatLog->chat_log_anchor_user_id == $nUserId ? 'Y' : 'N');
            $flg                  = $oChatTimCheckService->delete_item($saveData);
            if ( $flg ) {
                $this->log->info($nUserId . "心跳删除IM掉线问题:[{$oChatLog->id}]");
            }
        }

        $this->success(
            [ 'status' => $oChatLog->status ]
        );
    }

    /**
     * 删除心跳
     *
     * @param $chat_log_id 记录id
     */
    private function delChatHeartbeat( $chat_log_id )
    {
        $chat_log = UserPrivateChatLog::findFirst($chat_log_id);
        if ( !$chat_log ) {
            return TRUE;
        }
        $oUser = new ChatHeartbeatService(2);
        $str1  = "{$chat_log->chat_log_user_id}:{$chat_log_id}";
        $oUser->delUser($str1);
        $oAnchor = new ChatHeartbeatService(1);
        $str2    = "{$chat_log->chat_log_anchor_user_id}:{$chat_log_id}";
        $oAnchor->delUser($str2);

        // 删除掉用户主播的 TIM聊天中的掉线
        $oChatTimCheckService = new ChatTimCheckService();
        $saveData             = sprintf('%s-%s-%s', $chat_log->chat_log_anchor_user_id, $chat_log->id, 'Y');
        $oChatTimCheckService->delete_item($saveData);
        $saveData = sprintf('%s-%s-%s', $chat_log->chat_log_user_id, $chat_log->id, 'N');
        $oChatTimCheckService->delete_item($saveData);


        // 删掉支付
        $oChatPayService = new  ChatPayService($chat_log_id);
        $oChatPayService->delete();

        // 删除用户状态
        $oUserVideoChatService = new UserVideoChatService();
        $oUserVideoChatService->delete($chat_log->chat_log_user_id);

        // 删除流信息
        $userStreamId           = $chat_log->getStreamId('user');
        $anchorStreamId         = $chat_log->getStreamId('anchor');
        $oUserChatStreamService = new ChatStreamService($userStreamId);
        $oUserChatStreamService->delete();
        $oAnchorChatStreamService = new ChatStreamService($anchorStreamId);
        $oAnchorChatStreamService->delete();
        return TRUE;
    }


    /**
     * 主播看到的用戶列表
     * 先显示10个新VIP（即最近充值VIP 24小时内）
     * 再显示10个老VIP（VIP充值超过 24小时）
     * 再显示30个普通用户
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/users
     * @api {get} /live/anchor/users 001-190906主播看到的用户列表
     * @apiName users
     * @apiGroup Anchor
     * @apiDescription 主播看到的用户列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.batch_say_hi_total_interval  主播一键打招呼 总间隔时间
     * @apiSuccess {string} d.batch_say_hi_interval  主播一键打招呼 当前剩余间隔时间
     * @apiSuccess {string} d.item.anchor_call_flg  主播是否显示拨打按钮
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
    public function usersAction( $nUserId = 0 )
    {
        try {
            // 取在线的10个新VIP
            $now     = time();
            $endTime = time() - 24 * 3600;
//            $builder = $this->modelsManager
//                ->createBuilder()
//                ->from([ 'u' => User::class ])
//                ->leftJoin(UserRechargeActionLog::class, 'u.user_id = l.user_id', 'l')
//                ->columns('u.user_id,user_nickname,user_avatar,user_level,user_fans_total,user_follow_total,user_intro,user_sex,"N" as has_say_hi,user_online_status,user_member_expire_time,user_birth')
//                ->where("l.vip_last_time > {$endTime} AND user_is_anchor='N' AND user_is_superadmin ='N' AND user_online_status = 'Online'")
//                ->orderby('rand()');
//
//            $rowNewVip = $this->page($builder, 1, 10);

            $newVIPCacheKey  = 'anchor_uses_api:new_vip';
            $newVIPCacheData = $this->redis->get($newVIPCacheKey);
            if ( !$newVIPCacheData ) {
                $newVIPSql       = <<<NEWVIPSQL
SELECT u.user_id,user_nickname,user_avatar,user_level,user_fans_total,user_follow_total,user_intro,user_sex,"N" as has_say_hi,
user_online_status,user_member_expire_time,user_birth,u.user_coin,u.user_free_coin FROM user_recharge_action_log as l INNER JOIN `user` as u 
ON u.user_id = l.user_id WHERE l.vip_last_time > $endTime AND user_is_anchor='N' AND user_is_superadmin ='N'
 AND user_online_status = 'Online' ORDER BY user_id desc limit 100
NEWVIPSQL;
                $rowNewVipResult = $this->selfPage($newVIPSql, '', 1, 100);
                $rowNewVip       = $rowNewVipResult['items'];
                // 60秒缓存
                $this->redis->set($newVIPCacheKey, json_encode($rowNewVip), 60);
            } else {
                $rowNewVip = json_decode($newVIPCacheData, TRUE);
            }

            // 普通用户根据用户ID 排序 取100个 然后中间随机抽取相应个数
            $newVIPArr = array_random($rowNewVip, 10);

            unset($rowNewVip);

//            // 取在线的10个老VIP用户
//            $builder = $this->modelsManager
//                ->createBuilder()
//                ->from([ 'u' => User::class ])
//                ->leftJoin(UserRechargeActionLog::class, 'u.user_id = l.user_id', 'l')
//                ->columns('u.user_id,user_nickname,user_avatar,user_level,user_fans_total,user_follow_total,user_intro,user_sex,"N" as has_say_hi,user_online_status,user_member_expire_time,user_birth')
//                ->where("l.vip_last_time < {$endTime}  AND user_member_expire_time > {$now} AND user_is_anchor='N' AND user_is_superadmin ='N' AND user_online_status = 'Online'")
//                ->orderby('rand()');
//
//            $rowOldVip = $this->page($builder, 1, 10);

            $oldVIPCacheKey  = 'anchor_uses_api:old_vip';
            $oldVIPCacheData = $this->redis->get($oldVIPCacheKey);
            if ( !$oldVIPCacheData ) {
                $oldVIPSql       = <<<OLDVIPSQL
SELECT u.user_id,user_nickname,user_avatar,user_level,user_fans_total,user_follow_total,user_intro,user_sex,"N" as has_say_hi,
user_online_status,user_member_expire_time,user_birth,u.user_coin,u.user_free_coin FROM user_recharge_action_log as l INNER JOIN `user` as u 
ON u.user_id = l.user_id WHERE l.vip_last_time < $endTime  AND user_member_expire_time > $now AND 
user_is_anchor='N' AND user_is_superadmin ='N' AND user_online_status = 'Online' ORDER BY user_id desc limit 200
OLDVIPSQL;
                $rowOldVipResult = $this->selfPage($oldVIPSql, '', 1, 200);
                $rowOldVip       = $rowOldVipResult['items'];
                $this->redis->set($oldVIPCacheKey, json_encode($rowOldVip), 60);
            } else {
                $rowOldVip = json_decode($oldVIPCacheData, TRUE);
            }
            // 普通用户根据用户ID 排序 取500个 然后中间随机抽取相应个数
            $oldVIPArr = array_random($rowOldVip, 10);
            unset($rowOldVip);

            // 取普通用户 和上面的 新VIP 老VIP 总共返回50条数据
            $normalCount = 50 - count($newVIPArr) - count($oldVIPArr);

//            $builder = $this->modelsManager
//                ->createBuilder()
//                ->from([ 'u' => User::class ])
//                ->columns('user_id,user_nickname,user_avatar,user_level,user_fans_total,user_follow_total,user_intro,user_sex,"N" as has_say_hi,user_online_status,user_member_expire_time,user_birth')
//                ->where("user_is_anchor='N' AND user_is_superadmin ='N' AND user_online_status = 'Online' AND user_member_expire_time < {$now}")
//                ->orderby('u.user_id desc');


            $normalCacheKey  = 'anchor_uses_api:normal';
            $normalCacheData = $this->redis->get($normalCacheKey);
            if ( !$normalCacheData ) {
                $dataSql = <<<DATASQL
SELECT user_id,user_nickname,user_avatar,user_level,user_fans_total,user_follow_total,user_intro,user_sex,"N" as has_say_hi,
user_online_status,user_member_expire_time,user_birth,user_coin,user_free_coin FROM `user` WHERE user_is_anchor='N' AND user_is_superadmin ='N' 
AND user_online_status = 'Online' AND user_member_expire_time < $now ORDER BY user_id desc limit 200
DATASQL;

                $rowNormalResult = $this->selfPage($dataSql, '', 1, 200);
                $rowNormal       = $rowNormalResult['items'];
                $this->redis->set($normalCacheKey, json_encode($rowNormal), 60);
            } else {
                $rowNormal = json_decode($normalCacheData, TRUE);
            }
            $normalArr = array_random($rowNormal, $normalCount);
            unset($rowNormal);

            $items = array_merge($newVIPArr, $oldVIPArr, $normalArr);

            $oAnchorSayhiService = new AnchorSayhiService($nUserId);
            $existSayHi          = $oAnchorSayhiService->getData();

            // 获取主播信息
            $oAnchor = Anchor::findFirst([
                'user_id = :user_id:',
                'bind' => [
                    'user_id' => $nUserId
                ]
            ]);

            $oAnchorPrice = 10000000000;
            if ( $oAnchor ) {
                $oAnchorPrice = $oAnchor->anchor_chat_price;
            }

            foreach ( $items as &$item ) {
                if ( in_array($item['user_id'], $existSayHi) ) {
                    $item['has_say_hi'] = 'Y';
                }
                $item['chat_room_id']    = UserChatDialog::getChatRoomId($item['user_id'], $nUserId);
                $item['user_is_member']  = $item['user_member_expire_time'] == 0 ? 'N' : (time() > $item['user_member_expire_time'] ? 'O' : 'Y');
                $totalCoin               = $item['user_coin'] + $item['user_free_coin'];
                $item['anchor_call_flg'] = 'N';
                if ( $oAnchorPrice <= $totalCoin && $item['user_online_status'] == 'Online' ) {
                    $item['anchor_call_flg'] = 'Y';
                }
                unset($item['user_coin']);
                unset($item['user_free_coin']);

            }


            // 获取一键打招呼的当前间隔时间 和总间隔时间
            $oAnchorBatchSayhiService     = new AnchorBatchSayhiService($nUserId);
            $lastActionTime               = $oAnchorBatchSayhiService->getData();
            $anchor_batch_say_hi_interval = Kv::get(Kv::ANCHOR_BATCH_SAY_HI_INTERVAL);
            $batch_say_hi_interval        = $anchor_batch_say_hi_interval;
            if ( !$lastActionTime || $lastActionTime < time() - $anchor_batch_say_hi_interval ) {
                $batch_say_hi_interval = '0';
            } else if ( $lastActionTime + $anchor_batch_say_hi_interval > time() ) {
                $batch_say_hi_interval = $lastActionTime + $anchor_batch_say_hi_interval - time();
            }

            $row = [
                'items'                       => $items,
                'count'                       => count($items),
                'new_vip_total'               => count($newVIPArr),
                'old_vip_total'               => count($oldVIPArr),
                'batch_say_hi_total_interval' => $anchor_batch_say_hi_interval,
                'batch_say_hi_interval'       => (string)$batch_say_hi_interval,
            ];
        } catch ( Exception $e ) {
            $this->error(
                ResponseError::FAIL,
                sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage())
            );
        }
        $this->success($row);
    }

    /**
     * 一对一私聊
     * @param int $nUserId
     */
    public function privateChatMinuteBakAction( $nUserId = 0 )
    {
        $chat_log_id = $this->getParams('chat_log', 'int', 0);
        try {
            $chat_log = UserPrivateChatLog::findFirst([
                'id=:id:',
                'bind' => [ 'id' => $chat_log_id ]
            ]);
            if ( !$chat_log ) {
                $this->error(10002);
            }
            $dialog = UserPrivateChatDialog::findFirst($chat_log->dialog_id);
            if ( $dialog->status == 0 ) {
                $this->success();
            }
            $oUserChatPay = UserChatPay::findFirst([
                'user_id=:user_id: AND anchor_user_id=:anchor_user_id: AND update_time >=:update_time: AND chat_log_id=:chat_log_id: ORDER BY update_time DESC',
                'bind' => [
                    'user_id'        => $chat_log->chat_log_user_id,
                    'anchor_user_id' => $chat_log->chat_log_anchor_user_id,
                    'chat_log_id'    => $chat_log_id,
                    'update_time'    => time() - 40,
                ]
            ]);
            // 60秒内不重复收费
            $user = User::findFirst($chat_log->chat_log_user_id);
            if ( $oUserChatPay ) {
                $row['user'] = [
                    'user_coin' => $user->user_coin + $user->user_free_coin,
                ];
                $this->success($row);
            }
            // Start a transaction
            $this->db->begin();
            $oUserAnchor = User::findFirst("user_id={$chat_log->chat_log_anchor_user_id}");
            $oAnchor     = Anchor::findFirst("user_id = {$chat_log->chat_log_anchor_user_id}");
            $chat_fee    = $oAnchor->anchor_chat_price;
            // 记录私聊支付
            $oUserChatPay                 = new UserChatPay();
            $oUserChatPay->user_id        = $nUserId;
            $oUserChatPay->anchor_user_id = $chat_log->chat_log_anchor_user_id;
            $oUserChatPay->chat_log_id    = $chat_log_id;
            $oUserChatPay->chat_fee       = $chat_fee;
            $oUserChatPay->group_id       = $oUserAnchor->user_group_id;
            if ( $oUserChatPay->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserChatPay->getMessages())), ResponseError::OPERATE_FAILED);
            }
            // 操作用户的数据
            $user->user_coin -= $chat_fee;
            if ( $user->user_coin < 0 ) {
                $this->db->rollback();
                throw new Exception(ResponseError::getError(ResponseError::USER_COIN_NOT_ENOUGH), ResponseError::USER_COIN_NOT_ENOUGH);
            }
            if ( $user->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $user->getMessages())), ResponseError::OPERATE_FAILED);
            }
            // 记录用户流水
            $oUserFinanceLog                      = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type    = UserFinanceLog::AMOUNT_COIN;
            $oUserFinanceLog->user_id             = $nUserId;
            $oUserFinanceLog->user_current_amount = $user->user_coin;
            $oUserFinanceLog->user_last_amount    = $user->user_coin + $chat_fee;
            $oUserFinanceLog->consume_category_id = UserConsumeCategory::PRIVATE_CHAT;
            $oUserFinanceLog->consume             = -$chat_fee;
            $oUserFinanceLog->remark              = '一对一私聊计时收费';
            $oUserFinanceLog->flow_id             = $chat_log_id;
            $oUserFinanceLog->group_id            = $oUserAnchor->user_group_id;
            if ( $oUserFinanceLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())), ResponseError::OPERATE_FAILED);
            }
            // 操作主播的数据
            $nRatio = Kv::get(Kv::KEY_COIN_TO_DOT_RATIO);
            if ( $nRatio > 0 && $nRatio < 100 ) {
                $anchor_dot = sprintf('%.2f', $oAnchor->anchor_chat_price * ($nRatio / 100));
            } else {
                $anchor_dot = $oAnchor->anchor_chat_price;
            }
            $oUserAnchor->user_dot           += $anchor_dot;
            $oUserAnchor->user_collect_total += $anchor_dot;
            if ( $oUserAnchor->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserAnchor->getMessages())), ResponseError::OPERATE_FAILED);
            }
            // 记录主播流水
            $oUserFinanceLog                      = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type    = UserFinanceLog::AMOUNT_DOT;
            $oUserFinanceLog->user_id             = $chat_log->chat_log_anchor_user_id;
            $oUserFinanceLog->user_current_amount = $oUserAnchor->user_dot;
            $oUserFinanceLog->user_last_amount    = $oUserAnchor->user_dot - $anchor_dot;
            $oUserFinanceLog->consume_category_id = UserConsumeCategory::PRIVATE_CHAT;
            $oUserFinanceLog->consume             = +$anchor_dot;
            $oUserFinanceLog->remark              = '一对一私聊计时收益';
            $oUserFinanceLog->flow_id             = $chat_log_id;
            $oUserFinanceLog->group_id            = $oUserAnchor->user_group_id;
            $oUserFinanceLog->consume_source      = -$chat_fee;
            if ( $oUserFinanceLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())), ResponseError::OPERATE_FAILED);
            }
            // Commit a transaction
            $this->db->commit();
            $row['user'] = [
                'user_coin' => sprintf('%.2f', $user->user_coin + $user->user_free_coin),
            ];
        } catch ( \Phalcon\Db\Exception $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( \PDOException $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/privateChatInfo
     * @api {get} /live/anchor/privateChatInfo 一对一视频聊天信息
     * @apiName privateChatInfo
     * @apiGroup VideoChat
     * @apiDescription 一对一视频聊天信息
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} chat_log 视频聊天id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} chat_log 视频聊天id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {number} d.chat_log 聊天id
     * @apiSuccess {number} d.status 状态
     * @apiSuccess {number} d.user_id 对方用户id
     * @apiSuccess {String} d.user_nickname 对方昵称
     * @apiSuccess {String} d.user_avatar 对方头像
     * @apiSuccess {String} d.user_level 对方等级
     * @apiSuccess {String} d.play_rtmp 对方拉流地址
     * @apiSuccess {String} d.play_flv 对方拉流地址
     * @apiSuccess {String} d.play_m3u8 对方拉流地址
     * @apiSuccess {String} d.push_url 自己的推流地址
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *       "c": 0,
     *       "m": "请求成功",
     *       "d": {
     *               "chat_log": "792",
     *               "status": "2",
     *               "user_id": "178",
     *               "user_nickname": "是一只薛定谔的猫",
     *               "user_avatar": "https://lebolive-1255651273.image.myqcloud.com/image/2018/08/08/922c515182986bc3b3ae3176005ac287",
     *               "user_level": "1",
     *               "play_rtmp": "rtmp://18640.liveplay.myqcloud.com/live/18640_lebo_178_792_2?bizid=18640&txSecret=7df2eaf266bc915419e7e00af90f3eda&txTime=5B85717F",
     *               "play_flv": "http://18640.liveplay.myqcloud.com/live/18640_lebo_178_792_2.flv?bizid=18640&txSecret=7df2eaf266bc915419e7e00af90f3eda&txTime=5B85717F",
     *               "play_m3u8": "http://18640.liveplay.myqcloud.com/live/18640_lebo_178_792_2.m3u8?bizid=18640&txSecret=7df2eaf266bc915419e7e00af90f3eda&txTime=5B85717F",
     *               "push_url": "rtmp://18640.livepush.myqcloud.com/live/18640_lebo_172_792_2?bizid=18640&txSecret=da294233ab585766c2d9afc689314cc2&txTime=5B85717F"
     *       },
     *       "t": 1535338576
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function privateChatInfoAction( $nUserId = 0 )
    {

        $chat_log_id = $this->getParams('chat_log');
        try {
            $oUserPrivateChatLog = UserPrivateChatLog::findFirst([
                'id=:id:',
                'bind' => [ 'id' => $chat_log_id ]
            ]);
            if ( !$oUserPrivateChatLog ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            if ( !in_array($nUserId, [
                $oUserPrivateChatLog->chat_log_anchor_user_id,
                $oUserPrivateChatLog->chat_log_user_id
            ]) ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            // 获取网宿的共同推流地址
            $this->liveServer->setStreamName($chat_log_id);
            $wangsuPushInfo = $this->liveServer->pushUrl();

            //不是邀请者 或者被邀请者 看不到
            if ( $nUserId == $oUserPrivateChatLog->chat_log_user_id ) {
                //邀请者看到的是自己的推流地址 对方的信息 和对方的拉流地址
                $inviteeUser = User::findFirst($oUserPrivateChatLog->chat_log_anchor_user_id);
                $this->liveServer->setStreamName($inviteeUser->user_id . '_' . $oUserPrivateChatLog->id . '_2');
                $row = [
                    'chat_log'      => $oUserPrivateChatLog->id,
                    'status'        => $oUserPrivateChatLog->status,
                    'user_id'       => $inviteeUser->user_id,
                    'user_nickname' => $inviteeUser->user_nickname,
                    'user_avatar'   => $inviteeUser->user_avatar,
                    'user_level'    => $inviteeUser->user_level,
                    'play_rtmp'     => $this->liveServer->playUrl('rtmp'),
                    'play_flv'      => $this->liveServer->playUrl('flv'),
                    'play_m3u8'     => $this->liveServer->playUrl('m3u8'),
                    'wangsu'        => [
                        'push_url' => $wangsuPushInfo['push_url']
                    ]
                ];
                $this->liveServer->setStreamName($nUserId . '_' . $oUserPrivateChatLog->id . '_2');
                $row['push_url'] = $this->liveServer->pushUrl()['push_url'];
            } else {
                //被邀请者看到的是自己的推流地址 对方的信息 和对方的拉流地址
                $inviterUser = User::findFirst($oUserPrivateChatLog->chat_log_user_id);
                $this->liveServer->setStreamName($inviterUser->user_id . '_' . $oUserPrivateChatLog->id . '_2');
                $row = [
                    'chat_log'      => $oUserPrivateChatLog->id,
                    'status'        => $oUserPrivateChatLog->status,
                    'user_id'       => $inviterUser->user_id,
                    'user_nickname' => $inviterUser->user_nickname,
                    'user_avatar'   => $inviterUser->user_avatar,
                    'user_level'    => $inviterUser->user_level,
                    'play_rtmp'     => $this->liveServer->playUrl('rtmp'),
                    'play_flv'      => $this->liveServer->playUrl('flv'),
                    'play_m3u8'     => $this->liveServer->playUrl('m3u8'),
                    'wangsu'        => [
                        'push_url' => $wangsuPushInfo['push_url']
                    ]
                ];
                $this->liveServer->setStreamName($nUserId . '_' . $oUserPrivateChatLog->id . '_2');
                $row['push_url'] = $this->liveServer->pushUrl()['push_url'];
                $anchor = Anchor::findFirst([
                    'user_id' =>  $oUserPrivateChatLog->chat_log_anchor_user_id,
                ]);
                $row['price'] = $anchor->anchor_chat_price;
                if($nUserId == $oUserPrivateChatLog->chat_log_user_id) {
                    $oUser = User::findFirst($nUserId);
                    $row['price'] = $oUser->getVip1V1VideoPrice($anchor->anchor_chat_price);
                }

            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/sendSnatchChat
     * @api {post} /live/anchor/sendSnatchChat 发起抢聊
     * @apiName anchor-sendSnatchChat
     * @apiGroup Chat
     * @apiDescription 发起抢聊
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} anchor_user_id 主播id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} anchor_user_id 主播id
     * @apiSuccess {number} c 返回码   10077 时，为主播当前空闲，再直接请求邀请
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {String="N","Y"} d.snatch_flg 是否抢聊成功
     * @apiSuccess {number} d.interval_coin  步长
     * @apiSuccess {number} d.now_guard_coin  当前守护金额
     * @apiSuccess {number} d.current_user_guard_coin   当前自己已买的守护金额
     * @apiSuccess {number} d.shouldPayCoin  还需购买多少金币成为守护
     * @apiSuccess {number} d.chat_log  抢聊成功后，显示当前聊天的id
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *          "c": 0,
     *          "m": "请求成功",
     *          "d": {
     *                  "snatch_flg": "N",
     *                  "interval_coin": 10,
     *                  "now_guard_coin": "100",
     *                  "current_user_guard_coin": "0",
     *                  "shouldPayCoin": "100",
     *                  "chat_log": 0
     *          },
     *          "t": "1542858743"
     *      }
     *  ###   飘屏推送  （发动抢聊特权）
     *    {
     *        "type": "scroll_msg",
     *        "msg": "飘屏消息",
     *        "data": {
     *                "type": "start_snatch",
     *                "info": {
     *                    "user_nickname": "1181732245amxij11151741020",
     *                    "user_avatar": "http://tvax4.sinaimg.cn/crop.0.0.40.40.180/007dqVi7ly8ftm2u9xgx9j3014014a9t.jpg",
     *                    "user_level": "1",
     *                    "anchor_user_nickname": "Steven09131112487",
     *                    "anchor_user_avatar": "http://thirdwx.qlogo.cn/mmopen/vi_32/DYAIOgq83eqiaMzLBn0wU7UqvVEsicpYunuqbxta3QKiaCBnpibBrCvluCDqH0ZJiaq7pue7DnC7yh2ZNMYaoVj9JCw/132",
     *                    "anchor_user_level": "1"
     *                    "content": "1181732245amxij11151741020 对 Steven09131112487 发动了守护特权抢聊"
     *            }
     *        }
     *    }
     *  #### 推送
     *     type:   send_snatch
     *     data:
     *            user_id  用户id
     *            user_nickname  用户昵称
     *            snatch_id  抢聊id
     *
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function sendSnatchChatAction( $nUserId = 0 )
    {
        $this->forceUpdate($nUserId);
        $anchorUserId = $this->getParams('anchor_user_id');
        try {
            $oUserVideoChatService = new UserVideoChatService();
            $oUserVideoChatService->save($nUserId);
            $oUser = User::findFirst([
                'user_id = :user_id:',
                'bind' => [ 'user_id' => $nUserId ]
            ]);
            if ( $oUser->user_is_anchor == 'Y' ) {
                throw new Exception(ResponseError::getError(ResponseError::FORBIDDEN_ANCHOR_CALL_ANCHOR), ResponseError::FORBIDDEN_ANCHOR_CALL_ANCHOR);
            }
            if ( $oUser->user_is_superadmin == 'S' ) {
                throw new Exception('该账号暂不支持此功能哦', ResponseError::PARAM_ERROR);
            }
            $anchor = Anchor::findFirst([
                'user_id = :user_id:',
                'bind' => [ 'user_id' => $anchorUserId ]
            ]);
            if ( !$anchor ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }

            $snatchFlg = 'Y';
            // 该主播的守护金币
            $nowGuardCoin = intval(Kv::get(Kv::GUARD_MIN_COIN));
            // 该用户当前的守护金币
            $currentUserGuardCoin = 0;
            // 还需支付
            // 每次挤下守护 需要超过的金币数
            $intervalGuardCoin = intval(Kv::get(Kv::GUARD_INTERVAL_COIN));
            if ( $anchor->anchor_guard_id != $nUserId ) {
                $nowGuardCoin = $anchor->anchor_guard_coin;
                // 该用户不是当前守护
                // 判断该用户对该主播的守护值
                $oUserGuard = UserGuard::findFirst([
                    'user_id = :user_id: AND anchor_user_id = :anchor_user_id:',
                    'bind'  => [
                        'user_id'        => $nUserId,
                        'anchor_user_id' => $anchorUserId
                    ],
                    'cache' => [
                        'lifetime' => 3600,
                        'key'      => UserGuard::getCacheKey($nUserId . '_' . $anchorUserId)
                    ]
                ]);
                if ( $oUserGuard ) {
                    $currentUserGuardCoin = $oUserGuard->total_coin;
                }
                $snatchFlg = 'N';
            }
            $shouldPayCoin = $nowGuardCoin - $currentUserGuardCoin + $intervalGuardCoin;
            $row           = [
                'snatch_flg'              => $snatchFlg,
                'interval_coin'           => (int)$intervalGuardCoin,
                'now_guard_coin'          => (string)$nowGuardCoin,
                'current_user_guard_coin' => (string)$currentUserGuardCoin,
                'shouldPayCoin'           => (string)$shouldPayCoin,
                'chat_log'                => 0,
            ];
            if ( $snatchFlg == 'N' ) {
                // 抢聊失败，需要购买守护
                $this->success($row);
            }

            if ( $oUser->user_coin + $oUser->user_free_coin < $anchor->anchor_chat_price ) {
                throw new Exception(ResponseError::getError(ResponseError::USER_COIN_NOT_ENOUGH), ResponseError::USER_COIN_NOT_ENOUGH);
            }

            if ( $anchor->anchor_chat_status == 3 ) {
                throw new Exception(ResponseError::getError(ResponseError::NOT_IN_CHAT), ResponseError::NOT_IN_CHAT);
            }
            if ( $anchor->anchor_chat_status != 2 ) {
                throw new Exception(ResponseError::getError(ResponseError::NOT_ONLINE), ResponseError::NOT_ONLINE);
            }
            $oUserPrivateChatLog = UserPrivateChatLog::findFirst([
                'chat_log_anchor_user_id=:chat_log_anchor_user_id: AND status = 4',
                'bind'  => [ 'chat_log_anchor_user_id' => $anchorUserId ],
                'order' => 'id desc'
            ]);
            if ( !$oUserPrivateChatLog ) {
                throw new Exception(ResponseError::getError(ResponseError::NOT_IN_CHAT), ResponseError::NOT_IN_CHAT);
            }
            if ( $oUserPrivateChatLog->status == 1 || $oUserPrivateChatLog->create_time > time() - 70 ) {
                // 通话不足一分钟，不能抢单
                throw new Exception(ResponseError::getError(ResponseError::NOT_SNATCH_CHAT), ResponseError::NOT_SNATCH_CHAT);
            }
            // 2.1.0版本判断 正在聊天的用户是iOS版本 将不能发起抢聊  显示提示为通话不足1分钟
            $lastInviterUser = UserAccount::findFirst([
                'user_id = :user_id:',
                'bind' => [ 'user_id' => $oUserPrivateChatLog->chat_log_user_id ]
            ]);
            if ( $lastInviterUser->user_os_type == 'iOS' ) {
//                throw new Exception('暂时不支持跨系统抢聊 ', ResponseError::NOT_SNATCH_CHAT);
            }

            $oUserPrivateChatLog->snatch_user_id = $nUserId;
            $oUserPrivateChatLog->save();
            // 生成一条抢聊记录
            $oUserSnatchLog                       = new UserSnatchLog();
            $oUserSnatchLog->user_id              = $nUserId;
            $oUserSnatchLog->anchor_user_id       = $oUserPrivateChatLog->chat_log_anchor_user_id;
            $oUserSnatchLog->snatched_user_id     = $oUserPrivateChatLog->chat_log_user_id;
            $oUserSnatchLog->chat_log_id          = 0;
            $oUserSnatchLog->snatched_chat_log_id = $oUserPrivateChatLog->id;
            $oUserSnatchLog->status               = 'C';
            $oUserSnatchLog->save();

            $aPushMessage = [
                'user_id'       => $nUserId,
                'user_nickname' => $oUser->user_nickname,
                'snatch_id'     => $oUserSnatchLog->id,
                'price'         => $oUser->getVip1V1VideoPrice($anchor->anchor_chat_price)
            ];
            $userArr      = [
                $oUserPrivateChatLog->chat_log_anchor_user_id,
                $oUserPrivateChatLog->chat_log_user_id,
            ];
            $this->timServer->setUid($userArr);
            $this->timServer->sendSnatchChatBatch($aPushMessage);

            $row['price']     = $aPushMessage['price'];
            $row['snatch_id'] = $oUserSnatchLog->id;
            $row['chat_log']  = $oUserPrivateChatLog->id;


            // 发送飘屏
            $this->timServer->setRid(APP_ENV == 'dev' ? 'total_user_dev' : 'total_user');
            $oAnchorUser = User::findFirst($anchorUserId);
            $this->timServer->sendScrollMsg([
                'type' => 'start_snatch',
                'info' => [
                    'user_nickname'        => $oUser->user_nickname,
                    'user_avatar'          => $oUser->user_avatar,
                    'user_level'           => $oUser->user_level,
                    'anchor_user_nickname' => $oAnchorUser->user_nickname,
                    'anchor_user_avatar'   => $oAnchorUser->user_avatar,
                    'anchor_user_level'    => $oAnchorUser->user_level,
                    'title'                => sprintf('%s 对 %s', $oUser->user_nickname, $oAnchorUser->user_nickname),
                    'content'              => '发动了守护特权抢聊',
                ]
            ]);

        } catch ( Exception $e ) {
            $oUserVideoChatService = new UserVideoChatService();
            $oUserVideoChatService->delete($nUserId);
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/checkSnatch
     * @api {post} /live/anchor/checkSnatch 判断抢聊状态
     * @apiName checkSnatch
     * @apiGroup Chat
     * @apiDescription 判断抢聊状态
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} snatch_id 抢聊id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} snatch_id 抢聊id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.status 状态  Y为成功 N为失败
     * @apiSuccess {string} d.chat_log 状态  新聊天记录id
     * @apiSuccess {string} d.user_id 状态  抢聊用户id
     * @apiSuccess {string} d.user_nickname 抢聊用户昵称
     * @apiSuccess {string} d.user_avatar 抢聊用户头像
     * @apiSuccess {string} d.user_level 抢聊用户等级
     * @apiSuccess {string} d.new_live_key 新聊天房间key
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": {
     *                 "status": "N",
     *                 "chat_log": "100",
     *                 "user_id": "0",
     *                 "user_nickname": "100",
     *                 "user_avatar": "111",
     *                 "user_level": "1",
     *                 "new_live_key": "123213213213dfgdfsdfdafsdfsdfdsf"
     *         },
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
    public function checkSnatchAction( $nUserId = 0 )
    {
        $sChatLog = $this->getParams('snatch_id');
        try {
            $oUserSnatchLog = UserSnatchLog::findFirst($sChatLog);
            if ( !$oUserSnatchLog ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $newRoomKey = $this->timServer->genPrivateMapKey($nUserId, $oUserSnatchLog->chat_log_id);
            $oUser      = User::findFirst($oUserSnatchLog->user_id);
            if ( $oUserSnatchLog->status == 'C' && $oUserSnatchLog->create_time < time() - 8 ) {
                // 抢聊状态为N 且 创建时长 据现在超过8秒 即抢聊用户没有请求第二次确认抢聊
                $oUserSnatchLog->status = 'N';
                $oUserSnatchLog->save();
                $oUserVideoChatService = new UserVideoChatService();
                $oUserVideoChatService->delete($oUserSnatchLog->user_id);
            }
            $row = [
                'status'        => $oUserSnatchLog->status,
                'chat_log'      => $oUserSnatchLog->chat_log_id,
                'user_id'       => $oUser->user_id,
                'user_nickname' => $oUser->user_nickname,
                'user_avatar'   => $oUser->user_avatar,
                'user_level'    => $oUser->user_level,
                'new_live_key'  => $newRoomKey,
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/snatchChat
     * @api {post} /live/anchor/snatchChat 确定抢聊
     * @apiName snatchChat
     * @apiGroup Chat
     * @apiDescription 确定抢聊
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} chat_log 抢聊聊天id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} chat_log 抢聊聊天id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {number} d.last_chat_log 抢聊的id
     * @apiSuccess {String} d.last_live_key  进入之前的房间的key
     * @apiSuccess {String} d.new_chat_log  新的聊天id
     * @apiSuccess {String} d.new_live_key  进入新房间的key
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *          "c": 0,
     *          "m": "请求成功",
     *          "d": {
     *                  "last_chat_log": "4199",
     *                  "last_live_key": "eJxNjl1PgzAYhf8L10YLZZOZeFGh4BT2IRsuiwnpoLBuCKwUGDP*d7s5jFdv8jznvDlfysL1b0kUFXUuQtGVVHlQgHJzwSymuWAJo1xCqPaYlCWLQyJCyON-6SrehxclmaoDoA6gDo2rpMeScRqSRPw*A32robxiRS6ZJhuqJg34k4J9nveoA10zhvcjoF15XVG*qROpEEKWd8LtEvrZKkUIO9vNaH86SI4*7trzQY-9QJbKhoc981nL1qsuEn48nGWWc0ydgz5PZnjq2dOJ4zawtXeBltVoujTHZmobmO-eFg1hXfk*LiyiT6wywHW6tvQnauaCuBYKXrcvyvcPF*lk2g__"
     *                  "new_chat_log": "4200",
     *                  "new_live_key": "eJxNjl1PgzAYhf8L10YLZZOZeFGh4BT2IRsuiwnpoLBuCKwUGDP*d7s5jFdv8jznvDlfysL1b0kUFXUuQtGVVHlQgHJzwSymuWAJo1xCqPaYlCWLQyJCyON-6SrehxclmaoDoA6gDo2rpMeScRqSRPw*A32robxiRS6ZJhuqJg34k4J9nveoA10zhvcjoF15XVG*qROpEEKWd8LtEvrZKkUIO9vNaH86SI4*7trzQY-9QJbKhoc981nL1qsuEn48nGWWc0ydgz5PZnjq2dOJ4zawtXeBltVoujTHZmobmO-eFg1hXfk*LiyiT6wywHW6tvQnauaCuBYKXrcvyvcPF*lk2g__"
     *          },
     *          "t": "1542867902"
     *      }
     *  // 推送
     *     type:   start_snatch
     *     data:
     *            chat_log  新聊天id
     *            user_id  用户id
     *            user_nickname  用户昵称
     *            user_avatar  用户头像
     *            user_level  用户等级
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function snatchChatAction( $nUserId = 0 )
    {

        $sChatLog = $this->getParams('chat_log');

        try {
            $oUserPrivateChatLogLast = UserPrivateChatLog::findFirst($sChatLog);
            if ( !$oUserPrivateChatLogLast ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }

            // 判断是否超过抢聊时间了
            $oUserSnatchLog = UserSnatchLog::findFirst([
                'snatched_chat_log_id = :snatched_chat_log_id: AND user_id = :user_id:',
                'bind'  => [
                    'snatched_chat_log_id' => $sChatLog,
                    'user_id'              => $nUserId
                ],
                'order' => 'id desc'
            ]);
            if ( $oUserSnatchLog->status == 'C' && $oUserSnatchLog->create_time < time() - 8 ) {
                // 抢聊状态为N 且 创建时长 据现在超过8秒 即抢聊用户没有请求第二次确认抢聊
                $oUserSnatchLog->status = 'N';
                $oUserSnatchLog->save();
                throw new Exception(ResponseError::getError(ResponseError::SNATCH_OVERTIME), ResponseError::SNATCH_OVERTIME);
            }

            /**
             * 挂断前一个聊天
             **/
            $result = $this->httpRequest(sprintf('%s/v1/live/anchor/hangupChat?%s', $this->config->application->api_url, http_build_query([
                'uid'          => $nUserId,
                'debug'        => 1,
                'chat_log'     => $sChatLog,
                'cli_api_key'  => $this->config->application->cli_api_key,
                'hang_up_type' => 'auto',
                'detail'       => '被抢聊'
            ])));

            $this->log->info($result);

            $anchorUserId = $oUserPrivateChatLogLast->chat_log_anchor_user_id;

            // 创建一条聊天记录
            $dialog_model   = new UserPrivateChatDialog();
            $dialog_id      = $dialog_model->getDialogId($nUserId, $anchorUserId);
            $dialog         = UserPrivateChatDialog::findFirst($dialog_id);
            $dialog->status = 1;
            $dialog->save();

            $anchor = Anchor::findFirst([
                'user_id = :user_id:',
                'bind' => [ 'user_id' => $anchorUserId ]
            ]);
            // 如果主播是签约主播 且当前是签约时段
            if ( $anchor->anchor_is_sign == 'Y' ) {
                $oAnchorUser = User::findFirst($anchorUserId);
                AnchorSignStat::signAnchorStatAdd($oAnchorUser, $anchor, AnchorSignStat::TYPE_CALLED);
            }
            $oUserPrivateChatLog                          = new UserPrivateChatLog();
            $oUserPrivateChatLog->chat_log_user_id        = $nUserId;
            $oUserPrivateChatLog->chat_log_anchor_user_id = $anchorUserId;
            $oUserPrivateChatLog->inviter_id              = $nUserId;
            $oUserPrivateChatLog->invitee_id              = $anchorUserId;
            $chatLogId                                    = $oUserPrivateChatLog->addData($nUserId, $anchorUserId, 4, $dialog_id, UserPrivateChatLog::CHAT_TYPE_NORMAL, 'Y');
//            var_dump(['user_id' => $nUserId, 'anchor_user_id' => $anchorUserId,'dialog_id' =>  $dialog_id,'chat_log_id' => $chatLogId]);

            $newRoomKey   = $this->timServer->genPrivateMapKey($nUserId, $chatLogId);
            $oldRoomKey   = $this->timServer->genPrivateMapKey($nUserId, $oUserPrivateChatLogLast->id);
            $oUser        = User::findFirst($nUserId);
            $aPushMessage = [
                'chat_log'      => $chatLogId,
                'user_id'       => $oUser->user_id,
                'user_nickname' => $oUser->user_nickname,
                'user_avatar'   => $oUser->user_avatar,
                'user_level'    => $oUser->user_level,
                'new_live_key'  => $newRoomKey,
            ];
            // 给之前的聊天的两人发送开始抢聊通知
            $this->timServer->setUid([
                $oUserPrivateChatLogLast->chat_log_anchor_user_id,
                $oUserPrivateChatLogLast->chat_log_user_id
            ]);
            $this->timServer->startSnatchChatBatch($aPushMessage);

            // 抢聊用户进入房间
            $createTime = time();
            // 记录聊天开始 可以开始扣费 (延迟1秒)
            $oVideoChatService = new VideoChatService();
            $videoChatStr      = sprintf('%s:%s', $oUserPrivateChatLog->chat_log_user_id, $chatLogId);
            $oVideoChatService->save($videoChatStr, date('s', $createTime));

            // 此处第一次付费
            $result = $this->httpRequest(sprintf('%s/v1/live/anchor/privateChatMinuteNew?%s', $this->config->application->api_url, http_build_query([
                'uid'         => $nUserId,
                'debug'       => 1,
                'chat_log'    => $chatLogId,
                'cli_api_key' => $this->config->application->cli_api_key,
            ])));

            //删除上次礼物收益
            $oVideoChatService->deleteGiftData(sprintf("%s:%s", $oUserPrivateChatLog->chat_log_anchor_user_id, $oUserPrivateChatLog->chat_log_user_id));
            //删除上次游戏收益
            $oVideoChatService->deleteChatGameData(sprintf("%s:%s", $oUserPrivateChatLog->chat_log_anchor_user_id, $oUserPrivateChatLog->chat_log_user_id));
            $this->log->info($nUserId . ' 用户进入房间结束：' . $chatLogId . "第一次扣费结果：" . $result);
//        }

            $oUserSnatchLog->status      = 'Y';
            $oUserSnatchLog->chat_log_id = $chatLogId;
            $oUserSnatchLog->save();


            // 将主播的聊天状态改为2
            $anchor->anchor_chat_status = 2;
            $anchor->save();
            $row = [
                'last_chat_log' => $oUserPrivateChatLogLast->id,
                'last_live_key' => $oldRoomKey,
                'new_chat_log'  => $chatLogId,
                'new_live_key'  => $newRoomKey,
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/batchSayHi
     * @api {post} /live/anchor/batchSayHi 主播一键打招呼
     * @apiName anchor-batchSayHi
     * @apiGroup Chat
     * @apiDescription 主播一键打招呼  判断最后一次打招呼时间 是否间隔达到要求
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {Object} d.batch_say_hi_total_interval  主播一键打招呼 总间隔时间
     * @apiSuccess {Object} d.batch_say_hi_interval  主播一键打招呼 当前剩余间隔时间
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
    public function batchSayHiAction( $nUserId = 0 )
    {
        try {
            $oUser = User::findFirst($nUserId);
            if ( $oUser->user_is_anchor == 'N' ) {
                throw new Exception(ResponseError::getError(ResponseError::IS_NOT_ANCHOR), ResponseError::IS_NOT_ANCHOR);
            }
            $oAnchorBatchSayhiService = new AnchorBatchSayhiService($nUserId);

            $lastActionTime               = $oAnchorBatchSayhiService->getData();
            $anchor_batch_say_hi_interval = Kv::get(Kv::ANCHOR_BATCH_SAY_HI_INTERVAL);
            $waitTime                     = 0;
            if ( $lastActionTime ) {
                $waitTime = $lastActionTime + $anchor_batch_say_hi_interval - time();
            }
            if ( $waitTime > 0 ) {
                $waitMin    = intval($waitTime / 60);
                $waitSecond = $waitTime - $waitMin * 60;
                $waitFlg    = sprintf('%d秒', $waitSecond);
                if ( $waitMin ) {
                    $waitFlg = sprintf('%d分%d秒', $waitMin, $waitSecond);
                }
                throw new Exception(sprintf(ResponseError::getError(ResponseError::FORBIDDEN_ACTION_INTERVAL), $waitFlg), ResponseError::FORBIDDEN_ACTION_INTERVAL);
            }
            $row = [
                'batch_say_hi_total_interval' => $anchor_batch_say_hi_interval,
                'batch_say_hi_interval'       => $anchor_batch_say_hi_interval,
            ];

            $checkUserIds = $this->getCheckUserIds();
            if ( !in_array($nUserId, $checkUserIds) ) {
                $oAnchorBatchSayhiService->save(time());
                // 队列进行
                $oTaskQueueService = new TaskQueueService();
                $oTaskQueueService->enQueue([
                    'task'   => 'chat',
                    'action' => 'anchorBatchSayHi',
                    'param'  => [
                        'user_id' => $nUserId,
                    ],
                ]);
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.2.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/updateDispatch
     * @api {get} /live/anchor/updateDispatch 002-190912修改派单开关
     * @apiName anchor-updateDispatch
     * @apiGroup Anchor
     * @apiDescription 002-190912修改派单开关
     * @apiParam (正常请求) {String='Y','N'} anchor_dispatch_open_flg  派单开关
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug) {String='Y','N'} anchor_dispatch_open_flg  派单开关
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     */
    public function updateDispatchAction( $nUserId = 0 )
    {
        $openFlg = $this->getParams('anchor_dispatch_open_flg');
        try {
            if ( !in_array($openFlg, [
                'Y',
                'N'
            ]) ) {
                $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '派单开关'));
            }
            $oAnchorDispatch = AnchorDispatch::findFirst([
                'anchor_dispatch_user_id = :anchor_dispatch_user_id:',
                'bind' => [
                    'anchor_dispatch_user_id' => $nUserId
                ]
            ]);
            if ( !$oAnchorDispatch ) {
                $this->error(ResponseError::PARAM_ERROR, sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '无法设置'));
            }
            $oAnchorDispatch->anchor_dispatch_open_flg = $openFlg;
            if ( $oAnchorDispatch->save() === FALSE ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oAnchorDispatch->getMessages()), ResponseError::OPERATE_FAILED);
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * sendChatAction 房间聊天
     *
     * @param int $nUserId
     */
    public function sendChatAction( $nUserId = 0 )
    {
        $nAnchorUserId = $this->getParams('anchor_user_id', 'int', 0);
        $toUserId      = $this->getParams('to_user_id', 'int', 0);
        $sContent      = $this->getParams('content', 'string', '');

        try {

            if ( $this->banword($sContent) ) {
                throw new Exception(ResponseError::getError(ResponseError::BANWORD), ResponseError::BANWORD);
            }

            $nCurTime = time();

            $oUser = User::findFirst($nUserId);

            if ( $oUser->user_is_deny_speak == 'Y' ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::USER_PROHIBIT_TALK),
                    ResponseError::USER_PROHIBIT_TALK
                );
            }

            $aMsg['user_id'] = $nUserId;
            $aMsg['content'] = $sContent;
            $aMsg['time']    = $nCurTime;

            // 用户数据
            $aPushMessage['user'] = [
                'user_id'       => $aMsg['user_id'],
                'user_nickname' => $oUser->user_nickname,
            ];

            // 聊天数据
            $aPushMessage['chat'] = [
                'content' => $aMsg['content'],
                'time'    => $aMsg['time'],
            ];

            if ( !$toUserId ) {
                $this->timServer->setRid($nAnchorUserId);
                $this->timServer->setUid('');
                $this->timServer->sendRoomChatSignal($aPushMessage);
            } else {
                $userArr = [
                    $nAnchorUserId,
                    $toUserId
                ];
                if ( $toUserId == $nAnchorUserId ) {
                    $userArr = [
                        $nAnchorUserId,
                        $nUserId
                    ];
                }
                $this->timServer->setUid($userArr);
                $this->timServer->sendChatSignalBatch($aPushMessage);
            }
//

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success();
    }


    public function testAction()
    {
        $anchorList = $this->modelsManager->createBuilder()
            ->from([ 'a' => Anchor::class ])
            ->join(User::class, 'u.user_id = a.user_id', 'u')
            ->columns('u.user_id,u.user_nickname,u.user_avatar')
            ->where('a.anchor_hot_time > 0 AND a.anchor_chat_status = 3')
            ->orderBy('rand()')
            ->limit(20, 0)->getQuery()->execute()->toArray();
        $this->timServer->setUid('');
        $this->timServer->setRid(Room::B_CHAT_ID);
        $flg = $this->timServer->sendRoomStartVideoChatSignal([
            'room_id'              => Room::B_CHAT_ID,
            'anchor_user_id'       => 1,
            'anchor_user_nickname' => '111111',
            'user_id'              => 2,
            'user_nickname'        => '222222',
            'anchor_list'          => $anchorList
        ]);
        var_dump($flg);
        die;
        $appInfo = $this->getAppInfo('qq', 'tianmi');
        $jPush   = new JiGuangApi($appInfo['jpush_app_key'], $appInfo['jpush_master_secret'], NULL, APP_ENV == 'dev' ? FALSE : TRUE);
        $res     = $jPush->push([ 'alias' => [ "428" ] ], '视频消息', "【xxxx】邀请您进行快聊", [
            'type'    => 'private_chat',
            'chat_id' => 1111
        ]);
        var_dump($appInfo['jpush_app_key'], $appInfo['jpush_master_secret'], $res);
        die;
        $this->timServer->setRid('total_user');
        $flg = $this->timServer->createRoom('正式服大群', 'BChatRoom');
        var_dump($flg);
        die;

        $action = $this->getParams('action');
        $key    = $this->getParams('key');
        switch ( $action ) {
            case 'scroll_msg':
                if ( $key != 'scroll_msg_hhh' ) {
                    echo 'error';
                    return FALSE;
                }
                $type        = $this->getParams('type');
                $anchorId    = $this->getParams('anchor_user_id');
                $userId      = $this->getParams('user_id');
                $number      = $this->getParams('number', 'int', 1);
                $oAnchorUser = User::findFirst($anchorId);
                $oUser       = User::findFirst($userId);
                $this->timServer->setRid(APP_ENV == 'dev' ? 'total_user_dev' : 'total_user');
                for ( $i = 0; $i < $number; $i++ ) {

                    $flg = $this->timServer->sendScrollMsg([
                        'type' => $type,
                        'info' => [
                            'user_nickname'        => $oUser->user_nickname,
                            'user_avatar'          => $oUser->user_avatar,
                            'user_level'           => $oUser->user_level,
                            'anchor_user_nickname' => $oAnchorUser->user_nickname,
                            'anchor_user_avatar'   => $oAnchorUser->user_avatar,
                            'anchor_user_level'    => $oAnchorUser->user_level,
                            'title'                => sprintf('%s 成为 %s', $oUser->user_nickname, $oAnchorUser->user_nickname),
                            'content'              => '最新的守护者',
                        ]
                    ]);
                    echo '第' . ($i + 1) . '次<br />';
                    var_dump($flg);
                    echo '<br />';
                }
                break;
            case 'create_name':
                if ( $key != 'create_name_hzjkb24' ) {
                    echo 'error';
                    return FALSE;
                }
                $oUserNameConfig = new UserNameConfig();
                $name            = $oUserNameConfig->getRandName();
                var_dump($name);
                die;
                break;
            case 'anchor_user_list':
                if ( $key != '111_hzjkb24' ) {
                    echo 'error';
                    return FALSE;
                }
                $now         = time();
                $normalCount = 31;
                $builder     = $this->modelsManager
                    ->createBuilder()
                    ->from([ 'u' => User::class ])
                    ->columns('user_id')
                    ->orderby('user_id desc');

                $rowNormal = $this->page($builder, 1, 2);
                $normalArr = array_random($rowNormal['items'], 3);
                echo '<pre>';
                var_dump($rowNormal['items'], $normalArr);
                die;
                break;
            case 'level_config':
                if ( $key != '222_hzjkb24' ) {
                    echo 'error';
                    return FALSE;
                }
                $type      = $this->getParams('level_type');
                $levelData = LevelConfig::find([
                    'level_type = :level_type:',
                    'bind'  => [
                        'level_type' => $type
                    ],
                    'order' => 'level_exp',
                    'cache' => [
                        'lifetime' => 3600,
                        'key'      => LevelConfig::getCacheKey($type)
                    ]
                ]);
                var_dump($levelData->toArray());
                die;
                break;
            case 'system_unread':
                if ( $key != '222_hzjkb24' ) {
                    echo 'error';
                    return FALSE;
                }
                $nUserId                  = $this->getParams('user_id');
                $nUserSystemMessageUnread = \app\models\UserSystemMessageDialog::sum([
                    'user_id=:user_id:',
                    'bind'   => [
                        'user_id' => $nUserId,
                    ],
                    'column' => 'user_system_message_unread + user_notification_message_unread',
                ]);
                var_dump($nUserSystemMessageUnread);
                die;

                break;

            // 普通用户根据用户ID 排序 取200个 然后中间随机抽取相应个数
            default:
                echo 'error';
                return FALSE;
        }
        return FALSE;


        $rand      = rand(0, mb_strlen($firstNameStr) - 1);
        $firstName = mb_substr($firstNameStr, $rand, 1, 'utf-8');
        var_dump(mb_strlen($firstNameStr));
        die;
        $num = 2;
        $b   = '';
        for ( $i = 0; $i < $num; $i++ ) {
            // 使用chr()函数拼接双字节汉字，前一个chr()为高位字节，后一个为低位字节
            $a = chr(mt_rand(0xB0, 0xD0)) . chr(mt_rand(0xA1, 0xF0));
            // 转码
            $b .= iconv('GB2312', 'UTF-8', $a);
        }
        var_dump($firstName . $b);
        die;

        $anchorId = $this->getParams('anchor_user_id');
        $userId   = $this->getParams('user_id');
        $number   = $this->getParams('number', 'int', 1);
        $number   = min(10, $number);
//        $flg      = UserGuard::getTodayFreeTimes($anchorId, $userId);
//        var_dump($flg);
//        die;
        $type        = $this->getParams('type');
        $oAnchorUser = User::findFirst($anchorId);
        $oUser       = User::findFirst($userId);
        $this->timServer->setRid(APP_ENV == 'dev' ? 'total_user_dev' : 'total_user');
        for ( $i = 0; $i < $number; $i++ ) {

            $flg = $this->timServer->sendScrollMsg([
                'type' => $type,
                'info' => [
                    'user_nickname'        => $oUser->user_nickname,
                    'user_avatar'          => $oUser->user_avatar,
                    'user_level'           => $oUser->user_level,
                    'anchor_user_nickname' => $oAnchorUser->user_nickname,
                    'anchor_user_avatar'   => $oAnchorUser->user_avatar,
                    'anchor_user_level'    => $oAnchorUser->user_level,
                    'title'                => sprintf('%s 成为 %s', $oUser->user_nickname, $oAnchorUser->user_nickname),
                    'content'              => '最新的守护者',
                ]
            ]);
            echo '第' . $i . '次<br />';
            var_dump($flg);
            echo '<br />';
        }
        die;

//        $group_name = '广播大群';
//        $group_type = 'BChatRoom';
//        $this->timServer->setRid('total_user');
//        $flg = $this->timServer->createRoom($group_name, $group_type);
//        var_dump($flg);
        die;
        $data = Anchor::findFirst([
            'order' => 'anchor_id desc',
            'limit' => [
                1,
                2
            ]
        ]);
        var_dump($data->toArray());
        die;
//        $oCustomerUser = User::find([
//            'user_is_superadmin = :user_is_superadmin:',
//            'bind'  => [
//                'user_is_superadmin' => 'C',
//            ],
//            'order' => 'user_online_status desc,rand()'
//        ]);
//        if ( !$oCustomerUser ) {
//            return $user_customer_id;
//        }
//        $oCustomerUserArr        = $oCustomerUser->toArray();
//        $customer_id             = $oCustomerUserArr[0]['user_id'];
        $customer_id      = 0;
        $oCustomerService = new CustomerService();
        $oCustomerUserArr = $oCustomerService->getData();
        var_dump($oCustomerUserArr);
        if ( $oCustomerUserArr ) {
            $onlineCustomerArr = [];
            foreach ( $oCustomerUserArr as $itemUserId => $customerStatus ) {
                $customer_id = $itemUserId;
                if ( $customerStatus == User::USER_ONLINE_STATUS_ONLINE ) {
                    $onlineCustomerArr[] = $itemUserId;
                }
            }
            if ( $onlineCustomerArr ) {
                $customer_id = array_rand($onlineCustomerArr)[0];
            }
        }
        var_dump($oCustomerUserArr, $onlineCustomerArr, $customer_id);

        return $customer_id;
        $keyArr = [
            'user_test:20181101',
            'user_test:20181102',
        ];
        $newKey = 'user_test';
        $flg    = $this->redis->bitOp('and', $newKey, $keyArr[0], $keyArr[1]);

        $data = $this->redis->bitCount($newKey);
        var_dump([
            'flg'      => $flg,
            'new_data' => $data
        ]);

        die;
        // 取出anchor_check_img 为examine 和 cover  取出anchor_images为normal
        echo '<pre>';
        $count = Anchor::count();
        var_dump($count);
        for ( $i = 0; $i * 100 < $count; $i++ ) {
            $offset  = 100 * $i;
            $oAnchor = Anchor::find([
                'limit'   => [
                    100,
                    $offset
                ],
                'columns' => 'user_id,anchor_check_img,anchor_images,anchor_video_cover'
            ]);
            $saveAll = [];
            $now     = time();
            foreach ( $oAnchor as $item ) {
                if ( $item->anchor_check_img ) {
                    $checkImages = explode(',', $item->anchor_check_img);
                    foreach ( $checkImages as $checkItem ) {
                        $tmp = [
                            'user_id'      => $item->user_id,
                            'img_src'      => $checkItem,
                            'position'     => 'examine',
                            'visible_type' => 'normal',
                            'create_time'  => $now,
                            'update_time'  => $now,
                        ];
                        if ( $checkItem == $item->anchor_video_cover ) {
                            $tmp['position'] = 'cover';
                        }
                        $saveAll[] = $tmp;
                    }
                }
                if ( $item->anchor_images ) {
                    $normalImages = explode(',', $item->anchor_images);
                    foreach ( $normalImages as $normalItem ) {
                        $tmp       = [
                            'user_id'      => $item->user_id,
                            'img_src'      => $normalItem,
                            'position'     => 'normal',
                            'visible_type' => 'normal',
                            'create_time'  => $now,
                            'update_time'  => $now,
                        ];
                        $saveAll[] = $tmp;
                    }
                }
            }
            if ( $saveAll ) {
                // 存入anchorImage
                $oAnchorImage = new AnchorImage();
                $flg          = $oAnchorImage->saveAll($saveAll);
                var_dump($flg);
                echo '<hr />';
            }
        }
        die;
        $oAnchor = Anchor::find([
            'limit'   => [
                5,
                0
            ],
            'columns' => 'user_id,anchor_check_img,anchor_images'
        ])->toArray();

        var_dump($oAnchor);
        die;


        $sql        = "select user_id,count(1) as recharge_times,sum(user_recharge_order_coin) as recharge_total_coin,sum(user_recharge_order_fee) as recharge_total_money,
max(user_recharge_order_update_time) as recharge_last_time,min(user_recharge_order_update_time) as recharge_first_time
from user_recharge_order where user_recharge_order_status = 'Y' group by user_id";
        $oUser      = new User();
        $connection = $oUser->getReadConnection();
        $result     = $connection->query($sql);
        $result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $rechargeData = $result->fetchAll();

        $sql    = "select user_id,count(1) as vip_times,sum(user_vip_order_combo_month) as vip_total_month,sum(user_vip_order_combo_fee) as vip_total_money,
max(user_vip_order_update_time) as vip_last_time,min(user_vip_order_update_time) as vip_first_time
from user_vip_order where user_vip_order_status = 'Y' group by user_id";
        $result = $connection->query($sql);
        $result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $vipData = $result->fetchAll();


        $hotAnchor = Anchor::find([
            'anchor_chat_status = 3 AND anchor_hot_man > 0',
            'columns' => 'user_id'
        ])->toArray();
        var_dump(array_column($hotAnchor, 'user_id'));
        die;
        $number = 1259.5080 * (100 / 100);
        var_dump([
            round($number, 2, PHP_ROUND_HALF_EVEN),
            (string)(floor($number * 100) / 100),
            number_format($number, 2, '.', ''),
            $number

        ]);
        die;
        $this->timServer->setRid('matchCenterRoom_all');
        $this->timServer->setRid('matchCenterRoomDev');
        $this->timServer->createRoom('匹配大厅红人主播dev', 'ChatRoom');


//        $this->timServer->setRid('matchCenterRoomDev_all');
//        $flg = $this->timServer->createRoom('匹配大厅其他主播dev','ChatRoom');
//        var_dump($flg);

//        $this->timServer->setRid('matchCenterRoom_all');
//        $this->timServer->createRoom('匹配大厅其他主播','ChatRoom');
        die;
    }

}