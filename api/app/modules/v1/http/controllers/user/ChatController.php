<?php
/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 用户聊天控制器                                                         |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\user;

use app\helper\JiGuangApi;
use app\models\Agent;
use app\models\Anchor;
use app\models\AnchorDispatch;
use app\models\AnchorImage;
use app\models\AnchorSignStat;
use app\models\Banword;
use app\models\DispatchChat;
use app\models\GuideMsgList;
use app\models\IsrobotTalk;
use app\models\Kv;
use app\models\LevelConfig;
use app\models\UserBlack;
use app\models\UserChatEvaluationLog;
use app\models\UserFinanceLog;
use app\models\UserGuideLog;
use app\models\UserGuideVideo;
use app\models\UserGuideVideoLog;
use app\models\UserIntimate;
use app\models\UserMatchLog;
use app\models\UserPrivateChatDialog;
use app\models\UserPrivateChatLog;
use app\models\UserSet;
use app\models\UserVideo;
use app\models\UserVideoMessage;
use app\services\AnchorSayhiService;
use app\services\AnchorStatService;
use app\services\AnchorTotalSayhiService;
use app\services\TaskQueueService;
use app\services\UserSayhiService;
use app\services\UserService;
use app\services\UserVideoChatService;
use app\services\VideoChatService;
use Exception;
use app\services;
use app\models\User;
use app\models\UserChat;
use app\models\UserFollow;
use app\models\UserChatLog;
use app\helper\ResponseError;
use app\models\SystemMessage;
use app\models\UserChatDialog;
use app\models\UserSystemMessage;
use app\models\UserSystemMessageDialog;
use app\http\controllers\ControllerBase;
use fast\Arr;

/**
 * ChatController 用户聊天控制器
 */
class ChatController extends ControllerBase
{
    use UserService;

    /**
     * dialogListAction 对话列表
     *
     * @param int $nUserId
     */
    public function dialogListAction( $nUserId = 0 )
    {
        $nPage     = $this->getParams('page', 'int', 0);
        $nPgaesize = $this->getParams('pagesize', 'int', 20);
        try {
            $builder            = $this->modelsManager->createBuilder()->from([ 'ucd' => UserChatDialog::class ])
                ->join(User::class, 'u.user_id=ucd.to_user_id', 'u')
                ->columns('u.user_id,u.user_is_superadmin,u.user_nickname,u.user_avatar,u.user_level,ucd.user_chat_room_id chat_room_id,ucd.user_chat_content content,
                ucd.user_chat_unread unread,user_chat_dialog_update_time time,user_member_expire_time,u.user_sex,u.user_birth')->where('ucd.user_id=:user_id:', [ 'user_id' => $nUserId ])->orderBy('user_chat_dialog_update_time desc');
            $row['user_dialog'] = $this->page($builder, $nPage, $nPgaesize);

            $oUser = User::findFirst($nUserId);
            // 有客服参与的对话 不禁用关键词

            $oBanword = new Banword();
            foreach ( $row['user_dialog']['items'] as &$dialogItem ) {
                $dialogItem['content']        = $oUser->user_is_superadmin == 'C' || $dialogItem['user_is_superadmin'] == 'C' ? $dialogItem['content'] : $oBanword->filterContent($dialogItem['content']);
                $dialogItem['chat_room_id']   = UserChatDialog::getChatRoomId($dialogItem['user_id'], $nUserId);
                $dialogItem['user_is_member'] = $dialogItem['user_member_expire_time'] == 0 ? 'N' : (time() > $dialogItem['user_member_expire_time'] ? 'O' : 'Y');
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * dialogAction 对话详情
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/chat/dialog
     * @api {get} /user/chat/dialog 对话详情
     * @apiName chat-dialog
     * @apiGroup Chat
     * @apiDescription  对话详情
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String}  chat_room_id 聊天房间id
     * @apiParam (正常请求){String}  dialog_id 最后一个id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String}  chat_room_id 聊天房间id
     * @apiParam (debug){String}  dialog_id 最后一个id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.user_dialog
     * @apiSuccess {object} d.user_dialog.from_user
     * @apiSuccess {number} d.user_dialog.from_user.user_id
     * @apiSuccess {String} d.user_dialog.from_user.user_nickname
     * @apiSuccess {number} d.user_dialog.from_user.user_level
     * @apiSuccess {String='normal(普通)','once(阅后即焚)','destroy(已焚毁)'} d.user_dialog.from_user.send_read_type
     * @apiSuccess {String} d.user_dialog.from_user.user_avatar
     * @apiSuccess {String} d.user_dialog.from_user.user_is_member
     * @apiSuccess {object} d.user_dialog.to_user
     * @apiSuccess {number} d.user_dialog.to_user.user_id
     * @apiSuccess {String} d.user_dialog.to_user.user_nickname
     * @apiSuccess {number} d.user_dialog.to_user.user_level
     * @apiSuccess {String='normal(普通)','once(阅后即焚)','destroy(已焚毁)'} d.user_dialog.to_user.send_read_type
     * @apiSuccess {String} d.user_dialog.to_user.user_avatar
     * @apiSuccess {String} d.user_dialog.to_user.user_is_member
     * @apiSuccess {String} d.user_dialog.msg
     * @apiSuccess {number} d.user_dialog.time
     * @apiSuccess {number} d.user_dialog.dialog_id
     * @apiSuccess {String} d.user_dialog.type
     * @apiSuccess {String} d.user_dialog.source_url
     * @apiSuccess {String} d.user_dialog.extra
     * @apiSuccess {number} d.user_dialog.income
     * @apiSuccess {number} d.user_dialog.pay_coin
     * @apiSuccess {number} d.user_dialog.video_chat_status
     * @apiSuccess {number} d.user_dialog.video_chat_duration
     * @apiSuccess {String} d.user_dialog.video_chat_has_callback
     * @apiSuccess {String} d.user_dialog.exp  用户获得经验
     * @apiSuccess {String} d.user_dialog.intimate_value  获得亲密度
     * @apiSuccess {String} d.user_dialog.anchor_exp  主播获得经验（魅力值）
     * @apiSuccess {object} d.user
     * @apiSuccess {String} d.user.is_follow
     * @apiSuccess {String} d.user.has_reply
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *                 "user_dialog": [{
     *                     "from_user": {
     *                         "user_id": "318",
     *                         "user_nickname": "渐入佳境",
     *                         "user_level": "1",
     *                         "send_read_type": "noce",
     *                         "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1107915107\/63F3F098E6FAC4B5C210CA2458C66BE6\/100",
     *                         "user_is_member": "N"
     *                 },
     *                 "to_user": {
     *                         "user_id": "320",
     *                         "user_nickname": "—",
     *                         "user_level": "1",
     *                         "send_read_type": "noce",
     *                         "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/4D6E1DCB3B3823B4E4075970C929E3DC\/100",
     *                         "user_is_member": "N"
     *                 },
     *                 "msg": "对方邀请您视频聊天",
     *                 "time": "1543819438",
     *                 "dialog_id": "2858",
     *                 "type": "video_chat",
     *                 "source_url": "",
     *                 "extra": "a:2:{s:17:\"video_chat_status\";s:6:\"1\";s:19:\"video_chat_duration\";i:0;}",
     *                 "income": "0.00",
     *                 "pay_coin": "0.00",
     *                 "video_chat_status": "1",
     *                 "video_chat_duration": "0",
     *                 "video_chat_has_callback": "Y"
     *             }],
     *             "user": {
     *                     "is_follow": "N",
     *                     "has_reply": true
     *             }
     *         },
     *         "t": "1543980263"
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function dialogAction( $nUserId = 0 )
    {
        $nPgaesize   = $this->getParams('pagesize', 'int', 20);
        $sChatRoomId = $this->getParams('chat_room_id', 'string', '');
        $nUserChatId = $this->getParams('dialog_id', 'int', 0);
        $this->redis->hSet('user_app_version', $nUserId, $this->getParams('app_version'));
        try {
            $builder = $this->modelsManager->createBuilder()->from(UserChat::class)
                ->columns('user_chat_id dialog_id,user_chat_content content,user_chat_create_time time,
                user_chat_send_user_id sender,user_chat_receiv_user_id receiver,user_chat_type,user_chat_source_url,user_chat_extra,user_chat_income,user_chat_price,user_chat_send_read_type,user_chat_receive_read_type')
                ->andWhere('user_chat_room_id=:user_chat_room_id: AND (user_chat_pay_type != "G" or user_chat_receiv_user_id = :nUserId:)', [
                    'user_chat_room_id' => $sChatRoomId,
                    'nUserId'           => $nUserId
                ]);
            if ( $nUserChatId != 0 ) {
                $builder->andWhere('user_chat_id<:user_chat_id:', [ 'user_chat_id' => $nUserChatId ]);
            }
            $data               = $builder->orderBy('user_chat_id desc')->limit($nPgaesize)->getQuery()->execute()->toArray();
            $row['user_dialog'] = [];
            if ( !empty($data) ) {
                $aUserId[] = $data[0]['sender'];
                $aUserId[] = $data[0]['receiver'];
                $aUser     = User::find([
                    'user_id in ({user_id:array})',
                    'bind' => [
                        'user_id' => $aUserId,
                    ]
                ])->toArray();
                foreach ( $aUser as $v ) {
                    $aUserData[ $v['user_id'] ] = $v;
                }
            }
            $isNewVersion = $this->checkVersionMatch($this->getParams('app_version'));
            $oBanword     = new Banword();
            foreach ( $data as $v ) {
                if ( in_array($v['user_chat_type'], UserChat::VIP_MSG_TYPE_ARR) && !$isNewVersion ) {
                    $content        = '您当前的APP版本过低，暂不支持查看该多媒体消息，请更新到最新版本或等待版本更新';
                    $user_chat_type = 'word';
                } else {
                    // 客服参与的聊天 不屏蔽关键词
                    if ( $v['user_chat_type'] == 'word' && $aUserData[ $v['sender'] ]['user_is_superadmin'] != 'C' && $aUserData[ $v['receiver'] ]['user_is_superadmin'] != 'C' ) {
                        $content = $oBanword->filterContent($v['content']);
                    } else {
                        $content = $v['content'];
                    }
                    $user_chat_type = $v['user_chat_type'];
                }

                $exp                  = '0';
                $anchorExp            = '0';
                $intimateValue        = '0';
                $videoChatStatus      = 0;
                $videoChatDuration    = '0';
                $videoChatHasCallback = 'Y';
                if ( $user_chat_type == UserChat::TYPE_VIDEO_CHAT ) {
                    $extraArr             = unserialize($v['user_chat_extra']);
                    $videoChatStatus      = $extraArr['video_chat_status'] ?? '';
                    $videoChatDuration    = $extraArr['video_chat_duration'] ?? '0';
                    $videoChatHasCallback = $extraArr['video_chat_has_callback'] ?? 'Y';
                } else if ( $user_chat_type == UserChat::TYPE_GIFT ) {
                    $extraArr             = unserialize($v['user_chat_extra']);
                    $exp                  = $extraArr['exp'] ?? '0';
                    $intimateValue        = $extraArr['intimate_value'] ?? '0';
                    $anchorExp            = $extraArr['anchor_exp'] ?? '0';
                    $v['user_chat_extra'] = $extraArr['gift_number'] ?? '1';
                }
                $row['user_dialog'][] = [
                    'from_user'               => [
                        'user_id'        => isset($aUserData[ $v['sender'] ]) ? $aUserData[ $v['sender'] ]['user_id'] : '0',
                        'user_nickname'  => isset($aUserData[ $v['sender'] ]) ? $aUserData[ $v['sender'] ]['user_nickname'] : '',
                        'user_avatar'    => isset($aUserData[ $v['sender'] ]) ? $aUserData[ $v['sender'] ]['user_avatar'] : '',
                        'user_level'     => isset($aUserData[ $v['sender'] ]) ? $aUserData[ $v['sender'] ]['user_level'] : '',
                        'send_read_type' => $v['user_chat_send_read_type'],
                        'user_is_member' => isset($aUserData[ $v['sender'] ]) ? $aUserData[ $v['sender'] ]['user_member_expire_time'] == 0 ? 'N' : (time() > $aUserData[ $v['sender'] ]['user_member_expire_time'] ? 'O' : 'Y') : '',
                    ],
                    'to_user'                 => [
                        'user_id'        => isset($aUserData[ $v['receiver'] ]) ? $aUserData[ $v['receiver'] ]['user_id'] : '0',
                        'user_nickname'  => isset($aUserData[ $v['receiver'] ]) ? $aUserData[ $v['receiver'] ]['user_nickname'] : '',
                        'user_avatar'    => isset($aUserData[ $v['receiver'] ]) ? $aUserData[ $v['receiver'] ]['user_avatar'] : '',
                        'user_level'     => isset($aUserData[ $v['receiver'] ]) ? $aUserData[ $v['receiver'] ]['user_level'] : '',
                        'send_read_type' => $v['user_chat_receive_read_type'],
                        'user_is_member' => isset($aUserData[ $v['receiver'] ]) ? $aUserData[ $v['receiver'] ]['user_member_expire_time'] == 0 ? 'N' : (time() > $aUserData[ $v['receiver'] ]['user_member_expire_time'] ? 'O' : 'Y') : '',
                    ],
                    'msg'                     => $content,
                    'time'                    => $v['time'],
                    'dialog_id'               => $v['dialog_id'],
                    'type'                    => $user_chat_type,
                    'source_url'              => $v['user_chat_source_url'],
                    'extra'                   => $v['user_chat_extra'],
                    'income'                  => $v['user_chat_income'],
                    'pay_coin'                => $v['user_chat_price'],
                    'video_chat_status'       => $videoChatStatus,
                    'video_chat_duration'     => (string)$videoChatDuration,
                    'video_chat_has_callback' => (string)$videoChatHasCallback,
                    'exp'                     => $exp,
                    'intimate_value'          => $intimateValue,
                    'anchor_exp'              => $anchorExp
                ];
            }
            // 将未读数至0
            if ( $nUserChatId == 0 ) {
                $oUserChatDialog = UserChatDialog::findFirst([
                    'user_id=:user_id: and user_chat_room_id=:user_chat_room_id:',
                    'bind' => [
                        'user_id'           => $nUserId,
                        'user_chat_room_id' => $sChatRoomId,
                    ]
                ]);
                if ( $oUserChatDialog && $oUserChatDialog->user_chat_unread != 0 ) {
                    $oUserChatDialog->user_chat_unread = 0;
                    $oUserChatDialog->save();
                }
            }
            $aUserId                  = explode('_', $sChatRoomId);
            $isFollow                 = UserFollow::findFirst([
                'user_id=:user_id: and to_user_id=:to_user_id:',
                'bind' => [
                    'user_id'    => $nUserId,
                    'to_user_id' => $nUserId == $aUserId[0] ? $aUserId[1] : $aUserId[0],
                ]
            ]);
            $row['user']['is_follow'] = $isFollow ? 'Y' : 'N';
            $oAnchor                  = Anchor::findFirst([
                'user_id=:user_id:',
                'bind' => [ 'user_id' => $nUserId ]
            ]);
            $hasReply                 = TRUE;
            if ( $oAnchor->anchor_is_positive == 'N' ) {
                $userToAnchorChat    = UserChat::findFirst([
                    'user_chat_room_id = :user_chat_room_id: and user_chat_send_user_id = :user_chat_send_user_id:',
                    'bind' => [
                        'user_chat_room_id'      => $sChatRoomId,
                        'user_chat_send_user_id' => $nUserId == $aUserId[0] ? $aUserId[1] : $aUserId[0]
                    ]
                ]);
                $oUserPrivateChatLog = UserPrivateChatLog::findFirst([
                    'chat_log_user_id = :chat_log_user_id: and chat_log_anchor_user_id = :chat_log_anchor_user_id:',
                    'bind' => [
                        'chat_log_user_id'        => $nUserId == $aUserId[0] ? $aUserId[1] : $aUserId[0],
                        'chat_log_anchor_user_id' => $nUserId
                    ]
                ]);
                $hasReply            = $userToAnchorChat || $oUserPrivateChatLog ? 'Y' : 'N';
            }

            $row['user']['has_reply'] = $hasReply;

        } catch ( Exception $e ) {
            $this->error(ResponseError::FAIL, $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * sendAction 发送聊天
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/chat/send
     * @api {post} /user/chat/send 发送消息
     * @apiName 发送聊天
     * @apiGroup Chat
     * @apiDescription 发送消息
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} chat_room_id 聊天id
     * @apiParam (正常请求){String} to_user_id 接收方用户id
     * @apiParam (正常请求){String} content 内容  根据类型传值 如果是诱导 则传诱导id 第三次诱导 msg_type 需要传image
     * @apiParam (正常请求){String} pay 是否确认付费  确认为1
     * @apiParam (正常请求){String='sayHi(打招呼)','normal(普通消息)','guide(诱导)'} type 聊天类型
     * @apiParam (正常请求){String='word(文字)','image(图片)','video(视频)','voice(语音)'} msg_type 消息类型
     * @apiParam (正常请求){String} extra 额外信息（接收方 收到时 同样返回）
     * @apiParam (正常请求){String='index(首页)','profile（个人资料）','video(视频诱导)'} guide_location 诱导位置 类型为guide时 添加 默认为index
     * @apiParam (正常请求){Number=1,2,3} guide_sort 诱导第几次 视频通话诱导不用传
     * @apiParam (正常请求){String} is_read_once 1为阅后即焚， 0为普通（默认）
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} chat_room_id 聊天id
     * @apiParam (debug){String} to_user_id 接收方用户id
     * @apiParam (debug){String} content 内容  根据类型传值
     * @apiParam (debug){String} pay 是否确认付费  确认为1
     * @apiParam (debug){String='sayHi(打招呼)','normal(普通消息)'} type 聊天类型
     * @apiParam (debug){String='word(文字)','image(图片)','video(视频)','voice(语音)'} msg_type 消息类型
     * @apiParam (debug){String} extra 额外信息（接收方 收到时 同样返回）
     * @apiParam (debug){String='index(首页)','profile（个人资料）','video(视频诱导)'} guide_location 诱导位置 类型为guide时 添加 默认为index
     * @apiParam (debug){Number=1,2,3} guide_sort 诱导第几次 视频通话诱导不用传
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {String} d.pushContent
     * @apiSuccess {String} d.chat_room_id
     * @apiSuccess {object[]} d.dialog
     * @apiSuccess {object} d.dialog.from_user
     * @apiSuccess {number} d.dialog.from_user.user_id
     * @apiSuccess {String} d.dialog.from_user.user_nickname
     * @apiSuccess {String} d.dialog.from_user.user_avatar
     * @apiSuccess {String} d.dialog.from_user.user_level
     * @apiSuccess {String} d.dialog.from_user.user_is_member
     * @apiSuccess {String} d.dialog.msg
     * @apiSuccess {number} d.dialog.time
     * @apiSuccess {number} d.dialog.dialog_id
     * @apiSuccess {String='word(文字)','image(图片)','video(视频)','voice(语音)','video_chat(视频聊天)'} d.dialog.type
     * @apiSuccess {String} d.dialog.source_url
     * @apiSuccess {String} d.dialog.extra
     * @apiSuccess {String} d.dialog.video_chat_status  视频聊天状态
     * @apiSuccess {String} d.dialog.video_chat_duration  视频聊天时长
     * @apiSuccess {String} d.dialog.video_chat_has_callback  视频聊天是否已回拨  Y 为已回拨 N 为未回拨
     * @apiSuccess {number} d.dialog.income
     * @apiSuccess {object} d.dialog.to_user
     * @apiSuccess {number} d.dialog.to_user.user_id
     * @apiSuccess {String} d.dialog.to_user.user_nickname
     * @apiSuccess {String} d.dialog.to_user.user_avatar
     * @apiSuccess {String} d.dialog.to_user.user_level
     * @apiSuccess {String} d.dialog.to_user.user_is_member
     * @apiSuccess {String} d.guide_id
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *           {
     *               "c": 0,
     *               "m": "请求成功",
     *               "d": {
     *                       "pushContent": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/08\/1533700478894.png",
     *                       "chat_room_id": "251_168",
     *                       "dialog": {
     *                               "from_user": {
     *                                   "user_id": "168",
     *                                   "user_nickname": "啦啦啦",
     *                                   "user_level": "1",
     *                                   "user_avatar": "http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/e1u7Ut4rUff6QDfsRXuTjJwpuqaEBeyBL8FC7bIu6fcuXkogvUBRYLVCIRFLQicgwxVVC3dibibSbkxM88BXsQVSA\/132",
     *                                   "user_is_member": "N"
     *                               },
     *                               "msg": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/08\/1533700478894.png",
     *                               "time": 1542003726,
     *                               "dialog_id": "2456",
     *                               "type": "image",
     *                               "source_url": null,
     *                               "extra": "",
     *                               "income": 0,
     *                               "to_user": {
     *                                   "user_id": "251",
     *                                   "user_nickname": "Dawn09101048222",
     *                                   "user_level": "1",
     *                                   "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/9012BAEA9B36E6AE8846D0EFE9C05A13\/100",
     *                                   "user_is_member": "Y"
     *                               }
     *                       },
     *                       "guide_id": '0'
     *               },
     *               "t": "1542003727"
     *           }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function sendAction( $nUserId = 0 )
    {

        $sChatRoomId    = $this->getParams('chat_room_id', 'string', '');
        $nToUserId      = $this->getParams('to_user_id', 'int', 0);
        $sContent       = $this->getParams('content', 'string', '');
        $sSendType      = $this->getParams('type', 'string', 'normal');
        $payChat        = $this->getParams('pay', 'int');
        $sMsgType       = $this->getParams('msg_type', 'string', 'word');
        $sExtra         = $this->getParams('extra', 'string', '');
        $sIsService     = $this->getParams('is_service', 'string', '');
        $sGuideLocation = $this->getParams('guide_location', 'string', 'index');
        $sGuideSort     = $this->getParams('guide_sort', 'int', 0);
        $sIsReadOnce    = $this->getParams('is_read_once', 'int', 0);
        $callback_flg   = FALSE;
        try {
            $sChatRoomId = UserChatDialog::getChatRoomId($nUserId, $nToUserId);
            if ( $nToUserId == 1 ) {
                throw new Exception(
                    '此功能正在开发中，敬请期待',
                    ResponseError::PARAM_ERROR
                );
            }
            if ( !in_array($sMsgType, UserChat::MSG_TYPE_ARR) ) {
                throw new Exception(
                    sprintf('msg_type %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }
            $oToUser = User::findFirst([
                'user_id = :user_id:',
                'bind' => [ 'user_id' => $nToUserId ]
            ]);
            if ( !$oToUser ) {
                throw new Exception(
                    sprintf('user_id %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }
            $oUser = User::findFirst($nUserId);
            if ( $oUser->user_is_deny_speak == 'Y' ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::USER_PROHIBIT_TALK),
                    ResponseError::USER_PROHIBIT_TALK
                );
            }

            // 判断用户接收用户是否免打扰
            $toUserSet = UserSet::findFirst($nToUserId);
            if ( $toUserSet->user_get_stranger_msg_flg == 'N' ) {

                // 添加接受用户对话
                $oToUserChatDialog = UserChatDialog::findFirst([
                    'user_id=:user_id: and to_user_id=:to_user_id:',
                    'bind' => [
                        'user_id'    => $nToUserId,
                        'to_user_id' => $nUserId,
                    ]
                ]);
                if ( !$oToUserChatDialog ) {
                    // 没有回复过  的陌生人
                    throw new Exception(
                        ResponseError::getError(ResponseError::NOT_GET_STRANGER_MSG),
                        ResponseError::NOT_GET_STRANGER_MSG
                    );
                }
            }

            $sendChatPrice  = 0;
            $sendChatIncome = 0;
            $pay_type       = 'S';
            $guideId        = '0';

            switch ( $sSendType ) {
                case 'guide':
                    if ( $oUser->user_coin + $oUser->user_free_coin > 20 ) {
                        $this->success();
                    }
                    $pay_type = 'G';
                    if ( $oUser->user_is_anchor == 'Y' ) {
                        // 主播不能请求诱导  由用户客户端发起请求 选择主播进行诱导
                        throw new Exception(
                            ResponseError::getError(ResponseError::PARAM_ERROR),
                            ResponseError::PARAM_ERROR
                        );
                    }
                    if ( $sGuideLocation != 'video' ) {
                        $adAgentArr = $this->getStayGuideMsgAgent();
                        if ( $oUser->user_invite_agent_id && !in_array($oUser->user_invite_agent_id, $adAgentArr) ) {
                            // 诱导
                            $this->success([]);
                        }
                    }
                    /**
                     * 用户定时请求诱导
                     * 1：此时内容为诱导话术id，如果为0则为第一次诱导，话术随机从话术库取一组 ，并发送该话术的第一句话
                     * 2. 诱导id存在，并能取到 则发送该话术的第二句话
                     * 3. 诱导id存在 并能取到 并且消息类型为image（代表第三句）
                     */
                    // 发送用户改为接受用户
                    $tmp       = $nToUserId;
                    $nToUserId = $nUserId;
                    $nUserId   = $tmp;
                    $oUser     = User::findFirst($nUserId);
                    $oToUser   = User::findFirst($nToUserId);
                    if ( $oUser->user_is_anchor == 'N' ) {
                        // 发送诱导的必须是主播
                        $this->success();
                    }
                    if ( $oUser->user_online_status == User::USER_ONLINE_STATUS_OFFLINE ) {
                        // 主播不在线的话 不发诱导
                        $this->success();
                    }
                    if ( $sMsgType == 'video' ) {
                        // 发送诱导 电话未接听
//                        $sContent = '❌ 未接听';
                        $sContent = '对方邀请您视频聊天';
                        $sMsgType = 'video_chat';
                        $sExtra   = serialize([
                            'video_chat_status'       => 1,
                            'video_chat_duration'     => 0,
                            'video_chat_has_callback' => 'Y',
                        ]);

                        // 添加诱导记录
                        $oUserGuideLog                       = new UserGuideLog();
                        $oUserGuideLog->guide_user_id        = $oToUser->user_id;
                        $oUserGuideLog->guide_anchor_user_id = $oUser->user_id;
                        $oUserGuideLog->guide_type           = UserGuideLog::TYPE_VIDEO;
                        $flg                                 = $oUserGuideLog->save();
                    } else {
                        $thisGuideMsgType = GuideMsgList::MSG_TYPE_EMPTY;
                        $thisGuideContent = '';
                        $thisGuideExtra   = '';
                        switch ( $sGuideSort ) {
                            case 1:
                                //第一句
                                $oUserLastGuideService = new services\UserLastGuideService($sGuideLocation, $nToUserId);
                                $lastGuideMsg          = $oUserLastGuideService->getData();
                                if ( $lastGuideMsg ) {
                                    // 非第一次 第一句话
                                    $oGuideMsgList = GuideMsgList::findFirst([
                                        'location_type = :location_type: AND third_msg_type != :third_msg_type:',
                                        'bind'  => [
                                            'location_type'  => $sGuideLocation,
                                            'third_msg_type' => $lastGuideMsg['third_msg_type']
                                        ],
                                        'order' => 'rand()'
                                    ]);
                                } else {
                                    // 第一次 第一句话
                                    $oGuideMsgList = GuideMsgList::findFirst([
                                        'location_type = :location_type:',
                                        'bind'  => [
                                            'location_type' => $sGuideLocation,
                                        ],
                                        'order' => 'rand()'
                                    ]);
                                }

                                if ( !$oGuideMsgList ) {
                                    // 没有话术
                                    $this->success();
                                }
                                $oUserLastGuideService->save($oGuideMsgList->toArray());
                                $thisGuideMsgType = $oGuideMsgList->first_msg_type;
                                $thisGuideContent = $oGuideMsgList->first_content;
                                $thisGuideExtra   = $oGuideMsgList->first_extra;
                                $guideId          = $oGuideMsgList->id;

                                // 添加诱导记录
                                $oUserGuideLog                       = new UserGuideLog();
                                $oUserGuideLog->guide_user_id        = $oToUser->user_id;
                                $oUserGuideLog->guide_anchor_user_id = $oUser->user_id;
                                $oUserGuideLog->guide_type           = $sGuideLocation == 'index' ? UserGuideLog::TYPE_STAY_INDEX : UserGuideLog::TYPE_STAY_PROFILE;
                                $oUserGuideLog->guide_config_id      = $guideId;
                                $oUserGuideLog->save();

                                // 添加主播统计诱导次数
                                $oAnchorStatService = new AnchorStatService($oUser->user_id);
                                $oAnchorStatService->save(AnchorStatService::GUIDE_MSG_TIMES, 1);
                                $oAnchorStatService->save(AnchorStatService::GUIDE_USER_COUNT, $oToUser->user_id);

                                break;
                            case 2:
                                $oGuideMsgList = GuideMsgList::findFirst(intval($sContent));
                                if ( !$oGuideMsgList ) {
                                    // 没有话术
                                    $this->success();
                                }
                                $thisGuideMsgType = $oGuideMsgList->second_msg_type;
                                $thisGuideContent = $oGuideMsgList->second_content;
                                $thisGuideExtra   = $oGuideMsgList->second_extra;
                                $guideId          = $oGuideMsgList->id;
                                break;
                            case 3:
                                $oGuideMsgList = GuideMsgList::findFirst(intval($sContent));
                                if ( !$oGuideMsgList ) {
                                    // 没有话术
                                    $this->success();
                                }
                                $thisGuideMsgType = $oGuideMsgList->third_msg_type;
                                $thisGuideContent = $oGuideMsgList->third_content;
                                $thisGuideExtra   = $oGuideMsgList->third_extra;
                                $guideId          = $oGuideMsgList->id;
                                break;
                            default:
                                $this->success();

                        }
                        switch ( $thisGuideMsgType ) {
                            case GuideMsgList::MSG_TYPE_EMPTY:
                                $this->success();
                                break;
                            case GuideMsgList::MSG_TYPE_VOICE:
                                $sContent = $thisGuideContent;
                                $sMsgType = 'voice';
                                $sExtra   = intval($thisGuideExtra);
                                break;
                            case GuideMsgList::MSG_TYPE_WORD:
                                $sContent = $thisGuideContent;
                                $sMsgType = 'word';
                                break;
                            case GuideMsgList::MSG_TYPE_IMAGE:
                                $oAnchorImage = AnchorImage::findFirst([
                                    'user_id = :user_id:',
                                    'bind'  => [
                                        'user_id' => $nUserId
                                    ],
                                    'order' => 'visible_type,rand()'
                                ]);
                                $sContent     = '';
                                if ( $oAnchorImage ) {
                                    $sContent = $oAnchorImage->img_src;
                                }
                                $sMsgType = 'image';
                                break;
                            case GuideMsgList::MSG_TYPE_VIDEO:
                                $oUserVideo = UserVideo::findFirst([
                                    'user_id = :user_id: AND is_show = 1 AND watch_type = "free"',
                                    'bind'  => [
                                        'user_id' => $nUserId
                                    ],
                                    'order' => 'rand()'
                                ]);
                                if ( $oUserVideo ) {
                                    $sContent = $oUserVideo->play_url;
                                    $sExtra   = $oUserVideo->cover;
                                } else {
                                    $oAnchor  = Anchor::findFirst([
                                        'user_id=:user_id:',
                                        'bind' => [ 'user_id' => $nUserId ]
                                    ]);
                                    $sContent = $oAnchor->anchor_video;
                                    $sExtra   = $oAnchor->anchor_video_cover;
                                }
                                $sMsgType = 'video';
                                break;
                        }
                        if ( empty($sContent) ) {
                            $this->success();
                        }
                    }
                    break;
                case 'say_hi':
                    $pay_type = 'S';
                    //打招呼
                    if ( $oUser->user_is_anchor == 'Y' ) {

                        $checkUserIds = $this->getCheckUserIds();
                        if ( in_array($nToUserId, $checkUserIds) ) {
//                        if($nToUserId == 66668830 or $nToUserId == 66881197){
                            // 审核账号 不接受打招呼
                            throw new Exception(
                                '发送成功',
                                ResponseError::USER_PROHIBIT_TALK
                            );
                        }
                        // 主播给用户打招呼
                        // 对所用用户来讲，1小时内只能sayHi 50 个用户，计时从最后一个开始
                        $singleTimes              = Kv::get(Kv::ANCHOR_SAY_HI_TOTAL_NUM);
                        $intervalTime             = Kv::get(Kv::ANCHOR_SAY_HI_TOTAL_INTERVAL);
                        $oAnchorTotalSayhiService = new AnchorTotalSayhiService($nUserId);
                        $data                     = $oAnchorTotalSayhiService->getData();
                        if ( $data && $data[ AnchorTotalSayhiService::KEY_TIMES ] != 0 && $data[ AnchorTotalSayhiService::KEY_TIMES ] % $singleTimes == 0 ) {
                            //除 $singleTimes 取余为0则需要判断最后一次记录时间 是否间隔不到一小时， 如果不到 则不能发送
                            $next_time = $data[ AnchorTotalSayhiService::KEY_LAST_TIME ] + $intervalTime - time();
                            if ( $next_time > 0 ) {
                                throw new Exception(ResponseError::getError(ResponseError::FORBIDDEN_TOTAL_SAY_HI, $singleTimes, 60, intval($next_time / 60)), ResponseError::FORBIDDEN_TOTAL_SAY_HI);
                            }
                        }
                        // 对单一用户 一天只有sayHi 一次
                        $oAnchorSayhiService = new AnchorSayhiService($nUserId);
                        $flg                 = $oAnchorSayhiService->save($nToUserId);
                        if ( $flg == 0 ) {
                            throw new Exception(ResponseError::getError(ResponseError::FORBIDDEN_SAY_HI), ResponseError::FORBIDDEN_SAY_HI);
                        }
                        $callback_flg = TRUE;
                        $gender       = 'female';
                        if ( $oToUser->user_sex == 1 ) {
                            $gender = 'male';
                        }
//                        $oContent = IsrobotTalk::findFirst([
//                            'isrobot_talk_type = :isrobot_talk_type: AND isrobot_talk_user_type = :isrobot_talk_user_type:',
//                            'bind' => ['isrobot_talk_type' => $gender,'isrobot_talk_user_type' => IsrobotTalk::USER_TYPE_ANCHOR],
//                            'order' => 'rand()'
//                        ]);
//                        $oContent = IsrobotTalk::findFirst([
//                            'isrobot_talk_type = :isrobot_talk_type:',
//                            'bind'  => [ 'isrobot_talk_type' => $gender ],
//                            'order' => 'rand()'
//                        ]);
                        $oContent = IsrobotTalk::findFirst([
                            'isrobot_talk_user_type = :isrobot_talk_user_type:',
                            'bind'  => [ 'isrobot_talk_user_type' => IsrobotTalk::USER_TYPE_ANCHOR ],
                            'order' => 'rand()'
                        ]);
                        $sContent = $oContent->isrobot_talk_content;
                    } else {
                        // 用户给主播打招呼
                        $gender = 'female';
                        if ( $oToUser->user_sex == 1 ) {
                            $gender = 'male';
                        }
//                        $oContent = IsrobotTalk::findFirst([
//                            'isrobot_talk_type = :isrobot_talk_type: AND isrobot_talk_user_type = :isrobot_talk_user_type:',
//                            'bind' => ['isrobot_talk_type' => $gender,'isrobot_talk_user_type' => IsrobotTalk::USER_TYPE_USER],
//                            'order' => 'rand()'
//                        ]);
//                        $oContent = IsrobotTalk::findFirst([
//                            'isrobot_talk_type = :isrobot_talk_type:',
//                            'bind'  => [ 'isrobot_talk_type' => $gender ],
//                            'order' => 'rand()'
//                        ]);
                        $oContent = IsrobotTalk::findFirst([
                            'isrobot_talk_user_type = :isrobot_talk_user_type:',
                            'bind'  => [ 'isrobot_talk_user_type' => IsrobotTalk::USER_TYPE_USER ],
                            'order' => 'rand()'
                        ]);
                        $sContent = $oContent->isrobot_talk_content;
                    }
                    break;
                case 'normal':


                    if ( $oToUser->user_is_superadmin == 'C' ) {
                        // 和客服聊天 没有限制
                        $pay_type = 'F';

                    } else if ( $oUser->user_is_superadmin == 'Y' || $oUser->user_is_superadmin == 'C' ) {
                        // 巡管以及官方人员发消息 无限制

                    } else {
                        // 主播给用户发发消息限制  只有当用户有发送给主播时才能发
                        if ( $oUser->user_is_anchor == 'Y' ) {
                            if ( $oToUser->user_is_anchor == 'Y' ) {
                                throw new Exception(
                                    ResponseError::getError(ResponseError::ANCHOR_CAN_NOT_CHAT_TO_ANCHOR),
                                    ResponseError::ANCHOR_CAN_NOT_CHAT_TO_ANCHOR
                                );
                            }
                            $pay_type = 'A';
                            $oAnchor  = Anchor::findFirst([
                                'user_id=:user_id:',
                                'bind' => [ 'user_id' => $nUserId ]
                            ]);
                            if ( $oAnchor->anchor_is_positive == 'N' ) {
                                // 非积极主播 是不能主动给用户发消息
                                $userToAnchorChat    = UserChat::findFirst([
                                    'user_chat_room_id = :user_chat_room_id: and user_chat_send_user_id = :user_chat_send_user_id:',
                                    'bind' => [
                                        'user_chat_room_id'      => $sChatRoomId,
                                        'user_chat_send_user_id' => $nToUserId
                                    ]
                                ]);
                                $oUserPrivateChatLog = UserPrivateChatLog::findFirst([
                                    'chat_log_user_id = :chat_log_user_id: and chat_log_anchor_user_id = :chat_log_anchor_user_id:',
                                    'bind' => [
                                        'chat_log_user_id'        => $nToUserId,
                                        'chat_log_anchor_user_id' => $nUserId
                                    ]
                                ]);
                                if ( !$userToAnchorChat && !$oUserPrivateChatLog ) {
                                    // 用户没有给主播发过消息
                                    throw new Exception(
                                        ResponseError::getError(ResponseError::ANCHOR_CAN_NOT_CHAT_TO_USER),
                                        ResponseError::ANCHOR_CAN_NOT_CHAT_TO_USER
                                    );
                                }
                            }
                        } else {
                            if ( $oToUser->user_is_anchor == 'N' ) {
                                throw new Exception(
                                    ResponseError::getError(ResponseError::USER_CAN_NOT_CHAT_TO_USER),
                                    ResponseError::USER_CAN_NOT_CHAT_TO_USER
                                );
                            }
                            $pay_type         = 'V';
                            $agentFreeChatFlg = 'N';
                            if ( $oUser->user_invite_agent_id ) {
                                $oAgent = Agent::findFirst($oUser->user_invite_agent_id);
                                if ( $oAgent ) {
                                    $agentFreeChatFlg = $oAgent->chat_free_flg;
                                }
                            }
                            $intimateData = UserIntimate::getIntimateLevel($nToUserId, $nUserId, $oToUser->user_is_anchor, $oUser->user_is_anchor);
                            // 用户发消息  VIP用户 可以无限发 普通用户只能发送10条  (代理商可以设置为不需要付费)  亲密度等级达到一定值 聊天不用钱
                            if ( $oUser->user_member_expire_time <= time() && $agentFreeChatFlg == 'N' && $intimateData['level_free_chat'] == 'N' ) {
                                //不是vip不能发视频，语音 图片
                                if ( in_array($sMsgType, UserChat::VIP_MSG_TYPE_ARR) ) {
                                    throw new Exception(
                                        sprintf('msg_type %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                                        ResponseError::PARAM_ERROR
                                    );
                                }


                                $sendChatPrice = intval(Kv::get(Kv::CHAT_PRICE));
                                if ( $payChat ) {
                                    if ( $oUser->user_is_superadmin == 'S' ) {
                                        $this->error(10002, '该账号无此权限哦');
                                    }
                                    $oAnchor        = Anchor::findFirst([
                                        'user_id=:user_id:',
                                        'bind' => [ 'user_id' => $oToUser->user_id ]
                                    ]);
                                    $nRatio         = $oAnchor->getCoinToDotRatio($oToUser, Anchor::RATIO_CHAT);
                                    $sendChatIncome = sprintf('%.2f', $sendChatPrice * ($nRatio / 100));
                                    // 付费
                                    $flg = UserFinanceLog::addSendChat($oUser, $sendChatPrice, $oToUser, $nRatio);
                                    if ( $flg !== TRUE ) {
                                        throw new Exception(
                                            $flg['m'],
                                            $flg['c']
                                        );
                                    }
                                    $pay_type = 'P';
                                } else {

                                    $oUserTodayChatService = new services\UserTodayChatService($nUserId);
                                    $sendCount             = $oUserTodayChatService->save();
                                    // 如果正在上架中  默认免费3条
                                    $appInfo = $this->getAppInfo();
                                    if ( $appInfo['on_publish'] == 'Y' ) {
                                        $maxSendChatCount = max(intval(Kv::get(Kv::CHAT_FREE_COUNT)), 3);
                                    } else {
                                        $maxSendChatCount = intval(Kv::get(Kv::CHAT_FREE_COUNT));
                                    }
                                    if ( $sendCount > $maxSendChatCount ) {
                                        //大于最大值了  不能直接发送了 需要买了
                                        throw new Exception(
                                            sprintf(ResponseError::getError(ResponseError::USER_CHAT_SHOULD_PAY), $sendChatPrice),
                                            ResponseError::USER_CHAT_SHOULD_PAY
                                        );
                                    }
                                    $sendChatPrice = 0;
                                    $pay_type      = 'F';
                                }
                            }
                        }
                        // 用户不能跟用户发消息
                        if ( $oUser->user_is_anchor == 'N' && $oToUser->user_is_anchor == 'N' ) {
                            throw new Exception(
                                ResponseError::getError(ResponseError::ANCHOR_CAN_NOT_CHAT_TO_USER),
                                ResponseError::ANCHOR_CAN_NOT_CHAT_TO_USER
                            );
                        }
                    }
                default:
            }
            if ( $sContent == '' ) {
                throw new Exception(sprintf('%s %s', 'content', ResponseError::getError(ResponseError::PARAM_ERROR)), ResponseError::PARAM_ERROR);
            }
            $black = UserBlack::findFirst([
                'user_id = :user_id: and to_user_id = :to_user_id:',
                'bind' => [
                    'user_id'    => $nUserId,
                    'to_user_id' => $nToUserId
                ]
            ]);
            if ( $black ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::IN_BLACK),
                    ResponseError::IN_BLACK
                );
            }
            $black = UserBlack::findFirst([
                'user_id = :user_id: and to_user_id = :to_user_id:',
                'bind' => [
                    'user_id'    => $nToUserId,
                    'to_user_id' => $nUserId
                ]
            ]);
            if ( $black ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::HAS_BEEN_BLACKED),
                    ResponseError::HAS_BEEN_BLACKED
                );
            }
            // 添加消息
            $oUserChat                              = new UserChat();
            $oUserChat->user_chat_room_id           = $sChatRoomId;
            $oUserChat->user_chat_send_user_id      = $nUserId;
            $oUserChat->user_chat_receiv_user_id    = $nToUserId;
            $oUserChat->user_chat_content           = $sContent;
            $oUserChat->user_chat_pay_type          = $pay_type;
            $oUserChat->user_chat_type              = $sMsgType;
            $oUserChat->user_chat_price             = $sendChatPrice;
            $oUserChat->user_chat_income            = $sendChatIncome;
            $oUserChat->user_chat_extra             = $sExtra;
            $oUserChat->user_chat_send_read_type    = $sIsReadOnce ? 'once' : 'normal';
            $oUserChat->user_chat_receive_read_type = $sIsReadOnce ? 'once' : 'normal';
            $aPushMessage                           = $oUserChat->addMessage($oUser, $oToUser);
            $aPushMessage['guide_id']               = $guideId;

            if ( $sSendType != 'guide' ) {
// 如果接收消息的用户是主播主播且是签约主播 且当前是签约时段
                if ( $oToUser->user_is_anchor == 'Y' ) {
                    AnchorSignStat::signAnchorStatAddReceiveUserCount($oToUser, $nUserId);
                }

                // 如果发送消息的用户是主播主播且是签约主播 且当前是签约时段
                if ( $oUser->user_is_anchor == 'Y' ) {
                    AnchorSignStat::signAnchorStatAddReplyUserCount($oUser, $nToUserId);
                }

                if ( $oUser->user_is_superadmin == 'C' && empty($sIsService) ) {
                    // 发送者是客服 并且是手动的  key为 customer_service_manual:客服id:用户id
                    $customerServiceManualKey = sprintf('customer_service_manual:%d:%d', $nUserId, $nToUserId);
                    $this->redis->set($customerServiceManualKey, time(), 3600);
                }

                if ( $oToUser->user_is_superadmin == 'C' ) {
                    // 如果是客服，且人工客服半小时内没有手动回复，则需要自动回复   key为 customer_service_manual:客服id:用户id
                    $customerServiceManualKey = sprintf('customer_service_manual:%d:%d', $nToUserId, $nUserId);
                    $lastTime                 = $this->redis->get($customerServiceManualKey);
                    if ( time() - $lastTime > 1800 ) {
                        // 队列进行
                        $oTaskQueueService = new TaskQueueService();
                        $oTaskQueueService->enQueue([
                            'task'   => 'chat',
                            'action' => 'customerServiceAuto',
                            'param'  => [
                                'user_chat_id' => $oUserChat->user_chat_id,
                            ],
                        ]);
                    }
                }


                $toUserVersion      = $this->redis->hGet('user_app_version', $nToUserId);
                $toUserIsNewVersion = $this->checkVersionMatch($toUserVersion);
                if ( in_array($aPushMessage['dialog']['type'], UserChat::VIP_MSG_TYPE_ARR) && !$toUserIsNewVersion ) {
                    $aPushMessage['dialog']['msg']  = '您当前的APP版本过低，暂不支持查看该多媒体消息，请更新到最新版本或等待版本更新';
                    $aPushMessage['dialog']['type'] = 'word';
                }
            }


            $this->timServer->setUid($nToUserId);
            $this->timServer->setAccountId($nUserId);
            $this->timServer->sendChatSignal($aPushMessage);
            $row = $aPushMessage;
        } catch ( Exception $e ) {
            if ( $callback_flg ) {
                $oAnchorSayhiService->delete_item($nToUserId);
            }
            $this->error($e->getCode(), $e->getMessage());
        }
        if ( $sSendType == 'say_hi' ) {
            $oAnchorTotalSayhiService->save(time());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/chat/destroyChat
     * @api {post} /user/chat/destroyChat 焚毁消息
     * @apiName destroyChat
     * @apiGroup Chat
     * @apiDescription 焚毁消息
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} dialog_id 聊天id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} dialog_id 聊天id
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
    public function destroyChatAction( $nUserId = 0 )
    {
        $sDialogId = $this->getParams('dialog_id');
        try {
            $oUserChat = UserChat::findFirst($sDialogId);
            if ( $oUserChat->user_chat_send_read_type == 'normal' || !in_array($nUserId, [
                    $oUserChat->user_chat_send_user_id,
                    $oUserChat->user_chat_receiv_user_id
                ]) ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            if ( $nUserId == $oUserChat->user_chat_send_user_id ) {
                if ( $oUserChat->user_chat_send_read_type != 'once' ) {
                    $this->success();
                }
                $oUserChat->user_chat_send_read_type = 'destroy';
            } else if ( $nUserId == $oUserChat->user_chat_receiv_user_id ) {
                if ( $oUserChat->user_chat_receive_read_type != 'once' ) {
                    $this->success();
                }
                $oUserChat->user_chat_receive_read_type = 'destroy';
            }
            if ( !$oUserChat->save() ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserChat->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }

    /**
     * systemDialogListAction 最近系统对话列表
     *
     * @param int $nUserId
     */
    public function systemDialogListAction( $nUserId = 0 )
    {
        try {
            if ( $this->getParams('app_name') == 'tanhua' ) {
                $row['system_dialog'][] = [
                    'content' => '优惠充值',
                    'unread'  => '0',
                    'time'    => time(),
                    'type'    => UserSystemMessageDialog::TYPE_SYSTEM,
                ];
                $this->success($row);
            }
            $oUserSystemMessageDialog = UserSystemMessageDialog::findFirst([
                'user_id=:user_id: and user_system_message_type=:type:',
                'bind' => [
                    'user_id' => $nUserId,
                    'type'    => 'system',
                ]
            ]);
            $row['system_dialog']     = [];
            $oUser                    = User::findFirst($nUserId);
            $oSystemMessage           = SystemMessage::findFirst([
                'system_message_create_time >= :time: and system_message_push_type=0 order by system_message_id desc',
                'bind' => [
                    'time' => $oUser->user_create_time,
                ]
            ]);
            if ( isset($oSystemMessage->system_message_content) ) {
                $aData                                  = json_decode($oSystemMessage->system_message_content, 1);
                $oSystemMessage->system_message_content = isset($aData['data']['content']) ? $aData['data']['content'] : '';
            }
            if ( empty($oUserSystemMessageDialog) ) {
                // 初始化
                $oUserSystemMessageDialog                                         = new UserSystemMessageDialog();
                $oUserSystemMessageDialog->user_id                                = $nUserId;
                $oUserSystemMessageDialog->system_message_content                 = isset($oSystemMessage->system_message_id) ? $oSystemMessage->system_message_content : '';
                $oUserSystemMessageDialog->system_message_id                      = isset($oSystemMessage->system_message_id) ? $oSystemMessage->system_message_id : '0';
                $oUserSystemMessageDialog->user_system_message_unread             = isset($oSystemMessage->system_message_id) ? SystemMessage::count([
                    'system_message_create_time >= :time: and system_message_push_type=0',
                    'bind' => [
                        'time' => $oUser->user_create_time,
                    ]
                ]) : '0';
                $oUserSystemMessageDialog->user_system_message_dialog_update_time = isset($oSystemMessage->system_message_create_time) ? $oSystemMessage->system_message_create_time : '0';
                $oUserSystemMessageDialog->save();
            } else if ( $oSystemMessage && $oUserSystemMessageDialog->system_message_id < $oSystemMessage->system_message_id ) {
                // 更新消息
                $oUserSystemMessageDialog->system_message_content                 = $oSystemMessage->system_message_content;
                $oUserSystemMessageDialog->user_system_message_unread             += SystemMessage::count([
                    'system_message_push_type=0 AND system_message_id>:system_message_id:',
                    'bind' => [
                        'system_message_id' => $oUserSystemMessageDialog->system_message_id,
                    ],
                ]);
                $oUserSystemMessageDialog->system_message_id                      = $oSystemMessage->system_message_id;
                $oUserSystemMessageDialog->user_system_message_dialog_update_time = $oSystemMessage->system_message_create_time;
                $oUserSystemMessageDialog->save();
            }
            $row['system_dialog'][] = [
                'content' => $oUserSystemMessageDialog->system_message_content,
                'unread'  => (string)$oUserSystemMessageDialog->user_system_message_unread,
                'time'    => $oUserSystemMessageDialog->user_system_message_dialog_update_time,
                'type'    => isset($oUserSystemMessageDialog->user_system_message_type) ? $oUserSystemMessageDialog->user_system_message_type : UserSystemMessageDialog::TYPE_SYSTEM,
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * systemDialogAction 系统对话详情
     *
     * @param int $nUserId
     */
    public function systemDialogAction( $nUserId = 0 )
    {
        $nPgaesize        = $this->getParams('pagesize', 'int', 20);
        $sType            = $this->getParams('type', 'string', UserSystemMessageDialog::TYPE_SYSTEM);
        $nSystemMessageId = $this->getParams('dialog_id', 'int', 0);
        $nPage            = $this->getParams('page', 'int', 1);
        try {
//            $checkUserIds = $this->getCheckUserIds();
//            if ( in_array($nUserId, $checkUserIds) ) {
//                $sType = 'checkUser';
//            }
            $oUser = User::findFirst($nUserId);
            switch ( $sType ) {
                case 'checkUser':
                    $row['system_dialog'] = [];
                    break;
                // 系统消息
                case UserSystemMessageDialog::TYPE_SYSTEM:
                default:
//                    $builder = $this->modelsManager->createBuilder()->from([ 'sm' => SystemMessage::class ])->leftJoin(UserSystemMessage::class, sprintf('usm.system_message_id=sm.system_message_id AND usm.user_id="%d"', $nUserId), 'usm')->columns('sm.system_message_id dialog_id,sm.system_message_content msg,sm.system_message_create_time time')->where('(sm.system_message_push_type=0 and sm.system_message_create_time >= :time:) OR usm.user_id IS NOT NULL', [
//                        'time' => $oUser->user_create_time,
//                    ]);

                    if ( $oUser->user_is_anchor == 'Y' ) {
                        $builder = $this->modelsManager->createBuilder()
                            ->from([ 'sm' => SystemMessage::class ])
                            ->columns('sm.system_message_id dialog_id,sm.system_message_content msg,sm.system_message_create_time time')
                            ->where('(sm.user_id like :user_id: or sm.system_message_push_type = 0 or sm.system_message_push_type = 2) and sm.system_message_create_time >= :time:', [
                                'time'    => $oUser->user_create_time,
                                'user_id' => "%" . $nUserId . "%",
                            ]);
                    } else {
                        $builder = $this->modelsManager->createBuilder()
                            ->from([ 'sm' => SystemMessage::class ])
                            ->columns('sm.system_message_id dialog_id,sm.system_message_content msg,sm.system_message_create_time time')
                            ->where('(sm.user_id like :user_id: OR sm.system_message_push_type = 0 OR ( sm.system_message_push_type = 1 AND sm.user_id like :user_id: )) and sm.system_message_create_time >= :time:', [
                                'time'    => $oUser->user_create_time,
                                'user_id' => "%" . $nUserId . "%",
                            ]);
                    }
                    if ( $nSystemMessageId != 0 ) {
                        $builder->andWhere('sm.system_message_id<:system_message_id:', [ 'system_message_id' => $nSystemMessageId ])->limit($nPgaesize);
                    } else if ( $nPage != 0 ) {
                        $builder->limit($nPgaesize, (max($nPage - 1, 0)) * $nPgaesize);
                    }
                    $row['system_dialog'] = $builder->orderBy('sm.system_message_id desc')->getQuery()->execute();
                    if ( $nSystemMessageId == 0 && $this->getParams('app_name') == 'tanhua' ) {
                        $row['system_dialog'] = $row['system_dialog']->toArray();
                        $rechrageUrl          = sprintf('%s/pay_new.php?uid=%s', $this->config->application->h5_charge_url, $nUserId);
                        $rechrgeSystemMsg     = [
                            'dialog_id' => '0',
                            'msg'       => json_encode([
                                'type' => 'general',
                                'data' => [
                                    'content' => '置顶：由于苹果政策原因，请点击此处: ' . $rechrageUrl . '，跳转优惠充值页面，获得更多优惠充值请联系客服：TTbaby02',
                                    'url'     => $rechrageUrl
                                ],
                            ])
                        ];
                        array_unshift($row['system_dialog'], $rechrgeSystemMsg);
                    }
                    if ( $nSystemMessageId == 0 && time() >= strtotime('2019-02-04') && time() < strtotime('2019-02-18') ) {
//                        if( $nSystemMessageId == 0 && in_array($nUserId,[66667615,66668830]) ){
                        if ( $this->getParams('app_name') != 'tanhua' ) {
                            // 上面未将数据转为数组
                            $row['system_dialog'] = $row['system_dialog']->toArray();
                        }
                        $activityUrl = sprintf('http://activity.sxypaopao.com/index/index/index/user/%s', $nUserId);
                        $activityMsg = [
                            'dialog_id' => '0',
                            'msg'       => json_encode([
                                'type' => 'general',
                                'data' => [
                                    'content' => '金猪迎春开门红，万元红包大放送。感谢您支持泡泡，春节活动来袭，每位用户都可参与哦。详情请看首页Banner或者戳这里。',
                                    'url'     => $activityUrl
                                ],
                            ])
                        ];
                        array_unshift($row['system_dialog'], $activityMsg);
                    }

                    // 将未读数至0
                    if ( $nSystemMessageId == 0 ) {
                        $oUserSystemMessageDialog = UserSystemMessageDialog::findFirst([
                            'user_id=:user_id: and user_system_message_type=:type:',
                            'bind' => [
                                'user_id' => $nUserId,
                                'type'    => $sType,
                            ]
                        ]);
                        if ( $oUserSystemMessageDialog && $oUserSystemMessageDialog->user_system_message_unread != 0 ) {
                            $oUserSystemMessageDialog->user_system_message_unread = 0;
                            $oUserSystemMessageDialog->save();
                        }
                    }
                    break;
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * ignoreUnreadAction 忽略未读数
     *
     * @param int $nUserId
     */
    public function ignoreUnreadAction( $nUserId = 0 )
    {
        $aParam = $this->getParams();
        try {
            if ( isset($aParam['chat_room_id']) ) {
                $sChatRoomId = $aParam['chat_room_id'];
                // 用户对话
                if ( $sChatRoomId != '' ) {
                    $oUserChatDialog = UserChatDialog::findFirst([
                        'user_id=:user_id: and user_chat_room_id=:user_chat_room_id:',
                        'bind' => [
                            'user_id'           => $nUserId,
                            'user_chat_room_id' => $sChatRoomId,
                        ]
                    ]);
                    $aChatRoomId     = array_values(array_diff(explode('_', $sChatRoomId), [ $nUserId ]));
                    if ( $oUserChatDialog === FALSE ) {
                        $oUserChatDialog                    = new UserChatDialog();
                        $oUserChatDialog->user_id           = $nUserId;
                        $oUserChatDialog->user_chat_unread  = '0';
                        $oUserChatDialog->user_chat_room_id = $sChatRoomId;
                        $oUserChatDialog->to_user_id        = $aChatRoomId[0];
                        $oUserChatDialog->save();
                    }
                    if ( $oUserChatDialog->user_chat_unread != 0 ) {
                        $oUserChatDialog->user_chat_unread = 0;
                        $oUserChatDialog->save();
                    }
                } else {
                    $phql = sprintf('UPDATE %s SET user_chat_unread=0 WHERE user_id=:user_id:', UserChatDialog::class);
                    $this->modelsManager->executeQuery($phql, [ 'user_id' => $nUserId ]);
//                    忽略视频消息未读数
                    $videoInviterSql = sprintf('UPDATE %s SET inviter_unread=0 WHERE inviter_id=:user_id:', UserPrivateChatDialog::class);
                    $this->modelsManager->executeQuery($videoInviterSql, [ 'user_id' => $nUserId ]);
                    $videoInviteeSql = sprintf('UPDATE %s SET invitee_unread=0 WHERE invitee_id=:user_id:', UserPrivateChatDialog::class);
                    $this->modelsManager->executeQuery($videoInviteeSql, [ 'user_id' => $nUserId ]);
                }
            }
            if ( isset($aParam['type']) ) {
                $sType = $aParam['type'];
                // 系统对话
                if ( $sType != '' ) {
                    $oUserSystemMessageDialog = UserSystemMessageDialog::findFirst([
                        'user_id=:user_id: and user_system_message_type=:type:',
                        'bind' => [
                            'user_id' => $nUserId,
                            'type'    => $sType,
                        ]
                    ]);
                    if ( $oUserSystemMessageDialog === FALSE ) {
                        $oUserSystemMessageDialog                             = new UserSystemMessageDialog();
                        $oUserSystemMessageDialog->user_id                    = $nUserId;
                        $oUserSystemMessageDialog->type                       = $sType;
                        $oUserSystemMessageDialog->user_system_message_unread = '0';
                        $oUserSystemMessageDialog->save();
                    }
                    if ( $oUserSystemMessageDialog->user_system_message_unread != 0 ) {
                        $oUserSystemMessageDialog->user_system_message_unread = '0';
                        $oUserSystemMessageDialog->save();
                    }
                } else {
                    $phql = sprintf('UPDATE %s SET user_system_message_unread=0 WHERE user_id=:user_id:', UserSystemMessageDialog::class);
                    $this->modelsManager->executeQuery($phql, [ 'user_id' => $nUserId ]);
                    $video_sql = sprintf('UPDATE %s SET is_read = 1 WHERE user_id=:user_id:', UserVideoMessage::class);
                    $this->modelsManager->executeQuery($video_sql, [ 'user_id' => $nUserId ]);
                }
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/chat/unread
     * @api {get} /user/chat/unread 未读消息数
     * @apiName chat-unread
     * @apiGroup Message
     * @apiDescription 未读消息数
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
     * @apiSuccess {object} d.unread
     * @apiSuccess {number} d.unread.total   总数
     * @apiSuccess {number} d.unread.user_chat   聊天消息
     * @apiSuccess {number} d.unread.system_message  系统消息
     * @apiSuccess {number} d.unread.video_message   小视频消息
     * @apiSuccess {number} d.unread.video_chat_message  视频聊天消息
     * @apiSuccess {number} d.unread.posts_message  动态消息
     * @apiSuccess {number} d.unread.notify_unread  通知消息
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *    {
     *        "c": 0,
     *        "m": "请求成功",
     *        "d": {
     *                "unread": {
     *                    "total": "99",
     *                    "user_chat": "30",
     *                    "system_message": "12",
     *                    "video_message": "0",
     *                    "video_chat_message": "57",
     *                    "posts_message": "0"
     *            }
     *        },
     *        "t": "1550373274"
     *    }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function unreadAction( $nUserId = 0 )
    {
        $row = $this->getUserMessageUnread(NULL, $nUserId);
        $this->success($row);
    }

    /**
     * deleteUserDialogAction 删除用户对话框
     *
     * @param int $nUserId
     */
    public function deleteUserDialogAction( $nUserId = 0 )
    {
        $sChatRoomId = $this->getParams('chat_room_id', 'string', '');
        if ( $sChatRoomId == '' ) {
            $this->error(ResponseError::PARAM_ERROR, sprintf('chat_room_id %s', ResponseError::getError(ResponseError::PARAM_ERROR)));
        }
        $phql = sprintf('DELETE FROM %s WHERE user_id=:user_id: and user_chat_room_id=:chat_room_id:', UserChatDialog::class);
        $this->modelsManager->executeQuery($phql, [
            'user_id'      => $nUserId,
            'chat_room_id' => $sChatRoomId,
        ]);
        $this->success();
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/chat/deleteUserDialogAll
     * @api {get} /user/chat/deleteUserDialogAll 删除所有用户对话框
     * @apiName deleteUserDialogAll
     * @apiGroup Chat
     * @apiDescription 删除所有用户对话框
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
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
    public function deleteUserDialogAllAction( $nUserId = 0 )
    {
        $phql = sprintf('DELETE FROM %s WHERE user_id=:user_id:', UserChatDialog::class);
        $this->modelsManager->executeQuery($phql, [
            'user_id' => $nUserId,
        ]);
        $this->success();
    }

    /**
     * deleteSystemDialogAction 删除系统对话框
     *
     * @param int $nUserId
     */
    public function deleteSystemDialogAction( $nUserId = 0 )
    {
        $sType = $this->getParams('type', 'string', UserSystemMessageDialog::TYPE_SYSTEM);
        $phql  = sprintf('DELETE FROM %s WHERE user_id=:user_id: and user_system_message_type=:type:', UserSystemMessageDialog::class);
        $this->modelsManager->executeQuery($phql, [
            'user_id' => $nUserId,
            'type'    => $sType,
        ]);
        $this->success();
    }


    /**
     * 用户批量给主播打招呼
     */
    public function sayHiAction( $nUserId = 0 )
    {
        try {
            $checkFlg = Kv::get(Kv::USER_SAY_HI_FLG);
            if ( $checkFlg == 'Y' ) {
                //判断用户是否今天已经批量打过招呼
                $oAnchorSayhiService = new UserSayhiService($nUserId);
                $flg                 = $oAnchorSayhiService->save(time());
                if ( !$flg ) {
                    throw new Exception(ResponseError::getError(ResponseError::FORBIDDEN_SAY_HI_USER), ResponseError::FORBIDDEN_SAY_HI_USER);
                }
                $checkUserIds = $this->getCheckUserIds();
                if ( !in_array($nUserId, $checkUserIds) ) {
                    // 队列进行
                    $oTaskQueueService = new TaskQueueService();
                    $oTaskQueueService->enQueue([
                        'task'   => 'chat',
                        'action' => 'userSayHi',
                        'param'  => [
                            'user_id' => $nUserId,
                        ],
                    ]);
                }
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }

    /**
     * 视频聊天价格设置列表
     */
    public function chatPaySettingListAction( $nUserId = 0 )
    {
        $anchor = Anchor::findFirst([
            'user_id =:user_id:',
            'bind' => [ 'user_id' => $nUserId ]
        ]);

        $minPrice = Kv::get(Kv::PRIVATE_PRICE_MIN);

        // 聊天设置最大值 根据主播等级配置
        $maxPrice = LevelConfig::getAnchorMaxPrice($anchor->anchor_level);

        $maxPrice = max($maxPrice, $minPrice);

        $minPrice = intval(intval(Kv::get(Kv::PRIVATE_PRICE_MIN)) / 10) * 10;
        $maxPrice = intval(intval($maxPrice) / 10) * 10;

        $list = [];
        for ( $price = $minPrice; $price <= $maxPrice; $price += 10 ) {
            $list[] = [
                'price' => $price
            ];
        }
        $this->success($list);
    }



    /*-------------------抢单功能------------------------*/

    /**
     * 诱导视频支付
     */
    public function guidePayAction( $nUserId = 0 )
    {
        $sGuideVideoId = $this->getParams('guide_video_id', 'int');
        try {
            $oUser = User::findFirst($nUserId);
            // 价格为后台设置的赠送金币
            $price = intval(Kv::get(Kv::REGISTER_REWARD_COIN));
            if ( $oUser->user_free_coin < $price ) {
                $price = $oUser->user_free_coin;
            }
            if ( $price == 0 ) {
                throw new Exception(ResponseError::getError(ResponseError::USER_COIN_NOT_ENOUGH), ResponseError::USER_COIN_NOT_ENOUGH);
            }
            $oUserGuideVideo = UserGuideVideo::findFirst($sGuideVideoId);
            if ( !$oUserGuideVideo ) {
                $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
            }

            $flg = UserFinanceLog::addGuidePay($oUser, $price, $oUserGuideVideo);
            if ( $flg !== TRUE ) {
                throw new Exception(
                    $flg['m'],
                    $flg['c']
                );
            }
        } catch ( \Phalcon\Db\Exception $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( \PDOException $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * 用户进入匹配大厅
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/chat/matchCenter
     * @api {post} /user/chat/matchCenter 用户进入匹配大厅
     * @apiName matchCenter
     * @apiGroup Chat
     * @apiDescription 用户进入匹配大厅
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} is_beauty 是否为高颜值  1为选择
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} is_beauty 是否为高颜值  1为选择
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
    public function matchCenterAction( $nUserId = 0 )
    {
        $sIsAll       = $this->getParams('is_all', 'string', '');
        $sIsBeauty    = $this->getParams('is_beauty', 'string', '');
        $sDispatchFlg = $this->getParams('dispatch_flg', 'string', 'Y');
        if ( $sIsAll ) {
            $sDispatchFlg = 'N';
        }
        $this->log->info('matchCenter:' . json_encode([
                'is_all'       => $sIsAll,
                'is_beauty'    => $sIsBeauty,
                'dispatch_flg' => $sDispatchFlg
            ]));
        $checkUserIds = $this->getCheckUserIds();
        if ( in_array($nUserId, $checkUserIds) ) {
            $this->success();
        }
        $this->forceUpdate($nUserId);
        try {
            $oUser = User::findFirst($nUserId);
            if ( $sIsBeauty && $oUser->user_member_expire_time < time() ) {
                throw new Exception(ResponseError::getError(ResponseError::NOT_VIP), ResponseError::NOT_VIP);
            }
            //判断是否为新用户 是否开启诱导匹配视频 并且是否进入过诱导匹配视频
            if ( $oUser->user_total_coin == 0 ) {
                if ( $oUser->user_free_coin == 0 && $oUser->user_free_match_time == 0 ) {
                    throw new Exception(ResponseError::getError(ResponseError::USER_COIN_NOT_ENOUGH), ResponseError::USER_COIN_NOT_ENOUGH);
                }
                $guideVideoTime = intval(Kv::get(Kv::NEW_USER_VIDEO_PLAY_TIME));
                if ( $guideVideoTime > 0 ) {
                    $oUserGuideVideo = UserGuideVideo::findFirst([
                        'order' => 'rand()'
                    ]);
                    if ( $oUserGuideVideo ) {
                        // 后台设置了视频； 查询是否进入过
                        $oUserGuideVideoLog = UserGuideVideoLog::findFirst([
                                'user_id = :user_id:',
                                'bind' => [
                                    'user_id' => $nUserId
                                ]
                            ]
                        );
                        if ( !$oUserGuideVideoLog ) {
                            //没有进入过
                            $row = [
                                'price'          => intval(Kv::get(Kv::REGISTER_REWARD_COIN)),
                                'play_time'      => $guideVideoTime,
                                'play_url_id'    => $oUserGuideVideo->id,
                                'play_url'       => $oUserGuideVideo->video_url,
                                'anchor_user_id' => $oUserGuideVideo->anchor_user_id,
                            ];
                            $this->success($row);
                        }
                    }
                }
            } else {
                if ( !$sIsBeauty ) {
//                    全部用户都优先红人
//                    $sIsAll = TRUE;
                }
            }
            $matchPrice = intval(Kv::get(Kv::CHAT_MATCH_PRICE));
            if ( $oUser->user_free_match_time == 0 && $oUser->user_coin + $oUser->user_free_coin < $matchPrice ) {
                // 余额不足
                throw new Exception(ResponseError::getError(ResponseError::USER_COIN_NOT_ENOUGH), ResponseError::USER_COIN_NOT_ENOUGH);
            }
            if ( $oUser->user_is_superadmin == 'S' ) {
                $this->error(10002, '该账号暂不支持此功能哦');
            }
            $matchCenterRoomId = Kv::get(Kv::MATCH_CENTER_ROOM_ID);
            if ( $sIsAll ) {
                $matchCenterRoomId .= '_all';
            }
            // 如果使用的是免费时长 需要优先派单
            if ( $oUser->user_free_match_time > 0 && $sDispatchFlg == 'Y' ) {
                if ( $this->_dispatchAnchor($nUserId) === TRUE ) {
                    $row = [
                        'room_id'     => $matchCenterRoomId,
                        'free_times'  => $oUser->user_free_match_time,
                        'match_price' => $matchPrice,
                        'msg'         => ''
                    ];
                    $this->success($row);
                }
            }

            $saveStr = $oUser->getMatchCenterStr();
            //匹配大厅房间号
            $row = [
                'room_id'     => $matchCenterRoomId,
                'free_times'  => $oUser->user_free_match_time,
                'match_price' => $matchPrice,
                'msg'         => ''
            ];

            $notMatchAnchorId                  = [];
            $user_match_single_anchor_interval = intval(Kv::get(Kv::USER_MATCH_SINGLE_ANCHOR_INTERVAL));
            if ( $user_match_single_anchor_interval > 0 ) {
                $oMatchCenterUserAnchorService = new services\MatchCenterUserAnchorService($nUserId);
                $notMatchAnchorId              = $oMatchCenterUserAnchorService->getData($user_match_single_anchor_interval);
                // 删除掉指定时间之前的
                $oMatchCenterUserAnchorService->delete_item(0, time() - $user_match_single_anchor_interval);
            }


            if ( $sIsBeauty ) {
                // 区分颜值
// 将用户加入有序集合中 加入时间为分值 用户id为值
                $oMatchCenterBeautyService = new services\MatchCenterBeautyService();
                $flg                       = $oMatchCenterBeautyService->save($saveStr, $sIsAll);
                if ( $flg ) {
                    // 腾讯云通信模拟join类型
//                $this->timServer->setRid($matchCenterRoomId);
//                $this->timServer->setUid('');
                    // 查到所有在线的空闲的主播 且是高颜值
                    $beautyAnchor = Anchor::find([
                        'anchor_chat_status = 3 AND anchor_is_beauty = "Y"',
                        'columns' => 'user_id'
                    ])->toArray();
                    if ( $sIsAll || empty($beautyAnchor) ) {

                        $normalAnchor = Anchor::find([
                            'anchor_chat_status = 3',
                            'columns' => 'user_id'
                        ])->toArray();

                        $sendAnchorIds = array_column($normalAnchor, 'user_id');
                        if ( $notMatchAnchorId ) {
                            $sendAnchorIds = array_diff($sendAnchorIds, $notMatchAnchorId);
                        }
                        $this->timServer->setUid($sendAnchorIds);
                        $this->timServer->setExtra($oUser,FALSE);
                        $this->timServer->sendJoinSignal(TRUE);
                        $row['msg'] = '推送到普通主播';
                    } else {
                        $sendAnchorIds = array_column($beautyAnchor, 'user_id');
                        if ( $notMatchAnchorId ) {
                            $sendAnchorIds = array_diff($sendAnchorIds, $notMatchAnchorId);
                            sort($sendAnchorIds);
                        }
                        $this->timServer->setUid($sendAnchorIds);
                        $this->timServer->setExtra($oUser,FALSE);
                        $this->timServer->sendJoinSignal(TRUE);
                        $row['msg'] = '推送到高颜值主播';
                    }
                    // 记录进入匹配大厅时间
                    if ( $this->getParams('is_all', 'string', '') == '' ) {
                        // 没有传值 代表第一次设置时间
                        $oMatchCenterBeautyService->setEnterTime($nUserId);
                    }

                } else {
                    $row['msg'] = '已经在匹配队列中';
                }

            } else {
                // 区分红人
                // 将用户加入有序集合中 加入时间为分值 用户id为值
                $oMatchCenterUserService = new services\MatchCenterUserService();
                $flg                     = $oMatchCenterUserService->save($saveStr, $sIsAll);
                if ( $flg ) {
                    // 腾讯云通信模拟join类型
//                $this->timServer->setRid($matchCenterRoomId);
//                $this->timServer->setUid('');
                    // 查到所有在线的空闲的主播
                    $hotAnchor = Anchor::find([
                        'anchor_chat_status = 3 AND anchor_hot_man > 0',
                        'columns' => 'user_id'
                    ])->toArray();
                    if ( $sIsAll || empty($hotAnchor) ) {

                        $normalAnchor  = Anchor::find([
                            'anchor_chat_status = 3 AND anchor_hot_man = 0',
                            'columns' => 'user_id'
                        ])->toArray();
                        $sendAnchorIds = array_column($normalAnchor, 'user_id');
                        if ( $notMatchAnchorId ) {
                            $sendAnchorIds = array_diff($sendAnchorIds, $notMatchAnchorId);
                            sort($sendAnchorIds);
                        }
                        $this->timServer->setUid($sendAnchorIds);
                        $this->timServer->setExtra($oUser,FALSE);
                        $this->timServer->sendJoinSignal(TRUE);
                        $row['msg'] = '推送到普通主播';
                    } else {
                        $sendAnchorIds = array_column($hotAnchor, 'user_id');
                        if ( $notMatchAnchorId ) {
                            $sendAnchorIds = array_diff($sendAnchorIds, $notMatchAnchorId);
                        }
                        $this->timServer->setUid($sendAnchorIds);
                        $this->timServer->setExtra($oUser,FALSE);
                        $this->timServer->sendJoinSignal(TRUE);
                        $row['msg'] = '推送到红人主播';
                    }

                    // 记录进入匹配大厅时间
                    if ( $this->getParams('is_all', 'string', '') == '' ) {
                        // 没有传值 代表第一次设置时间
                        $oMatchCenterUserService->setEnterTime($nUserId);
                    }

                } else {
                    $row['msg'] = '已经在匹配队列中';
                }
            }


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
     * 离开匹配大厅
     */
    public function leaveMatchCenterAction( $nUserId = 0 )
    {
        try {
            $oUser = User::findFirst($nUserId);
            // 判断是否存在派单
            if ( $oUser->user_free_match_time > 0 ) {
                $oDispatchChat = DispatchChat::findFirst([
                    'dispatch_chat_user_id = :dispatch_chat_user_id:',
                    'bind'  => [
                        'dispatch_chat_user_id' => $nUserId
                    ],
                    'order' => 'dispatch_chat_id desc'
                ]);
                if ( $oDispatchChat && $oDispatchChat->dispatch_chat_status == 0 ) {
                    $oDispatchChat->dispatch_chat_status        = -1;
                    $oDispatchChat->dispatch_chat_wait_duration = time() - $oDispatchChat->dispatch_chat_create_time;
                    $oDispatchChat->save();
                    // 推送取消
                    $oUserPrivateChatLog                = UserPrivateChatLog::findFirst($oDispatchChat->dispatch_chat_chat_id);
                    $invitee_anchor                     = Anchor::findFirst([
                        'user_id = :user_id:',
                        'bind' => [ 'user_id' => $oUserPrivateChatLog->chat_log_anchor_user_id ]
                    ]);
                    $invitee_anchor->anchor_chat_status = 3;
                    $invitee_anchor->save();
                    $oUserPrivateChatLog->status   = 1;
                    $oUserPrivateChatLog->duration = time() - $oUserPrivateChatLog->create_time;
                    $oUserPrivateChatLog->save();
                    $dialog         = UserPrivateChatDialog::findFirst($oUserPrivateChatLog->dialog_id);
                    $dialog->status = 0;
                    $dialog->save();
                    $this->timServer->setUid($oUserPrivateChatLog->invitee_id);
                    $this->timServer->cancelPrivateChat([
                        'chat_log' => $oUserPrivateChatLog->id
                    ]);
                    $this->success();
                }
            }

            $oMatchCenterUserService = new services\MatchCenterUserService();

            $saveStr = $oUser->getMatchCenterStr();

            $flg                       = $oMatchCenterUserService->delete_item($saveStr);
            $oMatchCenterBeautyService = new services\MatchCenterBeautyService();
            $flg2                      = $oMatchCenterBeautyService->delete_item($saveStr);
            if ( $flg + $flg2 != 0 ) {
                // 删除成功 才需要发消息给大厅 如果删除失败 则表示已经不在大厅里面
                //匹配大厅房间号
                $allAnchor = Anchor::find([
                    'anchor_chat_status = 3',
                    'columns' => 'user_id'
                ])->toArray();
                $this->timServer->setUid(array_column($allAnchor, 'user_id'));
                $this->timServer->setExtra($oUser,FALSE);
                $this->timServer->sendLeaveSignal(TRUE);
//                $matchCenterRoomId = Kv::get(Kv::MATCH_CENTER_ROOM_ID);
//                $this->timServer->setRid($matchCenterRoomId);
//                $this->timServer->setUid();
//                $this->timServer->setExtra($oUser);
//                $this->timServer->sendLeaveSignal();
//
//                $matchCenterRoomId .= '_all';
//                $this->timServer->setRid($matchCenterRoomId);
//                $this->timServer->setUid();
//                $this->timServer->setExtra($oUser);
//                $this->timServer->sendLeaveSignal();

                //获取时间 并且记录匹配失败
                $start_time = $oMatchCenterUserService->getEnterTime($nUserId);
                if ( $start_time ) {
                    $oMatchCenterUserService->deleteEnterTime($nUserId);
                    $end_time                      = time();
                    $oUserMatchLog                 = new UserMatchLog();
                    $oUserMatchLog->user_id        = $nUserId;
                    $oUserMatchLog->user_type      = $oUser->user_total_coin > 0 ? UserMatchLog::USER_TYPE_OLD : UserMatchLog::USER_TYPE_NEW;
                    $oUserMatchLog->duration       = $end_time - $start_time;
                    $oUserMatchLog->match_success  = 'N';
                    $oUserMatchLog->create_time    = $start_time;
                    $oUserMatchLog->update_time    = $end_time;
                    $oUserMatchLog->has_free_times = $oUser->user_free_match_time;
                    $oUserMatchLog->save();
                }

            } else {
                // 如果此时有主播同时接单了 那么直接挂断该聊天
//                $chat_log = UserPrivateChatLog::findFirst([
//                    'chat_log_user_id=:chat_log_user_id: AND status = 4',
//                    'bind' => [ 'chat_log_user_id' => $nUserId ]
//                ]);
//                if($chat_log){
//                    // 需要挂断
//                    @file_get_contents(sprintf('%s/v1/live/anchor/hangUpChat?%s', $this->config->application->api_url, http_build_query([
//                        'user_id' => $nUserId,
//                        'chat_log' => $chat_log->id,
//                    ])));
//                }
            }
        } catch ( \Phalcon\Db\Exception $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( \PDOException $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @param int $nUserId
     * 匹配大厅用户列表
     */
    public function matchCenterUsersAction( $nUserId = 0 )
    {
        try {
            $oUser = User::findFirst($nUserId);
            if ( $oUser->user_is_anchor != 'Y' ) {
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::IS_NOT_ANCHOR)),
                    ResponseError::IS_NOT_ANCHOR
                );
            }
            $oAnchor = Anchor::findFirst([
                "user_id = :user_id:",
                'bind' => [ 'user_id' => $nUserId ]
            ]);
            $isAll   = TRUE;
            if ( $oAnchor->anchor_hot_man > 0 ) {
                $isAll = FALSE;
            }
            $oMatchCenterUserService = new services\MatchCenterUserService();
            $nUserInfoList           = $oMatchCenterUserService->getData($isAll);

            $isBeautyALL = TRUE;
            if ( $oAnchor->anchor_is_beauty == 'N' ) {
                $isBeautyALL = FALSE;
            }
            $oMatchCenterBeautyService = new services\MatchCenterBeautyService();
            $nUserInfoBeautyList       = $oMatchCenterBeautyService->getData($isBeautyALL);

            $userList = array_merge($nUserInfoBeautyList, $nUserInfoList);


            //获取该主播看不到的用户数据（最近匹配过的数据）
            $notAllowUserIds                   = [];
            $user_match_single_anchor_interval = intval(Kv::get(Kv::USER_MATCH_SINGLE_ANCHOR_INTERVAL));
            if ( $user_match_single_anchor_interval > 0 ) {
                $oMatchCenterUserAnchorService = new services\MatchCenterUserAnchorService($nUserId);
                $notAllowUserIds               = $oMatchCenterUserAnchorService->getData($user_match_single_anchor_interval);
            }
            // 2018-07-17 取20个用户
            $row = [];
            foreach ( $userList as $key => $item ) {
                if ( $key > 50 ) {
                    break;
                }
                $itemInfo = json_decode($item, TRUE);
                if ( !in_array($itemInfo['user_id'], $notAllowUserIds) ) {
                    $row['user_list'][] = json_decode($item);
                }
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    /**
     * 主播抢单（抢匹配用户）
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/chat/selectUser
     * @api {get} /user/chat/selectUser 主播抢单
     * @apiName selectUser
     * @apiGroup Chat
     * @apiDescription 主播抢单
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} user_id 用户id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} user_id 用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.push_url 腾讯云推流地址
     * @apiSuccess {string} d.chat_log 聊天id
     * @apiSuccess {string} d.live_key 聊天房间key
     * @apiSuccess {string} d.play_rtmp 腾讯云RTMP播放地址
     * @apiSuccess {string} d.play_flv 腾讯云FLV播放地址
     * @apiSuccess {string} d.play_m3u8 腾讯云M3U8播放地址
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
    public function selectUserAction( $nUserId = 0 )
    {
        $nSelectUserId = $this->getParams('user_id', 'int');
        $this->forceUpdate($nUserId);
        try {
            $oSelectUser = User::findFirst($nSelectUserId);
            if ( !$oSelectUser ) {
                throw new Exception(
                    sprintf('user_id %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }
            $oUser  = User::findFirst($nUserId);
            $anchor = Anchor::findFirst([
                'user_id = :user_id:',
                'bind' => [ 'user_id' => $nUserId ]
            ]);
            if ( !$anchor ) {
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::IS_NOT_ANCHOR)),
                    ResponseError::IS_NOT_ANCHOR
                );
            }
            if ( $anchor->anchor_private_forbidden == 1 ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::CHAT_FORBIDDEN),
                    ResponseError::CHAT_FORBIDDEN
                );
            }
            $black = UserBlack::findFirst([
                'user_id = :user_id: and to_user_id = :to_user_id:',
                'bind' => [
                    'user_id'    => $nUserId,
                    'to_user_id' => $nSelectUserId
                ]
            ]);
            if ( $black ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::IN_BLACK),
                    ResponseError::IN_BLACK
                );
            }

            $saveStr                   = $oSelectUser->getMatchCenterStr();
            $oMatchCenterUserService   = new services\MatchCenterUserService();
            $flg                       = $oMatchCenterUserService->delete_item($saveStr);
            $oMatchCenterBeautyService = new services\MatchCenterBeautyService();
            $flg2                      = $oMatchCenterBeautyService->delete_item($saveStr);
            if ( $flg + $flg2 == 0 ) {
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::MATCH_USER_NOT_EXISTS)),
                    ResponseError::MATCH_USER_NOT_EXISTS
                );
            }

            $allAnchor = Anchor::find([
                'anchor_chat_status = 3',
                'columns' => 'user_id'
            ])->toArray();
            $this->timServer->setUid(array_column($allAnchor, 'user_id'));
            $this->timServer->setExtra($oSelectUser,FALSE);
            $this->timServer->sendLeaveSignal(TRUE);

//            // 抢单成功 创建连接 推送到匹配大厅 将数据该用户数据删除
//            $matchCenterRoomId = Kv::get(Kv::MATCH_CENTER_ROOM_ID);
//            $this->timServer->setRid($matchCenterRoomId);
//            $this->timServer->setUid();
//            $this->timServer->setExtra($oSelectUser);
//            $this->timServer->sendLeaveSignal();
//
//            $matchCenterRoomId .= '_all';
//            $this->timServer->setRid($matchCenterRoomId);
//            $this->timServer->setUid();
//            $this->timServer->setExtra($oSelectUser);
//            $this->timServer->sendLeaveSignal();

            $oUserPrivateChatDialog                       = new UserPrivateChatDialog();
            $oUserPrivateChatLog                          = new UserPrivateChatLog();
            $dialog_id                                    = $oUserPrivateChatDialog->getDialogId($nSelectUserId, $nUserId, TRUE);
            $oUserPrivateChatLog->inviter_id              = $nSelectUserId;
            $oUserPrivateChatLog->invitee_id              = $nUserId;
            $oUserPrivateChatLog->chat_log_user_id        = $nSelectUserId;
            $oUserPrivateChatLog->chat_log_anchor_user_id = $nUserId;
            $oUserPrivateChatLog->free_times_type         = $oSelectUser->user_free_match_time > 0 ? UserPrivateChatLog::FREE_TIME_TYPE_GIVE : UserPrivateChatLog::FREE_TIME_TYPE_EMPTY;
            $chat_log                                     = $oUserPrivateChatLog->addData($nSelectUserId, $nUserId, 0, $dialog_id, UserPrivateChatLog::CHAT_TYPE_MATCH);

            $anchor->anchor_chat_status = 2;
            $anchor->save();

            // 获取网宿的共同推流地址
            $this->liveServer->setStreamName($chat_log);
            $wangsuPushInfo = $this->liveServer->pushUrl();


            // 主播获取主播推流地址，用户拉流地址；推送给用户 主播的拉流地址，用户的推流地址
            $this->liveServer->setStreamName($nUserId . '_' . $chat_log . '_2');
            $pushInfo     = $this->liveServer->pushUrl();
            $aPushMessage = [
                'chat_log'        => $chat_log,
                'f_user_id'       => $nUserId,
                'f_user_nickname' => $oUser->user_nickname,
                'f_user_avatar'   => $oUser->user_avatar,
                'f_user_level'    => $oUser->user_level,
                'play_rtmp'       => $this->liveServer->playUrl('rtmp'),
                'play_flv'        => $this->liveServer->playUrl('flv'),
                'play_m3u8'       => $this->liveServer->playUrl('m3u8'),

                'wangsu' => [
                    'push_url' => $wangsuPushInfo['push_url']
                ]
            ];

            $this->liveServer->setStreamName($nSelectUserId . '_' . $chat_log . '_2');

            $selectUserPushInfo       = $this->liveServer->pushUrl();
            $aPushMessage['push_url'] = $selectUserPushInfo['push_url'];

            $row = [
                'push_url'  => $pushInfo['push_url'],
                'chat_log'  => $chat_log,
                'live_key'  => $this->timServer->genPrivateMapKey($nUserId, $chat_log),
                'play_rtmp' => $this->liveServer->playUrl('rtmp'),
                'play_flv'  => $this->liveServer->playUrl('flv'),
                'play_m3u8' => $this->liveServer->playUrl('m3u8'),
                'wangsu'    => [
                    'push_url' => $wangsuPushInfo['push_url']
                ]
            ];
            $this->timServer->setRid();
            $this->timServer->setUid($nSelectUserId);
            $this->timServer->matchSuccessPrivateChat($aPushMessage);


//            $this->timServer->setRid($nUserId);
//            $this->timServer->setAccountId($nUserId);
//            $this->timServer->createRoom(sprintf('私聊_%s', $nUserId), 'ChatRoom');

            //获取时间 并且记录匹配成功
            $start_time = $oMatchCenterUserService->getEnterTime($nSelectUserId);
            if ( $start_time ) {
                $oMatchCenterUserService->deleteEnterTime($nSelectUserId);
                $end_time                      = time();
                $oUserMatchLog                 = new UserMatchLog();
                $oUserMatchLog->user_id        = $nSelectUserId;
                $oUserMatchLog->user_type      = $oSelectUser->user_total_coin > 0 ? UserMatchLog::USER_TYPE_OLD : UserMatchLog::USER_TYPE_NEW;
                $oUserMatchLog->duration       = $end_time - $start_time;
                $oUserMatchLog->match_success  = 'Y';
                $oUserMatchLog->anchor_user_id = $nUserId;
                $oUserMatchLog->anchor_type    = $anchor->anchor_hot_man > 0 ? UserMatchLog::ANCHOR_TYPE_HOT : UserMatchLog::ANCHOR_TYPE_NORMAL;
                $oUserMatchLog->create_time    = $start_time;
                $oUserMatchLog->update_time    = $end_time;
                $oUserMatchLog->chat_log_id    = $chat_log;
                $oUserMatchLog->has_free_times = $oSelectUser->user_free_match_time;
                $oUserMatchLog->save();
            }

            // 修改用户状态
            $oUserVideoChatService = new UserVideoChatService();
            $oUserVideoChatService->save($oUserPrivateChatLog->chat_log_user_id);


            // 等待用户进入房间
            $this->redis->zAdd('wait_user_enter_match', time(), sprintf('%s-%s', $chat_log, $nSelectUserId));

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    /**
     * 用户进入私聊房间
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/chat/userEnterRoom
     * @api {post} /user/chat/userEnterRoom 用户进入私聊房间
     * @apiName userEnterRoom
     * @apiGroup Chat
     * @apiDescription 用户进入私聊房间
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
     * @apiSuccess {string} d.live_key 房间鉴权key
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
    public function userEnterRoomAction( $nUserId = 0 )
    {
        $chat_log_id = $this->getParams('chat_log', 'int', 0);
        $this->log->info($nUserId . ' 用户进入房间开始：' . $chat_log_id);
        $chat_log = UserPrivateChatLog::findFirst([
            'id=:id:',
            'bind' => [ 'id' => $chat_log_id ]
        ]);
        if ( $chat_log->status != 0 ) {
            $this->error(10002);
        }
        $oUser = User::findFirst($nUserId);
        if ( $nUserId != $chat_log->chat_log_user_id ) {
            $this->error(10002);
        }
        //需要通知主播开始对聊
//        if ( $chat_log->chat_type == UserPrivateChatLog::CHAT_TYPE_MATCH ) {

        $createTime = time();
        // 记录聊天开始 可以开始扣费 (延迟1秒)
        $oVideoChatService = new VideoChatService();
        $videoChatStr      = sprintf('%s:%s', $chat_log->chat_log_user_id, $chat_log->id);
        $oVideoChatService->save($videoChatStr, date('s', $createTime));
        $chat_log->create_time = $createTime;
        $chat_log->status      = 4;
        $flg                   = $chat_log->save();
        if ( $flg === FALSE ) {
            $this->log->info('用户进入房间，修改记录出错' . json_encode($chat_log->getMessages()));
        }

        $this->timServer->setUid($chat_log->chat_log_anchor_user_id);
        $this->timServer->userMatchSuccessPrivateChat([
            'user_id'       => $oUser->user_id,
            'user_nickname' => $oUser->user_nickname,
            'user_avatar'   => $oUser->user_avatar,
            'user_level'    => $oUser->user_level
        ]);
        // 此处第一次付费
        $result = $this->httpRequest(sprintf('%s/v1/live/anchor/privateChatMinuteNew?%s', $this->config->application->api_url, http_build_query([
            'uid'         => $chat_log->chat_log_user_id,
            'debug'       => 1,
            'chat_log'    => $chat_log->id,
            'cli_api_key' => $this->config->application->cli_api_key,
        ])));

        //删除上次礼物收益
        $oVideoChatService->deleteGiftData(sprintf("%s:%s", $chat_log->chat_log_anchor_user_id, $chat_log->chat_log_user_id));
        //删除上次游戏收益
        $oVideoChatService->deleteChatGameData(sprintf("%s:%s", $chat_log->chat_log_anchor_user_id, $chat_log->chat_log_user_id));

        $this->log->info($nUserId . ' 用户进入房间结束：' . $chat_log_id . "第一次扣费结果：" . $result);
//        }
        $this->redis->zDelete('wait_user_enter_match', sprintf('%s-%s', $chat_log->id, $nUserId));
        $row = [
            'live_key' => $this->timServer->genPrivateMapKey($nUserId, $chat_log_id)
        ];

        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/chat/anchorEnterRoom
     * @api {post} /user/chat/anchorEnterRoom 主播进入房间
     * @apiName chat-anchorEnterRoom
     * @apiGroup Chat
     * @apiDescription 主播主动拨打用户时进入房间
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
     * @apiSuccess {string} d.live_key 房间鉴权key
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
    public function anchorEnterRoomAction( $nUserId = 0 )
    {
        $chat_log_id = $this->getParams('chat_log', 'int', 0);
        try {
            $this->log->info($nUserId . ' 主播进入房间开始：' . $chat_log_id);
            $oUserPrivateChatLog = UserPrivateChatLog::findFirst([
                'id=:id:',
                'bind' => [ 'id' => $chat_log_id ]
            ]);
            if ( $oUserPrivateChatLog->status != 0 ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $oUser = User::findFirst($nUserId);
            if ( $nUserId != $oUserPrivateChatLog->chat_log_anchor_user_id ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }

            $createTime = time();
            // 记录聊天开始 可以开始扣费 (延迟1秒)
            $oVideoChatService = new VideoChatService();
            $videoChatStr      = sprintf('%s:%s', $oUserPrivateChatLog->chat_log_user_id, $oUserPrivateChatLog->id);
            $oVideoChatService->save($videoChatStr, date('s', $createTime + 2));
            $oUserPrivateChatLog->create_time = $createTime;
            $oUserPrivateChatLog->status      = 4;
            $flg                              = $oUserPrivateChatLog->save();
            if ( $flg === FALSE ) {
                $this->log->info('主播进入房间，修改记录出错' . json_encode($oUserPrivateChatLog->getMessages()));
            }

            // 此处第一次付费
            $result = $this->httpRequest(sprintf('%s/v1/live/anchor/privateChatMinuteNew?%s', $this->config->application->api_url, http_build_query([
                'uid'         => $oUserPrivateChatLog->chat_log_user_id,
                'debug'       => 1,
                'chat_log'    => $oUserPrivateChatLog->id,
                'cli_api_key' => $this->config->application->cli_api_key,
            ])));

            $this->timServer->setUid($oUserPrivateChatLog->chat_log_user_id);
            $this->timServer->userMatchSuccessPrivateChat([
                'user_id'       => $oUser->user_id,
                'user_nickname' => $oUser->user_nickname,
                'user_avatar'   => $oUser->user_avatar,
                'user_level'    => $oUser->user_level
            ]);
            //删除上次礼物收益
            $oVideoChatService->deleteGiftData(sprintf("%s:%s", $oUserPrivateChatLog->chat_log_anchor_user_id, $oUserPrivateChatLog->chat_log_user_id));
            //删除上次游戏收益
            $oVideoChatService->deleteChatGameData(sprintf("%s:%s", $oUserPrivateChatLog->chat_log_anchor_user_id, $oUserPrivateChatLog->chat_log_user_id));

            $this->log->info($nUserId . ' 主播进入房间结束：' . $chat_log_id . "第一次扣费结果：" . $result);
//        }
            $row = [
                'live_key' => $this->timServer->genPrivateMapKey($nUserId, $chat_log_id)
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/chat/addCommentChat
     * @api {post} /user/chat/addCommentChat 聊天后的评价
     * @apiName chat-commentChat
     * @apiGroup Chat
     * @apiDescription 聊天后的评价
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} chat_log 聊天记录id
     * @apiParam (正常请求){Number{1-5}} star 评论星级
     * @apiParam (正常请求){String} detail 评论描述(将标签以半角逗号分隔传入)
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} chat_log 聊天记录id
     * @apiParam (debug){Number{1-5}} star 评论星级
     * @apiParam (debug){String} detail 评论描述
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
    public function addCommentChatAction( $nUserId = 0 )
    {
        $chat_log_id = $this->getParams('chat_log', 'int', 0);
        $star        = $this->getParams('star', 'int', 1);
        $detail      = $this->getParams('detail', 'string', '');

        try {
            if ( ($star < 1 && $star > 5) || empty($detail) ) {
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }
            $oUserPrivateChatLog = UserPrivateChatLog::findFirst($chat_log_id);
            if ( $oUserPrivateChatLog->status != 6 && $oUserPrivateChatLog->chat_log_user_id != $nUserId ) {
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }
            $oUserChatEvaluationLog                    = new UserChatEvaluationLog();
            $connection                                = $oUserChatEvaluationLog->getWriteConnection();
            $oUserChatEvaluationLog->user_id           = $nUserId;
            $oUserChatEvaluationLog->anchor_user_id    = $oUserPrivateChatLog->chat_log_anchor_user_id;
            $oUserChatEvaluationLog->chat_id           = $chat_log_id;
            $oUserChatEvaluationLog->star              = $star;
            $oUserChatEvaluationLog->evaluation_type   = $star >= 3 ? 'G' : 'B';
            $oUserChatEvaluationLog->evaluation_detail = $detail;
            $oUserChatEvaluationLog->duration          = $oUserPrivateChatLog->duration;
            if ( !$oUserChatEvaluationLog->save() ) {
                $connection->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserChatEvaluationLog->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            $oAnchor                            = Anchor::findFirst([
                'user_id=:user_id:',
                'bind' => [ 'user_id' => $oUserPrivateChatLog->chat_log_anchor_user_id ]
            ]);
            $oAnchor->anchor_comment_total_star += $star;
            if ( $star >= 3 ) {
                $oAnchor->anchor_praise_count += 1;
            } else {
                $oAnchor->anchor_dissatisfaction_count += 1;
            }
            if ( !$oAnchor->save() ) {
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
     * @param $nUserId
     * 派单
     * 查找设置的派单主播（在线且没有达到今日派单最大值）
     */
    private function _dispatchAnchor( $nUserId )
    {
        $oDispatchAnchor = $this->modelsManager
            ->createBuilder()
            ->from([ 'd' => AnchorDispatch::class ])
            ->join(Anchor::class, 'a.user_id = d.anchor_dispatch_user_id', 'a')
            ->where('anchor_chat_status = :anchor_chat_status: AND a.anchor_dispatch_flg = :anchor_dispatch_flg: AND d.anchor_dispatch_open_flg = :anchor_dispatch_open_flg:',
                [
                    'anchor_chat_status'       => 3,
                    'anchor_dispatch_flg'      => 'Y',
                    'anchor_dispatch_open_flg' => 'Y',
                ])
            ->columns('a.anchor_id,a.user_id,d.anchor_dispatch_price,d.anchor_dispatch_max_day_times,d.anchor_dispatch_today_times,d.anchor_dispatch_last_time')
            ->orderBy('anchor_id')
            ->getQuery()
            ->execute()
            ->toArray();

        $today = strtotime('today');

        $selectAnchorArr = [];
        $randArr         = [];
        foreach ( $oDispatchAnchor as $anchorItem ) {
            if ( $anchorItem['anchor_dispatch_last_time'] >= $today && $anchorItem['anchor_dispatch_today_times'] >= $anchorItem['anchor_dispatch_max_day_times'] ) {
                // 最后一次派单是今天 且匹配次数已经超过最大值
                continue;
            }
            $itemUserId                     = $anchorItem['user_id'];
            $randArr[]                      = $itemUserId;
            $selectAnchorArr[ $itemUserId ] = $anchorItem;
        }

        if ( empty($randArr) ) {
            return FALSE;
        }
        $selectUserId = array_random($randArr);
//        if ( $nUserId == 451 ) {
//            $selectUserId = 425;
//        } elseif ( $nUserId == 427 ) {
//            $selectUserId = 424;
//        }
        $selectAnchor = $selectAnchorArr[ $selectUserId ] ?? [];
        if ( !$selectAnchor ) {
            return FALSE;
        }

        // 给选择的主播发送邀请通话数据
        $oUser = User::findFirst($nUserId);

        // 修改用户状态
        $oUserVideoChatService = new UserVideoChatService();
        $flg                   = $oUserVideoChatService->save($oUser->user_id);

        $oUserPrivateChatLog                          = new UserPrivateChatLog();
        $oUserPrivateChatLog->is_user_call            = 'Y';
        $oUserPrivateChatLog->chat_log_user_id        = $oUser->user_id;
        $oUserPrivateChatLog->chat_log_anchor_user_id = $selectUserId;
        $oUserPrivateChatLog->inviter_id              = $nUserId;
        $oUserPrivateChatLog->invitee_id              = $selectUserId;
        $oUserPrivateChatLog->free_times_type         = UserPrivateChatLog::FREE_TIME_TYPE_GIVE;
        $oUserPrivateChatDialog                       = new UserPrivateChatDialog();
        $dialog_id                                    = $oUserPrivateChatDialog->getDialogId($nUserId, $selectUserId);

        $oAnchor = Anchor::findFirst($selectAnchor['anchor_id']);

        $oAnchor->anchor_chat_status = 2;
        $oAnchor->save();
        $dialog         = UserPrivateChatDialog::findFirst($dialog_id);
        $dialog->status = 1;
        $dialog->save();

        $addDataReturn                               = $oUserPrivateChatLog->addData($nUserId, $selectUserId, 0, $dialog_id, UserPrivateChatLog::CHAT_TYPE_DISPATCH, 'N', TRUE);
        $chat_log                                    = $addDataReturn['id'];
        $chatPushData                                = $addDataReturn['push'];
        $userChatId                                  = $addDataReturn['user_chat_id'];
        $oUserChat                                   = UserChat::findFirst($userChatId);
        $userChatExtraArr                            = unserialize($oUserChat->user_chat_extra);
        $userChatExtraArr['video_chat_has_callback'] = 'Y';
        $oUserChat->user_chat_extra                  = serialize($userChatExtraArr);
        $oUserChat->save();

        $oDispatchChat                               = new DispatchChat();
        $oDispatchChat->dispatch_chat_user_id        = $nUserId;
        $oDispatchChat->dispatch_chat_anchor_user_id = $selectUserId;
        $oDispatchChat->dispatch_chat_wait_duration  = 0;
        $oDispatchChat->dispatch_chat_status         = 0;
        $oDispatchChat->dispatch_chat_chat_id        = $chat_log;
        $oDispatchChat->dispatch_chat_price          = $selectAnchor['anchor_dispatch_price'];
        $oDispatchChat->save();

        // 派单主播今日数据添加
        $oAnchorDispatch                               = AnchorDispatch::findFirst([
            'anchor_dispatch_user_id = :anchor_dispatch_user_id:',
            'bind' => [
                'anchor_dispatch_user_id' => $selectUserId
            ]
        ]);
        $oAnchorDispatch->anchor_dispatch_today_times  += 1;
        $oAnchorDispatch->anchor_dispatch_last_time    = time();
        $oAnchorDispatch->anchor_dispatch_total_number += 1;
        $oAnchorDispatch->save();

        $dispatchOverTime = intval(Kv::get(Kv::DISPATCH_OVER_TIME));

        //TIM发送信号
        $aPushMessage = [
            'is_say_hi'                => $chatPushData['dialog']['is_say_hi'],
            'is_guard_user'            => 'N',
            'no_income_free_time'      => 0,
            'no_income_free_time_type' => $oUserPrivateChatLog->free_times_type,
            'is_user_call'             => 'Y',
            'chat_log'                 => $chat_log,
            'f_user_id'                => $nUserId,
            'f_user_nickname'          => $oUser->user_nickname,
            'f_user_avatar'            => $oUser->user_avatar,
            'f_user_level'             => $oUser->user_level,
            'anchor_video_url'         => $oAnchor->anchor_video,
            'price'                    => $selectAnchor['anchor_dispatch_price'],
            'is_free_match_flg'        => 'Y',
            'free_match_over_time'     => (int)$dispatchOverTime,
            'play_rtmp'                => '',
            'play_flv'                 => '',
            'play_m3u8'                => '',
            'wangsu'                   => [
                'push_url' => ''
            ]
        ];
        $oToUser      = User::findFirst($selectUserId);
        $appInfo      = $this->getAppInfo('qq', $oToUser->user_app_flg ? $oToUser->user_app_flg : 'tianmi');
        $jPush        = new JiGuangApi($appInfo['jpush_app_key'], $appInfo['jpush_master_secret'], NULL, APP_ENV == 'dev' ? FALSE : TRUE);
        $res          = $jPush->push([ 'alias' => [ "{$selectUserId}" ] ], '视频消息', "【{$oUser->user_nickname}】邀请您进行快聊", [
            'type'    => 'private_chat',
            'chat_id' => $chat_log
        ]);
        $this->timServer->setUid($selectUserId);
        $this->timServer->sendPrivateChat($aPushMessage);

        return TRUE;

    }

    /**
     * 接受新用户派单聊天
     * @apiVersion 1.2.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/chat/acceptDispatch
     * @api {get} /user/chat/acceptDispatch 001-190819接受新用户派单聊天
     * @apiName chat-acceptDispatch
     * @apiGroup Chat
     * @apiDescription 001-190819接受新用户派单聊天
     * @apiParam (正常请求) {String} chat_log 视频聊天ID
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug) {String} chat_log 视频聊天ID
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.chat_log 聊天ID
     * @apiSuccess {string} d.live_key 聊天秘钥
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": {
     *              "chat_log": "1000",
     *              "live_key": "msdfsdfsdfsadfasdfsdf"
     *          },
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     */
    public function acceptDispatchAction( $nUserId = 0 )
    {
        $chatLogId = $this->getParams('chat_log');
        try {
            if ( empty($chatLogId) ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $oUserPrivateChatLog = UserPrivateChatLog::findFirst($chatLogId);
            if ( !$oUserPrivateChatLog || $oUserPrivateChatLog->invitee_id != $nUserId ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            if ( $oUserPrivateChatLog->status != 0 ) {
                throw new Exception(ResponseError::getError(ResponseError::STATUS_ERROR), ResponseError::STATUS_ERROR);
            }

            $oDispatchChat = DispatchChat::findFirst([
                'dispatch_chat_chat_id = :dispatch_chat_chat_id:',
                'bind' => [
                    'dispatch_chat_chat_id' => $chatLogId
                ]
            ]);
            if ( !$oDispatchChat ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            //添加操作锁 防止用户同时在请求取消邀请
            $flg = $this->redis->sAdd('changeChat', $chatLogId);
            if ( $flg == 0 ) {
                throw new Exception('对方已取消', ResponseError::PARAM_ERROR);
            }


            // 获取网宿的共同推流地址
            $this->liveServer->setStreamName($chatLogId);
            $wangsuPushInfo = $this->liveServer->pushUrl();


            $oUser = User::findFirst($nUserId);
            // 主播获取主播推流地址，用户拉流地址；推送给用户 主播的拉流地址，用户的推流地址
            $this->liveServer->setStreamName($nUserId . '_' . $chatLogId . '_2');
            $pushInfo     = $this->liveServer->pushUrl();
            $aPushMessage = [
                'chat_log'        => $chatLogId,
                'f_user_id'       => $nUserId,
                'f_user_nickname' => $oUser->user_nickname,
                'f_user_avatar'   => $oUser->user_avatar,
                'f_user_level'    => $oUser->user_level,
                'play_rtmp'       => $this->liveServer->playUrl('rtmp'),
                'play_flv'        => $this->liveServer->playUrl('flv'),
                'play_m3u8'       => $this->liveServer->playUrl('m3u8'),

                'wangsu' => [
                    'push_url' => $wangsuPushInfo['push_url']
                ]
            ];

            $nSelectUserId = $oUserPrivateChatLog->inviter_id;

            $this->liveServer->setStreamName($nSelectUserId . '_' . $chatLogId . '_2');

            $selectUserPushInfo       = $this->liveServer->pushUrl();
            $aPushMessage['push_url'] = $selectUserPushInfo['push_url'];

            $row = [
                'push_url'  => $pushInfo['push_url'],
                'chat_log'  => $chatLogId,
                'live_key'  => $this->timServer->genPrivateMapKey($nUserId, $chatLogId),
                'play_rtmp' => $this->liveServer->playUrl('rtmp'),
                'play_flv'  => $this->liveServer->playUrl('flv'),
                'play_m3u8' => $this->liveServer->playUrl('m3u8'),
                'wangsu'    => [
                    'push_url' => $wangsuPushInfo['push_url']
                ]
            ];
            $this->timServer->setRid();
            $this->timServer->setUid($nSelectUserId);
            $this->timServer->matchSuccessPrivateChat($aPushMessage);

            $oDispatchChat->dispatch_chat_status        = 1;
            $oDispatchChat->dispatch_chat_wait_duration = time() - $oDispatchChat->dispatch_chat_create_time;
            $oDispatchChat->save();

            // 等待用户进入房间
            $this->redis->zAdd('wait_user_enter_match', time(), sprintf('%s-%s', $chatLogId, $nSelectUserId));

            $this->redis->sRem('changeChat', $chatLogId);

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * 取消接听派单
     *    用户进入匹配大厅
     * @apiVersion 1.2.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/chat/cancelDispatch
     * @api {get} /user/chat/cancelDispatch 002-190819取消接听派单
     * @apiName chat-cancelDispatch
     * @apiGroup Chat
     * @apiDescription 002-190819取消接听派单
     * @apiParam (正常请求) {String} chat_log  聊天ID
     * @apiParam (正常请求) {String='cancel(取消)','overtime(超时)'} cancel_type  取消类型
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug) {String} chat_log  聊天ID
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     */
    public function cancelDispatchAction( $nUserId = 0 )
    {
        $chatLogId  = $this->getParams('chat_log');
        $cancelType = $this->getParams('cancel_type', 'string', 'cancel');
        try {
            if ( empty($chatLogId) ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $oUserPrivateChatLog = UserPrivateChatLog::findFirst($chatLogId);
            if ( !$oUserPrivateChatLog || $oUserPrivateChatLog->invitee_id != $nUserId ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            if ( $oUserPrivateChatLog->status != 0 ) {
                throw new Exception(ResponseError::getError(ResponseError::STATUS_ERROR), ResponseError::STATUS_ERROR);
            }
            $oDispatchChat = DispatchChat::findFirst([
                'dispatch_chat_chat_id = :dispatch_chat_chat_id:',
                'bind' => [
                    'dispatch_chat_chat_id' => $chatLogId
                ]
            ]);
            if ( !$oDispatchChat ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            //添加操作锁 防止用户同时在请求取消邀请
            $flg = $this->redis->sAdd('changeChat', $chatLogId);
            if ( $flg == 0 ) {
                throw new Exception('对方已取消', ResponseError::PARAM_ERROR);
            }
            $oUserPrivateChatLog->status   = 5;
            $oUserPrivateChatLog->duration = time() - $oUserPrivateChatLog->create_time;
            $oUserPrivateChatLog->save();
            $invitee_anchor                     = Anchor::findFirst([
                'user_id = :user_id:',
                'bind' => [ 'user_id' => $oUserPrivateChatLog->chat_log_anchor_user_id ]
            ]);
            $invitee_anchor->anchor_chat_status = 3;
            $invitee_anchor->save();

            $dialog         = UserPrivateChatDialog::findFirst($oUserPrivateChatLog->dialog_id);
            $dialog->status = 0;
            $dialog->save();

            // 手动取消或者超时
            $oDispatchChat->dispatch_chat_status        = $cancelType == 'cancel' ? -2 : -3;
            $oDispatchChat->dispatch_chat_wait_duration = time() - $oDispatchChat->dispatch_chat_create_time;
            $oDispatchChat->save();

            // 删除用户状态
            $oUserVideoChatService = new UserVideoChatService();
            $oUserVideoChatService->delete($oUserPrivateChatLog->chat_log_user_id);

            //用户进入匹配大厅
            $result = $this->httpRequest(sprintf('%s/v1/user/chat/matchCenter?%s', $this->config->application->api_url, http_build_query([
                'uid'          => $oUserPrivateChatLog->chat_log_user_id,
                'debug'        => 1,
                'dispatch_flg' => 'N',
                'cli_api_key'  => $this->config->application->cli_api_key,
            ])));

            $row = [
                'flg' => $result,
                'url' => sprintf('%s/v1/user/chat/matchCenter?%s', $this->config->application->api_url, http_build_query([
                    'uid'          => $oUserPrivateChatLog->chat_log_user_id,
                    'debug'        => 1,
                    'dispatch_flg' => 'N',
                    'cli_api_key'  => $this->config->application->cli_api_key,
                ]))
            ];
            $this->log->info(json_encode($row));
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


}