<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 用户信息控制器                                                         |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\user;


use app\models\AnchorDailyTaskLog;
use app\models\AnchorDispatch;
use app\models\AnchorImage;
use app\models\AnchorSourceCertification;
use app\models\AnchorSourceCertificationDetail;
use app\models\AnchorTitleConfig;
use app\models\AppList;
use app\models\Banword;
use app\models\Kv;
use app\models\LevelConfig;
use app\models\LevelRewardLog;
use app\models\TaskConfig;
use app\models\UserActionLog;
use app\models\UserConsumeCategory;
use app\models\UserFinanceLog;
use app\models\UserGiftLog;
use app\models\UserGuard;
use app\models\UserIntimate;
use app\models\UserPrivateChatLog;
use app\models\UserProfileSetting;
use app\models\UserSet;
use app\models\UserViewLog;
use app\services\TaskQueueService;
use Cassandra\Varint;
use Exception;

use app\models\User;
use app\models\UserBlack;
use app\models\Anchor;
use app\models\UserFollow;
use app\models\UserAccount;
use app\helper\ResponseError;
use app\models\UserChatDialog;
use app\http\controllers\ControllerBase;
use fast\Date;

/**
 * ProfileController
 */
class ProfileController extends ControllerBase
{
    use \app\services\UserService;

    /**
     * indexAction 获取用户信息
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/index
     * @api {get} /user/profile/index 001-190912获取用户信息
     * @apiName 获取用户信息-profile-index
     * @apiGroup Profile
     * @apiDescription 获取用户信息
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.user_id 用户id
     * @apiSuccess {string} d.user_nickname 用户昵称
     * @apiSuccess {string} d.user_avatar 头像
     * @apiSuccess {string} d.user_coin 金币
     * @apiSuccess {string} d.user_dot 佣金
     * @apiSuccess {string} d.user_consume_total 用户总消费
     * @apiSuccess {string} d.user_collect_total 用户总收礼
     * @apiSuccess {string} d.user_intro 个人简介
     * @apiSuccess {string} d.user_birth 生日
     * @apiSuccess {string} d.user_intro 个人简介
     * @apiSuccess {string} d.user_lat 纬度
     * @apiSuccess {string} d.user_lng 经度
     * @apiSuccess {string} d.user_invite_code 邀请码
     * @apiSuccess {string} d.user_invite_total 邀请总数
     * @apiSuccess {string} d.user_follow_total 关注数
     * @apiSuccess {string} d.user_fans_total 粉丝数
     * @apiSuccess {string} d.user_is_certification 是否认证
     * @apiSuccess {string} d.user_is_anchor 是否主播
     * @apiSuccess {string} d.user_phone 手机号码
     * @apiSuccess {string} d.user_token_expire_time token过期时间戳
     * @apiSuccess {string} d.user_member_expire_time 会员过期时间戳
     * @apiSuccess {string} d.user_is_member 是否为会员
     * @apiSuccess {string} d.access_token access_token
     * @apiSuccess {string} d.anchor_ranking 主播排名
     * @apiSuccess {string} d.ws_url 弃用
     * @apiSuccess {string} d.user_constellation 用户星座
     * @apiSuccess {string} d.user_img 用户图集
     * @apiSuccess {string} d.user_video 用户视频
     * @apiSuccess {string} d.user_video_cover 用户视频封面
     * @apiSuccess {string} d.user_home_town 用户家乡
     * @apiSuccess {string} d.user_hobby 爱好
     * @apiSuccess {string} d.user_profession 用户职业
     * @apiSuccess {string} d.user_emotional_state 用户情感状况
     * @apiSuccess {string} d.user_income 用户收入
     * @apiSuccess {string} d.user_height 用户身高
     * @apiSuccess {string} d.user_register_time 注册时间
     * @apiSuccess {object} d.tim 腾讯云TIM
     * @apiSuccess {string} d.tim.sign 腾讯云签名
     * @apiSuccess {string} d.tim.account 腾讯云账号
     * @apiSuccess {string} d.tim.account_type 腾讯云账号类型
     * @apiSuccess {string} d.tim.app_id 腾讯云app_id
     * @apiSuccess {object} d.match_center_info 匹配信息
     * @apiSuccess {string} d.match_center_info.room_id 匹配大厅房间id
     * @apiSuccess {number} d.match_center_info.match_price 匹配价格
     * @apiSuccess {string} d.h5_pay_url H5充值地址
     * @apiSuccess {string} d.h5_vip_url H5购买VIP地址
     * @apiSuccess {string} d.customer_service_id 客服id
     * @apiSuccess {number} d.guide_video_time 诱导视频时长
     * @apiSuccess {object} d.anchor_info 主播信息
     * @apiSuccess {string} d.anchor_info.anchor_tip 主播标签
     * @apiSuccess {string} d.anchor_info.anchor_character 主播性格
     * @apiSuccess {string} d.anchor_info.anchor_good_topic 主播擅长话题
     * @apiSuccess {string} d.anchor_info.anchor_dress 主播爱穿
     * @apiSuccess {string} d.anchor_info.anchor_stature 主播身材
     * @apiSuccess {string} d.anchor_info.anchor_check_img 主播审核图片
     * @apiSuccess {string} d.anchor_info.anchor_images 主播图片
     * @apiSuccess {string} d.anchor_info.anchor_emotional_state 情感状态
     * @apiSuccess {string} d.anchor_info.anchor_connection_rate 接通率
     * @apiSuccess {string='Y','N'} d.anchor_info.anchor_dispatch_flg 是否派单主播
     * @apiSuccess {string='Y','N'} d.anchor_info.anchor_dispatch_open_flg 派单状态是否打开
     * @apiSuccess {object} d.user_signin  签到信息
     * @apiSuccess {String} d.user_signin.is_signin  是否签到 Y 是 N 否
     * @apiSuccess {String} d.user_signin.tips  签到提示
     * @apiSuccess {object} d.unread 消息未读
     * @apiSuccess {number} d.unread.total  总未读数
     * @apiSuccess {String} d.unread.user_chat  聊天未读数
     * @apiSuccess {String} d.unread.system_message 系统消息未读数
     * @apiSuccess {number} d.unread.video_message 小视频消息未读数
     * @apiSuccess {number} d.unread.video_chat_message 视频聊天未读数
     * @apiSuccess {number} d.unread.notify_unread 通知消息未读数
     * @apiSuccess {number} d.unread.posts_message 动态消息未读数
     * @apiSuccess {number} d.unread.say_hi_unread 打招呼未读消息数
     * @apiSuccess {number} d.free_times 免费匹配时长（分钟）
     * @apiSuccess {object} d.first_share_reward 首次邀请奖励信息
     * @apiSuccess {number} d.first_share_reward.free_times 赠送的免费时长
     * @apiSuccess {number} d.first_share_reward.total_over_time_hour 总过期时间（小时）
     * @apiSuccess {number} d.first_share_reward.over_time_second 剩余过期时间（秒）
     * @apiSuccess {object} d.share 分享信息
     * @apiSuccess {String} d.share.logo logo（暂不使用）
     * @apiSuccess {String} d.share.content 文案（暂不使用）
     * @apiSuccess {String} d.share.url 分享地址
     * @apiSuccess {String} d.b_chat_room 广播大群id
     * @apiSuccess {String} d.posts_num 动态数量
     * @apiSuccess {String} d.short_video_num 小视频数
     * @apiSuccess {object} d.daily_task 每日任务信息
     * @apiSuccess {number} d.daily_task.daily_total_count 每日任务总数
     * @apiSuccess {number} d.daily_task.daily_finish_count 每日完成数
     * @apiSuccess {String='Y','N'} d.user_get_stranger_msg_flg 是否接受陌生人的消息
     * @apiSuccess {String} d.all_view_count 累计访客
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *       "c": 0,
     *       "m": "请求成功",
     *       "d": {
     *           "user_id": "172",
     *           "user_nickname": "18823369189",
     *           "user_avatar": "http://lebolive-1255651273.file.myqcloud.com/avatar.jpg",
     *           "user_sex": "1",
     *           "user_coin": "10.00",
     *           "user_dot": "100.00",
     *           "user_consume_total": "0.00",
     *           "user_collect_total": "0.00",
     *           "user_intro": "",
     *           "user_birth": "",
     *           "user_lat": "0.000000",
     *           "user_lng": "0.000000",
     *           "user_invite_code": "W21309",
     *           "user_invite_total": "0",
     *           "user_follow_total": "0",
     *           "user_fans_total": "0",
     *           "user_is_certification": "Y",
     *           "user_is_anchor": "Y",
     *           "user_phone": "188****9189",
     *           "user_token_expire_time": "1536483157",
     *           "user_member_expire_time": "1533867150",
     *           "user_is_member": "O",
     *           "access_token": "xYNJYC957HNtAWMSN3CIhrroVKfsspC4RQBM6e7XFQAzOTi.ysbTTL9_HwvlVJuvsEbGKC7IFCCTncwNjN3Q",
     *           "anchor_ranking": "0",
     *           "ws_url": "?uid=172&token=&extra=eyJ1c2VyIjp7InVzZXJfaWQiOiIxNzIiLCJ1c2VyX2F2YXRhciI6Imh0dHA6XC9cL2xlYm9saXZlLTEyNTU2NTEyNzMuZmlsZS5teXFjbG91ZC5jb21cL2F2YXRhci5qcGciLCJ1c2VyX25pY2tuYW1lIjoiMTg4MjMzNjkxODkiLCJ1c2VyX3NleCI6IjEiLCJ1c2VyX2lzX21lbWJlciI6Ik8ifX0%3D",
     *           "user_constellation": "",
     *           "user_img": null,
     *           "user_video": "",
     *           "user_video_cover": "",
     *           "user_home_town": "",
     *           "user_hobby": "",
     *           "user_profession": "",
     *           "user_emotional_state": "",
     *           "user_income": "",
     *           "user_height": "",
     *           "apple_online": 1,
     *           "user_register_time": "1533623873",
     *           "tim": {
     *                   "sign": "eJxNjV9PgzAUR78Lz8b1zwrDZA84NzSOBSNBY5Y0SAu7TqCWwhjG7y7iFn095-zu-bSi9eNlkqZVUxpujkpaVxayLkYMQpYGMpB6gNghJ5woBYInhlMt-tW12PNR-cRThBCz8ey8kZ0CLXmSmd9jjDEyJCfbSl1DVQ6CIMwwoQj9SQOFHCd06hJMMT3-g3zAwfJhcbfCgR32u*fsJfLKTQdt*BZEjn2T*03cm1v9Gl-7e*fozNzUAy8q4LB69yWpl6HshPtx2E682Nw-Fe12sl4o3O9k1jeV6fAmn8*tr29ib1g8",
     *                   "account": "172",
     *                   "account_type": "20760",
     *                   "app_id": "1400056182"
     *           },
     *           "match_center_info": {
     *                   "room_id": "matchCenterRoomDev",
     *                   "match_price": 10
     *           },
     *            "h5_pay_url": "http://dev.charge.sxypaopao.com/pay.php?uid=172",
     *            "h5_vip_url": "http://dev.charge.sxypaopao.com/vip.php?uid=172",
     *            "customer_service_id": "181",
     *            "guide_video_time": 15,
     *            "anchor_info": {
     *                   "anchor_tip": "",
     *                   "anchor_character": "",
     *                   "anchor_good_topic": "",
     *                   "anchor_dress": "",
     *                   "anchor_stature": "",
     *                   "anchor_images": "",
     *                   "anchor_check_img": "",
     *                   "anchor_dispatch_flg": "Y",
     *                   "anchor_dispatch_open_flg": "Y"
     *           },
     *              "user_signin": {
     *                  "is_signin": "Y",
     *                "tips": "今天还没有签到哦！"
     *              },
     *              "unread": {
     *                  "total": "0",
     *                "user_chat": "",
     *                "system_message": "",
     *                "video_message": "0",
     *                "video_chat_message": "0"
     *              },
     *            "free_times": "1",
     *            "first_share_reward": {
     *                "free_times": 0,
     *                "total_over_time_hour": 0,
     *                "over_time_second": 0
     *            },
     *             "share": {
     *                 "logo": "http://dev.api.sxypaopao.com/assets/images/logo.png",
     *                 "url": "http://dev.h5.sxypaopao.com/register?channelCode=gGgdfpvuzWOBWncQ&invite_code=P52215",
     *                 "content": "我在泡泡直播"
     *             },
     *             "posts_num" : "100",
     *             "short_video_num" : "100",
     *               "daily_task": {
     *                  "daily_total_count": 6,
     *                  "daily_finish_count": 2
     *              },
     *              "user_get_stranger_msg_flg" : "Y",
     *              "all_view_count" : "10"
     *       },
     *       "t": 1534921313
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function indexAction( $nUserId = 0 )
    {
        try {
            $oUser = User::findFirst($nUserId);
            if ( !$oUser ) {
                throw new Exception(ResponseError::getError(ResponseError::USER_NOT_EXISTS), ResponseError::USER_NOT_EXISTS);
            }
            $oUserAccount = UserAccount::findFirst($nUserId);
            $row          = $this->getUserInfoHandle($oUser, $oUserAccount);
            // 匹配显示数字信息
            $row = array_merge($row, Kv::getMatchShowNumber());
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    /**
     * /**
     * 修改用户信息
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/update
     * @api {get} /user/profile/update 修改用户信息
     * @apiName 修改用户信息-profile-update
     * @apiGroup Profile
     * @apiDescription 修改用户信息
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} user_nickname 昵称
     * @apiParam (正常请求){String} user_avatar 头像
     * @apiParam (正常请求){String} user_sex 性别
     * @apiParam (正常请求){String} user_intro 简介
     * @apiParam (正常请求){String} user_birth 生日
     * @apiParam (正常请求){String} user_lat 纬度
     * @apiParam (正常请求){String} user_lng 经度
     * @apiParam (正常请求){String} user_constellation 星座
     * @apiParam (正常请求){String} user_home_town 家乡
     * @apiParam (正常请求){String} user_hobby 爱好
     * @apiParam (正常请求){String} user_profession 职业
     * @apiParam (正常请求){String} user_emotional_state 用户情感状态
     * @apiParam (正常请求){String} user_income 收入
     * @apiParam (正常请求){String} user_height 身高
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} user_nickname 昵称
     * @apiParam (debug){String} user_avatar 头像
     * @apiParam (debug){String} user_sex 性别
     * @apiParam (debug){String} user_intro 简介
     * @apiParam (debug){String} user_birth 生日
     * @apiParam (debug){String} user_lat 纬度
     * @apiParam (debug){String} user_lng 经度
     * @apiParam (debug){String} user_constellation 星座
     * @apiParam (debug){String} user_home_town 家乡
     * @apiParam (debug){String} user_hobby 爱好
     * @apiParam (debug){String} user_profession 职业
     * @apiParam (debug){String} user_emotional_state 用户情感状态
     * @apiParam (debug){String} user_income 收入
     * @apiParam (debug){String} user_height 身高
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
    public function updateAction( $nUserId = 0 )
    {
        $param = $this->getParams();

        // 可以修改的字段
        $aAllow = [
            'user_nickname',
            'user_avatar',
            'user_sex',
            'user_intro',
            'user_birth',
            'user_lat',
            'user_lng',
            'user_constellation',
            'user_img',
            'user_home_town',
            'user_hobby',
            'user_profession',
            'user_emotional_state',
            'user_income',
            'user_height',
        ];

        try {

            if ( isset($param['user_nickname']) ) {
//                if ( $this->isPublish($nUserId, AppList::PUBLISH_CAN_NOT_CHANGE_PROFILE) ) {
//                    // 是审核中 不能修改
//                    throw new Exception(ResponseError::getError(ResponseError::CHECKED_CANNOT_UPDATE), ResponseError::CHECKED_CANNOT_UPDATE);
//                }
                if ( $this->banword($param['user_nickname'], Banword::LOCATION_PROFILE) ) {
                    throw new Exception(ResponseError::getError(ResponseError::BANWORD), ResponseError::BANWORD);
                }

                if ( mb_strlen($param['user_nickname'] > 10) ) {
                    throw new Exception(ResponseError::getError(ResponseError::NICKNAME_LENGTH), ResponseError::NICKNAME_LENGTH);
                }

                // 判断上次修改时间是否超过设置时间
                $user_nickname_change_interval_day = Kv::get(Kv::USER_NICKNAME_CHANGE_INTERVAL_DAY);
                if ( $user_nickname_change_interval_day ) {
                    $oUserActionLog = UserActionLog::findFirst($nUserId);
                    if ( $oUserActionLog ) {
                        $lastChangeNicknameTime = $oUserActionLog->change_nickname_time;
                        if ( time() - $lastChangeNicknameTime < $user_nickname_change_interval_day * 86400 ) {
                            throw new Exception(sprintf(ResponseError::getError(ResponseError::CHANGE_NICKNAME_FORBIDDEN), $user_nickname_change_interval_day), ResponseError::CHANGE_NICKNAME_FORBIDDEN);
                        }
                    }
                }
            }

            if ( isset($param['user_intro']) ) {
                if ( $this->banword($param['user_intro'], Banword::LOCATION_PROFILE) ) {
                    throw new Exception(ResponseError::getError(ResponseError::BANWORD), ResponseError::BANWORD);
                }
                if ( $this->isPublish($nUserId, AppList::PUBLISH_CAN_NOT_CHANGE_PROFILE) ) {
                    // 是审核中 不能修改
                    throw new Exception(ResponseError::getError(ResponseError::CHECKED_CANNOT_UPDATE), ResponseError::CHECKED_CANNOT_UPDATE);
                }
            }

            if ( isset($param['user_hobby']) ) {
                if ( $this->banword($param['user_hobby']) ) {
                    throw new Exception(ResponseError::getError(ResponseError::BANWORD), ResponseError::BANWORD);
                }
            }

            if ( isset($param['user_profession']) ) {
                if ( $this->banword($param['user_profession']) ) {
                    throw new Exception(ResponseError::getError(ResponseError::BANWORD), ResponseError::BANWORD);
                }
            }

            if ( isset($param['user_nickname']) ) {
                $bool = User::findFirst([
                    'user_nickname=:user_nickname:',
                    'bind' => [
                        'user_nickname' => $param['user_nickname'],
                    ]
                ]);

                if ( $bool ) {
                    throw new Exception(ResponseError::getError(ResponseError::USERNAME_EXISTS), ResponseError::USERNAME_EXISTS);
                }
            }

            $oUser  = User::findFirst($nUserId);
            $oldSex = $oUser->user_sex;

            foreach ( $param as $key => $value ) {
                if ( in_array($key, $aAllow) && $value ) {
                    $oUser->$key = $value;
                }
            }
            if ( $oUser->user_avatar == 'http://cskj-1257854899.file.myqcloud.com/static/paopao.png' && $oldSex == 0 ) {

                if ( $oUser->user_sex == 1 ) {
                    // 男
                    $oUser->user_avatar = 'https://lebolive-1255651273.image.myqcloud.com/static/images/head/sex1.png';
                } else if ( $oUser->user_sex == 2 ) {
                    // 女
                    $oUser->user_avatar = 'https://lebolive-1255651273.image.myqcloud.com/static/images/head/sex2.png';
                }
            }

            if ( $oUser->save() === FALSE ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUser->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            if ( $oUser->user_invite_user_id && $oldSex == 0 && $oUser->user_sex == 2 ) {
                $sql        = 'update user set user_invite_total_female = user_invite_total_female + 1 where user_id = ' . $oUser->user_invite_user_id;
                $conneciton = $oUser->getWriteConnection();
                $conneciton->execute($sql);
            }

            // 如果是修改昵称 则需要记录修改时间
            if ( isset($param['user_nickname']) ) {
                $oUserActionLog = UserActionLog::findFirst($nUserId);
                if ( !$oUserActionLog ) {
                    $oUserActionLog          = new UserActionLog();
                    $oUserActionLog->user_id = $nUserId;
                }
                $oUserActionLog->change_nickname_time = time();
                $oUserActionLog->save();

                // 需要修改 数据库中的关联用户昵称
                $oTaskQueueService = new TaskQueueService();
                $oTaskQueueService->enQueue([
                    'task'   => 'user',
                    'action' => 'changeNickname',
                    'param'  => [
                        'user_id' => $nUserId,
                    ],
                ]);
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success();
    }

    /**
     * cardAction 获取用户名片信息
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/card
     * @api {get} /user/profile/card 001-190909获取用户名片信息
     * @apiName 用户名片-profile-card
     * @apiGroup Profile
     * @apiDescription 获取用户名片信息
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} user_id 用户id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} user_id 用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {number} d 数据内容
     * @apiSuccess {number} d.user_id  用户id
     * @apiSuccess {String} d.user_nickname  昵称
     * @apiSuccess {String} d.user_avatar 头像
     * @apiSuccess {number} d.user_sex 性别
     * @apiSuccess {number} d.user_consume_total 消费
     * @apiSuccess {number} d.user_collect_total 收益
     * @apiSuccess {String} d.user_intro 简介
     * @apiSuccess {String} d.user_birth  生日
     * @apiSuccess {number} d.user_follow_total  关注
     * @apiSuccess {number} d.user_fans_total 粉丝
     * @apiSuccess {String} d.user_is_anchor 是否是主播
     * @apiSuccess {number} d.anchor_ranking 主播排行榜
     * @apiSuccess {String} d.is_follow 是否关注
     * @apiSuccess {String} d.chat_room_id 聊天房间id
     * @apiSuccess {String} d.is_card_effect 卡片特效
     * @apiSuccess {number} d.is_online 在线
     * @apiSuccess {String} d.user_img 用户图册
     * @apiSuccess {number} d.user_register_time 用户注册时间
     * @apiSuccess {String} d.user_home_town 家乡
     * @apiSuccess {String} d.user_constellation 用户星座
     * @apiSuccess {String} d.user_hobby 爱好
     * @apiSuccess {String} d.user_profession 用户职业
     * @apiSuccess {String} d.user_emotional_state 用户情感状况
     * @apiSuccess {String} d.user_income 用户收入
     * @apiSuccess {String} d.user_height 用户身高
     * @apiSuccess {String} d.is_black  是否黑名单
     * @apiSuccess {String} d.share_url 分享地址
     * @apiSuccess {object} d.gift_img 礼物图册
     * @apiSuccess {object} d.gift_img.live_gift_logo 礼物logo
     * @apiSuccess {object} d.gift_img.total 礼物总数
     * @apiSuccess {object} d.gift_img.live_gift_name 礼物名称
     * @apiSuccess {object} d.gift_img.live_gift_coin 礼物价格
     * @apiSuccess {number} d.total_gift 礼物数量
     * @apiSuccess {String} d.user_is_member 是否为会员
     * @apiSuccess {object} d.anchor_info 主播信息
     * @apiSuccess {string} d.anchor_info.anchor_tip 主播标签
     * @apiSuccess {string} d.anchor_info.anchor_character 主播性格
     * @apiSuccess {string} d.anchor_info.anchor_good_topic 主播擅长话题
     * @apiSuccess {string} d.anchor_info.anchor_dress 主播爱穿
     * @apiSuccess {string} d.anchor_info.anchor_stature 主播身材
     * @apiSuccess {string} d.anchor_info.anchor_check_img 主播审核图片
     * @apiSuccess {string} d.anchor_info.anchor_images 主播图片
     * @apiSuccess {string} d.anchor_info.anchor_emotional_state 情感状态
     * @apiSuccess {string} d.anchor_info.anchor_connection_rate 接通率
     * @apiSuccess {string} d.anchor_info.anchor_video 封面视频
     * @apiSuccess {string} d.anchor_info.anchor_video_cover 视频封面
     * @apiSuccess {string} d.anchor_info.anchor_chat_price 通话价格
     * @apiSuccess {string} d.anchor_info.anchor_chat_status 主播聊天状态
     * @apiSuccess {object} d.anchor_info.anchor_guard 主播守护
     * @apiSuccess {string} d.anchor_info.anchor_guard.user_id 守护用户id
     * @apiSuccess {string} d.anchor_info.anchor_guard.user_nickname 守护用户昵称
     * @apiSuccess {string} d.anchor_info.anchor_guard.user_avatar 守护用户头像
     * @apiSuccess {string} d.anchor_info.anchor_guard.anchor_guard_coin 守护金币
     * @apiSuccess {string} d.anchor_info.anchor_guard.anchor_guard_level 守护等级
     * @apiSuccess {string} d.anchor_info.anchor_guard.anchor_guard_level_name 守护等级名称
     * @apiSuccess {String} d.anchor_info.anchor_level  主播等级
     * @apiSuccess {String} d.anchor_info.anchor_title_number  主播称号值
     * @apiSuccess {String} d.anchor_info.anchor_title_name  主播称号名称
     * @apiSuccess {String} d.anchor_info.anchor_custom_title  主播自定义标签
     * @apiSuccess {object} d.anchor_images_list  图片集
     * @apiSuccess {string} d.anchor_images_list.img_src  图片地址
     * @apiSuccess {string} d.anchor_images_list.visible_type 可见类型  normal vip (vip用户查看时 会变为normal)
     * @apiSuccess {object} d.guard_list  守护中的列表
     * @apiSuccess {number} d.guard_list.user_id    用户id
     * @apiSuccess {String} d.guard_list.user_nickname 用户昵称
     * @apiSuccess {String} d.guard_list.user_avatar 用户头像
     * @apiSuccess {number} d.guard_list.total_coin  用户守护值
     * @apiSuccess {number} d.guard_list.current_level  守护等级
     * @apiSuccess {String} d.guard_list.current_level_name   守护等级名称
     * @apiSuccess {String} d.guard_list.guard_status  守护状态  Y 为守护中， N 为守护过
     * @apiSuccess {number} d.no_income_free_time  剩余不计算收益的免费时长数
     * @apiSuccess {String='guard(守护)'} d.no_income_free_time_type  不计算收益的免费时长类型
     * @apiSuccess {object} d.user_intimate  亲密信息
     * @apiSuccess {number} d.user_intimate.level  亲密等级
     * @apiSuccess {String} d.user_intimate.level_name  亲密等级名称
     * @apiSuccess {number} d.user_intimate.total_value  亲密值
     * @apiSuccess {string} d.user_wechat 微信(不能查看时为隐藏带***  为空时则不提示 主播未填)
     * @apiSuccess {string} d.wechat_price 微信价格
     * @apiSuccess {string} d.user_v_wechat_price 大V微信价格
     * @apiSuccess {string} d.user_v_wechat 大V微信
     * @apiSuccess {string} d.all_view_count 总访客数
     * @apiSuccess {string} d.anchor_call_flg 主播是否能拨打对方
     * @apiSuccess {string} d.owner_anchor_price 自己的是主播 主播拨打价格
     * @apiSuccessExample Success-Response:
     *  {
     *      "c": 0,
     *      "m": "请求成功",
     *      "d": {
     *              "all_view_count": "171",
     *              "user_id": "171",
     *              "user_nickname": "L--Steven",
     *              "user_avatar": "http://tvax3.sinaimg.cn/crop.0.0.512.512.180/881b51bbly8fs6z7jhuajj20e80e8mxl.jpg",
     *              "user_sex": "2",
     *              "user_consume_total": "2253.00",
     *              "user_collect_total": "3.20",
     *              "user_intro": "",
     *              "user_birth": "",
     *              "user_follow_total": "1",
     *              "user_fans_total": "0",
     *              "user_is_anchor": "Y",
     *              "anchor_ranking": "0",
     *              "is_follow": "N",
     *              "chat_room_id": "172_171",
     *              "is_card_effect": "N",
     *              "is_online": 1,
     *              "user_img": null,
     *              "user_register_time": "1533554656",
     *              "user_home_town": "",
     *              "user_constellation": "",
     *              "user_hobby": "",
     *              "user_profession": "",
     *              "user_emotional_state": "",
     *              "user_income": "",
     *              "user_height": "",
     *              "is_black": false,
     *              "share_url": "http://dev.h5.sxypaopao.com/shareuser?user_id=171",
     *              "gift_img": [
     *                  {
     *                      "live_gift_logo": "https://lebolive-1255651273.image.myqcloud.com/image/2018/08/23/1534997938083.png",
     *                      "total": "10",
     *                      "live_gift_name": "名字",
     *                      "live_gift_coin": "100"
     *                  }
     *              ]
     *              "total_gift": 0,
     *              "user_is_member": "N",
     *              "anchor_info": {
     *                  "anchor_tip": "",
     *                  "anchor_character": "",
     *                  "anchor_good_topic": "",
     *                  "anchor_dress": "",
     *                  "anchor_check_img": "",
     *                  "anchor_emotional_state": "",
     *                  "anchor_images": "",
     *                  "anchor_stature": ""
     *                  "anchor_connection_rate": "",
     *                  "anchor_video_cover": "",
     *                  "anchor_video": "",
     *                  "anchor_chat_price": "",
     *                  "anchor_chat_status": "",
     *                  "total_gift": "1000",
     *                  "user_is_member": "N",
     *                  "anchor_guard": {
     *                      "user_id": "".
     *                      "user_nickname": "",
     *                      "user_avatar": "",
     *                      "anchor_guard_coin": "",
     *                      "anchor_guard_level": "",
     *                      "anchor_guard_level_name": "",
     *                   }
     *              },
     *              "anchor_images_list": [
     *                  {
     *                      "img_src": "https://lebolive-1255651273.image.myqcloud.com/image/2018/08/23/1534997938083.png",
     *                      "visible_type": "normal"
     *                  },
     *                  {
     *                      "img_src": "https://lebolive-1255651273.image.myqcloud.com/image/2018/10/30/1540865312180.png",
     *                      "visible_type": "normal"
     *                  }
     *              ],
     *              "guard_list": [
     *                  {
     *                      "user_id": "258",
     *                      "user_nickname": "LYXXMY一样",
     *                      "user_avatar": "http://thirdqq.qlogo.cn/qqapp/1106652113/23F1690D0AD8715603EA3D3E1AF30D19/100",
     *                      "total_coin": "370",
     *                      "current_level": "1",
     *                      "current_level_name": "初级守护",
     *                      "guard_status": "Y"
     *                  }
     *              ],
     *              "user_wechat" : '',
     *              "wechat_price" : '',
     *              "user_v_wechat_price" : '',
     *              "user_v_wechat" : '',
     *              "user_intimate": {
     *                  "level": 0,
     *                  "level_name": "萍水相逢",
     *                  "total_value": 420
     *              },
     *              "anchor_call_flg" : "Y",
     *      },
     *      "t": 1534924613
     *  }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function cardAction( $nUserId )
    {

        $nToUserId = $this->getParams('user_id', 'int', 0);
        // 0表示不在房间获取用户卡片信息(计算房管)
        $nAnchorUserId = $this->getParams('anchor_user_id', 'int', 0);

        try {
            $oToUser = User::findFirst($nToUserId);

            if ( !$oToUser ) {
                throw new Exception(ResponseError::getError(ResponseError::USER_NOT_EXISTS), ResponseError::USER_NOT_EXISTS);
            }

            // 加入一个5秒的缓存  防止同一时间多次访问
            $cacheDataKey = sprintf('card:%s:%s', $nToUserId, $nUserId);
            $cacheData    = $this->redis->get($cacheDataKey);
            if ( $cacheData ) {
                $row = json_decode($cacheData, TRUE);
            } else {

                $oAnchor   = Anchor::findFirst([
                    'user_id=:user_id:',
                    'bind' => [
                        'user_id' => $nToUserId,
                    ]
                ]);
                $is_online = $oToUser->user_online_status == 'Online' ? 1 : 0;

                $isFollow = UserFollow::findFirst([
                    'user_id=:user_id: and to_user_id=:to_user_id:',
                    'bind' => [
                        'user_id'    => $nUserId,
                        'to_user_id' => $nToUserId,
                    ]
                ]);

                $black = UserBlack::findFirst([
                    'user_id = :user_id: and to_user_id = :to_user_id:',
                    'bind' => [
                        'user_id'    => $nUserId,
                        'to_user_id' => $nToUserId
                    ]
                ]);

                $anchorInfo          = [
                    'anchor_level'           => '',
                    'anchor_title_number'    => '0',
                    'anchor_title_name'      => '',
                    'anchor_tip'             => '',
                    'anchor_character'       => '',
                    'anchor_good_topic'      => '',
                    'anchor_dress'           => '',
                    'anchor_stature'         => '',
                    'anchor_images'          => '',
                    'anchor_check_img'       => '',
                    'anchor_emotional_state' => '',
                    'anchor_connection_rate' => '',
                    'anchor_video'           => '',
                    'anchor_video_cover'     => '',
                    'anchor_chat_price'      => '',
                    'anchor_chat_status'     => '',
                    'anchor_weight'          => '',
                    'anchor_bwh'             => '',
                    'anchor_profession_tip'  => '',
                    'anchor_notice_tip'      => '',
                    'anchor_guard'           => [
                        'user_id'       => '',
                        'user_nickname' => '',
                        'user_avatar'   => '',
                        'user_level'    => '',
                        'free_times'    => '0'
                    ]
                ];
                $no_income_free_time = 0;
                if ( $oAnchor ) {
                    $no_income_free_time = UserGuard::getTodayFreeTimes($oAnchor->user_id, $nUserId);

                    $anchorTitleInfo = AnchorTitleConfig::getInfo($oAnchor->anchor_title_id);

                    $anchorInfo = [
                        'anchor_title_number'    => $anchorTitleInfo['number'],
                        'anchor_title_name'      => $anchorTitleInfo['name'],
                        'anchor_custom_title'    => $oAnchor->anchor_custom_title,
                        'anchor_level'           => $oAnchor->anchor_level,
                        'anchor_tip'             => $oAnchor->anchor_tip,
                        'anchor_character'       => $oAnchor->anchor_character,
                        'anchor_good_topic'      => $oAnchor->anchor_good_topic,
                        'anchor_dress'           => $oAnchor->anchor_dress,
                        'anchor_stature'         => $oAnchor->anchor_stature,
                        'anchor_images'          => $oAnchor->anchor_images,
                        'anchor_check_img'       => $oAnchor->anchor_check_img,
                        'anchor_emotional_state' => $oAnchor->anchor_emotional_state,
                        'anchor_connection_rate' => $oAnchor->getConnectionRate(),
                        'anchor_video'           => $oAnchor->anchor_video,
                        'anchor_video_cover'     => $oAnchor->anchor_video_cover,
                        'anchor_chat_price'      => $oAnchor->anchor_chat_price,
                        'anchor_chat_status'     => $oAnchor->anchor_chat_status,
                        'anchor_weight'          => $oAnchor->anchor_weight,
                        'anchor_bwh'             => $oAnchor->anchor_bwh,
                        'anchor_profession_tip'  => $oAnchor->anchor_profession_tip,
                        'anchor_notice_tip'      => $oAnchor->anchor_notice_tip,
                        'anchor_guard'           => [
                            'user_id'                 => '',
                            'user_nickname'           => '',
                            'user_avatar'             => '',
                            'user_level'              => '',
                            'anchor_guard_coin'       => '',
                            'anchor_guard_level'      => '',
                            'anchor_guard_level_name' => '',
                        ]
                    ];
                    if ( $oAnchor->anchor_guard_id ) {
                        //查找守护信息
                        $oGuardUser = User::findFirst($oAnchor->anchor_guard_id);
                        if ( $oGuardUser ) {
                            $anchorInfo['anchor_guard'] = [
                                'user_id'                 => $oGuardUser->user_id,
                                'user_nickname'           => $oGuardUser->user_nickname,
                                'user_avatar'             => $oGuardUser->user_avatar,
                                'user_level'              => $oGuardUser->user_level,
                                'anchor_guard_coin'       => $oAnchor->anchor_guard_coin,
                                'anchor_guard_level'      => $oAnchor->anchor_guard_level,
                                'anchor_guard_level_name' => $oAnchor->anchor_guard_level_name,
                            ];
                        }
                    }
                }

                $gift_img = $this->getLiftByUid($nToUserId, 2, 1, 5);


                $user_img = $oToUser->user_img;
                $app_os   = strtolower($this->getParams('app_os'));
                if ( $app_os == 'ios' ) {
                    $userImgArr   = explode(',', $anchorInfo['anchor_images']);
                    $userImgArr2  = explode(',', $anchorInfo['anchor_check_img']);
                    $mergeUserImg = array_merge($userImgArr2, $userImgArr);
                    foreach ( $mergeUserImg as $key => $item ) {
                        if ( empty($item) ) {
                            unset($mergeUserImg[ $key ]);
                        }
                    }
                    $user_img = implode(',', $mergeUserImg);
                }


                $oUser   = User::findFirst($nUserId);
                $columns = 'img_src,visible_type';
                if ( $oUser->user_member_expire_time > time() ) {
                    $columns = 'img_src,"normal" as visible_type';
                }
                // 获取主播相册
                $oAnchorImage = AnchorImage::find([
                    'user_id = :user_id: AND position != :position:',
                    'bind'    => [
                        'user_id'  => $nToUserId,
                        'position' => 'cover'
                    ],
                    'columns' => $columns
                ]);

                $guardList = [];
                if ( $oToUser->user_is_anchor == 'N' ) {
                    $builder   = $this->modelsManager
                        ->createBuilder()
                        ->from([ 'ug' => UserGuard::class ])
                        ->join(User::class, 'ug.anchor_user_id=u.user_id', 'u')
                        ->columns('u.user_id,u.user_nickname,u.user_avatar,u.user_level,ug.total_coin,ug.current_level,ug.current_level_name,ug.guard_status')
                        ->where('ug.user_id = :user_id: AND ug.guard_status = "Y"', [
                            'user_id' => $nToUserId
                        ])
                        ->orderBy('total_coin desc');
                    $row       = $this->page($builder, 1, 3);
                    $guardList = $row['items'];
                }

                // 添加访客记录
                if ( $nToUserId != $nUserId ) {

                    $oUserViewLog = UserViewLog::findFirst([
                        'user_view_user_id = :user_view_user_id: AND user_viewed_user_id = :user_viewed_user_id:',
                        'bind' => [
                            'user_view_user_id'   => $nUserId,
                            'user_viewed_user_id' => $nToUserId,
                        ]
                    ]);
                    if ( $oUserViewLog && time() - $oUserViewLog->user_view_update_time > 60 ) {
                        // 存在 且 上次时间大于60秒
                        $oUserViewLog->user_view_count += 1;

                        if ( $oUserViewLog->user_view_update_time < strtotime(date('Y-m-d')) ) {
                            // 如果上次查看是昨天 则当日访客记录归零
                            $oUserViewLog->user_view_today_count = 1;
                        } else {
                            $oUserViewLog->user_view_today_count += 1;
                        }
                        $oUserViewLog->save();

                        // 保存总浏览次数
                        $this->db->execute(sprintf("update user set user_viewed_count = user_viewed_count + 1 where user_id = %s", $oToUser->user_id));
                    } else if ( !$oUserViewLog ) {
//                        $oUserViewLog                        = new UserViewLog();
//                        $oUserViewLog->user_view_user_id     = $nUserId;
//                        $oUserViewLog->user_viewed_user_id   = $nToUserId;
//                        $oUserViewLog->user_view_count       = 1;
//                        $oUserViewLog->user_view_today_count = 1;
//                        $oUserViewLog->save();
//                        $this->db->execute(sprintf("update user set user_viewed_count = user_viewed_count + 1 where user_id = %s", $oToUser->user_id));
                        $now       = time();
                        $insertSql = <<<INSERTSQL
INSERT INTO user_view_log(user_view_user_id,user_viewed_user_id,user_view_count,user_view_today_count,user_view_create_time,user_view_update_time)
SELECT $nUserId,$nToUserId,1,1,$now,$now FROM DUAL WHERE NOT EXISTS(SELECT 1 FROM user_view_log WHERE user_view_user_id = $nUserId AND user_viewed_user_id = $nToUserId);
INSERTSQL;
                        $this->db->execute($insertSql);
                        if ( $this->db->affectedRows() > 0 ) {
                            // 保存总浏览次数
                            $this->db->execute(sprintf("update user set user_viewed_count = user_viewed_count + 1 where user_id = %s", $oToUser->user_id));
                        }
                    }
                }

                $intimateData = UserIntimate::getIntimateLevel($nToUserId, $nUserId, $oToUser->user_is_anchor, $oUser->user_is_anchor);

                $userWechat = $oToUser->user_wechat;

                if ( $intimateData['total_value'] < $oToUser->user_wechat_price && $userWechat ) {
                    $wechatLength = mb_strlen($userWechat);
                    if ( $wechatLength <= 2 ) {
                        $userWechat = mb_substr($userWechat, 0, 1) . '*****';
                    } else {
                        $userWechat = str_replace(mb_substr($userWechat, 1, $wechatLength - 2), '*****', $userWechat);
                    }
                }

                // 大V微信
                $vWechat       = $oToUser->user_v_wechat;
                $vWechatLength = mb_strlen($vWechat);
                if ( $vWechatLength <= 2 ) {
                    $vWechat = mb_substr($vWechat, 0, 1) . '*****';
                } else {
                    $vWechat = str_replace(mb_substr($vWechat, 1, $vWechatLength - 2), '*****', $vWechat);
                }


                $anchorCallFlg    = 'N';
                $ownerAnchorPrice = '0';
                if ( $oUser->user_is_anchor == 'Y' && $oToUser->user_is_anchor == 'N' ) {
                    // 自己是主播 对方是用户  判断对方的余额是否大于自己的单价
                    $oOwnerAnchor     = Anchor::findFirst([
                        'user_id=:user_id:',
                        'bind' => [
                            'user_id' => $nUserId,
                        ]
                    ]);
                    $ownerAnchorPrice = $oOwnerAnchor ? $oOwnerAnchor->anchor_chat_price : '0';
                    if ( $oOwnerAnchor && $oOwnerAnchor->anchor_chat_price <= $oToUser->user_coin + $oToUser->user_free_coin ) {
                        $anchorCallFlg = 'Y';
                    }
                }


                $row = [
                    // 用户ID
                    'user_id'                  => $oToUser->user_id,
                    // 用户昵称
                    'user_nickname'            => $oToUser->user_nickname,
                    // 用户头像
                    'user_avatar'              => $oToUser->user_avatar,
                    // 用户等级
                    'user_level'               => $oToUser->user_level,
                    // 用户性别
                    'user_sex'                 => $oToUser->user_sex,
                    // 用户送礼总额
                    'user_consume_total'       => sprintf('%.2f', $oToUser->user_consume_total),
                    // 用户收礼总额
                    'user_collect_total'       => sprintf('%.2f', $oToUser->user_collect_total),
                    // 用户简介
                    'user_intro'               => $oToUser->user_intro,
                    // 用户生日
                    'user_birth'               => $oToUser->user_birth,
                    // 用户关注数
                    'user_follow_total'        => $oToUser->user_follow_total,
                    // 用户粉丝数
                    'user_fans_total'          => $oToUser->user_fans_total,
                    // 是否主播
                    'user_is_anchor'           => $oToUser->user_is_anchor,
                    // 主播排名
                    'anchor_ranking'           => isset($oAnchor->anchor_ranking) ? $oAnchor->anchor_ranking : '0',
                    // 是否关注
                    'is_follow'                => $isFollow ? 'Y' : 'N',
                    // 用户聊天ID
                    'chat_room_id'             => UserChatDialog::getChatRoomId($oToUser->user_id, $nUserId),
                    // 是否有信息卡特效
                    'is_card_effect'           => 'N',
                    'is_online'                => $is_online,
                    'user_img'                 => $user_img,
                    'user_register_time'       => $oToUser->user_register_time,
                    'user_home_town'           => $oToUser->user_home_town,
                    'user_constellation'       => $oToUser->user_constellation,
                    'user_hobby'               => $oToUser->user_hobby,
                    'user_profession'          => $oToUser->user_profession,
                    'user_emotional_state'     => $oToUser->user_emotional_state,
                    //用户收入
                    'user_income'              => $oToUser->user_income,
                    //用户身高
                    'user_height'              => $oToUser->user_height,
                    'is_black'                 => $black ? TRUE : FALSE,
                    'share_url'                => APP_WEB_URL . '/shareuser?user_id=' . $nToUserId,
                    'gift_img'                 => $gift_img['items'],
                    'total_gift'               => $this->getTotalGift($nToUserId),
                    'user_is_member'           => $oToUser->user_member_expire_time == 0 ? 'N' : (time() > $oToUser->user_member_expire_time ? 'O' : 'Y'),
                    // 主播信息
                    'anchor_info'              => $anchorInfo,
                    // 图片集
                    'anchor_images_list'       => $oAnchorImage,
                    'guard_list'               => $guardList,
                    'no_income_free_time'      => (string)$no_income_free_time,
                    'no_income_free_time_type' => UserPrivateChatLog::FREE_TIME_TYPE_GUARD,
                    // 微信价格
                    'wechat_price'             => $oToUser->user_wechat_price,
                    // 用户微信
                    'user_wechat'              => $userWechat,
                    // 是否为有微信
                    'has_wechat'               => $oToUser->user_v_wechat ? 'Y' : 'N',
                    // 亲密度
                    'user_intimate'            => $intimateData,
                    // 微信售卖次数
                    'user_wechat_sale_count'   => $oToUser->user_wechat_sale_count,
                    // 是否为摄影师
                    'user_is_photographer'     => $oToUser->user_is_photographer,
                    // 总访客数
                    'all_view_count'           => $oToUser->user_viewed_count,
                    // 主播是否显示拨打按钮
                    'anchor_call_flg'          => $anchorCallFlg,
                    // 自己是主播时 自己的拨打价格
                    'owner_anchor_price'       => $ownerAnchorPrice,
                    // 大V微信价格
                    'user_v_wechat_price'      => $oToUser->user_v_wechat_price,
                    // 大V微信
                    'user_v_wechat'            => $vWechat,
                ];
                $this->redis->set($cacheDataKey, json_encode($row));
                $this->redis->expire($cacheDataKey, 5);
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * balanceAction 用户余额 TODO
     *
     * @param int $nUserId
     */
    public function balanceAction( $nUserId = '' )
    {
        $oUser       = User::findFirst($nUserId);
        $row['user'] = [
            'user_dot'  => sprintf('%.2f', $oUser->user_dot),
            'user_coin' => sprintf('%.2f', $oUser->user_coin + $oUser->user_free_coin),
        ];
        $this->success($row);
    }

    public function getLiftByUid( $user_id, $type, $page, $pagesize )
    {
        try {
            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'a' => UserGiftLog::class ])
                ->columns('live_gift_logo,sum(live_gift_number) as total,live_gift_name,live_gift_coin')
                ->where("anchor_user_id={$user_id}")
                ->groupBy([ 'live_gift_name' ])
                ->orderby("total desc");
            $row     = $this->page($builder, $page, $pagesize);

        } catch ( Exception $e ) {
            $this->error(
                ResponseError::FAIL,
                sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage())
            );
        }
        if ( $type == 1 ) {
            $data = array_column($row['items'], 'live_gift_logo');
            return $data;
        }
        return $row;
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

    public function addBlackAction( $nUserId )
    {
        $anchor_user_id = $this->getParams('anchor_user_id');
        if ( empty($anchor_user_id) ) {
            $this->error(10002);
        }
        try {
            $type = $this->getParams('type', 'int', 1);
            if ( $type == 1 ) {
                $model             = new UserBlack();
                $model->user_id    = $nUserId;
                $model->to_user_id = $anchor_user_id;
                $model->create();
            } else {
                $model = UserBlack::findFirst([
                    'user_id=:user_id: and to_user_id =:to_user_id:',
                    'bind' => [
                        'user_id'    => $nUserId,
                        'to_user_id' => $anchor_user_id
                    ]
                ]);
                if ( $model ) {
                    $model->delete();
                }
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();

    }

    //获取用户收礼总数
    public function getTotalGift( $nUserId )
    {
        $data = UserGiftLog::query()
            ->columns("sum(live_gift_number) as total")
            ->where("anchor_user_id={$nUserId}")
            ->execute()
            ->toArray();
        return $data[0]['total'] ? $data[0]['total'] : 0;
    }

    /**
     * 属性选择
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/select
     * @api {get} /user/profile/select 属性选择
     * @apiName 属性选择-profile-select
     * @apiGroup Profile
     * @apiDescription 主播属性选择
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String='user_income','user_height','user_profession','anchor_tip','anchor_character','anchor_good_topic','anchor_dress','anchor_stature'} type 类型
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String='user_income','user_height','user_profession','anchor_tip','anchor_character','anchor_good_topic','anchor_dress','anchor_stature'} type 类型
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {String} d.name 名称
     * @apiSuccess {String} d.select 选择项  以半角逗号分隔
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *    {
     *        "c": 0,
     *        "m": "请求成功",
     *        "d": {
     *            "name": "主播擅长话题",
     *            "select": "情感,两性,私密,成人,段子,音乐,电影,运动"
     *        },
     *        "t": 1534926331
     *    }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function selectAction( $nUserId = 0 )
    {
        $sType               = $this->getParams('type');
        $oUserProfileSetting = UserProfileSetting::findFirst([
            'profile_key = :profile_key:',
            'bind' => [
                'profile_key' => $sType
            ]
        ]);
        $result              = [
            'name'   => '未知',
            'select' => '',
        ];
        if ( $oUserProfileSetting ) {
            $result = [
                'name'   => $oUserProfileSetting->profile_name,
                'select' => $oUserProfileSetting->profile_select,
            ];
        }
        $this->success($result);
    }


    /**
     * 修改主播信息
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/updateAnchor
     * @api {get} /user/profile/updateAnchor 修改主播信息
     * @apiName 修改主播信息-profile-updateanchor
     * @apiGroup Profile
     * @apiDescription 修改主播信息
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} anchor_tip 标签
     * @apiParam (正常请求){String} anchor_stature 身材
     * @apiParam (正常请求){String} anchor_good_topic 擅长话题
     * @apiParam (正常请求){String} anchor_dress 主播爱穿
     * @apiParam (正常请求){String} anchor_character 性格
     * @apiParam (正常请求){String} anchor_emotional_state 情感状态
     * @apiParam (正常请求){String} anchor_images 图片 以半角逗号分隔
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} anchor_tip 标签
     * @apiParam (debug){String} anchor_stature 身材
     * @apiParam (debug){String} anchor_good_topic 擅长话题
     * @apiParam (debug){String} anchor_dress 主播爱穿
     * @apiParam (debug){String} anchor_character 性格
     * @apiParam (debug){String} anchor_images 图片 以半角逗号分隔
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
    public function updateAnchorAction( $nUserId = 0 )
    {

        $param = $this->getParams();
//        $this->log->info(json_encode($param));

        // 可以修改的字段
        $aAllow = [
            'anchor_tip',
            'anchor_character',
            'anchor_good_topic',
            'anchor_dress',
            'anchor_stature',
            'anchor_emotional_state',
            'anchor_images',
            'anchor_weight',
            'anchor_bwh',
            'anchor_profession_tip',
            'anchor_notice_tip',
        ];
        try {
//            throw new Exception(
//                '正在更新模块，请稍后重试',
//                ResponseError::OPERATE_FAILED
//            );
            $oUser = User::findFirst($nUserId);
            if ( $oUser->user_is_anchor == 'N' ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::OPERATE_FAILED),
                    ResponseError::OPERATE_FAILED
                );
            }
            $oAnchor     = Anchor::findFirst([
                'user_id = :user_id:',
                'bind' => [
                    'user_id' => $nUserId
                ]
            ]);
            $imageDelete = [];
            $imageAdd    = [];
            $imageChange = FALSE;
            $oldImages   = $oAnchor->anchor_images ? explode(',', $oAnchor->anchor_images) : [];
            $newImages   = isset($param['anchor_images']) ? explode(',', $param['anchor_images']) : [];
            foreach ( $param as $key => $value ) {
                if ( in_array($key, $aAllow) ) {
                    if ( $key == 'anchor_images' && $oAnchor->anchor_images != $value ) {
                        // 图片有改变
                        $imageChange = TRUE;
                    }
                    $oAnchor->$key = $value;
                }
            }

            $now = time();
            if ( $imageChange ) {
                foreach ( $oldImages as $oldItem ) {
                    if ( !in_array($oldItem, $newImages) ) {
                        $imageDelete[] = $oldItem;
                    }
                }
                foreach ( $newImages as $newItem ) {
                    if ( !in_array($newItem, $oldImages) ) {
                        $imageAdd[] = [
                            'user_id'     => $nUserId,
                            'img_src'     => $newItem,
                            'position'    => 'normal',
                            'create_time' => $now,
                            'update_time' => $now
                        ];
                    }
                }
                // 查出所有普通图片
                $oAnchorImage   = AnchorImage::find([
                    'user_id = :user_id: AND position = :position:',
                    'bind' => [
                        'user_id'  => $nUserId,
                        'position' => 'normal'
                    ]
                ]);
                $imageDeleteIds = [];
                foreach ( $oAnchorImage AS $nowImage ) {
                    if ( in_array($nowImage->img_src, $imageDelete) ) {
                        $imageDeleteIds[] = $nowImage->id;
                    }
                }
                // 存在删除的 则执行删除
                if ( $imageDelete ) {
                    $deleteIdsStr = implode(',', $imageDeleteIds);
                    $sql          = "delete from anchor_image where id in ({$deleteIdsStr})";
                    $connection   = (new AnchorImage())->getWriteConnection();
                    $connection->execute($sql);
                }

                // 存在添加 则批量添加
                if ( $imageAdd ) {
                    (new AnchorImage())->saveAll($imageAdd);
                }
            }
            if ( $oAnchor->save() === FALSE ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oAnchor->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success();
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/anchor
     * @api {get} /user/profile/anchor 003-190912主播信息
     * @apiName 主播信息-profile-anchor
     * @apiGroup Profile
     * @apiDescription 主播信息
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {String} d.anchor_check_img 认证图片 以半角逗号分隔
     * @apiSuccess {String} d.anchor_tip   标签
     * @apiSuccess {String} d.anchor_character   性格
     * @apiSuccess {String} d.anchor_good_topic  擅长话题
     * @apiSuccess {String} d.anchor_dress  爱穿
     * @apiSuccess {String} d.anchor_stature  身材
     * @apiSuccess {String} d.anchor_images  图集  以半角逗号分隔
     * @apiSuccess {String} d.anchor_video  视频地址
     * @apiSuccess {String} d.anchor_video_cover  封面地址
     * @apiSuccess {String} d.anchor_emotional_state  情感状态
     * @apiSuccess {String} d.anchor_video_check_status  视频审核状态
     * @apiSuccess {String} d.anchor_image_check_status   图片审核状态
     * @apiSuccess {String} d.anchor_connection_rate   接通率
     * @apiSuccess {string='Y','N'} d.anchor_info.anchor_dispatch_flg 是否派单主播
     * @apiSuccess {string='Y','N'} d.anchor_info.anchor_dispatch_open_flg 派单状态是否打开
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *  {
     *      "c": 0,
     *      "m": "请求成功",
     *      "d": {
     *              "anchor_check_img": "https://lebolive-1255651273.image.myqcloud.com/image/2018/08/21/1534812775269.png,https://lebolive-1255651273.image.myqcloud.com/image/2018/08/19/1534688096385.png,https://lebolive-1255651273.image.myqcloud.com/image/2018/08/22/1534921921940.png,https://lebolive-1255651273.image.myqcloud.com/image/2018/08/22/1534928658889.png,https://lebolive-1255651273.image.myqcloud.com/image/2018/08/22/1534928662356.png",
     *              "anchor_tip": "",
     *              "anchor_character": "",
     *              "anchor_good_topic": "",
     *              "anchor_dress": "",
     *              "anchor_stature": "",
     *              "anchor_images": "",
     *              "anchor_video": "http://lebolive-1255651273.file.myqcloud.com/video/2018/08/22/1534922512994.mp4",
     *              "anchor_video_cover": "https://lebolive-1255651273.image.myqcloud.com/image/2018/08/21/1534812775269.png",
     *              "anchor_emotional_state": "",
     *              "anchor_video_check_status": "Y",
     *              "anchor_image_check_status": "Y",
     *              "anchor_connection_rate": "84.38",
     *              "anchor_dispatch_flg": "Y",
     *              "anchor_dispatch_open_flg": "Y"
     *      },
     *      "t": 1535015213
     *  }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function anchorAction( $nUserId = 0 )
    {

        try {
            $oUser = User::findFirst($nUserId);
            if ( !$oUser ) {
                throw new Exception(ResponseError::getError(ResponseError::USER_NOT_EXISTS), ResponseError::USER_NOT_EXISTS);
            }
            if ( $oUser->user_is_anchor == 'N' ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUser->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            $oAnchor = Anchor::findFirst([
                'user_id=:user_id:',
                'bind' => [
                    'user_id' => $oUser->user_id,
                ]
            ]);
            if ( !$oAnchor ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUser->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            $row = [
                'anchor_weight'             => $oAnchor->anchor_weight,
                'anchor_bwh'                => $oAnchor->anchor_bwh,
                'anchor_profession_tip'     => $oAnchor->anchor_profession_tip,
                'anchor_notice_tip'         => $oAnchor->anchor_notice_tip,
                'anchor_check_img'          => $oAnchor->anchor_check_img,
                'anchor_tip'                => $oAnchor->anchor_tip,
                'anchor_character'          => $oAnchor->anchor_character,
                'anchor_good_topic'         => $oAnchor->anchor_good_topic,
                'anchor_dress'              => $oAnchor->anchor_dress,
                'anchor_stature'            => $oAnchor->anchor_stature,
                'anchor_images'             => $oAnchor->anchor_images,
                'anchor_video'              => $oAnchor->anchor_video,
                'anchor_video_cover'        => $oAnchor->anchor_video_cover,
                'anchor_emotional_state'    => $oAnchor->anchor_emotional_state,
                'anchor_video_check_status' => $oAnchor->anchor_video_check_status,
                'anchor_image_check_status' => $oAnchor->anchor_image_check_status,
                'anchor_connection_rate'    => sprintf('%.2f', $oAnchor->anchor_called_count == 0 ? 100 : $oAnchor->anchor_chat_count / $oAnchor->anchor_called_count * 100),
                'anchor_dispatch_flg'       => $oAnchor->anchor_dispatch_flg,
                'anchor_dispatch_open_flg'  => 'N',
            ];

            if ( $oAnchor->anchor_dispatch_flg == 'Y' ) {
                $oAnchorDispatch = AnchorDispatch::findFirst([
                    'anchor_dispatch_user_id = :anchor_dispatch_user_id:',
                    'bind' => [
                        'anchor_dispatch_user_id' => $oAnchor->user_id
                    ]
                ]);
                if ( $oAnchorDispatch ) {
                    $row['anchor_dispatch_open_flg'] = $oAnchorDispatch->anchor_dispatch_open_flg;
                }
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/videoUpdate
     * @api {get} /user/profile/videoUpdate 视频更改
     * @apiName 视频更改-videoUpdate
     * @apiGroup Profile
     * @apiDescription 视频更改
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} video 视频地址
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} video 视频地址
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
    public function videoUpdateAction( $nUserId = 0 )
    {
        $sVideo = $this->getParams('video', 'string', '');
        try {
            if ( empty($sVideo) ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'video'),
                    ResponseError::USER_IS_CERTIFICATION
                );
            }
            //判断是不是主播
            $oUser = User::findFirst($nUserId);
            if ( $oUser->user_is_anchor == 'N' ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), ''),
                    ResponseError::OPERATE_FAILED
                );
            }
            $oAnchor = Anchor::findFirst([
                'user_id=:user_id:',
                'bind' => [
                    'user_id' => $oUser->user_id,
                ]
            ]);
            if ( !$oAnchor ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), ''),
                    ResponseError::OPERATE_FAILED
                );
            }
            //判断主播视频提交的状态
            if ( $oAnchor->anchor_video_check_status == 'C' ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::ANCHOR_VIDEO_CHECK),
                    ResponseError::ANCHOR_VIDEO_CHECK
                );
            }
            $oAnchorSourceCertification = new AnchorSourceCertification();
            $connection                 = $oAnchorSourceCertification->getWriteConnection();
            $connection->begin();
            $oAnchorSourceCertification->user_id   = $nUserId;
            $oAnchorSourceCertification->auth_type = 'video';
            $oAnchorSourceCertification->status    = 'C';
            if ( $oAnchorSourceCertification->save() === FALSE ) {
                $connection->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oAnchorSourceCertification->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            $oAnchorSourceCertificationDetail                   = new AnchorSourceCertificationDetail();
            $oAnchorSourceCertificationDetail->user_id          = $nUserId;
            $oAnchorSourceCertificationDetail->source_url       = $sVideo;
            $oAnchorSourceCertificationDetail->sort_num         = 0;
            $oAnchorSourceCertificationDetail->certification_id = $oAnchorSourceCertification->id;
            $oAnchorSourceCertificationDetail->source_type      = 'video';
            $oAnchorSourceCertificationDetail->status           = 'C';
            if ( $oAnchorSourceCertificationDetail->save() === FALSE ) {
                $connection->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oAnchorSourceCertificationDetail->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            $oAnchor->anchor_video_check_status = 'C';
            if ( $oAnchor->save() === FALSE ) {
                $connection->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oAnchor->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            $connection->commit();

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/imageUpdate
     * @api {get} /user/profile/imageUpdate 图片更改
     * @apiName 图片更改-imageUpdate
     * @apiGroup Profile
     * @apiDescription 图片更改
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} image 图片地址 以半角逗号分隔
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} image 图片地址 以半角逗号分隔
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
    public function imageUpdateAction( $nUserId = 0 )
    {
        $sImages = $this->getParams('image', 'string', '');
        try {
            $sImagesArr = explode(',', $sImages);
            foreach ( $sImagesArr as $key => $item ) {
                if ( empty($item) ) {
                    unset($sImagesArr[ $key ]);
                }
            }
            $imageCount = 5;
            if ( count($sImagesArr) < $imageCount ) {
                throw new Exception(
                    sprintf(ResponseError::getError(ResponseError::USER_CERTIFICATION_IMAGES_COUNT), $imageCount),
                    ResponseError::USER_CERTIFICATION_IMAGES_COUNT
                );
            }
            //判断是不是主播
            $oUser = User::findFirst($nUserId);
            if ( $oUser->user_is_anchor == 'N' ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), ''),
                    ResponseError::OPERATE_FAILED
                );
            }
            $oAnchor = Anchor::findFirst([
                'user_id=:user_id:',
                'bind' => [
                    'user_id' => $oUser->user_id,
                ]
            ]);
            if ( !$oAnchor ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), ''),
                    ResponseError::OPERATE_FAILED
                );
            }
            //判断主播视频提交的状态
            if ( $oAnchor->anchor_image_check_status == 'C' ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::ANCHOR_IMAGE_CHECK),
                    ResponseError::ANCHOR_IMAGE_CHECK
                );
            }
            $oAnchorSourceCertification = new AnchorSourceCertification();
            $connection                 = $oAnchorSourceCertification->getWriteConnection();
            $connection->begin();
            $oAnchorSourceCertification->user_id   = $nUserId;
            $oAnchorSourceCertification->auth_type = 'img';
            $oAnchorSourceCertification->status    = 'C';
            if ( $oAnchorSourceCertification->save() === FALSE ) {
                $connection->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oAnchorSourceCertification->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }

            $saveAll     = [];
            $create_time = time();
            foreach ( $sImagesArr as $key => $item ) {
                $saveAll[] = [
                    'certification_id' => $oAnchorSourceCertification->id,
                    'source_url'       => $item,
                    'sort_num'         => $key,
                    'status'           => 'C',
                    'source_type'      => 'img',
                    'create_time'      => $create_time,
                    'update_time'      => $create_time,
                ];
            }

            $oAnchorSourceCertificationDetail = new AnchorSourceCertificationDetail();
            if ( $oAnchorSourceCertificationDetail->saveAll($saveAll) === FALSE ) {
                $connection->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oAnchorSourceCertificationDetail->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            $oAnchor->anchor_image_check_status = 'C';
            if ( $oAnchor->save() === FALSE ) {
                $connection->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oAnchor->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            $connection->commit();

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/level
     * @api {get} /user/profile/level 用户等级
     * @apiName profile-level
     * @apiGroup Profile
     * @apiDescription 用户等级
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {number} d.user_id   用户id
     * @apiSuccess {String} d.rank_hide   排行榜是否隐藏
     * @apiSuccess {String} d.user_avatar   用户头像
     * @apiSuccess {String} d.user_nickname   用户昵称
     * @apiSuccess {number} d.user_level  用户等级
     * @apiSuccess {number} d.user_exp  用户经验
     * @apiSuccess {number} d.this_level_exp  当前等级的经验
     * @apiSuccess {number} d.next_level_exp   下一等级所需经验
     * @apiSuccess {number} d.high_level_user_online   上线通知 所需等级
     * @apiSuccess {number} d.hide_rank_level   开启排行榜隐藏功能的等级
     * @apiSuccess {number='Y','N'} d.has_old_exp   是否有经验可以领取
     * @apiSuccess {object[]} d.rule
     * @apiSuccess {String} d.rule.content  规则内容
     * @apiSuccess {object[]} d.icon_rule   带标签规则显示
     * @apiSuccess {String} d.icon_rule.icon   图标
     * @apiSuccess {String} d.icon_rule.title   标题
     * @apiSuccess {String} d.icon_rule.detail  详情
     * @apiSuccess {object[]} d.level_reward   等级奖励列表
     * @apiSuccess {String} d.level_reward.level_value   等级值
     * @apiSuccess {String} d.level_reward.reward_coin   奖励金币数
     * @apiSuccess {String='Y(已经领取)','C(等待领取)','N(不能领取)'} d.level_reward.reward_flg  领取状态
     * @apiSuccess {String} d.rule_href  活动等级说明H5链接地址
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *         {
     *             "c": 0,
     *             "m": "请求成功",
     *             "d": {
     *                     "user_id": "311",
     *                     "rank_hide": "Y",
     *                     "user_avatar": "https://lebolive-1255651273.image.myqcloud.com/image/2018/12/07/1544150678158.png",
     *                     "user_level": "4",
     *                     "user_nickname": "11111",
     *                     "user_exp": 568990,
     *                     "this_level_exp": 24000,
     *                     "next_level_exp": 18880,
     *                     "high_level_user_online": "4",
     *                     "hide_rank_level": "3",
     *                     "has_old_exp": "Y",
     *                     "rule": [
     *                         {
     *                             "content": "每消耗1金币即可获得10金币"
     *                         },
     *                         {
     *                             "content": "每日签到任务可获得经验值"
     *                         }
     *                     ],
     *                     "icon_rule": [{
     *                         "icon": "http:\/\/youbo-1252571077.coscd.myqcloud.com\/gift\/cheers.png",
     *                             "title": "与小姐姐视频通话",
     *                             "detail": "提升等级最快方式"
     *                         }, {
     *                         "icon": "http:\/\/youbo-1252571077.coscd.myqcloud.com\/gift\/love_beauty.png",
     *                             "title": "赠送礼物",
     *                             "detail": "可获得更多经验值"
     *                         }, {
     *                         "icon": "http:\/\/static.greenlive.1booker.com\/upload\/image\/20171122\/1511316322286418.png",
     *                             "title": "成为会员",
     *                             "detail": "会员期间享受经验值200%加速"
     *                         }, {
     *                         "icon": "http:\/\/youbo-1252571077.coscd.myqcloud.com\/gift\/crystal_shoes.png",
     *                             "title": "日常签到",
     *                             "detail": "每天签到可获得经验值，还有金币奖励哦"
     *                         }],
     *                         "level_reward": [
     *                             {
     *                                 "level_value": "5",
     *                                 "reward_coin": "10",
     *                                 "reward_flg": "Y"
     *                             },
     *                             {
     *                                 "level_value": "10",
     *                                 "reward_coin": "20",
     *                                 "reward_flg": "C"
     *                             },
     *                             {
     *                                 "level_value": "15",
     *                                 "reward_coin": "30",
     *                                 "reward_flg": "N"
     *                             }
     *                         ],
     *                     "rule_href": "https://mail.163.com"
     *             },
     *             "t": "1545982324"
     *         }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function levelAction( $nUserId = 0 )
    {

        try {
            $oUser       = User::findFirst($nUserId);
            $nextLevel   = LevelConfig::findFirst([
                'level_value = :level_value: AND level_type = :level_type:',
                'bind' => [
                    'level_type'  => 'user',
                    'level_value' => $oUser->user_level + 1
                ]
            ]);
            $thisLevel   = LevelConfig::findFirst([
                'level_value = :level_value: AND level_type = :level_type:',
                'bind' => [
                    'level_type'  => 'user',
                    'level_value' => $oUser->user_level
                ]
            ]);
            $levelChange = 0;
            // 判断 等级是否正确  如果不正确 将修改
            if ( $thisLevel->level_exp > $oUser->user_exp ) {
                // 经验小于当前等级 则重新计算等级
                $levelChange = LevelConfig::getLevelInfo($oUser->user_exp, LevelConfig::LEVEL_TYPE_USER);
            } else if ( $nextLevel && $nextLevel->level_exp < $oUser->user_exp ) {
                // 下一级存在 当前经验大于下一级
                $levelChange = LevelConfig::getLevelInfo($oUser->user_exp, LevelConfig::LEVEL_TYPE_USER);
            }
            if ( $levelChange ) {
                $oUser->user_level = $levelChange['level'];
                $oUser->save();
                $nextLevel = LevelConfig::findFirst([
                    'level_value = :level_value: AND level_type = :level_type:',
                    'bind' => [
                        'level_type'  => 'user',
                        'level_value' => $oUser->user_level + 1
                    ]
                ]);
                $thisLevel = LevelConfig::findFirst([
                    'level_value = :level_value: AND level_type = :level_type:',
                    'bind' => [
                        'level_type'  => 'user',
                        'level_value' => $oUser->user_level
                    ]
                ]);
            }

            $has_old_exp = 'N';
            if ( $oUser->user_create_time < strtotime('2019-01-07 12:00:00') ) {
                $coinToExp   = Kv::get(Kv::COIN_TO_EXP);
                $shouldExp   = intval($oUser->user_consume_total * $coinToExp / 3);
                $has_old_exp = $shouldExp > $oUser->user_exp ? 'Y' : 'N';
            }

            // 获取等级奖励
            $levelRewardBuilder = $this->modelsManager
                ->createBuilder()
                ->from([ 'l' => LevelConfig::class ])
                ->leftJoin(LevelRewardLog::class, 'l.level_value = lr.level_reward_log_level_value AND level_reward_log_user_id = ' . $nUserId, 'lr')
                ->columns('l.level_value,l.reward_coin,lr.level_reward_log_id')
                ->where('l.level_type = :level_type: AND reward_coin > 0', [
                    'level_type' => LevelConfig::LEVEL_TYPE_USER,
                ])
                ->orderBy('l.level_value');

            $levelRewardRow = $this->page($levelRewardBuilder, 1, 100);

            foreach ( $levelRewardRow['items'] as &$levelRewardItem ) {
                if ( $levelRewardItem['level_value'] > $oUser->user_level ) {
                    // 当前等级 大于本身等级  不能领取
                    $levelRewardItem['reward_flg'] = 'N';
                } else if ( $levelRewardItem['level_reward_log_id'] ) {
                    // 已经领过
                    $levelRewardItem['reward_flg'] = 'Y';
                } else {
                    // 等待领取
                    $levelRewardItem['reward_flg'] = 'C';
                }
                unset($levelRewardItem['level_reward_log_id']);
            }

            $row = [
                'user_id'                => $oUser->user_id,
                'rank_hide'              => $oUser->user_remind == 1 ? 'N' : 'Y',
                'user_avatar'            => $oUser->user_avatar,
                'user_nickname'          => $oUser->user_nickname,
                'user_level'             => $oUser->user_level,
                'user_exp'               => intval($oUser->user_exp),
                'this_level_exp'         => intval($thisLevel->level_exp),
                'next_level_exp'         => $nextLevel ? intval($nextLevel->level_exp) : intval($thisLevel->level_exp),
                'high_level_user_online' => Kv::get(Kv::SCROLL_LEVEL_ONLINE),
                'hide_rank_level'        => Kv::get(Kv::HIDE_RANK_LEVEL),
                'has_old_exp'            => $has_old_exp,
                'rule'                   => [
                    [
                        'content' => sprintf('每消耗1%s即可获得%d经验', Kv::get(Kv::KEY_COIN_NAME), Kv::get(Kv::COIN_TO_EXP)),
                    ],
                    [
                        'content' => '每日签到任务可获得经验值',
                    ]
                ],
                'icon_rule'              => [
                    [
                        'icon'   => 'https://lebolive-1255651273.image.myqcloud.com/static/images/level/video_chat.png',
                        'title'  => '与小姐姐视频通话',
                        'detail' => '提升等级最快方式'
                    ],
                    [
                        'icon'   => 'https://lebolive-1255651273.image.myqcloud.com/static/images/level/send_gift.png',
                        'title'  => '赠送礼物',
                        'detail' => '可获得更多经验值'
                    ],
                    [
                        'icon'   => 'https://lebolive-1255651273.image.myqcloud.com/static/images/level/vip.png',
                        'title'  => '成为会员',
                        'detail' => '会员期间享受经验值200%加速'
                    ],
                    [
                        'icon'   => 'https://lebolive-1255651273.image.myqcloud.com/static/images/level/sign.png',
                        'title'  => '日常任务',
                        'detail' => '每天完成任务可获得经验值，还有金币奖励哦'
                    ]

                ],
                'level_reward'           => $levelRewardRow['items'],
                // 等级说明地址
                'rule_href'              => 'http://tiantongkeji.baiduux.com/h5/fe05698b-8146-85a9-c3ce-42a72cda00ce.html'
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/getLevelReward
     * @api {post} /user/profile/getLevelReward 获取等级奖励
     * @apiName profile-getLevelReward
     * @apiGroup Profile
     * @apiDescription 获取等级奖励
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} level_value 等级值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} level_value 等级值
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
    public function getLevelRewardAction( $nUserId = 0 )
    {
        $levelValue = $this->getParams('level_value');
        try {
            $oUser = User::findFirst($nUserId);
            if ( $oUser->user_level < $levelValue ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::LEVEL_FORBIDDEN),
                    ResponseError::LEVEL_FORBIDDEN
                );
            }
            $oLevelConfig = LevelConfig::findFirst([
                'level_value = :level_value: AND level_type = :level_type:',
                'bind' => [
                    'level_value' => $levelValue,
                    'level_type'  => LevelConfig::LEVEL_TYPE_USER
                ]
            ]);
            if ( !$oLevelConfig || $oLevelConfig->reward_coin <= 0 ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::LEVEL_REWARD_FORBIDDEN),
                    ResponseError::LEVEL_REWARD_FORBIDDEN
                );
            }
            // 判断是否领取过
            $oLevelRewardLog = LevelRewardLog::findFirst([
                'level_reward_log_user_id = :level_reward_log_user_id: AND level_reward_log_level_value = :level_reward_log_level_value:',
                'bind' => [
                    'level_reward_log_user_id'     => $nUserId,
                    'level_reward_log_level_value' => $levelValue,
                ]
            ]);
            if ( $oLevelRewardLog ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::LEVEL_REWARD_HAS_GET),
                    ResponseError::LEVEL_REWARD_HAS_GET
                );
            }

            $this->db->begin();
            $oLevelRewardLog                               = new LevelRewardLog();
            $oLevelRewardLog->level_reward_log_user_id     = $nUserId;
            $oLevelRewardLog->level_reward_log_level_value = $levelValue;
            $oLevelRewardLog->level_reward_log_coin        = $oLevelConfig->reward_coin;
            if ( $oLevelRewardLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oLevelRewardLog->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            // 记录用户流水
            $oUserFinanceLog                         = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
            $oUserFinanceLog->user_id                = $oUser->user_id;
            $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin + $oLevelConfig->reward_coin;
            $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin;
            $oUserFinanceLog->consume_category_id    = UserConsumeCategory::USER_LEVEL_REWARD;
            $oUserFinanceLog->consume                = +$oLevelConfig->reward_coin;
            $oUserFinanceLog->remark                 = "用户等级{$levelValue}奖励";
            $oUserFinanceLog->flow_id                = $oLevelRewardLog->level_reward_log_id;
            $oUserFinanceLog->flow_number            = '';
            $oUserFinanceLog->type                   = 0;
            $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin + $oLevelConfig->reward_coin;
            $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin;
            $flg                                     = $oUserFinanceLog->save();
            if ( $flg == FALSE ) {
                $this->db->rollback();
                return FALSE;
            }
            $sql = 'update `user` set user_free_coin = user_free_coin + ' . $oLevelConfig->reward_coin . ',user_total_free_coin = user_total_free_coin + ' . $oLevelConfig->reward_coin . ' where user_id = ' . $nUserId;
            $this->db->execute($sql);
            if ( $this->db->affectedRows() <= 0 ) {
                $this->db->rollback();
                return FALSE;
            }
            $this->db->commit();

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/rankHide
     * @api {post} /user/profile/rankHide 隐藏排行榜信息
     * @apiName profile-rankHide
     * @apiGroup Profile
     * @apiDescription 隐藏排行榜信息
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String='Y(隐藏)','N(显示)'} is_hide 是否隐藏
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} is_hide 是否隐藏
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
    public function rankHideAction( $nUserId = 0 )
    {
        $sIsHide = $this->getParams('is_hide');
        try {
            $oUser = User::findFirst($nUserId);
            if ( $oUser->user_level < intval(Kv::get(Kv::HIDE_RANK_LEVEL)) ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::LEVEL_FORBIDDEN),
                    ResponseError::LEVEL_FORBIDDEN
                );
            }
            $oUser->user_remind = $sIsHide == 'Y' ? '0' : '1';
            if ( $oUser->save() === FALSE ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUser->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success();
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/getOldExp
     * @api {post} /user/profile/getOldExp 获取原有经验
     * @apiName profile-getOldExp
     * @apiGroup Profile
     * @apiDescription 获取原有经验
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
     * @apiSuccess {String} d.user_exp  用户经验
     * @apiSuccess {String} d.user_level  用户等级
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
    public function getOldExpAction( $nUserId = 0 )
    {
        try {
            $oUser = User::findFirst($nUserId);
            if ( $oUser->user_create_time > strtotime('2019-01-07 12:00:00') ) {
                $row = [
                    'user_exp'   => $oUser->user_exp,
                    'user_level' => $oUser->user_level,
                ];
                $this->success($row);
            }
            $coinToExp = Kv::get(Kv::COIN_TO_EXP);
            $shouldExp = intval($oUser->user_consume_total * $coinToExp / 3);
            if ( $shouldExp > $oUser->user_exp ) {
                $oUser->user_exp   = $shouldExp;
                $oUser->user_level = User::getUserLevel($shouldExp);
                if ( $oUser->save() === FALSE ) {
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUser->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }
            }
            $row = [
                'user_exp'   => $oUser->user_exp,
                'user_level' => $oUser->user_level,
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/viewlist
     * @api {get} /user/profile/viewlist 002-190909谁看过我
     * @apiName profile-viewlist
     * @apiGroup Profile
     * @apiDescription 谁看过我
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){Number} page 页数
     * @apiParam (正常请求){Number} pagesize 每页数
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){Number} page 页数
     * @apiParam (debug){Number} pagesize 每页数
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {number} d.items.user_id    用户iD
     * @apiSuccess {String} d.items.user_nickname  用户昵称
     * @apiSuccess {String} d.items.user_avatar  用户头像
     * @apiSuccess {number} d.items.user_level  用户等级
     * @apiSuccess {String} d.items.user_birth  用户生日
     * @apiSuccess {number} d.items.user_sex  用户性别
     * @apiSuccess {number} d.items.view_time   访问时间戳
     * @apiSuccess {String} d.items.user_is_member  是否为会员
     * @apiSuccess {String} d.items.user_is_anchor  是否为主播
     * @apiSuccess {String} d.items.anchor_title_number  主播称号值
     * @apiSuccess {String} d.items.anchor_title_name  主播称号名称
     * @apiSuccess {String} d.items.anchor_custom_title  主播自定义标签
     * @apiSuccess {String} d.items.user_is_anchor  是否为主播
     * @apiSuccess {String} d.items.anchor_level  主播等级
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccess {number} d.today_view_user_count   今日访客数
     * @apiSuccess {number} d.today_view_count  今日浏览数
     * @apiSuccess {number} d.all_view_count  总浏览数
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *                 "items": [{
     *                     "user_id": "315",
     *                     "user_nickname": "LYXXMY3",
     *                     "user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/12\/13\/1544681627908.png",
     *                     "user_level": "16",
     *                     "user_birth": "",
     *                     "user_sex": "2",
     *                     "view_time": "1555555350",
     *                     "user_is_member": "Y",
     *                     "user_is_anchor": "N",
     *                     "anchor_level": "1",
     *                     "anchor_title_number": "0",
     *                     "anchor_title_name": "",
     *                     "anchor_custom_title": ""
     *                 }],
     *             "page": 1,
     *             "pagesize": 20,
     *             "pagetotal": 1,
     *             "total": 1,
     *             "prev": 1,
     *             "next": 1,
     *             "today_view_user_count": "1",
     *             "today_view_count": "1",
     *             "all_view_count": "1"
     *         },
     *         "t": "1555555583"
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function viewlistAction( $nUserId = 0 )
    {

        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $nPagesize = min($nPagesize, 100);
        try {
            $oUser   = User::findFirst($nUserId);
            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'l' => UserViewLog::class ])
                ->join(User::class, 'u.user_id= l.user_view_user_id', 'u')
                ->leftJoin(Anchor::class, 'u.user_id= a.user_id', 'a')
                ->columns('u.user_id,u.user_nickname,u.user_avatar,u.user_level,u.user_birth,
                u.user_sex,u.user_member_expire_time,l.user_view_update_time as view_time,
                a.anchor_level,a.anchor_title_id,u.user_is_anchor,0 as anchor_title_number,"" as anchor_title_name,a.anchor_custom_title')
                ->where('user_viewed_user_id=:user_id:', [
                    'user_id' => $nUserId,
                ])->orderBy('l.user_view_update_time desc');
            $row     = $this->page($builder, $nPage, $nPagesize);

            foreach ( $row['items'] as &$v ) {
                $v['user_is_member'] = $v['user_member_expire_time'] == 0 ? 'N' : (time() > $v['user_member_expire_time'] ? 'O' : 'Y');
                $v['anchor_level']   = $v['anchor_level'] ?? '';
                if ( $v['user_is_anchor'] == 'Y' ) {
                    $anchorTitleInfo             = AnchorTitleConfig::getInfo($v['anchor_title_id']);
                    $item['anchor_title_number'] = $anchorTitleInfo['number'];
                    $item['anchor_title_name']   = $anchorTitleInfo['name'];
                }
                unset($v['anchor_title_id']);
                unset($v['user_member_expire_time']);
            }


            $todayStat = (new UserViewLog())->getTodayStat($nUserId);

            $row['today_view_user_count'] = $todayStat['user_count'];
            $row['today_view_count']      = $todayStat['view_count'];
            $row['all_view_count']        = $oUser->user_viewed_count;

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/updateSet
     * @api {post} /user/profile/updateSet 修改设置
     * @apiName profile-updateSet
     * @apiGroup Profile
     * @apiDescription 修改设置
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String='Y','N'} user_get_stranger_msg_flg 陌生人打招呼开关  Y为开
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String='Y','N'} user_get_stranger_msg_flg 陌生人打招呼开关 Y为开
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
    public function updateSetAction( $nUserId = 0 )
    {
        $param = $this->getParams();
        try {
            $oUserSet = UserSet::findFirst($nUserId);
            if ( !$oUserSet ) {
                $oUserSet = new UserSet();
            }
            $aAllow = [
                'user_get_stranger_msg_flg',
                'user_get_call_flg'
            ];
            foreach ( $param as $key => $value ) {
                if ( in_array($key, $aAllow) && $value ) {
                    $oUserSet->$key = $value;
                }
            }
            if ( $oUserSet->save() === FALSE ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserSet->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/intimateInfo
     * @api {get} /user/profile/intimateInfo 亲密度信息
     * @apiName profile-intimateInfo
     * @apiGroup Profile
     * @apiDescription 亲密度信息
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} user_id 对方用户id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} user_id 对方用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.wechat_price 微信价格
     * @apiSuccess {string} d.user_wechat 微信(不能查看时为隐藏带***  为空时则不提示 主播未填)
     * @apiSuccess {string} d.rule_href 规则H5地址
     * @apiSuccess {object} d.user_intimate  亲密信息
     * @apiSuccess {number} d.user_intimate.level  亲密等级
     * @apiSuccess {String} d.user_intimate.level_name  亲密等级名称
     * @apiSuccess {number} d.user_intimate.total_value  亲密值
     * @apiSuccess {String='Y','N'} d.show_wechat_flg  是否可以查看微信
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *    {
     *        "c": 0,
     *        "m": "请求成功",
     *        "d": {
     *                "wechat_price": "0.00",
     *                "user_wechat": "",
     *                "rule_href": "https:\/\/www.baidu.com\/",
     *                "user_intimate": {
     *                        "level": 0,
     *                        "level_name": "萍水相逢",
     *                        "total_value": 3200,
     *                        "show_wechat_flg": 'Y'
     *                }
     *        },
     *        "t": "1556189890"
     *    }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function intimateInfoAction( $nUserId = 0 )
    {

        $nToUserId = $this->getParams('user_id');
        try {
            $oToUser = User::findFirst($nToUserId);
            $oUser   = User::findFirst($nUserId);

            $intimateData = UserIntimate::getIntimateLevel($nToUserId, $nUserId, $oToUser->user_is_anchor, $oUser->user_is_anchor);

            $userWechat = $oToUser->user_wechat;

            $showWechatFlg = 'Y';
            if ( Kv::get(Kv::INTIMATE_WECHAT_SHOW) == 'N' ) {
                // 后台关闭功能
                $userWechat    = '';
                $showWechatFlg = 'N';
            } else {
                if ( !$userWechat || $intimateData['total_value'] < $oToUser->user_wechat_price ) {
                    $showWechatFlg = 'N';
                    if ( $userWechat ) {
                        $wechatLength = mb_strlen($userWechat);
                        if ( $wechatLength <= 2 ) {
                            $userWechat = mb_substr($userWechat, 0, 1) . '*****';
                        } else {
                            $userWechat = str_replace(mb_substr($userWechat, 1, $wechatLength - 2), '*****', $userWechat);
                        }
                    }
                }

            }

            $row = [
                // 微信价格
                'wechat_price'    => $oToUser->user_wechat_price,
                // 用户微信
                'user_wechat'     => $userWechat,
                // 规则H5
                'rule_href'       => 'http://tiantongkeji.baiduux.com/h5/b39ce517-5472-7212-fce0-f4a94ac6c312.html',
                // 亲密度信息
                'user_intimate'   => $intimateData,
                // 是否可以查看微信
                'show_wechat_flg' => $showWechatFlg
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/anchorLevel
     * @api {get} /user/profile/anchorLevel 主播等级
     * @apiName profile-anchorLevel
     * @apiGroup Profile
     * @apiDescription 主播等级
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
     * @apiSuccess {number} d.anchor_level   主播等级
     * @apiSuccess {number} d.anchor_exp   主播经验
     * @apiSuccess {number} d.this_level_exp   本级的经验
     * @apiSuccess {number} d.next_level_exp   下一次的经验
     * @apiSuccess {String} d.anchor_level_rule_href    等级规则地址
     * @apiSuccess {object[]} d.daily_task_list
     * @apiSuccess {String} d.daily_task_list.task_name  任务名字
     * @apiSuccess {String} d.daily_task_list.task_flg    任务标识
     * @apiSuccess {number} d.daily_task_list.task_finish_times  需要完成度
     * @apiSuccess {number} d.daily_task_list.task_reward_dot   奖励佣金
     * @apiSuccess {number} d.daily_task_list.task_reward_exp   奖励经验
     * @apiSuccess {String} d.daily_task_list.task_type   类型
     * @apiSuccess {String} d.daily_task_list.reward_flg
     * @apiSuccess {number} d.daily_task_list.task_done_num   已完成进度
     * @apiSuccess {number} d.daily_task_finished_count   任务总完成数
     * @apiSuccess {number} d.daily_task_total_count   任务总数
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *    {
     *        "c": 0,
     *        "m": "请求成功",
     *        "d": {
     *                "anchor_level": "1",
     *                "anchor_exp": "100",
     *                "this_level_exp": 0,
     *                "next_level_exp": 200,
     *                "anchor_level_rule_href": "https://www.baidu.com",
     *                "daily_task_list": [
     *                    {
     *                        "task_name": "发布动态",
     *                        "task_flg": "task_anchor_daily_add_posts",
     *                        "task_finish_times": "5",
     *                        "task_reward_dot": "0.00",
     *                        "task_reward_exp": "100",
     *                        "task_type": "anchor_daily",
     *                        "reward_flg": "N",
     *                        "task_done_num": "0"
     *                    },
     *                    {
     *                        "task_name": "累计在线4小时",
     *                        "task_flg": "task_anchor_daily_online_hour",
     *                        "task_finish_times": "4",
     *                        "task_reward_dot": "0.00",
     *                        "task_reward_exp": "100",
     *                        "task_type": "anchor_daily",
     *                        "reward_flg": "N",
     *                        "task_done_num": "0"
     *                    },
     *                    {
     *                        "task_name": "单次视频通话超过10分钟",
     *                        "task_flg": "task_anchor_daily_video_chat_10_min",
     *                        "task_finish_times": "1",
     *                        "task_reward_dot": "0.00",
     *                        "task_reward_exp": "100",
     *                        "task_type": "anchor_daily",
     *                        "reward_flg": "N",
     *                        "task_done_num": "0"
     *                    },
     *                    {
     *                        "task_name": "累计视频通话60分钟",
     *                        "task_flg": "task_anchor_daily_video_chat_min",
     *                        "task_finish_times": "60",
     *                        "task_reward_dot": "0.00",
     *                        "task_reward_exp": "100",
     *                        "task_type": "anchor_daily",
     *                        "reward_flg": "N",
     *                        "task_done_num": "0"
     *                    },
     *                    {
     *                        "task_name": "社区点赞、评论",
     *                        "task_flg": "task_anchor_daily_posts_like_comment",
     *                        "task_finish_times": "1",
     *                        "task_reward_dot": "0.00",
     *                        "task_reward_exp": "100",
     *                        "task_type": "anchor_daily",
     *                        "reward_flg": "N",
     *                        "task_done_num": "0"
     *                    },
     *                    {
     *                        "task_name": "礼物佣金收益超过500",
     *                        "task_flg": "task_anchor_daily_gift_dot_over",
     *                        "task_finish_times": "500",
     *                        "task_reward_dot": "50.00",
     *                        "task_reward_exp": "0",
     *                        "task_type": "anchor_daily",
     *                        "reward_flg": "N",
     *                        "task_done_num": "0"
     *                    }
     *                ],
     *                "daily_task_finished_count": 0,
     *                "daily_task_total_count": 6
     *        },
     *        "t": "1556503468"
     *    }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function anchorLevelAction( $nUserId = 0 )
    {


        try {

            $oUser = User::findFirst($nUserId);

            if ( $oUser->user_is_anchor == 'N' ) {
                // 用户信息
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::FAIL)),
                    ResponseError::FAIL
                );
            }
            $oAnchor     = Anchor::findFirst([
                'user_id = :user_id:',
                'bind' => [
                    'user_id' => $nUserId,
                ]
            ]);
            $nextLevel   = LevelConfig::findFirst([
                'level_value = :level_value: AND level_type = :level_type:',
                'bind' => [
                    'level_type'  => LevelConfig::LEVEL_TYPE_ANCHOR,
                    'level_value' => $oAnchor->anchor_level + 1
                ]
            ]);
            $thisLevel   = LevelConfig::findFirst([
                'level_value = :level_value: AND level_type = :level_type:',
                'bind' => [
                    'level_type'  => LevelConfig::LEVEL_TYPE_ANCHOR,
                    'level_value' => $oAnchor->anchor_level
                ]
            ]);
            $levelChange = 0;
            // 判断 等级是否正确  如果不正确 将修改
            if ( $thisLevel->level_exp > $oAnchor->anchor_exp ) {
                // 经验小于当前等级 则重新计算等级
                $levelChange = LevelConfig::getLevelInfo($oAnchor->anchor_exp, LevelConfig::LEVEL_TYPE_ANCHOR);
            } else if ( $nextLevel && $nextLevel->level_exp < $oAnchor->anchor_exp ) {
                // 下一级存在 当前经验大于下一级
                $levelChange = LevelConfig::getLevelInfo($oAnchor->anchor_exp, LevelConfig::LEVEL_TYPE_ANCHOR);
            }
            if ( $levelChange ) {
                $oAnchor->anchor_level = $levelChange['level'];
                $oAnchor->save();
                $nextLevel = LevelConfig::findFirst([
                    'level_value = :level_value: AND level_type = :level_type:',
                    'bind' => [
                        'level_type'  => 'user',
                        'level_value' => $oAnchor->anchor_level + 1
                    ]
                ]);
                $thisLevel = LevelConfig::findFirst([
                    'level_value = :level_value: AND level_type = :level_type:',
                    'bind' => [
                        'level_type'  => 'user',
                        'level_value' => $oAnchor->anchor_level
                    ]
                ]);
            }

            // 任务信息开始

            // 每日任务信息
            $dailyTaskBuilder = $this->modelsManager
                ->createBuilder()
                ->from([ 'tc' => TaskConfig::class ])
                ->leftJoin(AnchorDailyTaskLog::class, "l.anchor_daily_task_id = tc.task_id AND l.anchor_daily_task_date = '" . date('Y-m-d') . "' AND anchor_daily_task_log_user_id = " . $nUserId, 'l')
                ->columns('tc.task_name,tc.task_flg,tc.task_finish_times,tc.task_reward_dot,tc.task_reward_exp,l.anchor_daily_task_log_id,tc.task_type')
                ->where('tc.task_on = "Y" AND tc.task_type = :task_type: ', [
                    'task_type' => TaskConfig::TASK_TYPE_ANCHOR_DAILY
                ])
                ->orderBy('tc.task_sort');

            $dailyTaskRow              = $this->page($dailyTaskBuilder, 1, 100);
            $daily_task_finished_count = 0;

            $oTaskConfig = new TaskConfig();
            foreach ( $dailyTaskRow['items'] as &$dailyTaskItem ) {
                // 已经领过
                $dailyTaskItem['reward_flg']    = 'Y';
                $dailyTaskItem['task_done_num'] = $dailyTaskItem['task_finish_times'];
                $daily_task_finished_count      += 1;
                if ( !$dailyTaskItem['anchor_daily_task_log_id'] ) {
                    // 没有完成需要 判断完成进度
                    $oTaskConfig->task_finish_times = $dailyTaskItem['task_finish_times'];
                    $oTaskConfig->task_flg          = $dailyTaskItem['task_flg'];
                    $oTaskConfig->task_type         = $dailyTaskItem['task_type'];
                    $taskFinished                   = $oTaskConfig->getTaskFinishDone($nUserId);
                    if ( $taskFinished === TRUE ) {
                        // 已完成 待领取奖励
                        $dailyTaskItem['reward_flg'] = 'C';
                    } else {
                        // 未完成  进度获取值
                        $dailyTaskItem['reward_flg']    = 'N';
                        $dailyTaskItem['task_done_num'] = (string)intval($taskFinished);
                        $daily_task_finished_count      -= 1;
                    }
                }
                unset($dailyTaskItem['anchor_daily_task_log_id']);
            }

            // 任务信息结束

            $row = [
                'anchor_level'              => $oAnchor->anchor_level,
                'anchor_exp'                => $oAnchor->anchor_exp,
                'this_level_exp'            => intval($thisLevel->level_exp),
                'next_level_exp'            => $nextLevel ? intval($nextLevel->level_exp) : intval($thisLevel->level_exp),
                'anchor_level_rule_href'    => 'http://tiantongkeji.baiduux.com/h5/80b32fe0-d8e9-79fc-ba31-b4bd23d73ace.html',
                'daily_task_list'           => $dailyTaskRow['items'],
                'daily_task_finished_count' => intval($daily_task_finished_count),
                'daily_task_total_count'    => count($dailyTaskRow['items']),
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

}