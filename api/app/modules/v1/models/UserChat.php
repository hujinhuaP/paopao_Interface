<?php

namespace app\models;

/**
 * UserChat 用户聊天对话ID
 */
class UserChat extends ModelBase
{
    public $user_chat_id;
    public $user_chat_room_id;
    public $user_chat_send_user_id;
    public $user_chat_receiv_user_id;
    public $user_chat_content;
    public $user_chat_create_time;
    public $user_chat_update_time;
    public $user_chat_price;
    public $user_chat_pay_type;
    public $user_chat_type;
    public $user_chat_source_url;
    public $user_chat_extra;
    public $user_chat_income;
    public $user_chat_send_read_type;
    public $user_chat_receive_read_type;
    public $user_chat_video_send_is_delete;
    public $user_chat_video_receiv_is_delete;

    /** @var string 文字 */
    const TYPE_WORD = 'word';
    /** @var string 礼物 */
    const TYPE_GIFT = 'gift';
    /** @var string 语音 */
    const TYPE_VOICE = 'voice';
    /** @var string 图片 */
    const TYPE_IMAGE = 'image';
    /** @var string 视频 */
    const TYPE_VIDEO = 'video';
    /** @var string 视频通话 */
    const TYPE_VIDEO_CHAT = 'video_chat';

    /** @var string 视频通话未接 */
    const EXTRA_VIDEO_CHAT_STATUS_MISSED = 'missed';

    const MSG_TYPE_ARR = [
        'word',
        'image',
        'video',
        'voice',
        'gift'
    ];

    // vip才能发的类型
    const VIP_MSG_TYPE_ARR = [
        'image',
        'video',
        'voice',
    ];

    public function beforeCreate()
    {
        $this->user_chat_update_time = time();
        $this->user_chat_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_chat_update_time = time();
    }

    /**
     * @param \app\models\User $oUser
     * @param \app\models\User $oToUser
     * @param string $content
     * @param string $type
     */
    public function addMessage($oUser, $oToUser)
    {

        $nUserId     = $oUser->user_id;
        $nToUserId   = $oToUser->user_id;
        $sChatRoomId = $this->user_chat_room_id;
        $this->save();

        switch ( $this->user_chat_type ) {
            case self::TYPE_VIDEO_CHAT:
                $sendDialogContent    = '视频聊天';
                $receiveDailogContent = '视频聊天';
                $pushContent          = $this->user_chat_content;
                break;
            case self::TYPE_VOICE:
                $sendDialogContent    = '发送一段语音';
                $receiveDailogContent = '收到一段语音';
                $pushContent          = $this->user_chat_content;
                break;
            case self::TYPE_IMAGE:
                $sendDialogContent    = '发送一张图片';
                $receiveDailogContent = '收到一张图片';
                $pushContent          = $this->user_chat_content;
                break;
            case self::TYPE_VIDEO:
                $sendDialogContent    = '发送一段视频';
                $receiveDailogContent = '收到一段视频';
                $pushContent          = $this->user_chat_content;
                break;
            case self::TYPE_GIFT:
                $sendDialogContent    = '送出一个礼物';
                $receiveDailogContent = '收到一个礼物';
                $pushContent          = $this->user_chat_content;
                break;
            case self::TYPE_WORD:
            default:
                $sendDialogContent = $receiveDailogContent = $pushContent = $oUser->user_is_superadmin == 'C' || $oToUser->user_is_superadmin == 'C' ? $this->user_chat_content : (new Banword())->filterContent($this->user_chat_content);

        }

        if ( $this->user_chat_pay_type != 'G' ) {
            // 添加发送用户对话
            $oUserChatDialog = UserChatDialog::findFirst([
                'user_id=:user_id: and to_user_id=:to_user_id:',
                'bind' => [
                    'user_id'    => $nUserId,
                    'to_user_id' => $nToUserId,
                ]
            ]);
            if ( !$oUserChatDialog ) {
                $oUserChatDialog                    = new UserChatDialog();
                $oUserChatDialog->user_chat_room_id = $sChatRoomId;
                $oUserChatDialog->user_id           = $nUserId;
                $oUserChatDialog->to_user_id        = $nToUserId;
            }
            $oUserChatDialog->user_chat_has_reply = 'Y';
            $oUserChatDialog->user_chat_id        = $this->user_chat_id;
            // 主播看不到自己发送的诱导消息
            $oUserChatDialog->user_chat_content            = $sendDialogContent;
            $oUserChatDialog->user_chat_unread             = 0;
            $oUserChatDialog->user_chat_dialog_update_time = time();
            $oUserChatDialog->save();
        }

        // 添加接受用户对话
        $oToUserChatDialog = UserChatDialog::findFirst([
            'user_id=:user_id: and to_user_id=:to_user_id:',
            'bind' => [
                'user_id'    => $nToUserId,
                'to_user_id' => $nUserId,
            ]
        ]);
        if ( !$oToUserChatDialog ) {
            $oToUserChatDialog                      = new UserChatDialog();
            $oToUserChatDialog->user_chat_room_id   = $sChatRoomId;
            $oToUserChatDialog->user_id             = $nToUserId;
            $oToUserChatDialog->to_user_id          = $nUserId;
            $oToUserChatDialog->user_chat_unread    = 0;
            $oToUserChatDialog->user_chat_has_reply = 'N';
        }
        $oToUserChatDialog->user_chat_id                 = $this->user_chat_id;
        $oToUserChatDialog->user_chat_content            = $receiveDailogContent;
        $oToUserChatDialog->user_chat_unread             += 1;
        $oToUserChatDialog->user_chat_dialog_update_time = time();
        $oToUserChatDialog->save();

        $exp                  = '0';
        $anchorExp            = '0';
        $intimateValue        = '0';
        $videoChatStatus      = '';
        $videoChatDuration    = '';
        $videoChatHasCallback = 'Y';
        if ( $this->user_chat_type == UserChat::TYPE_VIDEO_CHAT ) {
            $extraArr             = unserialize($this->user_chat_extra);
            $videoChatStatus      = $extraArr['video_chat_status'] ?? '';
            $videoChatDuration    = $extraArr['video_chat_duration'] ?? '0';
            $videoChatHasCallback = $extraArr['video_chat_has_callback'] ?? 'Y';
        } else if ( $this->user_chat_type == UserChat::TYPE_GIFT ) {
            $extraArr              = unserialize($this->user_chat_extra);
            $exp                   = $extraArr['exp'] ?? '0';
            $intimateValue         = $extraArr['intimate_value'] ?? '0';
            $anchorExp             = $extraArr['anchor_exp'] ?? '0';
            $this->user_chat_extra = $extraArr['gift_number'] ?? '1';
        }

        // 推送聊天的内容
        $aPushMessage = [
            'pushContent'  => $pushContent,
            'chat_room_id' => $sChatRoomId,
            'dialog'       => [
                'from_user'               => [
                    'user_id'        => $oUser->user_id,
                    'user_nickname'  => $oUser->user_nickname,
                    'user_avatar'    => $oUser->user_avatar,
                    'user_level'     => $oUser->user_level,
                    'send_read_type' => $this->user_chat_send_read_type,
                    'user_is_member' => $oUser->user_member_expire_time == 0 ? 'N' : (time() > $oUser->user_member_expire_time ? 'O' : 'Y'),
                ],
                'to_user'                 => [
                    'user_id'        => $oToUser->user_id,
                    'user_nickname'  => $oToUser->user_nickname,
                    'user_avatar'    => $oToUser->user_avatar,
                    'user_level'     => $oToUser->user_level,
                    'send_read_type' => $this->user_chat_receive_read_type,
                    'user_is_member' => $oToUser->user_member_expire_time == 0 ? 'N' : (time() > $oToUser->user_member_expire_time ? 'O' : 'Y'),
                ],
                'msg'                     => $pushContent,
                'time'                    => $this->user_chat_create_time,
                'dialog_id'               => $this->user_chat_id,
                'type'                    => $this->user_chat_type,
                'source_url'              => $this->user_chat_source_url,
                'extra'                   => $this->user_chat_extra,
                'income'                  => $this->user_chat_income,
                'pay_coin'                => $this->user_chat_price,
                'is_say_hi'               => $oToUserChatDialog->user_chat_has_reply == 'Y' ? 'N' : 'Y',
                'video_chat_status'       => $videoChatStatus,
                'video_chat_duration'     => (string)$videoChatDuration,
                'video_chat_has_callback' => (string)$videoChatHasCallback,
                'exp'                     => $exp,
                'intimate_value'          => $intimateValue,
                'anchor_exp'              => $anchorExp
            ],
        ];
        return $aPushMessage;

    }
}