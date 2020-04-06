<?php

namespace app\models;

use app\helper\JiGuangApi;
use app\services\ChatStreamService;
use Phalcon\Di;

/**
 * UserPrivateChatLog 一对一私聊记录表
 */
class UserPrivateChatLog extends ModelBase
{
    use \app\services\UserService;
    /*聊天模式  匹配*/
    const CHAT_TYPE_MATCH = 'match';
    /*聊天模式  普通拨打*/
    const CHAT_TYPE_NORMAL = 'normal';
    /** @var string 派单 */
    const CHAT_TYPE_DISPATCH = 'dispatch';
    /** @var string 免费类型  守护 */
    const FREE_TIME_TYPE_GUARD = 'guard';
    /** @var string 免费类型  空 */
    const FREE_TIME_TYPE_EMPTY = 'empty';
    /** @var string 免费类型  匹配赠送 */
    const FREE_TIME_TYPE_GIVE = 'give';

    public $id;
    public $inviter_id              = 0;
    public $invitee_id              = 0;
    public $chat_log_user_id        = 0;
    public $chat_log_anchor_user_id = 0;
    public $status                  = 0;
    public $dialog_id               = 0;
    public $duration                = 0;
    public $create_time             = 0;
    public $chat_type;
    public $snatch_user_id          = 0;
    public $is_snatch               = 'N';
    public $is_user_call            = 'Y';
    public $update_time             = 0;
    public $free_times_type         = self::FREE_TIME_TYPE_EMPTY;

    public function beforeCreate()
    {
        $this->create_time = time();
    }

    public function addData( $chat_log_user_id, $chat_log_anchor_user_id, $status, $dialog_id, $chat_type = self::CHAT_TYPE_NORMAL, $is_snatch = 'N', $returnPush = FALSE )
    {
        $model                          = new UserPrivateChatLog();
        $model->chat_log_user_id        = $this->chat_log_user_id ?? $chat_log_user_id;
        $model->chat_log_anchor_user_id = $this->chat_log_anchor_user_id ?? $chat_log_anchor_user_id;
        $model->status                  = $status;
        $model->dialog_id               = $dialog_id;
        $model->chat_type               = $chat_type;
        $model->is_snatch               = $is_snatch;
        $model->is_user_call            = $this->is_user_call;
        $model->inviter_id              = $this->inviter_id;
        $model->invitee_id              = $this->invitee_id;
        $model->free_times_type         = $this->free_times_type;
        $model->create();

        // 添加后 添加用户消息记录
        $oUserChat                           = new UserChat();
        $oUserChat->user_chat_room_id        = UserChatDialog::getChatRoomId($chat_log_user_id, $chat_log_anchor_user_id);
        $oUserChat->user_chat_send_user_id   = $chat_log_user_id;
        $oUserChat->user_chat_receiv_user_id = $chat_log_anchor_user_id;
        $oUserChat->user_chat_content        = $model->id;
        $oUserChat->user_chat_pay_type       = 'C';
        $oUserChat->user_chat_type           = UserChat::TYPE_VIDEO_CHAT;
        $oUserChat->user_chat_price          = 0;
        $oUserChat->user_chat_income         = 0;
        $oUserChat->user_chat_extra          = serialize([
            'video_chat_status'       => $status,
            'video_chat_duration'     => 0,
            'video_chat_has_callback' => 'N'
        ]);
        $oUser                               = User::findFirst($chat_log_user_id);
        $oToUser                             = User::findFirst($chat_log_anchor_user_id);
        $aPushMessage                        = $oUserChat->addMessage($oUser, $oToUser);

        if ( $returnPush ) {
            return [
                'push'         => $aPushMessage,
                'id'           => $model->id,
                'user_chat_id' => $oUserChat->user_chat_id,
            ];
        }
        return $model->id;
    }

    public function afterCreate()
    {
        $dialog = UserPrivateChatDialog::findFirst($this->dialog_id);
        if ( $dialog ) {
            $dialog->update_time = time();
            $dialog->save();
        }
    }

    public function beforeUpdate()
    {
        $log = self::getLog();
        // 同时修改 聊天中的聊天记录状态
        $oUserChat = UserChat::findFirst([
            'user_chat_room_id = :user_chat_room_id: AND user_chat_type = :user_chat_type: AND user_chat_content = :content:',
            'bind' => [
                'user_chat_room_id' => UserChatDialog::getChatRoomId($this->chat_log_user_id, $this->chat_log_anchor_user_id),
                'user_chat_type'    => UserChat::TYPE_VIDEO_CHAT,
                'content'           => $this->id
            ]
        ]);
        if ( $oUserChat ) {
            $extraData                        = unserialize($oUserChat->user_chat_extra);
            $extraData['video_chat_status']   = $this->status;
            $extraData['video_chat_duration'] = $this->duration;
            $oUserChat->user_chat_extra       = serialize($extraData);
            $oUserChat->save();
        }
        $log->info(sprintf("chat id :[%d] status to %d", $this->id, $this->status));
        $this->update_time = time();

        if ( $this->status == 4 ) {
            // 添加通话中的流的记录
//            $userStreamId = md5($this->id . '_' . $this->chat_log_user_id . '_main');
            $userStreamId           = $this->getStreamId('user');
            $oUserChatStreamService = new ChatStreamService($userStreamId);
            $oUserChatStreamService->save([
                'user_id'        => $this->chat_log_user_id,
                'chat_log_id'    => $this->id,
                'user_is_anchor' => 'N',
                'other_user_id'  => $this->chat_log_anchor_user_id,
            ]);
//            $anchorStreamId = md5($this->id . '_' . $this->chat_log_anchor_user_id . '_main');
            $anchorStreamId           = $this->getStreamId('anchor');
            $oAnchorChatStreamService = new ChatStreamService($anchorStreamId);
            $oAnchorChatStreamService->save([
                'user_id'        => $this->chat_log_anchor_user_id,
                'chat_log_id'    => $this->id,
                'user_is_anchor' => 'Y',
                'other_user_id'  => $this->chat_log_user_id,
            ]);
        }

        // 如果是 用户取消 超时取消  主播挂断 连续超过10个不同的用户 则将主播 在状态改为关闭
        if ( in_array($this->status, [
                2,
                5
            ]) || ($this->status == 1 && time() - $this->create_time > 5) ) {
            //1已取消(且时长超过5秒) 2对方拒绝 5对方无应答

            $redis        = self::getRedis();
            $notAcceptKey = sprintf('not_accept:%s', $this->chat_log_anchor_user_id);
            $redis->sAdd($notAcceptKey, $this->chat_log_user_id);
            $notAceeptCount = $redis->sCard($notAcceptKey);
            $setNumber = 5;
            if ( $notAceeptCount >= $setNumber ) {
                // 达到$setNumber个 修改状态 推送信息
                $oAnchor                     = Anchor::findFirst([
                    'user_id = :user_id:',
                    'bind' => [
                        'user_id' => $this->chat_log_anchor_user_id
                    ]
                ]);
                $oAnchor->anchor_chat_status = Anchor::CHAT_STATUS_OFF;
                $oAnchor->save();
                $this->log->info(json_encode($oAnchor->getMessages()));

                // 推送给主播信息
                $content   = sprintf('您连续未接听%s个用户拨打的视频通话，系统自动为您设置成“忙碌”状态，若需接单请前往重新开启',$setNumber);
                $timServer = self::getTimServer();
                $timServer->setUid($this->chat_log_anchor_user_id);
                $flg = $timServer->sendCloseChatStatusMsg([
                    'content' => $content
                ]);

                $anchorUser = User::findFirst($this->chat_log_anchor_user_id);
                $appInfo    = $this->getAppInfo('qq', $anchorUser->user_app_flg ? $anchorUser->user_app_flg : 'tianmi');
                $jPush      = new JiGuangApi($appInfo['jpush_app_key'], $appInfo['jpush_master_secret'], NULL, APP_ENV == 'dev' ? FALSE : TRUE);
                $res        = $jPush->push([ 'alias' => [ "{$this->chat_log_anchor_user_id}" ] ], '提示消息', $content, [
                    'type'    => 'close_chat_status',
                    'content' => $content
                ]);
                $this->log->info($res);
                // 清除
                $redis->delete($notAcceptKey);
            }
        } elseif ( $this->status == 4 ) {
            $redis        = self::getRedis();
            $notAcceptKey = sprintf('not_accept:%s', $this->chat_log_anchor_user_id);
            $redis->delete($notAcceptKey);
        }
    }

    /**
     * @param string $type
     * @return string
     * 获取此次通话记录流id
     */
    public function getStreamId( $type = 'user' )
    {
        $config = self::getConfig();
        if ( $type == 'user' ) {
            return $config->live->bizid . '_' . md5($this->id . '_' . $this->chat_log_user_id . '_main');
        } else {
            return $config->live->bizid . '_' . md5($this->id . '_' . $this->chat_log_anchor_user_id . '_main');
        }
    }

    public function test()
    {
        // 推送给主播信息
        $content   = '您连续未接听10个用户拨打的视频通话，系统自动为您设置成“忙碌”状态，若需接单请前往重新开启';
        $timServer = self::getTimServer();
        $timServer->setUid($this->chat_log_anchor_user_id);
        $flg1       = $timServer->sendCloseChatStatusMsg([
            'content' => $content
        ]);
        $anchorUser = User::findFirst($this->chat_log_anchor_user_id);
        $appInfo    = $this->getAppInfo('qq', $anchorUser->user_app_flg ? $anchorUser->user_app_flg : 'tianmi');
        $jPush      = new JiGuangApi($appInfo['jpush_app_key'], $appInfo['jpush_master_secret'], NULL, APP_ENV == 'dev' ? FALSE : TRUE);
        $res        = $jPush->push([ 'alias' => [ "{$this->chat_log_anchor_user_id}" ] ], '提示消息', $content, [
            'type'    => 'close_chat_status',
            'content' => $content
        ]);
        return [
            $flg1,
            $res
        ];
    }

}