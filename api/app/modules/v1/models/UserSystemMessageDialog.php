<?php

namespace app\models;

/**
* UserSystemMessageDialog 用户系统消息对话表
*/
class UserSystemMessageDialog extends ModelBase
{
	/** @var string 消息类型 */
    const TYPE_SYSTEM = 'system';

    public $user_system_message_dialog_id;
    public $user_id;
    public $system_message_id;
    public $system_message_content;
    public $user_system_message_type;
    public $user_system_message_unread;
    public $user_system_message_dialog_create_time;
    public $user_system_message_dialog_update_time;
    public $user_notification_message_content;
    public $user_notification_message_id;
    public $user_notification_message_unread;
    public $user_notification_message_update_time;


	public function beforeCreate()
    {
		$this->user_system_message_dialog_create_time = time();
    }

    public function beforeUpdate()
    {
        
    }


    /**
     * @param $oUser
     * @return UserSystemMessageDialog
     *
     * 更新通知信息 并获取dialog
     *
     */
    public static function updateNotificationInfo($oUser)
    {
        $nUserId = $oUser->user_id;
        $oUserSystemMessageDialog = UserSystemMessageDialog::findFirst([
            'user_id=:user_id:',
            'bind' => [
                'user_id' => $nUserId,
            ]
        ]);
        if ( !$oUserSystemMessageDialog ) {
            $oUserSystemMessageDialog = new UserSystemMessageDialog();
            $oUserSystemMessageDialog->user_id = $nUserId;
        }
        if ( $oUser->user_is_anchor == 'Y' ) {

            $oSystemMessage = SystemMessage::findFirst([
                "system_message_is_admin = 'Y' and (user_id like :user_id: or system_message_push_type = 0 or system_message_push_type = 2) and system_message_create_time >= :time:",
                'bind'  => [
                    'time'    => $oUser->user_create_time,
                    'user_id' => "%" . $nUserId . "%",
                ],
                'order' => 'system_message_id desc'
            ]);
        } else {
            $oSystemMessage = SystemMessage::findFirst([
                "system_message_is_admin = 'Y' and (user_id like :user_id: OR system_message_push_type = 0) and system_message_create_time >= :time:",
                'bind'  => [
                    'time'    => $oUser->user_create_time,
                    'user_id' => "%" . $nUserId . "%",
                ],
                'order' => 'system_message_id desc'
            ]);
        }

        if ( $oSystemMessage ) {

            if ( isset($oSystemMessage->system_message_content) ) {
                $aData                                  = json_decode($oSystemMessage->system_message_content, 1);
                $oSystemMessage->system_message_content = isset($aData['data']['content']) ? $aData['data']['content'] : '';
            }


            if ( $oSystemMessage && $oUserSystemMessageDialog->user_notification_message_id < $oSystemMessage->system_message_id ) {
                // 更新消息
                $oUserSystemMessageDialog->user_notification_message_content = $oSystemMessage->system_message_content;

                if ( $oUser->user_is_anchor == 'Y' ) {

                    $oUserSystemMessageDialog->user_notification_message_unread += SystemMessage::count([
                        "system_message_is_admin = 'Y' and (user_id like :user_id: or system_message_push_type = 0 or system_message_push_type = 2)  AND system_message_id>:system_message_id:",
                        'bind' => [
                            'system_message_id' => $oUserSystemMessageDialog->user_notification_message_id,
                            'user_id'           => "%" . $nUserId . "%",
                        ],
                    ]);
                } else {
                    $oUserSystemMessageDialog->user_notification_message_unread += SystemMessage::count([
                        "system_message_is_admin = 'Y' and (user_id like :user_id: OR system_message_push_type = 0)  AND system_message_id>:system_message_id:",
                        'bind' => [
                            'system_message_id' => $oUserSystemMessageDialog->user_notification_message_id,
                            'user_id'           => "%" . $nUserId . "%",
                        ],
                    ]);
                }

                $oUserSystemMessageDialog->user_notification_message_id          = $oSystemMessage->system_message_id;
                $oUserSystemMessageDialog->user_notification_message_update_time = $oSystemMessage->system_message_create_time;

                $oUserSystemMessageDialog->save();
            }
        }
        return $oUserSystemMessageDialog;
    }


}