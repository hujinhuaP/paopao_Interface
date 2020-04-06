<?php

namespace app\http\controllers\live;

use app\helper\AgoraDynamicKey;
use app\helper\ResponseError;
use app\models\Anchor;
use app\models\Banword;
use app\models\EnterRoomLog;
use app\models\FastChat;
use app\models\Kv;
use app\models\Room;
use app\models\RoomAdmin;
use app\models\RoomBlack;
use app\models\RoomChat;
use app\models\RoomChatForbidden;
use app\models\RoomSeat;
use app\models\User;
use app\models\UserAccount;
use app\models\UserChat;
use app\models\UserChatDialog;
use app\models\UserFollow;
use app\models\UserGiftLog;
use app\models\UserSystemMessage;
use app\services\UserOnlineService;
use Cassandra\Varint;
use Exception;
use app\services;
use app\http\controllers\ControllerBase;

/**
 * RoomController  房间管理
 */
class RoomController extends ControllerBase
{
    use services\UserService;


    public function initialize()
    {
        parent::initialize();
        $nUserId = $this->dispatcher->getParams()[0];

        if ( in_array($this->dispatcher->getActionName(), [
            'seatWaitList',
            'users',
            'user',
            'setAdmin',
            'setChatForbidden',
            'setBlack',
            'setKick',
            'setAdmin',
            'enterSeat',
            'enterHostSeat',
            'info',
            'inviteSeat',
            'setLeaveSeat',
            'leaveSeat',
            'detail',
            'changeSeat',
            'cancelWaitSeat',
            'setVoiceFlg',
            'heartbeat',
            'sendSeatEmoticon',
            'getLastRoomChat',
            'getAgoraToken',
            'sendVoiceRoomChat',
            'giftList',
            'countDown',
            'changeStep',
            'enterFriendSelect',
            'selectLikeUser',
            'startTeamBattlesStat',
            'restartFriendModel',
        ]) ) {
            $this->oUser = User::findFirst($nUserId);
            $roomId      = $this->getParams('room_id');
            if ( $this->oUser->user_enter_room_id != $roomId ) {
                $this->error(ResponseError::USER_NOT_IN_ROOM, ResponseError::getError(ResponseError::USER_NOT_IN_ROOM));
            }
        }
    }


    /**
     * @apiVersion 1.4.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/room/index
     * @api {get} /live/room/index 001-191213房间信息
     * @apiName Room-index
     * @apiGroup Room
     * @apiDescription 001-191213房间信息
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {number} d.voice_chat_room_id  房间ID
     * @apiSuccess {String} d.room_cover   封面
     * @apiSuccess {String} d.voice_chat_room_name  名称
     * @apiSuccess {String} d.voice_chat_room_title  标题
     * @apiSuccess {number} d.room_online_count  在线人数
     * @apiSuccess {String} d.show_avatar  3个头像 半角逗号分割
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *        "voice_chat_room_id": "1",
     *        "room_cover": "https:\/\/cskj-1257854899.image.myqcloud.com\/image\/20191212\/1576148359337448.png",
     *        "voice_chat_room_name": "JK live新人接待大厅",
     *        "voice_chat_room_title": "一起上麦热聊，结识更多有缘人",
     *        "room_online_count": "16",
     *        "show_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/static\/images\/head\/sex1.png,https:\/\/lebolive-1255651273.image.myqcloud.com\/static\/images\/head\/sex2.png,http:\/\/thirdqq.qlogo.cn\/g?b=oidb&k=oQTmSywo0icicfNMjpA6KZHg&s=100&t=1575790784"
     *    },
     *    "t": "1576202303"
     *   }
     */
    public function indexAction( $nUserId = 0 )
    {
        try {
            $oRoom = Room::findFirst(Room::B_CHAT_ID);
//            $showAvatar = [
//                'https://lebolive-1255651273.image.myqcloud.com/static/images/head/sex1.png',
//                'https://lebolive-1255651273.image.myqcloud.com/static/images/head/sex2.png',
//                'http://thirdqq.qlogo.cn/g?b=oidb&k=oQTmSywo0icicfNMjpA6KZHg&s=100&t=1575790784',
//            ];
            $showAvatar = [];
            $rank       = RoomSeat::getInfoByRank($oRoom->room_id);
            foreach ( $rank as $item ) {
                if ( empty($item['owner_user_id']) ) {
                    break;
                }
                $showAvatar[] = $item['owner_user_avatar'];
                if ( count($showAvatar) >= 3 ) {
                    break;
                }
            }
            $row = [
                'voice_chat_room_id'    => $oRoom->room_id,
                'room_cover'            => $oRoom->room_cover,
                'voice_chat_room_name'  => $oRoom->room_name,
                'voice_chat_room_title' => '一起上麦热聊，结识更多有缘人',
                'room_online_count'     => $oRoom->room_online_count,
                'show_avatar'           => implode(',', $showAvatar),
                'room_open_flg'         => $oRoom->room_open_flg
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.4.0
     * @api {get} /live/room/setting 002-191120房间设置
     * @apiName 002-room-setting
     * @apiGroup Room
     * @apiDescription 房间设置
     * @apiParam (正常请求){String} room_id 房间id 自己房间可以不用传
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {String} d.room_id  房间id  为空则为没有获得自己的房间
     * @apiSuccess {String} d.room_number   房间编号
     * @apiSuccess {String} d.room_name  房间名字
     * @apiSuccess {String} d.room_cover  房间封面
     * @apiSuccess {String} d.room_background  房间背景
     * @apiSuccess {String='Y','N'} d.room_heart_stat_open_flg  房间甜心值是否开启
     * @apiSuccess {String='super_admin(超级管理员)','owner(房主)','admin(管理员)'} d.owner_role_flg  自己在房间角色
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *    {
     *        "c": 0,
     *        "m": "请求成功",
     *        "d": {
     *            "owner_role_flg": "owner",
     *            "room_id": "",
     *            "room_number": "",
     *            "room_name": "绥化的阿施的房间",
     *            "room_cover": "",
     *            "room_background": "1",
     *            "room_heart_stat_open_flg": "Y"
     *        },
     *        "t": "1557908482"
     *    }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function settingAction( $nUserId = 0 )
    {
        $nRoomId = $this->getParams('room_id', 'string', '');

        try {
            $oRoom = Room::findFirst($nRoomId);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $oUser = User::findFirst($nUserId);
            if ( $oRoom->room_user_id == $nUserId ) {
                $ownerRoleFlg = 'owner';
            } elseif ( $oUser->user_is_superadmin == ' Y' ) {
                $ownerRoleFlg = 'super_admin';
            } else {
                $oRoomAdmin = RoomAdmin::checkAdmin($nRoomId, $nUserId);
                if ( !$oRoomAdmin ) {
                    throw new Exception(ResponseError::getError(ResponseError::ROOM_NO_ADMIN), ResponseError::ROOM_NO_ADMIN);
                }
                $ownerRoleFlg = 'admin';
            }


            $row = [
                'owner_role_flg'           => (string)$ownerRoleFlg,
                'room_id'                  => (string)$oRoom->room_id,
                'room_number'              => (string)$oRoom->room_number,
                'room_welcome_word'        => (string)$oRoom->room_welcome_word,
                'room_name'                => (string)$oRoom->room_name,
                'room_cover'               => (string)$oRoom->room_cover,
                'room_background'          => (int)$oRoom->room_background,
                'room_heart_stat_open_flg' => (string)$oRoom->room_heart_stat_open_flg,
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.4.0
     * @api {post} /live/room/updateSetting 003-190828修改房间设置
     * @apiName 003-room-updateSetting
     * @apiGroup Room
     * @apiDescription 修改房间设置
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (正常请求){String} room_name 房间名称
     * @apiParam (正常请求){String} room_cover 房间封面
     * @apiParam (正常请求){String} room_background 房间背景
     * @apiParam (正常请求){String} room_welcome_word 房间欢迎语
     * @apiParam (正常请求){String} room_heart_stat_open_flg 房间欢迎语
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": {
     *              "room_id" : "100"
     *         {,
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     {
     *         "c": 10010,
     *         "d": "",
     *         "m": "创建房间请先绑定手机号哦~",
     *         "t": 1534911421
     *     }
     */
    public function updateSettingAction( $nUserId = 0 )
    {
        $nRoomId               = $this->getParams('room_id');
        $sRoomName             = $this->getParams('room_name');
        $sRoomBackground       = $this->getParams('room_background');
        $sRoomWelcomeWord      = $this->getParams('room_welcome_word');
        $sRoomHeartStatOpenFlg = $this->getParams('room_heart_stat_open_flg');
        try {
            // 房间名称
            $nameLength = mb_strlen($sRoomName);
            if ( $nameLength > 20 || $nameLength < 4 ) {
                throw new Exception('房间名称长度请保持在4-20位', ResponseError::PARAM_ERROR);
            }

            if ( empty($sRoomBackground) ) {
                $sRoomBackground = 0;
            }

            if ( $sRoomHeartStatOpenFlg && !in_array($sRoomHeartStatOpenFlg, [
                    'Y',
                    'N'
                ]) ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '请设置甜心值设置'), ResponseError::PARAM_ERROR);
            }
            if ( !$nRoomId ) {
                $nRoomId = Room::B_CHAT_ID;
            }
            $oRoom = Room::findFirst($nRoomId);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $oldRoom = clone $oRoom;

            $oRoom->room_name                = $sRoomName;
            $oRoom->room_background          = $sRoomBackground;
            $oRoom->room_welcome_word        = $sRoomWelcomeWord;
            $oRoom->room_heart_stat_open_flg = $sRoomHeartStatOpenFlg;
            if ( $oldRoom->room_heart_stat_open_flg == 'N' && $oRoom->room_heart_stat_open_flg == 'Y' ) {
                $oRoom->room_heart_stat_start = time();
            }
            if ( $oRoom->save() === FALSE ) {
                throw new \Exception(
                    sprintf('%s[%s]-1', ResponseError::getError(ResponseError::OPERATE_FAILED), $oRoom->getMessage()),
                    ResponseError::OPERATE_FAILED
                );
            }

            $pushData = [
                'room_id'                  => $oRoom->room_id,
                'room_name'                => $oRoom->room_name,
                'room_background'          => (int)$oRoom->room_background,
                'room_welcome_word'        => $oRoom->room_welcome_word,
                'room_heart_stat_open_flg' => $oRoom->room_heart_stat_open_flg,
            ];
            // 推送房间修改
            $row = $pushData;
            $this->timServer->setRid($nRoomId);
            $flg        = $this->timServer->sendUpdateRoomSignal($pushData);
            $row['flg'] = $flg;
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.0.0
     * @apiSampleRequest http://192.168.0.188:89/v2/live/room/setNotice
     * @api {post} /live/room/setNotice 009-190520设置公告
     * @apiName room-setNotice
     * @apiGroup Room
     * @apiDescription 设置公告
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (正常请求){String} notice_title 公告标题
     * @apiParam (正常请求){String} notice_word 公告内容
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id
     * @apiParam (debug){String} notice_title 公告标题
     * @apiParam (debug){String} notice_word 公告内容
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
    public function setNoticeAction( $nUserId = 0 )
    {
        $noticeTitle = $this->getParams('notice_title');
        $noticeWord  = $this->getParams('notice_word');
        $nRoomId     = $this->getParams('room_id');
        try {
            if ( empty($nRoomId) ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $oRoom = Room::findFirst([
                'room_id = :room_id:',
                'bind' => [
                    'room_id' => $nRoomId
                ]
            ]);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            // 如果不是房主且不是超管 且不是管理员
            if ( $nUserId != $oRoom->room_user_id && $this->oUser->user_is_superadmin == 'Y' ) {
                $oRoomAdmin = RoomAdmin::checkAdmin($nRoomId, $nUserId);
                if ( !$oRoomAdmin ) {
                    throw new Exception(ResponseError::getError(ResponseError::ROOM_NO_ADMIN), ResponseError::ROOM_NO_ADMIN);
                }
            }
            $hasChange = FALSE;
            if ( $oRoom->room_notice_word != $noticeWord || $oRoom->room_notice_title != $noticeTitle ) {
                $hasChange = TRUE;
            }
            $oRoom->room_notice_word  = $noticeWord;
            $oRoom->room_notice_title = $noticeTitle;
            if ( $oRoom->save() === FALSE ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), json_encode($oRoom->getMessages())), ResponseError::OPERATE_FAILED);
            }
            if ( $hasChange ) {
                $this->timServer->setRid($nRoomId);
                $flg = $this->timServer->sendNoticeSignal([
                    'room_id'           => $nRoomId,
                    'room_notice_word'  => $noticeWord,
                    'room_notice_title' => $noticeTitle,
                ]);
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @apiVersion 1.4.0
     * @api {post} /live/room/enter 004-190916进入房间
     * @apiName room-enter
     * @apiGroup Room
     * @apiDescription 004-190701进入房间
     * @apiParam (正常请求){String} room_id 房间id 如果是房主可不填
     * @apiParam (正常请求){String} room_auth 房间密码
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id 如果是房主可不填
     * @apiParam (debug){String} room_auth 房间密码
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiUse EnterRoomTemplate
     */
    public function enterAction( $nUserId = 0 )
    {
        $nRoomId = $this->getParams('room_id');
        try {
            $oRoom = Room::findFirst($nRoomId);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $nRoomId = $oRoom->room_id;

            $this->oUser = User::findFirst($nUserId);

            if ( $this->oUser->user_is_superadmin != 'Y' && $oRoom->room_user_id != $nUserId ) {
                // 判断是否被拉黑
                $oRoomBlack = RoomBlack::checkResult($nRoomId, $nUserId);
                if ( $oRoomBlack ) {
                    throw new Exception(ResponseError::getError(ResponseError::USER_IN_ROOM_BLACK), ResponseError::USER_IN_ROOM_BLACK);
                }
            }

            $oRoom->enter($this->oUser);

            // 判斷用戶是否送过礼物
            RoomSeat::checkSeatCache($nUserId, $nRoomId);

            // 更新房间信息
            $oRoom = Room::findFirst($nRoomId);

            $row = $this->_getEnterInfo($oRoom, $nUserId);

            // 如果当前用户 存在座位上 则推送座位信息
            $seatUserArr  = array_column($row['room_seat'], 'user_id');
            $pushRoomSeat = [];
            if ( in_array($nUserId, $seatUserArr) ) {
                $pushRoomSeat = $row['room_seat'];
            }

            if ( $this->oUser->user_is_isrobot == 'Y' ) {
                $this->timServer->setExtra($this->oUser);
                $this->timServer->setRid($oRoom->room_id);
                $this->timServer->sendJoinSignal(FALSE, [
                    'room_id'           => $oRoom->room_id,
                    'room_online_count' => (int)$oRoom->room_online_count,
                    'user_role_flg'     => $oRoom->getUserRoleFlg($this->oUser),
                    'room_seat'         => $pushRoomSeat
                ]);

            } else {
                // 增加当前房间用户
                $oUserOnlineService = new UserOnlineService('room_user');
                $oUserOnlineService->save($this->oUser->user_id);

                $this->timServer->setExtra($this->oUser);
                $this->timServer->setRid($oRoom->room_id);
                $joinResult = $this->timServer->sendJoinSignal(FALSE, [
                    'room_id'           => $oRoom->room_id,
                    'room_online_count' => (int)$oRoom->room_online_count,
                    'user_role_flg'     => $oRoom->getUserRoleFlg($this->oUser),
                    'room_seat'         => $pushRoomSeat
                ]);
                $row['flg'] = $joinResult;
                if ( $joinResult['ErrorCode'] != 0 ) {
                    $this->timServer->setRid($oRoom->room_id);
                    $this->timServer->createRoom(sprintf('%s的聊天室', $oRoom->room_id), 'ChatRoom', [ [ 'Member_Account' => $this->oUser->user_id ] ]);
                } else {
                    // 将用户加入群
                    $this->timServer->setAccountId($this->oUser->user_id);
                    $this->timServer->joinRoom();
                }
            }

            // 添加心跳
            $oRoomHeartbeatService = new services\RoomHeartbeatService();
            $oRoomHeartbeatService->save(sprintf("%s_%s", $nRoomId, $nUserId));

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.0.0
     * @apiSampleRequest http://192.168.0.188:89/v2/live/room/users
     * @api {get} /live/room/users 004-190517房间用户列表
     * @apiName room-users
     * @apiGroup Room
     * @apiDescription 004-190517房间用户列表
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (正常请求){Number} page 页码
     * @apiParam (正常请求){Number} pagesize 页数（最大100）
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id
     * @apiParam (debug){Number} page 页码
     * @apiParam (debug){Number} pagesize 页数（最大100）
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object} d.room_owner_info  房主信息
     * @apiSuccess {number} d.room_owner_info.user_id    用户id
     * @apiSuccess {number} d.room_owner_info.user_number    用户编号
     * @apiSuccess {String} d.room_owner_info.user_nickname   用户昵称
     * @apiSuccess {String} d.room_owner_info.user_avatar   用户头像
     * @apiSuccess {number} d.room_owner_info.user_avatar_frame   用户头像框
     * @apiSuccess {String} d.room_owner_info.user_birth  用户生日
     * @apiSuccess {String} d.room_owner_info.user_gender  用户性别
     * @apiSuccess {number} d.room_owner_info.user_rich_level  用户财富等级
     * @apiSuccess {number} d.room_owner_info.user_charm_level  用户魅力等级
     * @apiSuccess {String='Y','N'} d.room_owner_info.user_on_room_flg  房主是否在房间
     * @apiSuccess {String='Y','N'} d.room_owner_info.room_seat_flg  房主是否在麦上
     * @apiSuccess {object[]} d.list   其他用户列表
     * @apiSuccess {number} d.list.user_id   用户id
     * @apiSuccess {number} d.list.user_number   用户编号
     * @apiSuccess {String} d.list.user_nickname  用户昵称
     * @apiSuccess {String} d.list.user_avatar  用户头像
     * @apiSuccess {number} d.list.user_avatar_frame  用户头像框
     * @apiSuccess {String} d.list.user_birth  用户生日
     * @apiSuccess {String} d.list.user_gender  用户性别
     * @apiSuccess {number} d.list.user_rich_level  用户财富等级
     * @apiSuccess {number} d.list.user_charm_level 用户魅力等级
     * @apiSuccess {String='Y','N'} d.list.room_user_chat_forbidden_flg  是否被禁言
     * @apiSuccess {String='Y','N'} d.list.room_seat_flg  是否在麦上
     * @apiSuccess {String='normal(普通用户)','super_admin(超级管理员)','owner(房主)','admin(管理员)'} d.list.user_role_flg  房间角色
     * @apiSuccess {object[]} d.super_admin_list  超级管理员列表
     * @apiSuccess {number} d.room_online_count  房间用户人数
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *        "room_owner_info": {
     *            "user_id": "13",
     *            "user_nickname": "绥化的阿施",
     *            "user_avatar": "",
     *            "user_birth": "",
     *            "user_sex": "1",
     *            "user_level": "1",
     *            "user_on_room_flg": "Y",
     *            "room_seat_flg": "Y"
     *        },
     *        "list": [{
     *            "user_id": "12",
     *            "user_nickname": "阿拉尔的小曹",
     *            "user_avatar": "",
     *            "user_avatar_frame": "0",
     *            "user_birth": "",
     *            "user_gender": "unset",
     *            "user_rich_level": "1",
     *            "user_charm_level": "1",
     *            "room_user_chat_forbidden_flg": "N",
     *            "user_role_flg": "admin",
     *            "room_seat_flg": "Y"
     *        }],
     *        "super_admin_list": [{
     *            "user_id": "12",
     *            "user_nickname": "阿拉尔的小曹",
     *            "user_avatar": "",
     *            "user_birth": "",
     *            "user_sex": "1",
     *            "user_level": "1",
     *            "user_role_flg": "super_admin"
     *        }],
     *        "room_online_count" : 100
     *    },
     *    "t": "1558075908"
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function usersAction( $nUserId = 0 )
    {
        $nRoomId   = $this->getParams('room_id');
        $nPage     = $this->getParams('page', 'int', 1);
        $nPageSize = $this->getParams('pagesize', 'int', 100);
        $nPageSize = min(100, $nPageSize);
        try {
            if ( !$nRoomId ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $oRoom = Room::findFirst([
                'room_id = :room_id:',
                'bind' => [
                    'room_id' => $nRoomId
                ]
            ]);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $roomOwnerId = $oRoom->room_user_id;
            $roomOwner   = User::findFirst($roomOwnerId);

            // 获取房间麦上用户
            $oRoomSeatList   = RoomSeat::getInfoByRank($nRoomId);
            $roomSeatUserArr = array_column($oRoomSeatList, 'user_id');


            // 获取房间内超管列表
            $superAdminList = User::find([
                'user_is_superadmin = :user_is_superadmin: AND user_enter_room_id = :user_enter_room_id:',
                'bind'    => [
                    'user_is_superadmin' => 'Y',
                    'user_enter_room_id' => $nRoomId
                ],
                'columns' => 'user_id,user_nickname,user_avatar,
                user_birth,user_sex,user_level,"super_admin" as user_role_flg,user_vip_level'
            ]);


            $builder = $this->modelsManager->createBuilder()->from([ 'u' => User::class ])
                ->leftJoin(RoomAdmin::class, 'r.room_admin_user_id = u.user_id AND r.room_admin_room_id = :user_enter_room_id:', 'r')
                ->columns('u.user_id,u.user_nickname,u.user_avatar,u.user_birth,u.user_sex,
                u.user_level,r.room_admin_id,u.user_vip_level,u.user_member_expire_time')->where('u.user_enter_room_id = :user_enter_room_id: AND u.user_id != :owner_user_id: AND u.user_is_superadmin != :user_is_superadmin:', [
                    'user_enter_room_id' => $nRoomId,
                    'user_is_superadmin' => 'Y',
                    'owner_user_id'      => $oRoom->room_user_id,
                ])->orderBy('u.user_is_isrobot,r.room_admin_id desc,u.user_level desc,u.user_id');

            // 房主排在最上面 如果page=1 则直接显示
            $result = $this->page($builder, $nPage, $nPageSize);

            $roomTotalUserCount = $result['total'] + count($superAdminList);
            if ( $roomOwner->user_enter_room_id == $nRoomId ) {
                $roomTotalUserCount += 1;
            }

            if ( $oRoom->room_online_count != $roomTotalUserCount ) {
                $oRoom->room_online_count = $roomTotalUserCount;
                $oRoom->save();
            }

            // 获取所有禁言列表
            $oRoomChatForbidden = RoomChatForbidden::getRoomList($nRoomId);

            $allResult   = [];
            $robotResult = [];
            $list        = $result['items'];
            foreach ( $list as $item ) {
                $item['user_role_flg']                = $item['room_admin_id'] ? 'admin' : 'normal';
                $item['room_user_chat_forbidden_flg'] = 'N';
                if ( in_array($item['user_id'], $oRoomChatForbidden) ) {
                    $item['room_user_chat_forbidden_flg'] = 'Y';
                }

                // 在不在麦上
                $item['room_seat_flg'] = 'N';
                if ( in_array($item['user_id'], $roomSeatUserArr) ) {
                    $item['room_seat_flg'] = 'Y';
                }

                $item['user_is_member'] = $item['user_member_expire_time'] > time() ? 'Y' : 'N';
                unset($item['room_admin_id']);
                $allResult[] = $item;
            }

            $allResult = array_merge($allResult, $robotResult);

            $row = [
                'room_owner_info'   => [
                    'user_id'          => $roomOwner->user_id,
                    'user_nickname'    => $roomOwner->user_nickname,
                    'user_avatar'      => $roomOwner->user_avatar,
                    'user_birth'       => $roomOwner->user_birth,
                    'user_sex'         => $roomOwner->user_sex,
                    'user_level'       => $roomOwner->user_level,
                    'user_is_member'   => $roomOwner->user_member_expire_time > time() ? 'Y' : 'N',
                    'user_on_room_flg' => $roomOwner->user_enter_room_id == $nRoomId ? 'Y' : 'N',
                    'room_seat_flg'    => $roomOwner->user_id == $oRoom->room_host_user_id ? 'Y' : in_array($roomOwner->user_id, $roomSeatUserArr) ? 'Y' : 'N'
                ],
                'list'              => $allResult,
                'super_admin_list'  => $superAdminList ?? [],
                'room_online_count' => (int)$roomTotalUserCount,
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.2.0
     * @apiSampleRequest http://192.168.0.188:89/v2/live/room/user
     * @api {get} /live/room/user 013-191107房间用户详情
     * @apiName room-user
     * @apiGroup Room
     * @apiDescription 013-190701房间用户详情
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (正常请求){String} to_user_id 用户id
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id
     * @apiParam (debug){String} to_user_id 用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {String} d.owner_house_flg  自己是否是房主
     * @apiSuccess {String} d.owner_host_flg  自己在不在主持位
     * @apiSuccess {String='normal(普通用户)','super_admin(超级管理员)','owner(房主)','admin(管理员)'} d.owner_role_flg  自己在房间角色
     * @apiSuccess {number} d.user_id  查看用户id
     * @apiSuccess {number} d.user_number    查看用户编号
     * @apiSuccess {String} d.user_nickname 查看用户昵称
     * @apiSuccess {String} d.user_avatar  查看用户头像
     * @apiSuccess {number} d.user_avatar_frame  查看用户头像框
     * @apiSuccess {String} d.user_birth  查看用户生日
     * @apiSuccess {String} d.user_gender  查看用户性别
     * @apiSuccess {number} d.user_rich_level   查看用户财富等级
     * @apiSuccess {number} d.user_charm_level 查看用户魅力等级
     * @apiSuccess {String} d.user_follow_flg   自己是否关注该用户
     * @apiSuccess {String} d.room_black_flg  查看用户是否被房间拉黑
     * @apiSuccess {String} d.room_seat_flg   查看用户是否在麦上
     * @apiSuccess {String} d.room_seat_number   麦序
     * @apiSuccess {String} d.room_chat_forbidden_flg   查看用户是否被禁言
     * @apiSuccess {String} d.room_heart_stat_open_flg   房间甜心值是否开启
     * @apiSuccess {String} d.room_seat_voice_flg   查看用户麦是否开启
     * @apiSuccess {String='N(普通用户)','S(超级管理员)'} d.user_group_flg  用户分组表示
     * @apiSuccess {String='normal(普通用户)','super_admin(超级管理员)','owner(房主)','admin(管理员)'} d.user_role_flg  房间角色
     * @apiSuccess {Number} d.room_seat_count_down_duration  麦上倒计时时长秒
     * @apiSuccess {String} d.room_category  房间类型
     * @apiSuccess {String} d.room_host_user_id  房间主持用户ID
     * @apiSuccess {String} d.user_bubble   气泡ID
     * @apiSuccess {Object} d.user_bubble_info   气泡显示详情
     * @apiSuccess {String} d.user_bubble_info.user_bubble_source   气泡显示资源
     * @apiSuccess {String} d.user_bubble_info.user_bubble_9patch   气泡资源点九图
     * @apiSuccess {String} d.user_bubble_info.user_bubble_gif_list   气泡四周GIF图
     * @apiSuccess {String} d.user_bubble_info.user_bubble_gif_list.left_top 气泡左上图
     * @apiSuccess {String} d.user_bubble_info.user_bubble_gif_list.right_top 气泡右上图
     * @apiSuccess {String} d.user_bubble_info.user_bubble_gif_list.left_bottom 气泡左下图
     * @apiSuccess {String} d.user_bubble_info.user_bubble_gif_list.right_bottom 气泡右下图
     * @apiSuccess {String} d.user_bubble_info.user_bubble_gif_list.left_bottom_2 气泡左下底层图
     * @apiSuccess {String} d.user_bubble_info.user_bubble_gif_list.right_bottom_2 气泡右下底层图
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *        "owner_house_flg": "Y",
     *        "owner_host_flg": "Y",
     *        "owner_role_flg": "normal",
     *        "user_id": "11",
     *        "user_number": "66692068",
     *        "user_nickname": "咸阳的阿阚",
     *        "user_avatar": "",
     *        "user_avatar_frame": "0",
     *        "user_birth": "",
     *        "user_gender": "unset",
     *        "user_rich_level": "1",
     *        "user_charm_level": "1",
     *        "user_follow_flg": "N",
     *        "room_black_flg": "N",
     *        "room_seat_flg": "N",
     *        "room_heart_stat_open_flg": "N",
     *        "room_seat_number": 8,
     *        "room_chat_forbidden_flg": "N",
     *        "room_seat_voice_flg": "N",
     *        "user_group_flg": "N",
     *        "user_role_flg": "normal",
     *        "room_seat_count_down_duration": 0,
     *        "user_bubble": "0",
     *      "user_bubble_info": {
     *          "user_bubble_source": "",
     *          "user_bubble_9patch": "",
     *          "user_bubble_gif_list": {
     *            "left_top": "",
     *            "right_top": "",
     *            "left_bottom": "",
     *            "right_bottom": "",
     *            "left_bottom_2": "",
     *            "right_bottom_2": ""
     *          }
     *         },
     *        "user_frame_source": "",
     *    },
     *    "t": "1558694383"
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function userAction( $nUserId = 0 )
    {
        $nToUserId = $this->getParams('to_user_id');
        $nRoomId   = $this->getParams('room_id');
        try {
            if ( !$nRoomId ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            if ( !$nToUserId ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '用户号'), ResponseError::PARAM_ERROR);
            }
            $oRoom = Room::findFirst([
                'room_id = :room_id:',
                'bind' => [
                    'room_id' => $nRoomId
                ]
            ]);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $oToUser = User::findFirst($nToUserId);
            if ( !$oToUser ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '用户号'), ResponseError::PARAM_ERROR);
            }

            $ownerRoomFlg   = FALSE;
            $userRoleFlg    = 'normal';
            $ownerRoleFlg   = 'normal';
            $ownerRoomAdmin = FALSE;

            // 判断自己是否为房主
            if ( $oRoom->room_user_id == $nUserId ) {
                $ownerRoomFlg = TRUE;
            } else {
                // 判断自己是否为管理员
                $ownerRoomAdmin = RoomAdmin::checkAdmin($nRoomId, $nUserId);
            }


            // 判断自己是否为超管
            if ( $this->oUser->user_is_superadmin == 'Y' ) {
                $ownerRoleFlg = 'super_admin';
            } elseif ( $nUserId == $oRoom->room_user_id ) {
                // 自己是房主
                $ownerRoleFlg = 'owner';
            } elseif ( $ownerRoomAdmin ) {
                $ownerRoleFlg = 'admin';
            }


            // 判断是否关注对方
            $oUserFollow = UserFollow::findFirst([
                'user_id=:user_id: and to_user_id=:to_user_id:',
                'bind' => [
                    'user_id'    => $nUserId,
                    'to_user_id' => $nToUserId,
                ]
            ]);

            $oRoomAdmin = FALSE;
            if ( in_array($ownerRoleFlg, [
                'super_admin',
                'owner'
            ]) ) {
                // 超管和房主才能设置管理员
                // 判断对方是否为管理员
                $oRoomAdmin = RoomAdmin::checkAdmin($nRoomId, $nToUserId);
            }

            $oRoomBlack = FALSE;
            if ( in_array($ownerRoleFlg, [
                'super_admin',
                'owner',
                'admin'
            ]) ) {
                // 超管、房主、管理员 才能设置拉黑
                // 判断对方是否房间拉黑
                $oRoomBlack = RoomBlack::checkResult($nRoomId, $nToUserId);
            }

            $room_seat_flg = 'Y';
            if ( $nToUserId != $oRoom->room_host_user_id ) {
                if ( $oToUser->user_room_seat_flg == 'N' ) {
                    $room_seat_flg = 'N';
                } else {
                    $toUserIndex = (new UserGiftLog())->getSendCoinWeekRankIndex($nToUserId, $nRoomId);
                    $lastIndex   = (new RoomSeat())->getLastSeatRank($nRoomId);
                    if ( $toUserIndex === FALSE || $toUserIndex > $lastIndex ) {
                        $room_seat_flg = 'N';
                    }
                }
            }


            $oRoomChatForbidden = FALSE;
            if ( in_array($ownerRoleFlg, [
                'super_admin',
                'owner',
                'admin'
            ]) ) {
                // 超管、房主、管理员 才能设置禁言
                // 判断对方是否房间禁言
                $oRoomChatForbidden = RoomChatForbidden::checkResult($nRoomId, $nToUserId);
            }

            // 判断对方是否为超管
            if ( $oToUser->user_is_superadmin == 'Y' ) {
                $userRoleFlg = 'super_admin';
            } elseif ( $nToUserId == $oRoom->room_user_id ) {
                // 对方是房主
                $userRoleFlg = 'owner';
            } elseif ( $oRoomAdmin ) {
                $userRoleFlg = 'admin';
            }

            $oUser = $this->oUser;

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
            $toUserAnchorInfo = [
                'anchor_chat_price'  => 0,
                'anchor_chat_status' => 0,
            ];
            if ( $oToUser->user_is_anchor == 'Y' ) {
                $oToUserAnchor    = Anchor::findFirst([
                    'user_id=:user_id:',
                    'bind' => [
                        'user_id' => $oToUser->user_id,
                    ]
                ]);
                $toUserAnchorInfo = [
                    'anchor_chat_price'  => $oToUserAnchor->anchor_chat_price,
                    'anchor_chat_status' => $oToUserAnchor->anchor_chat_status,
                ];
            }

            $row = [
                'owner_role_flg' => $ownerRoleFlg,
                'owner_host_flg' => $oRoom->room_host_user_id == $nUserId ? 'Y' : 'N',

                'user_id'                  => $oToUser->user_id,
                'user_nickname'            => $oToUser->user_nickname,
                'user_avatar'              => $oToUser->user_avatar,
                'user_birth'               => $oToUser->user_birth,
                'user_sex'                 => $oToUser->user_sex,
                'user_level'               => $oToUser->user_level,
                'user_is_member'           => $oToUser->user_member_expire_time > time() ? 'Y' : 'N',
                'user_vip_level'           => $oToUser->user_vip_level,
                'user_is_anchor'           => $oToUser->user_is_anchor,
                'user_follow_flg'          => $oUserFollow ? 'Y' : 'N',
                'room_black_flg'           => $oRoomBlack ? 'Y' : 'N',
                'room_seat_flg'            => $room_seat_flg,
                'room_chat_forbidden_flg'  => $oRoomChatForbidden ? 'Y' : 'N',
                'room_seat_voice_flg'      => $oToUser->user_room_seat_voice_flg,
                'room_heart_stat_open_flg' => $oRoom->room_heart_stat_open_flg,
                'user_role_flg'            => $userRoleFlg,
                'room_user_id'             => $oRoom->room_user_id,
                'room_host_user_id'        => $oRoom->room_host_user_id,
                'chat_room_id'             => UserChatDialog::getChatRoomId($nUserId, $nToUserId),
                'owner_anchor_info'        => [
                    // 主播是否显示拨打按钮
                    'anchor_call_flg'    => $anchorCallFlg,
                    // 自己是主播时 自己的拨打价格
                    'owner_anchor_price' => $ownerAnchorPrice,
                ],
                'to_user_anchor_info'      => $toUserAnchorInfo

            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.0.0
     * @apiSampleRequest http://192.168.0.188:89/v2/live/room/setAdmin
     * @api {post} /live/room/setAdmin 005-190517设置房间管理员
     * @apiName room-setadmin
     * @apiGroup Room
     * @apiDescription 005-190517设置房间管理员
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (正常请求){String} to_user_id 用户id
     * @apiParam (正常请求){String='Y','N'} remove_flg 是否是删除  Y 删除  N 添加
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id
     * @apiParam (debug){String} to_user_id 用户id
     * @apiParam (debug){String='Y','N'} remove_flg 是否是删除  Y 删除  N 添加
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
    public function setAdminAction( $nUserId = 0 )
    {
        $nRoomId   = $this->getParams('room_id');
        $nToUserId = $this->getParams('to_user_id');
        $removeFlg = $this->getParams('remove_flg');
        try {
            $oRoom = Room::findFirst([
                'room_id = :room_id:',
                'bind' => [
                    'room_id' => $nRoomId
                ]
            ]);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            if ( $oRoom->room_user_id != $nUserId ) {
                throw new Exception(ResponseError::getError(ResponseError::ROOM_NOT_OWNER), ResponseError::ROOM_NOT_OWNER);
            }
            $oToUser = User::findFirst($nToUserId);
            if ( !$oToUser ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '用户号'), ResponseError::PARAM_ERROR);
            }
            if ( $oToUser->user_is_superadmin == 'Y' ) {
                // 对方是超级管理员 无法操作
                throw new Exception(ResponseError::getError(ResponseError::USER_IS_SUPER_ADMIN), ResponseError::USER_IS_SUPER_ADMIN);
            }

            // 对方是否为管理员
            $oRoomAdmin = RoomAdmin::checkAdmin($nRoomId, $nToUserId);
            if ( $removeFlg == 'Y' ) {
                if ( $oRoomAdmin ) {
                    if ( $oRoom->room_host_user_id == $nToUserId ) {
                        // 对方正在主持位
                        throw new Exception(ResponseError::getError(ResponseError::CAN_NOT_REMOVE_ADMIN_ON_HOST), ResponseError::CAN_NOT_REMOVE_ADMIN_ON_HOST);
                    }
                    $oRoomAdmin->delete();
                    // 推送取消管理员
                    $this->timServer->setRid($nRoomId);
                    $this->timServer->sendCancelAdminSignal([
                        'room_id'       => $nRoomId,
                        'user_id'       => $nToUserId,
                        'user_nickname' => $oToUser->user_nickname,
                    ]);
                }
            } else {
                if ( !$oRoomAdmin ) {
                    $oRoomAdmin                     = new RoomAdmin();
                    $oRoomAdmin->room_admin_room_id = $nRoomId;
                    $oRoomAdmin->room_admin_user_id = $nToUserId;
                    if ( $oRoomAdmin->save() === FALSE ) {
                        throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oRoomAdmin->getMessage()), ResponseError::OPERATE_FAILED);
                    }
                    // 推送添加管理员
                    $this->timServer->setRid($nRoomId);
                    $this->timServer->sendAddAdminSignal([
                        'room_id'       => $nRoomId,
                        'user_id'       => $nToUserId,
                        'user_nickname' => $oToUser->user_nickname,
                    ]);
                }
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }

    /**
     * @apiVersion 1.0.0
     * @apiSampleRequest http://192.168.0.188:89/v2/live/room/setChatForbidden
     * @api {post} /live/room/setChatForbidden 006-190517设置禁言
     * @apiName room-setChatForbidden
     * @apiGroup Room
     * @apiDescription 006-190517设置禁言
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (正常请求){String} to_user_id 用户id
     * @apiParam (正常请求){String='Y','N'} remove_flg 是否是删除  Y 删除  N 添加
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id
     * @apiParam (debug){String} to_user_id 用户id
     * @apiParam (debug){String='Y','N'} remove_flg 是否是删除  Y 删除  N 添加
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
    public function setChatForbiddenAction( $nUserId = 0 )
    {
        $nRoomId   = $this->getParams('room_id');
        $nToUserId = $this->getParams('to_user_id');
        $removeFlg = $this->getParams('remove_flg');
        try {
            $oRoom = Room::findFirst([
                'room_id = :room_id:',
                'bind' => [
                    'room_id' => $nRoomId
                ]
            ]);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            // 如果不是房主 且不是管理员
            if ( $nUserId != $oRoom->room_user_id && $this->oUser->user_is_superadmin == 'Y' ) {
                $oRoomAdmin = RoomAdmin::checkAdmin($nRoomId, $nUserId);
                if ( !$oRoomAdmin ) {
                    throw new Exception(ResponseError::getError(ResponseError::ROOM_NO_ADMIN), ResponseError::ROOM_NO_ADMIN);
                }
            }

            $oToUser = User::findFirst($nToUserId);
            if ( !$oToUser ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '用户号'), ResponseError::PARAM_ERROR);
            }
            if ( $oToUser->user_is_superadmin == 'Y' ) {
                // 对方是超级管理员 无法操作
                throw new Exception(ResponseError::getError(ResponseError::USER_IS_SUPER_ADMIN), ResponseError::USER_IS_SUPER_ADMIN);
            }

            // 如果不是房主 此时 是管理员  需要判断对方是否为管理员
            if ( $nUserId != $oRoom->room_user_id ) {
                $oToUserRoomAdmin = RoomAdmin::checkAdmin($nRoomId, $nToUserId);
                if ( $oToUserRoomAdmin ) {
                    // 对方也是管理员 则不能禁言
                    throw new Exception(ResponseError::getError(ResponseError::ROOM_NOT_OWNER), ResponseError::ROOM_NOT_OWNER);
                }
            }

            // 对方是否已经被禁言
            $oRoomChatForbidden = RoomChatForbidden::checkResult($nRoomId, $nToUserId);
            if ( $removeFlg == 'Y' ) {
                if ( $oRoomChatForbidden ) {
                    $oRoomChatForbidden->delete();

                    // 推送禁言解除
                    $this->timServer->setRid($nRoomId);
                    $this->timServer->sendCancelProhibitTalkSignal([
                        'room_id'       => $nRoomId,
                        'user_id'       => $nToUserId,
                        'user_nickname' => $oToUser->user_nickname,
                    ]);
                }
            } else {
                if ( !$oRoomChatForbidden ) {
                    $oRoomChatForbidden                               = new RoomChatForbidden();
                    $oRoomChatForbidden->room_chat_forbidden_room_id  = $nRoomId;
                    $oRoomChatForbidden->room_chat_forbidden_user_id  = $nToUserId;
                    $oRoomChatForbidden->room_chat_forbidden_admin_id = $nUserId;
                    if ( $oRoomChatForbidden->save() === FALSE ) {
                        throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oRoomChatForbidden->getMessage()), ResponseError::OPERATE_FAILED);
                    }

                    // 推送被禁言
                    $this->timServer->setRid($nRoomId);
                    $this->timServer->sendProhibitTalkSignal([
                        'room_id'       => $nRoomId,
                        'user_id'       => $nToUserId,
                        'user_nickname' => $oToUser->user_nickname,
                    ]);
                }
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @apiVersion 1.0.0
     * @apiSampleRequest http://192.168.0.188:89/v2/live/room/setBlack
     * @api {post} /live/room/setBlack 007-190517设置黑名单
     * @apiName room-setBlack
     * @apiGroup Room
     * @apiDescription 007-190517设置黑名单
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (正常请求){String} to_user_id 用户id
     * @apiParam (正常请求){String='Y','N'} remove_flg 是否是删除  Y 删除  N 添加
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id
     * @apiParam (debug){String} to_user_id 用户id
     * @apiParam (debug){String='Y','N'} remove_flg 是否是删除  Y 删除  N 添加
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
    public function setBlackAction( $nUserId = 0 )
    {
        $nRoomId   = $this->getParams('room_id');
        $nToUserId = $this->getParams('to_user_id');
        $removeFlg = $this->getParams('remove_flg');
        try {
            $oRoom = Room::findFirst([
                'room_id = :room_id:',
                'bind' => [
                    'room_id' => $nRoomId
                ]
            ]);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            // 如果不是房主或不是超级管理员 且不是管理员
            if ( $nUserId != $oRoom->room_user_id && $this->oUser->user_is_superadmin != 'Y' ) {
                $oRoomAdmin = RoomAdmin::checkAdmin($nRoomId, $nUserId);
                if ( !$oRoomAdmin ) {
                    throw new Exception(ResponseError::getError(ResponseError::ROOM_NO_ADMIN), ResponseError::ROOM_NO_ADMIN);
                }
            }

            $oToUser = User::findFirst($nToUserId);
            if ( !$oToUser ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '用户号'), ResponseError::PARAM_ERROR);
            }
            if ( $oToUser->user_is_superadmin == 'Y' ) {
                // 对方是超级管理员 无法操作
                throw new Exception(ResponseError::getError(ResponseError::USER_IS_SUPER_ADMIN), ResponseError::USER_IS_SUPER_ADMIN);
            }
            if ( $oToUser->user_id == $oRoom->room_user_id ) {
                // 对方是房主 不能被拉黑
                throw new Exception(ResponseError::getError(ResponseError::USER_IS_ROOM_OWNER), ResponseError::USER_IS_ROOM_OWNER);
            }


            $oToUserRoomAdmin = RoomAdmin::checkAdmin($nRoomId, $nToUserId);
            if ( $oToUserRoomAdmin ) {
                // 对方也是管理员 则不能禁言
                throw new Exception(ResponseError::getError(ResponseError::ROOM_ADMIN_CAN_NOT_REMOVE), ResponseError::ROOM_ADMIN_CAN_NOT_REMOVE);
            }

            $oRoomBlack = RoomBlack::checkResult($nRoomId, $nToUserId);
            $flg        = [];
            if ( $removeFlg == 'Y' ) {
                if ( $oRoomBlack ) {
                    $oRoomBlack->delete();

                    // 解除拉黑  暂时不发推送
                }
            } else {
                if ( !$oRoomBlack ) {
                    $oRoomBlack                      = new RoomBlack();
                    $oRoomBlack->room_black_room_id  = $nRoomId;
                    $oRoomBlack->room_black_user_id  = $nToUserId;
                    $oRoomBlack->room_black_admin_id = $nUserId;
                    if ( $oRoomBlack->save() === FALSE ) {
                        throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oRoomBlack->getMessage()), ResponseError::OPERATE_FAILED);
                    }

                    $leaveHasSeat = $oRoom->leave($oToUser, FALSE);
                    $oRoomSeat    = [];
                    if ( is_array($leaveHasSeat) && $leaveHasSeat['room_seat'] ) {
                        $oRoomSeat = $leaveHasSeat['room_seat'];
                    }

                    // 踢出房间
                    $this->timServer->setRid($nRoomId);
                    $flg = $this->timServer->sendKickSignal([
                        'room_id'           => $nRoomId,
                        'user_id'           => $nToUserId,
                        'user_nickname'     => $oToUser->user_nickname,
                        'room_seat'         => $oRoomSeat,
                        'action_black_flg'  => 'Y',
                        'room_online_count' => $oRoom->room_online_count - 1 < 0 ? 0 : $oRoom->room_online_count - 1
                    ]);
                    $this->timServer->setRid('');
                    $this->timServer->setUid($nToUserId);
                    $flg = $this->timServer->sendKickSignal([
                        'room_id'           => $nRoomId,
                        'user_id'           => $nToUserId,
                        'user_nickname'     => $oToUser->user_nickname,
                        'room_seat'         => [],
                        'action_black_flg'  => 'Y',
                        'room_online_count' => $oRoom->room_online_count - 1 < 0 ? 0 : $oRoom->room_online_count - 1
                    ]);

                }
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($flg);
    }


    /**
     * @apiVersion 1.0.0
     * @apiSampleRequest http://192.168.0.188:89/v2/live/room/setKick
     * @api {post} /live/room/setKick 008-190520踢出房间
     * @apiName room-setKick
     * @apiGroup Room
     * @apiDescription 008-190520踢出房间
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (正常请求){String} to_user_id 用户id
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id
     * @apiParam (debug){String} to_user_id 用户id
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
    public function setKickAction( $nUserId = 0 )
    {
        $nRoomId   = $this->getParams('room_id');
        $nToUserId = $this->getParams('to_user_id');
        try {
            $oRoom = Room::findFirst([
                'room_id = :room_id:',
                'bind' => [
                    'room_id' => $nRoomId
                ]
            ]);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            // 如果不是房主或不是超级管理员 且不是管理员
            if ( $nUserId != $oRoom->room_user_id && $this->oUser->user_is_superadmin != 'Y' ) {
                $oRoomAdmin = RoomAdmin::checkAdmin($nRoomId, $nUserId);
                if ( !$oRoomAdmin ) {
                    throw new Exception(ResponseError::getError(ResponseError::ROOM_NO_ADMIN), ResponseError::ROOM_NO_ADMIN);
                }
            }

            $oToUser = User::findFirst($nToUserId);
            if ( !$oToUser ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '用户号'), ResponseError::PARAM_ERROR);
            }
            if ( $oToUser->user_is_superadmin == 'Y' ) {
                // 对方是超级管理员 无法操作
                throw new Exception(ResponseError::getError(ResponseError::USER_IS_SUPER_ADMIN), ResponseError::USER_IS_SUPER_ADMIN);
            }

            // 如果不是房主 此时 是管理员  需要判断对方是否为管理员
            if ( $nUserId != $oRoom->room_user_id ) {
                $oToUserRoomAdmin = RoomAdmin::checkAdmin($nRoomId, $nToUserId);
                if ( $oToUserRoomAdmin ) {
                    // 对方也是管理员 则不能禁言
                    throw new Exception(ResponseError::getError(ResponseError::ROOM_NOT_OWNER), ResponseError::ROOM_NOT_OWNER);
                }
            }

            // 退出房间
            $leaveHasSeat = $oRoom->leave($oToUser, FALSE);

            $oRoomSeat = [];
            if ( is_array($leaveHasSeat) && $leaveHasSeat['room_seat'] ) {
                $oRoomSeat = $leaveHasSeat['room_seat'];
            }

            // 发送踢出房间信息
            $this->timServer->setUid('');
            $this->timServer->setRid($nRoomId);
            $this->timServer->sendKickSignal([
                'room_id'           => $nRoomId,
                'user_id'           => $nToUserId,
                'user_nickname'     => $oToUser->user_nickname,
                'room_seat'         => $oRoomSeat,
                'action_black_flg'  => 'N',
                'room_online_count' => $oRoom->room_online_count - 1 < 0 ? 0 : $oRoom->room_online_count - 1
            ]);
            $this->timServer->setRid('');
            $this->timServer->setUid($nToUserId);
            $this->timServer->sendKickSignal([
                'room_id'           => $nRoomId,
                'user_id'           => $nToUserId,
                'user_nickname'     => $oToUser->user_nickname,
                'room_seat'         => $oRoomSeat,
                'action_black_flg'  => 'N',
                'room_online_count' => $oRoom->room_online_count - 1 < 0 ? 0 : $oRoom->room_online_count - 1
            ]);

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @apiVersion 1.0.0
     * @apiSampleRequest http://192.168.0.188:89/v2/live/room/enterSeat
     * @api {post} /live/room/enterSeat 015-190529上麦
     * @apiName room-enterSeat
     * @apiGroup Room
     * @apiDescription 上麦  团战模式，选择随机则不需要传座位序号
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (正常请求){String} seat_number 座位序号
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id
     * @apiParam (debug){String} seat_number 座位序号
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.live_model  麦序模式
     * @apiSuccess {string} d.save_seat_number 上的麦序（自由麦有效，排麦无效）
     * @apiSuccess {string} d.wait_count 等待人数
     * @apiSuccess {string} d.seat_voice_flg 上麦的声音是否开启
     * @apiSuccess {string} d.push_url 推流地址
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": {
     *              "live_model" : "free",
     *              "save_seat_number" : "1",
     *              "wait_count" : "1",
     *              "seat_voice_flg" : "Y",
     *              "push_url" : ""
     *          },
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
    public function enterSeatAction( $nUserId = 0 )
    {
        // 主持位不走这个接口 所有 seat_number = 0 默认为null
        $nRoomId     = $this->getParams('room_id');
        $nSeatNumber = $this->getParams('seat_number', 'int', 0);
        try {
            if ( empty($nRoomId) ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $oRoom = Room::findFirst([
                'room_id = :room_id:',
                'bind' => [
                    'room_id' => $nRoomId
                ]
            ]);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }

            // 判断用户是否在房间
            $oUser = $this->oUser;
            if ( $oUser->user_enter_room_id != $nRoomId ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '不在当前房间'), ResponseError::PARAM_ERROR);
            }

            // 判断用户排名是否在当前房间第四位之前
            $myIndex   = (new UserGiftLog())->getSendCoinWeekRankIndex($nUserId, $nRoomId);
            $lastIndex = (new RoomSeat())->getLastSeatRank($nRoomId);
            if ( $myIndex === FALSE || $myIndex > $lastIndex ) {
                throw new Exception('请先送礼超过当前麦位', ResponseError::OPERATE_FAILED);
            }
            $this->log->info('myindex:' . $myIndex . '; lastIndex:' . $lastIndex);

            $seatVoiceFlg = 'Y';

            $roomSeat = RoomSeat::getInfoByRank($nRoomId);

            $inSeat     = FALSE;
            $hasSeat    = FALSE;
            $seatNumber = 1;
            foreach ( $roomSeat as $item ) {
                if ( $item['owner_user_id'] == $nUserId ) {
                    $inSeat       = $item['user_room_seat_flg'] == 'Y' ? TRUE : FALSE;
                    $hasSeat      = TRUE;
                    $seatNumber   = $item['room_seat_number'];
                    $seatVoiceFlg = $item['room_seat_voice_flg'];
                    break;
                }
            }
            if ( $hasSeat === FALSE ) {
                throw new Exception('请先送礼超过当前麦位', ResponseError::OPERATE_FAILED);
            }
            if ( $inSeat === FALSE ) {
                $oUser = User::findFirst($nUserId);
                $this->timServer->setExtra($oUser);
                $this->timServer->setRid($nRoomId);
                $this->timServer->sendEnterSeatSignal([
                    'room_id'             => $nRoomId,
                    'seat_number'         => $seatNumber,
                    'room_seat_voice_flg' => $oUser->user_room_seat_voice_flg,
                    'rank_index'          => $myIndex === FALSE ? 100 : $myIndex
                ]);
                $oUser->user_room_seat_flg = 'Y';
                $oUser->save();
                RoomSeat::deleteRoomSeatCache($nRoomId);
            }

            $row = [
                'save_seat_number' => (string)$seatNumber,
                'seat_voice_flg'   => $seatVoiceFlg,
                'rank_index'       => $myIndex === FALSE ? 100 : $myIndex
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.5.0
     * @apiSampleRequest http://192.168.0.188:89/v2/live/room/enterHostSeat
     * @api {post} /live/room/enterHostSeat 014-191025上主持位
     * @apiName room-enterHostSeat
     * @apiGroup Room
     * @apiDescription 上主持位  如果是房主可以直接上主持位。  在房间内 如果自己是主持。如果接到有新的上主持位则客户端自己退出
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.live_model  麦序模式
     * @apiSuccess {string} d.wait_count 等待上麦人数
     * @apiSuccess {number} d.room_total_radio_guard 守护榜的人数
     * @apiSuccess {string} d.push_url 推流地址
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": {
     *              "live_model" : "free",
     *              "wait_count" : "10",
     *              "room_total_radio_guard" : 100,
     *              "push_url" : ""
     *          },
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
    public function enterHostSeatAction( $nUserId = 0 )
    {
        $nRoomId = $this->getParams('room_id');
        try {
            if ( empty($nRoomId) ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $oRoom = Room::findFirst([
                'room_id = :room_id:',
                'bind' => [
                    'room_id' => $nRoomId
                ]
            ]);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            if ( $nUserId != $oRoom->room_user_id ) {
                // 不是房主 就需要判断是否为管理员
                $oRoomAdmin = RoomAdmin::checkAdmin($nRoomId, $nUserId);
                if ( !$oRoomAdmin ) {
                    throw new Exception(ResponseError::getError(ResponseError::ROOM_NO_ADMIN), ResponseError::ROOM_NO_ADMIN);
                }
            }

            if($oRoom->room_host_user_id){
                $hostSeat = RoomSeat::findFirst([
                    'room_seat_room_id = :room_id: AND room_seat_user_id > 0 AND room_seat_number = 0',
                    'bind' => [
                        'room_id' => $nRoomId,
                    ]
                ]);
                if($hostSeat->room_seat_user_id != $oRoom->room_host_user_id ){
                    $oRoom->room_host_user_id = 0;
                    $oRoom->save();
                }
            }

            if ( $oRoom->room_host_user_id == $nUserId ) {
                // 当前主持是自己
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '已经是主持'), ResponseError::PARAM_ERROR);
            }
            if ( $oRoom->room_host_user_id ) {
                if ( $nUserId == $oRoom->room_user_id ) {
                    // 主持位有人 且当前是房主 则可将主持位上人挤下
                    $oRoomSeat = new RoomSeat();
                    $oRoomSeat->leave($nRoomId, $oRoom->room_host_user_id);
                } else {
                    // 主持位有人 且不是房主 则不能上
                    throw new Exception(ResponseError::getError(ResponseError::ROOM_HOST_EXISTS), ResponseError::ROOM_HOST_EXISTS);
                }
            }

            // 主持位为0
            $nSeatNumber    = 0;
            $saveSeat       = RoomSeat::enter($nRoomId, $nUserId, $nSeatNumber);
            $saveSeatNumber = $saveSeat ? $saveSeat->room_seat_number : FALSE;
            if ( $saveSeatNumber !== FALSE ) {
                $oRoom->room_host_user_id = $nUserId;

                $oRoom->save();
                // 推送
                $oUser = User::findFirst($nUserId);
                $this->timServer->setExtra($oUser);
                $this->timServer->setRid($nRoomId);
                $this->timServer->sendEnterSeatSignal([
                    'room_id'             => $nRoomId,
                    'seat_number'         => $saveSeatNumber,
                    'room_seat_voice_flg' => 'Y',
                    'action_user_id'      => $nUserId,
                    'action_flg'          => 'active'
                ]);

                $oRoom->updateRoomSeat();

            } else {
                throw new Exception(ResponseError::getError(ResponseError::NOT_SEAT), ResponseError::NOT_SEAT);
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @apiVersion 1.1.0
     * @apiSampleRequest https://api.dev.tiantongkeji.cn/v2/live/room/info
     * @api {get} /live/room/info 011-190815房间信息
     * @apiName room-info
     * @apiGroup Room
     * @apiDescription 011-190629房间信息
     * @apiParam (正常请求){string} room_id 房间id
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){string} room_id 房间id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object} d.room  房间信息
     * @apiSuccess {number} d.room.room_id   房间id
     * @apiSuccess {number} d.room.room_number  房间编号
     * @apiSuccess {String} d.room.room_cover   房间封面
     * @apiSuccess {String} d.room.room_background   房间背景
     * @apiSuccess {String} d.room.room_category   房间类型
     * @apiSuccess {String} d.room.room_name  房间名称
     * @apiSuccess {number} d.room.room_online_count   房间在线人数
     * @apiSuccess {number} d.room.room_total_collect  房间收藏总数
     * @apiSuccess {number} d.room.host_flg  是否为主持
     * @apiSuccess {String='normal(普通用户)','super_admin(超级管理员)','owner(房主)','admin(管理员)'} d.room.owner_role_flg 自己在房间角色
     * @apiSuccess {String} d.room.room_sign_flg 房间是否签约
     * @apiSuccess {object} d.owner   房主信息
     * @apiSuccess {number} d.owner.user_id   房主用户id
     * @apiSuccess {String} d.owner.user_nickname  房主用户昵称
     * @apiSuccess {String} d.owner.user_avatar   房主用户头像
     * @apiSuccess {number} d.owner.user_avatar_frame  房主用户头像框
     * @apiSuccess {object[]} d.admin_list    管理员信息
     * @apiSuccess {number} d.admin_list.user_id   管理员用户id
     * @apiSuccess {String} d.admin_list.user_nickname   管理员用户昵称
     * @apiSuccess {String} d.admin_list.user_avatar  管理员用户头像
     * @apiSuccess {number} d.admin_list.user_avatar_frame  管理员用户头像框
     * @apiSuccess {object[]} d.black_list  被拉黑用户信息
     * @apiSuccess {number} d.black_list.user_id   被拉黑用户ID
     * @apiSuccess {String} d.black_list.user_nickname  被拉黑用户昵称
     * @apiSuccess {String} d.black_list.user_avatar  被拉黑用户头像
     * @apiSuccess {number} d.black_list.user_avatar_frame  被拉黑用户头像框
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *        "room": {
     *            "room_id": "1",
     *            "room_number": "22011581",
     *            "room_cover": "https:\/\/cskj-1257854899.image.myqcloud.com\/image\/2019\/05\/01\/1556685174488.png",
     *            "room_name": "我的小房间",
     *            "room_background": "2",
     *            "room_category": "normal",
     *            "room_online_count": "2",
     *            "room_total_coin": "0",
     *            "room_total_collect": "0",
     *            "host_flg": "Y",
     *            "owner_role_flg" : "normal",
     *            "room_sign_flg": "Y"
     *        },
     *        "owner": {
     *            "user_id": "13",
     *            "user_nickname": "啦啦啦",
     *            "user_avatar": "",
     *            "user_avatar_frame": "0"
     *        },
     *        "admin_list": [{
     *            "user_id": "12",
     *            "user_nickname": "阿拉尔的小曹",
     *            "user_avatar": "",
     *            "user_avatar_frame": "0"
     *        }, {
     *            "user_id": "13",
     *            "user_nickname": "啦啦啦",
     *            "user_avatar": "",
     *            "user_avatar_frame": "0"
     *        }],
     *        "black_list": [{
     *            "user_id": "17",
     *            "user_nickname": "呼伦贝尔的小卞",
     *            "user_avatar": "",
     *            "user_avatar_frame": "0"
     *        }, {
     *            "user_id": "18",
     *            "user_nickname": "咸宁的碰碰香",
     *            "user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2019\/05\/21\/1558404577974.png",
     *            "user_avatar_frame": "0"
     *        }]
     *    },
     *    "t": "1558407355"
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function infoAction( $nUserId = 0 )
    {
        $nRoomId = $this->getParams('room_id');
        try {
            if ( empty($nRoomId) ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $oRoom = Room::findFirst([
                'room_id = :room_id:',
                'bind' => [
                    'room_id' => $nRoomId
                ]
            ]);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $ownerUser = User::findFirst($oRoom->room_user_id);

            $adminListBuilder = (new RoomAdmin)->getRoomDetail($nRoomId);

            $adminList = $this->page($adminListBuilder, 1, 100);

            // 拉黑列表
            $blackListBuilder = (new RoomBlack())->getRoomDetail($nRoomId);
            $blackList        = $this->page($blackListBuilder, 1, 100);

            // 判断自己在房间的角色
            // 判断自己是否为超管
            $ownerRoleFlg = 'normal';
            if ( $this->oUser->user_is_superadmin == 'Y' ) {
                $ownerRoleFlg = 'super_admin';
            } elseif ( $nUserId == $oRoom->room_user_id ) {
                // 自己是房主
                $ownerRoleFlg = 'owner';
            } elseif ( RoomAdmin::checkAdmin($oRoom->room_id, $nUserId) ) {
                $ownerRoleFlg = 'admin';
            }

            $row = [
                'room'       => [
                    'room_id'           => $oRoom->room_id,
                    'room_number'       => $oRoom->room_number,
                    'room_cover'        => $oRoom->room_cover,
                    'room_background'   => (int)$oRoom->room_background,
                    'room_name'         => $oRoom->room_name,
                    'room_online_count' => $oRoom->room_online_count,
                    'host_flg'          => $oRoom->room_host_user_id == $nUserId ? 'Y' : 'N',
                    'owner_role_flg'    => $ownerRoleFlg,
                ],
                'owner'      => [
                    'user_id'       => $ownerUser->user_id,
                    'user_nickname' => $ownerUser->user_nickname,
                    'user_avatar'   => $ownerUser->user_avatar,
                ],
                'admin_list' => $adminList['items'],

                'black_list' => $blackList['items'],

            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.0.0
     * @apiSampleRequest http://192.168.0.188:89/v2/live/room/leave
     * @api {post} /live/room/leave 012-190516离开房间
     * @apiName room-leave
     * @apiGroup Room
     * @apiDescription 004-190516离开房间
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": "",
     *    "t": "1558010433"
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function leaveAction( $nUserId = 0 )
    {
        $nRoomId = $this->getParams('room_id');

        $this->log->info($nUserId . ' room leave :' . $nRoomId);
        try {
            // 离开房间 修改用户表中当前进入房间字段  推送给房间离开信息
            if ( empty($nRoomId) ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $oRoom = Room::findFirst([
                'room_id = :room_id:',
                'bind' => [
                    'room_id' => $nRoomId
                ]
            ]);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $oUser = User::findFirst($nUserId);

            $oRoom->leave($oUser);

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @apiVersion 1.0.0
     * @apiSampleRequest http://192.168.0.188:89/v2/live/room/leaveSeat
     * @api {post} /live/room/leaveSeat 020-190622下麦
     * @apiName room-leaveSeat
     * @apiGroup Room
     * @apiDescription 019-190622下麦
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id
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
    public function leaveSeatAction( $nUserId = 0 )
    {
        $nRoomId = $this->getParams('room_id');
        try {
            if ( empty($nRoomId) ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $oRoom = Room::findFirst([
                'room_id = :room_id:',
                'bind' => [
                    'room_id' => $nRoomId
                ]
            ]);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $roomSeat        = [];
            $leaveSeatNumber = -1;
            $oUser           = User::findFirst($nUserId);
            $isHosts         = FALSE;
            if ( $nUserId == $oRoom->room_host_user_id ) {
                // 是主持
                $leaveSeatNumber          = (new RoomSeat())->leave($nRoomId, $nUserId);
                $oRoom->room_host_user_id = 0;
                $isHosts                  = TRUE;
                $oUser->user_room_seat_flg = 'Y';
                $oUser->save();
            } else {
                // 判断用户在不在座位上
                $myIndex   = (new UserGiftLog())->getSendCoinWeekRankIndex($nUserId, $nRoomId);
                $lastIndex = (new RoomSeat())->getLastSeatRank($nRoomId);
                if ( $myIndex !== FALSE && $myIndex <= $lastIndex ) {
                    // 在座位上

                    $oUser->user_room_seat_flg = 'N';
                    $oUser->save();
                    $roomSeat = RoomSeat::getInfoByRank($nRoomId, FALSE);
                    foreach ( $roomSeat as $item ) {
                        if ( $item['owner_user_id'] == $nUserId ) {
                            $leaveSeatNumber = $item['room_seat_number'];
                        }
                    }
                }
            }
            $this->timServer->setExtra($oUser);
            $this->timServer->setRid($nRoomId);
            $flg = $this->timServer->sendLeaveSeatSignal([
                'room_id'     => $nRoomId,
                'seat_number' => $leaveSeatNumber,
            ]);

            $oRoom->save();
            if ( $isHosts ) {
                $oRoom->updateRoomSeat();
            }

            $row = [
                'flg' => $flg
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://192.168.0.188:89/v2/live/room/detail
     * @api {post} /live/room/detail 021-190916最新房间信息
     * @apiName room-detail
     * @apiGroup Room
     * @apiDescription 021-190622最新房间信息  获取进入房间返回内容  必须在房间内
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiUse EnterRoomTemplate
     */
    public function detailAction( $nUserId = 0 )
    {
        $nRoomId = $this->getParams('room_id');
        try {
            $oRoom = Room::findFirst($nRoomId);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $this->oUser = User::findFirst($nUserId);

            if ( $this->oUser->user_enter_room_id != $nRoomId ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), '您已不在房间内'), ResponseError::FAIL);
            }

            $row = $this->_getEnterInfo($oRoom, $nUserId);

            // 添加心跳
            $oRoomHeartbeatService = new services\RoomHeartbeatService();
            $oRoomHeartbeatService->save(sprintf("%s_%s", $nRoomId, $nUserId));

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.4.0
     * @api {post} /live/room/setVoiceFlg 025-191211设置麦序声音
     * @apiName room-setVoiceFlg
     * @apiGroup Room
     * @apiDescription 025-190629设置麦序声音
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (正常请求){String} to_user_id 改变声音用户ID
     * @apiParam (正常请求){String='Y','N'} voice_flg 是否是开启  Y   N
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
    public function setVoiceFlgAction( $nUserId = 0 )
    {
        $nRoomId   = $this->getParams('room_id');
        $nToUserId = $this->getParams('to_user_id');
        $voiceFlg  = $this->getParams('voice_flg');
        try {
            $oRoom = Room::findFirst([
                'room_id = :room_id:',
                'bind' => [
                    'room_id' => $nRoomId
                ]
            ]);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            // 如果不是房主 且不是管理员
            if ( $nUserId != $oRoom->room_host_user_id ) {
                throw new Exception(ResponseError::getError(ResponseError::ROOM_NOT_HOST), ResponseError::ROOM_NOT_HOST);
            }

            $oToUser = User::findFirst($nToUserId);
            if ( $voiceFlg != $oToUser->user_room_seat_voice_flg ) {
                $oToUser->user_room_seat_voice_flg = $voiceFlg;
                $oToUser->save();
                // 推送麦序声音状态
                $this->timServer->setRid($nRoomId);
                $this->timServer->sendRoomSeatVoiceFlgSignal([
                    'room_id'             => $nRoomId,
                    'to_user_id'          => $nToUserId,
                    'room_seat_voice_flg' => $voiceFlg
                ]);
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }

    /**
     * @apiVersion 1.0.0
     * @apiSampleRequest http://192.168.0.188:89/v2/live/room/heartbeat
     * @api {post} /live/room/heartbeat 026-190629房间心跳
     * @apiName room-heartBeat
     * @apiGroup Room
     * @apiDescription 026-190629房间心跳
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id
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
    public function heartbeatAction( $nUserId = 0 )
    {
        $nRoomId = $this->getParams('room_id');
        try {
            $oRoom = Room::findFirst($nRoomId);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            $oRoomHeartbeatService = new services\RoomHeartbeatService();
            $oRoomHeartbeatService->save(sprintf("%s_%s", $oRoom->room_id, $nUserId));
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


    /**
     * @apiVersion 1.0.0
     * @apiSampleRequest http://192.168.0.188:89/v2/live/room/sendVoiceRoomChat
     * @api {post} /live/room/sendVoiceRoomChat 028-190702发送房间聊天
     * @apiName room-sendVoiceRoomChat
     * @apiGroup Room
     * @apiDescription 028-190702发送房间聊天
     * @apiParam (正常请求){String} room_id 房间id
     * @apiParam (正常请求){String} content 内容
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} room_id 房间id
     * @apiParam (debug){String} content 内容
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
    public function sendVoiceRoomChatAction( $nUserId = 0 )
    {
        $nRoomId = $this->getParams('room_id');
        $content = $this->getParams('content');
        try {
            if ( !$nRoomId ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'),
                    ResponseError::PARAM_ERROR
                );
            }
            if ( $this->oUser->user_enter_room_id != $nRoomId ) {
                // 输入的房间号 不是当前进入的房间
                throw new Exception(
                    '当前进入的房间不是操作的房间',
                    ResponseError::OPERATE_FAILED
                );
            }
            $oRoom = Room::findFirst($nRoomId);
            if ( !$oRoom ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'),
                    ResponseError::PARAM_ERROR
                );
            }
            // 判断是否禁言
            $oRoomChatForbidden = RoomChatForbidden::checkResult($nRoomId, $nUserId);

            if ( $oRoomChatForbidden ) {
                throw new Exception(ResponseError::getError(ResponseError::USER_ROOM_CHAT_FORBIDDEN), ResponseError::USER_ROOM_CHAT_FORBIDDEN);
            }

            $userRoleFlg = 'normal';
            // 判断自己是否为超管
            if ( $this->oUser->user_is_superadmin == 'Y' ) {
                $userRoleFlg = 'super_admin';
            } elseif ( $nUserId == $oRoom->room_user_id ) {
                // 自己是房主
                $userRoleFlg = 'owner';
            } elseif ( RoomAdmin::checkAdmin($nRoomId, $nUserId) ) {
                $userRoleFlg = 'admin';
            }

            $wordText = (new Banword())->filterContent($content);

            $oRoomChat                         = new RoomChat();
            $oRoomChat->room_chat_room_id      = $nRoomId;
            $oRoomChat->room_chat_user_id      = $nUserId;
            $oRoomChat->room_chat_content      = $content;
            $oRoomChat->room_chat_show_content = $wordText;
            if ( $oRoomChat->save() === FALSE ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oRoomChat->getMessage()), ResponseError::OPERATE_FAILED);
            }
            $this->timServer->setExtra($this->oUser);
            $this->timServer->setRid($nRoomId);
            $flg = $this->timServer->sendVoiceRoomChatSignal([
                'room_id'       => $nRoomId,
                'content'       => $wordText,
                'user_role_flg' => $userRoleFlg,

            ]);
            $row = [
                'flg' => $flg
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @param \app\models\Room $oRoom
     * @param $nUserId
     * @return array
     * 获取进入房间返回信息
     */
    private function _getEnterInfo( $oRoom, $nUserId )
    {

        // 房间内信息
        $row = $oRoom->getInsideDetail();

        // 用户与房间的关系数据
        $row = array_merge($row, $oRoom->getRelationship($nUserId, $this->oUser));

        // 获取快速回复列表
//        $row['fast_chat'] = FastChat::getList($oRoom);
        // 官方公告
        $kvData = Kv::many([
            Kv::EGG_SHOW_USER_LEVEL
        ]);

        $appInfo        = $this->getAppInfo();
        $shareUrlPrefix = $appInfo['share_url_prefix'] ?? $this->config->application->app_web_url;
        // 分享信息
        $row['share'] = [
            'title'   => sprintf("我正在【%s】里happy哦！这里的人太有意思了", $oRoom->room_name),
            'content' => '一个用声音交友的聊天室APP',
            'image'   => $oRoom->room_cover,
            'url'     => add_querystring_var($shareUrlPrefix, 'invite_code', $this->oUser->user_invite_code)
        ];

        $row['zego_token'] = $oRoom->getToken($nUserId, 'zego');

        $eggShowUserLevel = $kvData[ Kv::EGG_SHOW_USER_LEVEL ] ?? 0;

        $row['egg_open_flg'] = $appInfo['egg_open_flg'] ?? 'N';

        // 开启时需要判断是否等级达到有砸蛋开关
        if ( $row['egg_open_flg'] == 'Y' && $this->oUser->user_level < $eggShowUserLevel ) {
            $row['egg_open_flg'] = 'N';
        }

        return $row;
    }

    /**
     * @apiVersion 1.5.0
     * @apiSampleRequest https://api.dev.tiantongkeji.cn/v2/live/room/getLastRoomChat
     * @api {get} live/room/getLastRoomChat 032-191024获取最近房间聊天记录
     * @apiName room-getLastRoomChat
     * @apiGroup Room
     * @apiDescription 032-190806获取最近房间聊天记录  获取10条
     * @apiParam (正常请求) {String} room_id  房间ID
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug) {String} room_id  房间ID
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object} d.last_gift   最近一次礼物
     * @apiSuccess {number} d.last_gift.send_user_id  送礼用户ID
     * @apiSuccess {String} d.last_gift.send_user_nickname   送礼用户昵称
     * @apiSuccess {number} d.last_gift.get_user_id  获得礼物用户ID
     * @apiSuccess {String} d.last_gift.get_user_nickname  获得礼物用户昵称
     * @apiSuccess {String} d.last_gift.gift_logo  礼物logo
     * @apiSuccess {String} d.last_gift.gift_name  礼物名称
     * @apiSuccess {number} d.last_gift.gift_coin 礼物金币
     * @apiSuccess {number} d.last_gift.gift_number  礼物数量
     * @apiSuccess {object[]} d.list
     * @apiSuccess {number} d.list.user_id   用户ID
     * @apiSuccess {String} d.list.user_avatar  用户头像
     * @apiSuccess {String} d.list.user_nickname  用户昵称
     * @apiSuccess {String} d.list.user_gender  性别
     * @apiSuccess {String} d.list.content  文字内容
     * @apiSuccess {String='normal(普通用户)','super_admin(超级管理员)','owner(房主)','admin(管理员)'} d.list.user_role_flg   角色
     * @apiSuccess {String='(普通用户)','gold(黄金守护)','silver(白银守护)','bronze(青铜守护)'} d.list.user_radio_guard_top_flg   电台守护标识
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *        "last_gift": {
     *            "send_user_id": 14,
     *            "send_user_nickname": "哦nullKKK",
     *            "get_user_id": 63,
     *            "get_user_nickname": "hhhhh",
     *            "gift_logo": "https:\/\/tiantong-1259630769.image.myqcloud.com\/image\/20190807\/1565160120551740.png",
     *            "gift_name": "小甜筒",
     *            "gift_coin": 10,
     *            "gift_number": 1
     *        },
     *        "list": [{
     *            "user_id": "14",
     *            "user_avatar": "https:\/\/tiantong-1259630769.image.myqcloud.com\/image\/2019\/08\/14\/1565775481186.png",
     *            "user_nickname": "哦nullKKK",
     *            "user_gender": "female",
     *            "content": "jjuu",
     *            "user_role_flg": "owner",
     *            "user_radio_guard_top_flg": "gold"
     *        }]
     *    },
     *    "t": "1565867442"
     *   }
     */
    public function getLastRoomChatAction( $nUserId = 0 )
    {
        $nRoomId = $this->getParams('room_id');
        try {
            $oEnterRoomLog = EnterRoomLog::findFirst([
                'enter_room_user_id = :enter_room_user_id: AND enter_room_room_id = :enter_room_room_id: AND enter_room_offline_time = 0',
                'bind'  => [
                    'enter_room_user_id' => $this->oUser->user_id,
                    'enter_room_room_id' => $nRoomId
                ],
                'order' => 'enter_room_id desc'
            ]);
            $row           = [
                'list' => []
            ];
            if ( $oEnterRoomLog ) {
                $oRoom     = Room::findFirst($nRoomId);
                $oRoomChat = $this->modelsManager
                    ->createBuilder()
                    ->from([ 'l' => RoomChat::class ])
                    ->join(User::class, 'u.user_id = l.room_chat_user_id', 'u')
                    ->columns('l.room_chat_user_id as user_id,u.user_avatar,u.user_nickname,u.user_sex,
                    l.room_chat_show_content as content,u.user_is_superadmin,u.user_level,u.user_member_expire_time')
                    ->where('room_chat_room_id = :room_chat_room_id: AND room_chat_create_time >= :room_chat_create_time:',
                        [
                            'room_chat_room_id'     => $nRoomId,
                            'room_chat_create_time' => $oEnterRoomLog->enter_room_online_time
                        ])
                    ->limit(10)
                    ->orderBy('room_chat_id desc')
                    ->getQuery()
                    ->execute()
                    ->toArray();

                $data = [];

                foreach ( $oRoomChat as $item ) {
                    if ( $item['user_id'] == $oRoom->room_user_id ) {
                        $item['user_role_flg'] = 'owner';
                    } elseif ( $item['user_is_superadmin'] == 'Y' ) {
                        $item['user_role_flg'] = 'super_admin';
                    } elseif ( $item['user_id'] == $oRoom->room_host_user_id || RoomAdmin::checkAdmin($nRoomId, $item['user_id']) ) {
                        $item['user_role_flg'] = 'admin';
                    } else {
                        $item['user_role_flg'] = 'normal';
                    }
                    $item['user_is_member'] = $item['user_member_expire_time'] > time() ? 'Y' : 'N';
                    unset($item['user_is_superadmin']);
                    unset($item['user_member_expire_time']);
                    array_unshift($data, $item);
                }

                $row = [
                    'list' => $data
                ];
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.0.0
     * @apiSampleRequest https://api.dev.tiantongkeji.cn/v2/live/room/getAgoraToken
     * @api {get} live/room/getAgoraToken 033-190806获取声网token
     * @apiName room-getAgoraToken
     * @apiGroup Room
     * @apiDescription 033-190806获取声网token
     * @apiParam (正常请求) {String} room_id  房间id
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug) {String} room_id  房间id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.agora_token 声网token
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": {
     *              "agora_token" : "sdfasfswfsdfsfsdf"
     *          },
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     */
    public function getAgoraTokenAction( $nUserId = 0 )
    {
        $nRoomId = $this->getParams('room_id');
        try {
            $oRoom = Room::findFirst($nRoomId);
            $row   = [
                'agora_token' => $oRoom->getToken($nUserId)
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.2.0
     * @apiSampleRequest https://api.dev.tiantongkeji.cn/v2/live/room/resetHeartValue
     * @api {get} /live/room/resetHeartValue 039-190827重置甜心值
     * @apiName room-resetHeartValue
     * @apiGroup Room
     * @apiDescription 039-190827重置甜心值
     * @apiParam (正常请求) {String} room_id  房间ID
     * @apiParam (正常请求) {String} seat_number 麦序
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug) {String} room_id  房间ID
     * @apiParam (debug) {String} seat_number 麦序
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {number} d.room_id   房间ID
     * @apiSuccess {number} d.seat_number  麦序
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *        "room_id": "4",
     *        "seat_number": "2"
     *    },
     *    "t": "1566874262"
     *   }
     */
    public function resetHeartValueAction( $nUserId = 0 )
    {
        $nRoomId     = $this->getParams('room_id');
        $nSeatNumber = $this->getParams('seat_number', 'int', 0);
        try {
            if ( empty($nRoomId) ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }

            $oRoom = Room::findFirst([
                'room_id = :room_id:',
                'bind' => [
                    'room_id' => $nRoomId
                ]
            ]);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }
            if ( $nUserId != $oRoom->room_user_id ) {
                throw new Exception(ResponseError::getError(ResponseError::ROOM_NOT_HOST), ResponseError::ROOM_NOT_HOST);
            }
            $oRoomSeat = RoomSeat::findFirst([
                'room_seat_room_id = :room_seat_room_id: AND room_seat_number = :room_seat_number:',
                'bind' => [
                    'room_seat_room_id' => $nRoomId,
                    'room_seat_number'  => $nSeatNumber,
                ],
            ]);
            if ( $oRoomSeat ) {
                $oRoomSeat->room_seat_heart_stat_start = time();
                $oRoomSeat->room_seat_heart_value      = 0;
                $oRoomSeat->save();
            }

            $row = [
                'room_id'     => $nRoomId,
                'seat_number' => $nSeatNumber,
            ];

            // 推送清空甜心值
            $this->timServer->setRid($nRoomId);
            $flg        = $this->timServer->sendResetHeartValueSignal($row);
            $row['flg'] = $flg;
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.2.0
     * @apiSampleRequest https://api.dev.tiantongkeji.cn/v2/live/room/seatHeartValueStat
     * @api {get} /live/room/seatHeartValueStat 040-190828麦位收益
     * @apiName room-seatHeartValueStat
     * @apiGroup Room
     * @apiDescription 040-190828麦位收益
     * @apiParam (正常请求) {String} room_id  房间ID
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug) {String} room_id  房间ID
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string='Y','N'} d.reset_flg  是否重置了甜心值  N 不需要提示
     * @apiSuccess {string} d.reset_notice_content  重置甜心值提示内容
     * @apiSuccess {object[]} d.list
     * @apiSuccess {number} d.list.room_seat_number  麦序
     * @apiSuccess {number} d.list.user_id  用户ID
     * @apiSuccess {String} d.list.user_nickname   用户昵称
     * @apiSuccess {String} d.list.user_avatar 用户头像
     * @apiSuccess {String} d.list.user_gender  用户性别
     * @apiSuccess {number} d.list.user_avatar_frame   头像框
     * @apiSuccess {number} d.list.room_seat_heart_value   麦位甜心值
     * @apiSuccess {number} d.list.room_seat_heart_stat_start  麦位统计开始时间戳
     * @apiSuccess {number} d.list.user_heart_value  用户甜心值
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *        "reset_flg": "Y",
     *        "reset_notice_content": "当前麦位收益统计已超过48小时，请及时截屏保存，系统将在本次查看后清空甜心值",
     *        "list": [{
     *            "room_seat_number": "0",
     *            "user_id": "63",
     *            "user_nickname": "hhhhh",
     *            "user_avatar": "http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/XN5JWV1ScFoSgrVLRTicVFaM233UtlRNtSRMsfER9S6RUO3x5I3N2QpbjJqUQGfribp14qpjxEyMoTxkMrjqPovQ\/132",
     *            "user_gender": "male",
     *            "user_avatar_frame": "8",
     *            "room_seat_heart_value": "10",
     *            "room_seat_heart_stat_start": "1566905286",
     *            "user_heart_value": "10"
     *        }, {
     *            "room_seat_number": "1",
     *            "user_id": "0",
     *            "user_nickname": "",
     *            "user_avatar": "",
     *            "user_gender": "unset",
     *            "user_avatar_frame": "0",
     *            "room_seat_heart_value": "10",
     *            "room_seat_heart_stat_start": "1566905286",
     *            "user_heart_value": "0"
     *        }, {
     *            "room_seat_number": "2",
     *            "user_id": "0",
     *            "user_nickname": "",
     *            "user_avatar": "",
     *            "user_gender": "unset",
     *            "user_avatar_frame": "0",
     *            "room_seat_heart_value": "0",
     *            "room_seat_heart_stat_start": "1566905286",
     *            "user_heart_value": "0"
     *        }]
     *    },
     *    "t": "1566957078"
     *   }
     */
    public function seatHeartValueStatAction( $nUserId = 0 )
    {
        $nRoomId = $this->getParams('room_id');
        try {
            if ( empty($nRoomId) ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }

            $oRoom = Room::findFirst([
                'room_id = :room_id:',
                'bind' => [
                    'room_id' => $nRoomId
                ]
            ]);
            if ( !$oRoom ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '房间号'), ResponseError::PARAM_ERROR);
            }

            $oUser = User::findFirst($nUserId);
            if ( $oUser->user_is_superadmin != 'Y' && !in_array($nUserId, [
                    $oRoom->room_user_id,
                    $oRoom->room_host_user_id
                ]) && RoomAdmin::checkAdmin($nRoomId, $nUserId) == FALSE ) {
                // 不是超管 不是房主 不是主持 不是管理员
                throw new Exception(ResponseError::getError(ResponseError::ROOM_NO_ADMIN), ResponseError::ROOM_NO_ADMIN);
            }

            if ( $oRoom->room_heart_stat_open_flg == 'N' ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), '当前未开启甜心值，请先开启甜心值'), ResponseError::OPERATE_FAILED);
            }
            $oRoomSeatList = RoomSeat::getInfo($nRoomId);

            foreach ( $oRoomSeatList AS &$item ) {
                $item['user_heart_value'] = '0';
                if ( $item['user_id'] && $item['room_seat_heart_stat_start'] < time() ) {
                    $item['user_heart_value'] = (string)UserGiftLog::getHeartValue($item['user_id'], $item['room_seat_number'], $item['room_seat_heart_stat_start']);
                }
                unset($item['room_seat_voice_flg']);
                unset($item['room_seat_open_flg']);
            }

            // 如果房间开启甜心值超过限制 48小时 则清空所有麦位
            $resetFlg     = 'N';
            $resetContent = '';
            if ( $nUserId == $oRoom->room_host_user_id ) {
                $overTimeHour = 48;
                if ( time() - $oRoom->room_heart_stat_start > $overTimeHour * 3600 ) {
                    RoomSeat::startHeartStat($oRoom->room_id);
                    $oRoom->room_heart_stat_start = time();
                    $oRoom->save();
                    $resetFlg     = 'Y';
                    $resetContent = sprintf('当前麦位收益统计已超过%d小时，请及时截屏保存，系统将在本次查看后清空甜心值', $overTimeHour);
                }
            }

            $row = [
                'reset_flg'            => $resetFlg,
                'reset_notice_content' => $resetContent,
                'list'                 => $oRoomSeatList
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.4.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/room/selectUsers
     * @api {get} /live/room/selectUsers 054-191213房间搜索用户
     * @apiName Room-selectUsers
     * @apiGroup Room
     * @apiDescription 054-191213房间搜索用户
     * @apiParam (正常请求){String='not_room_admin(非房间管理员)','not_room_black(非房间拉黑人员)'} location 搜索位置
     * @apiParam (正常请求){String} search_key 搜索内容
     * @apiParam (正常请求){Number} page 页码
     * @apiParam (正常请求){Number} pagesize 页数（最大100）
     * @apiParam (正常请求){String} room_id 房间id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.list
     * @apiSuccess {number} d.list.user_id   用户ID
     * @apiSuccess {String} d.list.user_nickname   用户昵称
     * @apiSuccess {String} d.list.user_avatar  用户头像
     * @apiSuccess {String} d.list.user_birth   用户生日
     * @apiSuccess {number} d.list.user_sex  性别
     * @apiSuccess {number} d.list.user_level   等级
     * @apiSuccess {String} d.list.user_is_member   是否VIP
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *        "list": [{
     *            "user_id": "163",
     *            "user_nickname": "Tom",
     *            "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/FAB72B107E3DBBA9A3C08221D856F8A7\/100",
     *            "user_birth": "2018-08-24",
     *            "user_sex": "1",
     *            "user_level": "1",
     *            "user_is_member": "N"
     *        }]
     *    },
     *    "t": "1576204364"
     *   }
     */
    public function selectUsersAction( $nUserId = 0 )
    {
        $nRoomId    = $this->getParams('room_id');
        $nPage      = $this->getParams('page', 'int', 1);
        $nPageSize  = $this->getParams('pagesize', 'int', 20);
        $sSearchKey = $this->getParams('search_key', 'string');
        $sLocation  = $this->getParams('location', 'string', 'not_room_admin');
        try {
            $offset = ($nPage - 1) * $nPageSize;
            $nRoom  = Room::findFirst($nRoomId);

            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'u' => User::class ])
                ->columns('u.user_id,u.user_nickname,u.user_avatar,u.user_birth,u.user_sex,u.user_level,u.user_member_expire_time');
            switch ( $sLocation ) {
                case 'not_room_admin':
                    $builder->where(sprintf("u.user_id != :room_user_id: AND not exists(select 1 from %s where room_admin_room_id = :room_admin_room_id: AND room_admin_user_id = u.user_id)",
                        RoomAdmin::class),
                        [
                            'room_admin_room_id' => $nRoomId,
                            'room_user_id'       => $nRoom->room_user_id
                        ]);
                    break;
                case 'not_room_black':
                    $builder->where(sprintf("not exists(select 1 from %s where room_black_room_id = :room_black_room_id: AND room_black_user_id = u.user_id)", RoomBlack::class),
                        [
                            'room_black_room_id' => $nRoomId,
                        ]);
                    break;
            }
            if ( $sSearchKey ) {
                $builder->andWhere('u.user_nickname like :search: or u.user_id = :search_number:', [
                    'search'        => "%$sSearchKey%",
                    'search_number' => intval($sSearchKey)
                ]);
            }
            $row['list'] = $builder->limit($nPageSize, $offset)
                ->orderBy('user_id')
                ->getQuery()
                ->execute()
                ->toArray();

            foreach ( $row['list'] as &$item ) {
                $item['user_is_member'] = $item['user_member_expire_time'] > time() ? 'Y' : 'N';
                unset($item['user_member_expire_time']);
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.5.0
     * @apiSampleRequest https://api.dev.tiantongkeji.cn/v2/live/room/getZegoToken
     * @api {get} live/room/getZegoToken 053-191106获取即构token
     * @apiName room-getAgoraToken
     * @apiGroup Room
     * @apiDescription 053-191106获取即构token
     * @apiParam (正常请求) {String} room_id  房间id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.token 即构token
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": {
     *              "token" : "01ODQ0YjliNjk3MTI0N2EzZWNoaPqnRIx92EHt2xINVrw0HpcjQktlnZBwSKv95klQDzVWfzx28JaNJMS9ospVQMpjchRVESLtBa+LTgKrX\/cKwRL53BXpRcRhp12s2Cy1"
     *          },
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     */
    public function getZegoTokenAction( $nUserId = 0 )
    {
        $nRoomId = $this->getParams('room_id');
        try {
            $oRoom = Room::findFirst($nRoomId);
            $row   = [
                'token' => $oRoom->getToken($nUserId, 'zego')
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    public function getInviteAnchorAction( $nUserId = 0 )
    {
        $nRoomId = $this->getParams('room_id');
        try {
            $row['anchor_list'] = $this->modelsManager->createBuilder()
                ->from([ 'a' => Anchor::class ])
                ->join(User::class, 'u.user_id = a.user_id', 'u')
                ->columns('u.user_id,u.user_nickname,u.user_avatar')
                ->where('a.anchor_hot_time > 0 AND a.anchor_chat_status = 3')
                ->orderBy('rand()')
                ->limit(20, 0)->getQuery()->execute()->toArray();

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    public function testAction()
    {
        $data1 = (new RoomSeat())->getInfo(1);
        $data  = (new RoomSeat())->getInfoByRank(1);
        echo '<pre>';
        var_dump($data1, $data);
        die;
        $data = (new UserGiftLog())->getRoomSendCoinRank('week', 0);
        echo '<pre>';
        var_dump($data);
        die;
    }


}