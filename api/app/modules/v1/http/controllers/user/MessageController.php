<?php

namespace app\http\controllers\user;


use app\helper\ResponseError;
use app\models\Banword;
use app\models\ShortPostsMessage;
use app\models\SystemMessage;
use app\models\User;
use app\models\UserChat;
use app\models\UserChatDialog;
use app\models\UserPrivateChatDialog;
use app\models\UserPrivateChatLog;
use app\models\UserSystemMessage;
use app\models\UserSystemMessageDialog;
use app\models\UserVideoMessage;
use Exception;

use app\http\controllers\ControllerBase;

/**
 * MessageController 消息
 */
class MessageController extends ControllerBase
{
    use \app\services\UserService;

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/message/notify
     * @api {get} /user/message/notify 通知消息列表
     * @apiName message-notify
     * @apiGroup Message
     * @apiDescription 通知消息列表
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
     * @apiSuccess {object} d.system_dialog    系统消息最新一条
     * @apiSuccess {String} d.system_dialog.content   内容
     * @apiSuccess {number} d.system_dialog.unread    未读数
     * @apiSuccess {number} d.system_dialog.time        时间
     * @apiSuccess {object} d.notification_dialog    公告消息最新一条
     * @apiSuccess {String} d.notification_dialog.content   内容
     * @apiSuccess {number} d.notification_dialog.unread    未读数
     * @apiSuccess {number} d.notification_dialog.time        时间
     * @apiSuccess {object} d.short_video   小视频最新消息
     * @apiSuccess {number} d.short_video.num   未读数
     * @apiSuccess {number} d.short_video.time  最新时间
     * @apiSuccess {String} d.short_video.msg   消息内容
     * @apiSuccess {String} d.short_video.type  类型
     * @apiSuccess {object} d.short_posts    动态最新消息
     * @apiSuccess {number} d.short_posts.unread   未读数
     * @apiSuccess {String} d.short_posts.last_message_type  消息类型
     * @apiSuccess {String} d.short_posts.last_message_content  消息短内容
     * @apiSuccess {number} d.short_posts.last_message_time  时间戳
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *       {
     *           "c": 0,
     *           "m": "请求成功",
     *           "d": {
     *                   "system_dialog": {
     *                       "content": "哈哈哈哈哈哈哈哈关注了你",
     *                       "unread": "2",
     *                       "time": "1551766120",
     *                   },
     *                   "notification_dialog": {
     *                       "content": "这是公告",
     *                       "unread": "2",
     *                       "time": "1551766120",
     *                   },
     *                   "short_video": {
     *                           "num": "0",
     *                           "time": "0",
     *                           "msg": "",
     *                           "type": ""
     *                   },
     *                   "short_posts": {
     *                           "unread": "14",
     *                           "last_message_type": "comment",
     *                           "last_message_content": "您的动态收到一条评论",
     *                           "last_message_time": "1551940670"
     *                   }
     *           },
     *           "t": "1553077305"
     *       }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function notifyAction($nUserId = 0)
    {
        try {
            // 最新系统消息 开始
            $oUser = User::findFirst($nUserId);
            $oUserSystemMessageDialog = UserSystemMessageDialog::updateNotificationInfo($oUser);

            $row['system_dialog'] = [
                'content' => $oUserSystemMessageDialog->system_message_content,
                'unread'  => (string)$oUserSystemMessageDialog->user_system_message_unread,
                'time'    => $oUserSystemMessageDialog->user_system_message_dialog_update_time,
            ];

            $row['notification_dialog'] = [
                'content' => $oUserSystemMessageDialog->user_notification_message_content,
                'unread'  => (string)$oUserSystemMessageDialog->user_notification_message_unread,
                'time'    => $oUserSystemMessageDialog->user_notification_message_update_time,
            ];

            // 最新公告消息 结束


            // 最新小视频消息 开始
            $model               = new UserVideoMessage();
            $shortVideoUnreadNum = $model->getUnreadNum($nUserId);
            $oUserVideoMessage   = UserVideoMessage::findFirst("user_id={$nUserId} order by id DESC");
            $shortVideoLastTime  = '0';
            $shortVideoMsg       = '';
            $shortVideotype      = '';
            if ( $oUserVideoMessage ) {
                $shortVideoLastTime = $oUserVideoMessage->create_time;
                $shortVideoMsg      = $oUserVideoMessage->content;
                $shortVideotype     = $oUserVideoMessage->type;
            }
            $row['short_video'] = [
                'num'  => (string)$shortVideoUnreadNum,
                'time' => $shortVideoLastTime,
                'msg'  => $shortVideoMsg,
                'type' => $shortVideotype,
            ];
            // 最新小视频消息 结束

            // 最新动态 开始  （未读数  最新一条）
            $lastMessageAndUnread = ShortPostsMessage::getLastMessageAndUnread($nUserId);
            $row['short_posts']   = [
                'unread'               => (string)$lastMessageAndUnread['unread_count'],
                'last_message_type'    => $lastMessageAndUnread['last_message']['message_type'] ?? '',
                'last_message_content' => $lastMessageAndUnread['last_message_content'],
                'last_message_time'    => $lastMessageAndUnread['last_message']['create_time'] ?? '0',
            ];
            // 最新动态 结束

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/message/dialogList
     * @api {get} /user/message/dialogList 消息对话列表
     * @apiName message-dialogList
     * @apiGroup Message
     * @apiDescription 消息对话列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){Number} page  页码
     * @apiParam (正常请求){Number} pagesize  页数
     * @apiParam (正常请求){String='say_hi(打招呼)','normal(普通)'} dialog_type  类型
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){Number} page  页码
     * @apiParam (debug){Number} pagesize  页数
     * @apiParam (debug){String='say_hi(打招呼)','normal(普通)'} dialog_type  类型
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
    public function dialogListAction($nUserId = 0)
    {
        $dialogType = $this->getParams('dialog_type', 'string', 'normal');
        $nPage      = $this->getParams('page', 'int', 0);
        $nPgaesize  = $this->getParams('pagesize', 'int', 20);
        try {
            $builder = $this->modelsManager->createBuilder()->from([ 'ucd' => UserChatDialog::class ])
                ->join(User::class, 'u.user_id=ucd.to_user_id', 'u')
                ->columns('u.user_id,u.user_is_superadmin,u.user_nickname,u.user_avatar,u.user_level,ucd.user_chat_room_id chat_room_id,ucd.user_chat_content content,
                ucd.user_chat_unread unread,user_chat_dialog_update_time time,user_member_expire_time,u.user_sex,u.user_birth')
                ->where('ucd.user_id=:user_id:', [ 'user_id' => $nUserId ])
                ->orderBy('user_chat_dialog_update_time desc');
            if ( $dialogType == 'say_hi' ) {
                // 本人没有回复过的
                $builder->andWhere('user_chat_has_reply = "N"');
            } else {
                $builder->andWhere('user_chat_has_reply = "Y"');
            }
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
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/message/videoChatLog
     * @api {get} /user/message/videoChatLog 视频聊天记录
     * @apiName message-videoChatLog
     * @apiGroup Message
     * @apiDescription 视频聊天记录
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){Number} page  页码
     * @apiParam (正常请求){Number} pagesize  页数
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){Number} page  页码
     * @apiParam (debug){Number} pagesize  页数
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {number} d.items.dialog_id   聊天记录id
     * @apiSuccess {number} d.items.content  内容
     * @apiSuccess {number} d.items.time   时间戳
     * @apiSuccess {number} d.items.video_chat_status  视频聊天状态
     * @apiSuccess {number} d.items.video_chat_duration  时长
     * @apiSuccess {String} d.items.video_chat_has_callback   是否回拨
     * @apiSuccess {number} d.items.user_id   用户id
     * @apiSuccess {number} d.items.send_user_id   发起用户id
     * @apiSuccess {object} d.items.user_info  用户信息
     * @apiSuccess {String} d.items.user_info.user_id  用户id
     * @apiSuccess {String} d.items.user_info.user_avatar  用户头像
     * @apiSuccess {String} d.items.user_info.user_nickname  用户昵称
     * @apiSuccess {String} d.items.user_info.user_sex  用户性别
     * @apiSuccess {String} d.items.user_info.user_birth  用户生日
     * @apiSuccess {String} d.items.user_info.user_is_vip  用户是否为VIP
     * @apiSuccess {String} d.items.user_info.user_level  用户等级
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *    {
     *        "c": 0,
     *        "m": "请求成功",
     *        "d": {
     *                "items": [
     *                {
     *                    "dialog_id": "2859",
     *                    "content": "4469",
     *                    "time": "1543904025",
     *                    "video_chat_status": 6,
     *                    "video_chat_duration": 56,
     *                    "video_chat_has_callback": "Y",
     *                    "user_id": "321",
     *                    "send_user_id": "321",
     *                    "user_info": {
     *                    "user_id": "321",
     *                        "user_avatar": "http://lebolive-1255651273.file.myqcloud.com/avatar.jpg",
     *                        "user_nickname": "泡泡12041411158",
     *                        "user_sex": "2",
     *                        "user_birth": "",
     *                        "user_is_vip": "N",
     *                        "user_level": "1"
     *                    }
     *                },
     *                {
     *                    "dialog_id": "2860",
     *                    "content": "4470",
     *                    "time": "1543904295",
     *                    "video_chat_status": 6,
     *                    "video_chat_duration": 53,
     *                    "video_chat_has_callback": "Y",
     *                    "user_id": "321",
     *                    "send_user_id": "321",
     *                    "user_info": {
     *                    "user_id": "321",
     *                        "user_avatar": "http://lebolive-1255651273.file.myqcloud.com/avatar.jpg",
     *                        "user_nickname": "泡泡12041411158",
     *                        "user_sex": "2",
     *                        "user_birth": "",
     *                        "user_is_vip": "N",
     *                        "user_level": "1"
     *                    }
     *                }
     *            ],
     *            "page": 1,
     *            "pagesize": 2,
     *            "pagetotal": 374,
     *            "total": 747,
     *            "prev": 1,
     *            "next": 2
     *        },
     *        "t": "1553592832"
     *    }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function videoChatLogAction($nUserId = 0)
    {

        $nPage     = $this->getParams('page', 'int', 1);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        try {
            $oUser       = User::findFirst($nUserId);
            $offset      = ($nPage - 1) * $nPagesize;
            $maxNumber   = $nPage * $nPagesize;
            $anchorWhere = '';
            if ( $oUser->user_is_anchor == 'Y' ) {
                $anchorWhere = ' AND user_chat_pay_type = "C"';
            }
            $dataSql = <<<DATASQL
SELECT * FROM (            
(
SELECT user_chat_id dialog_id,user_chat_content content,user_chat_create_time time,user_chat_send_user_id send_user_id,
user_chat_receiv_user_id receiver,user_chat_extra FROM `user_chat` WHERE user_chat_type = "video_chat" AND user_chat_video_receiv_is_delete = 'N'
AND user_chat_receiv_user_id = $nUserId ORDER BY user_chat_id DESC LIMIT $maxNumber
)
UNION ALL
(
SELECT user_chat_id dialog_id,user_chat_content content,user_chat_create_time time,user_chat_send_user_id send_user_id,
user_chat_receiv_user_id receiver,user_chat_extra FROM `user_chat` WHERE user_chat_type = "video_chat" AND user_chat_video_send_is_delete = 'N'
AND user_chat_send_user_id = $nUserId $anchorWhere ORDER BY user_chat_id DESC LIMIT $maxNumber
)
) as t
ORDER BY t.dialog_id DESC LIMIT $nPagesize OFFSET $offset
DATASQL;
            $row     = $this->selfPage($dataSql, '', $nPage, $nPagesize);

//            $builder = $this->modelsManager->createBuilder()->from(UserChat::class)
//                ->columns('user_chat_id dialog_id,user_chat_content content,user_chat_create_time time,
//                user_chat_send_user_id  send_user_id,user_chat_receiv_user_id receiver,user_chat_extra')
//                ->andWhere('user_chat_type = "video_chat" AND (user_chat_receiv_user_id = :user_id: or user_chat_send_user_id = :user_id:)', [
//                    'user_id' => $nUserId
//                ])->orderBy('user_chat_id desc');
//            $row     = $this->page($builder, $nPage, $nPagesize);

            $userIdArr = [];
            foreach ( $row['items'] as &$item ) {
                $extraArr                        = unserialize($item['user_chat_extra']);
                $videoChatStatus                 = $extraArr['video_chat_status'] ?? '';
                $videoChatDuration               = $extraArr['video_chat_duration'] ?? '0';
                $videoChatHasCallback            = $extraArr['video_chat_has_callback'] ?? 'Y';
                $item['video_chat_status']       = (string)$videoChatStatus;
                $item['video_chat_duration']     = (string)$videoChatDuration;
                $item['video_chat_has_callback'] = (string)$videoChatHasCallback;

                if ( $item['send_user_id'] == $nUserId ) {
                    $userIdArr[]     = $item['receiver'];
                    $item['user_id'] = $item['receiver'];
                } else {
                    $userIdArr[]     = $item['send_user_id'];
                    $item['user_id'] = $item['send_user_id'];
                }
                unset($item['user_chat_extra']);
                unset($item['receiver']);
            }

            // 查询所有用户信息
            if ( $userIdArr ) {
                $userStr   = implode(',', $userIdArr);
                $oUserList = User::find([
                    "user_id in ($userStr)",
                    'columns' => 'user_id,user_avatar,user_nickname,user_sex,user_birth,user_member_expire_time,user_level'
                ]);

                $userArr = [];
                foreach ( $oUserList as $userItem ) {
                    $itemUserId                 = $userItem->user_id;
                    $userItemArr                = $userItem->toArray();
                    $userItemArr['user_is_vip'] = $userItemArr['user_member_expire_time'] > time() ? 'Y' : 'N';
                    unset($userItemArr['user_member_expire_time']);

                    $userArr[$itemUserId] = $userItemArr;
                }

                // 遍历结果 加入用户数据
                foreach ( $row['items'] as &$item ) {
                    $thisUser          = $userArr[$item['user_id']];
                    $item['user_info'] = $thisUser;
                }
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/message/systemDialog
     * @api {GET} /user/message/systemDialog 系统消息/公告消息列表
     * @apiName message-systemDialog
     * @apiGroup Message
     * @apiDescription 系统消息/公告消息列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String='normal(普通)','system（公告）'} type 类型
     * @apiParam (正常请求){String} dialog_id  上次请求最小dialog id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String='normal(普通)','system（公告）'} type 类型
     * @apiParam (debug){String} dialog_id  上次请求最小dialog id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {Object} d.system_dialog 内容
     * @apiSuccess {string} d.system_dialog.dialog_id 记录id
     * @apiSuccess {string} d.system_dialog.msg 内容
     * @apiSuccess {string} d.system_dialog.time 时间戳
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *       "c": 0,
     *       "m": "请求成功",
     *       "d": {
     *               "system_dialog": [{
     *                   "dialog_id": "3975",
     *                   "msg": "{\"type\":\"general\",\"data\":{\"content\":\"1111\",\"url\":\"\"}}",
     *                   "time": "1556088923"
     *           }, {
     *                   "dialog_id": "3974",
     *                   "msg": "{\"type\":\"general\",\"data\":{\"content\":\"7777\",\"url\":\"\"}}",
     *                   "time": "1556088576"
     *           }]
     *       },
     *       "t": "1556089575"
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function systemDialogAction($nUserId = 0)
    {

        $nPgaesize        = $this->getParams('pagesize', 'int', 20);
        $sType            = $this->getParams('type', 'string', UserSystemMessageDialog::TYPE_SYSTEM);
        $nSystemMessageId = $this->getParams('dialog_id', 'int', 0);
        $nPage            = $this->getParams('page', 'int', 1);
        $nPgaesize        = min($nPgaesize, 100);
        try {
            $checkUserIds = $this->getCheckUserIds();
            if ( in_array($nUserId, $checkUserIds) ) {
                $sType = 'checkUser';
            }
            $oUser = User::findFirst($nUserId);
            switch ( $sType ) {
                case 'checkUser':
                    $row['system_dialog'] = [];
                    break;
                // 系统消息
                case 'normal':
                    // 普通提示
                    $builder = $this->modelsManager->createBuilder()
                        ->from([ 'usm' => UserSystemMessage::class ])
                        ->join(SystemMessage::class, 'sm.system_message_id = usm.system_message_id', 'sm')
                        ->columns('sm.system_message_id dialog_id,sm.system_message_content msg,sm.system_message_create_time time')
                        ->where("system_message_is_admin = 'N' and usm.user_id = :user_id:", [
                            'user_id' => $nUserId,
                        ]);
                    if ( $nSystemMessageId != 0 ) {
                        $builder->andWhere('sm.system_message_id<:system_message_id:', [ 'system_message_id' => $nSystemMessageId ])->limit($nPgaesize);
                    } else if ( $nPage != 0 ) {
                        $builder->limit($nPgaesize, (max($nPage - 1, 0)) * $nPgaesize);
                    }
                    if ( $this->getParams('app_name') == 'huanggua' ) {
                        $row['system_dialog'] = [];
                    } else {
                        $row['system_dialog'] = $builder->orderBy('sm.system_message_id desc')->getQuery()->execute();
                    }
                    if ( $nSystemMessageId == 0 ) {
                        $oUserSystemMessageDialog = UserSystemMessageDialog::findFirst([
                            'user_id=:user_id:',
                            'bind' => [
                                'user_id' => $nUserId,
                            ]
                        ]);

                        if ( $oUserSystemMessageDialog && $oUserSystemMessageDialog->user_system_message_unread != 0 ) {
                            $oUserSystemMessageDialog->user_system_message_unread = 0;
                            $oUserSystemMessageDialog->save();
                        }
                    }
                    break;
                case UserSystemMessageDialog::TYPE_SYSTEM:
                default:
                    if ( $oUser->user_is_anchor == 'Y' ) {
                        $builder = $this->modelsManager->createBuilder()
                            ->from([ 'sm' => SystemMessage::class ])
                            ->columns('sm.system_message_id dialog_id,sm.system_message_content msg,sm.system_message_create_time time')
                            ->where("system_message_is_admin = 'Y' and (sm.user_id like :user_id: or sm.system_message_push_type = 0 or sm.system_message_push_type = 2) and sm.system_message_create_time >= :time:", [
                                'time'    => $oUser->user_create_time,
                                'user_id' => "%" . $nUserId . "%",
                            ]);
                    } else {
                        $builder = $this->modelsManager->createBuilder()
                            ->from([ 'sm' => SystemMessage::class ])
                            ->columns('sm.system_message_id dialog_id,sm.system_message_content msg,sm.system_message_create_time time')
                            ->where("system_message_is_admin = 'Y' and (sm.user_id like :user_id: OR sm.system_message_push_type = 0) and sm.system_message_create_time >= :time:", [
                                'time'    => $oUser->user_create_time,
                                'user_id' => "%" . $nUserId . "%",
                            ]);
                    }
                    if ( $nSystemMessageId != 0 ) {
                        $builder->andWhere('sm.system_message_id<:system_message_id:', [ 'system_message_id' => $nSystemMessageId ])->limit($nPgaesize);
                    } else if ( $nPage != 0 ) {
                        $builder->limit($nPgaesize, (max($nPage - 1, 0)) * $nPgaesize);
                    }
                    if ( $this->getParams('app_name') == 'huanggua' ) {
                        $row['system_dialog'] = [];
                    } else {
                        $row['system_dialog'] = $builder->orderBy('sm.system_message_id desc')->getQuery()->execute();
                    }
                    // 将未读数至0

                    if ( $nSystemMessageId == 0 ) {
                        $oUserSystemMessageDialog = UserSystemMessageDialog::findFirst([
                            'user_id=:user_id:',
                            'bind' => [
                                'user_id' => $nUserId,
                            ]
                        ]);

                        if ( $oUserSystemMessageDialog && $oUserSystemMessageDialog->user_notification_message_unread != 0 ) {
                            $oUserSystemMessageDialog->user_notification_message_unread = 0;
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
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/message/removeVideoChat
     * @api {post} /user/message/removeVideoChat 删除通话记录
     * @apiName message-removeVideoChat
     * @apiGroup Message
     * @apiDescription 删除通话记录
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} dialog_id 聊天记录ID
     * @apiParam (正常请求){String='Y','N'} is_clear 是否清除全部 清除全部传Y 将忽略dialog_id参数
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} dialog_id 聊天记录ID
     * @apiParam (debug){String='Y','N'} is_clear 是否清除全部 清除全部传Y 将忽略dialog_id参数
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
    public function removeVideoChatAction($nUserId = 0)
    {
        $dialogId = $this->getParams('dialog_id');
        $isClear = $this->getParams('is_clear');
        try{
            if($isClear == 'Y'){
                // 清除全部
                $clearSendSql = "update user_chat set user_chat_video_send_is_delete = 'Y' WHERE
 user_chat_send_user_id = {$nUserId} AND user_chat_video_send_is_delete = 'N'";

                $clearReceiveSql = "update user_chat set user_chat_video_receiv_is_delete = 'Y' WHERE
 user_chat_receiv_user_id = {$nUserId} AND user_chat_video_receiv_is_delete = 'N'";

                $this->db->execute($clearSendSql);
                $this->db->execute($clearReceiveSql);
            }else{
                if(!$dialogId){
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'dialog_id'),
                        ResponseError::USER_IS_CERTIFICATION
                    );
                }
                $oUserChat = UserChat::findFirst($dialogId);

                if( $oUserChat->user_chat_send_user_id == $nUserId ){

                    $oUserChat->user_chat_video_send_is_delete = 'Y';

                }elseif( $oUserChat->user_chat_receiv_user_id == $nUserId ){

                    $oUserChat->user_chat_video_receiv_is_delete = 'Y';
                }else{
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), 'dialog_id'),
                        ResponseError::FAIL
                    );
                }
                $oUserChat->save();
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }



}