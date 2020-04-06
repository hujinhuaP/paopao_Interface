<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 系统消息服务                                                           |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\services;

use app\models\Kv;
use Exception;
use app\helper\ResponseError;
use app\models\SystemMessage;
use app\models\UserSystemMessage;
use app\models\UserSystemMessageDialog;


/**
 * SystemMessageService
 */
trait SystemMessageService
{
    /**
     * sendGeneral 发送普通系统消息
     *
     * @param  int $nUserID
     * @param  string $sContent
     * @param  string $sUrl
     * @return bool
     */
    public function sendGeneral($nUserID = 0, $sContent, $sUrl = '',$isPush = '',$type = '')
    {
        $sType = SystemMessage::TYPE_GENERAL;
        if($type){
            $sType = $type;
        }
        $sTitle = $sContent;
        $sMsg   = json_encode([
            'type' => $sType,
            'data' => [
                'content' => $sContent,
                'url'     => $sUrl,
            ],
        ], JSON_UNESCAPED_UNICODE);

        $oSystemMessage                           = new SystemMessage();
        $oSystemMessage->system_message_title     = $sTitle;
        $oSystemMessage->system_message_content   = $sMsg;
        $oSystemMessage->system_message_type      = $sType;
        $oSystemMessage->system_message_push_type = $nUserID ? '1' : '0';
        if($isPush){
            $oSystemMessage->system_message_status    = 'N';
        }
        // 这里的用户id只是为了后台的显示，不要使用这个字段查询用户的系统消息，用户的系统消息在UserSystemMessage中有
        $oSystemMessage->user_id = $nUserID;
        if ( $oSystemMessage->save() === FALSE ) {
            return FALSE;
        }

        if ( $nUserID != 0 ) {


            if($isPush){
                // 队列进行
                $oTaskQueueService = new TaskQueueService();
                $oTaskQueueService->enQueue([
                    'task'   => 'systemmessage',
                    'action' => 'push',
                    'param'  => [
                        'system_message_id' => $oSystemMessage->system_message_id,
                    ],
                ]);
            }else{
                $oUserSystemMessage                    = new UserSystemMessage();
                $oUserSystemMessage->user_id           = $nUserID;
                $oUserSystemMessage->system_message_id = $oSystemMessage->system_message_id;

                if ( $oUserSystemMessage->save() === FALSE ) {
                    return FALSE;
                }

                $oUserSystemMessageDialog = UserSystemMessageDialog::findFirst([
                    'user_id=:user_id:',
                    'bind' => [
                        'user_id' => $nUserID,
                    ]
                ]);

                if ( $oUserSystemMessageDialog == FALSE ) {
                    $oUserSystemMessageDialog                             = new UserSystemMessageDialog();
                    $oUserSystemMessageDialog->user_system_message_unread = 0;
                }

                $oUserSystemMessageDialog->user_id                                = $nUserID;
                $oUserSystemMessageDialog->system_message_id                      = $oSystemMessage->system_message_id;
                $oUserSystemMessageDialog->system_message_content                 = $sTitle;
                $oUserSystemMessageDialog->user_system_message_type               = UserSystemMessageDialog::TYPE_SYSTEM;
                $oUserSystemMessageDialog->user_system_message_unread             += 1;
                $oUserSystemMessageDialog->user_system_message_dialog_update_time = $oSystemMessage->system_message_create_time;
                $oUserSystemMessageDialog->save();
                return TRUE;
            }
        }

        return TRUE;
    }

    /**
     * sendFollowMsg 发送关注系统消息
     *
     * @param  int $nUserID 接收用户id
     * @param  \app\models\User $oUser 关注的用户
     * @return bool
     */
    public function sendFollowMsg($nUserID, \app\models\User $oUser)
    {
        $sTitle = $oUser->user_nickname . '关注了你';
        $sMsg   = json_encode([
            'type' => SystemMessage::TYPE_FOLLOW,
            'data' => [
                'user_id' => $oUser->user_id,
                'content' => $sTitle,
            ],
        ], JSON_UNESCAPED_UNICODE);

        $oSystemMessage                           = new SystemMessage();
        $oSystemMessage->system_message_title     = $sTitle;
        $oSystemMessage->system_message_content   = $sMsg;
        $oSystemMessage->system_message_type      = SystemMessage::TYPE_FOLLOW;
        $oSystemMessage->system_message_push_type = '1';
        // 这里的用户id只是为了后台的显示，不要使用这个字段查询用户的系统消息，用户的系统消息在UserSystemMessage中有
        $oSystemMessage->user_id = $nUserID;
        if ( $oSystemMessage->save() === FALSE ) {
            return FALSE;
        }

        $oUserSystemMessage                    = new UserSystemMessage();
        $oUserSystemMessage->user_id           = $nUserID;
        $oUserSystemMessage->system_message_id = $oSystemMessage->system_message_id;

        if ( $oUserSystemMessage->save() === FALSE ) {
            return FALSE;
        }

        $oUserSystemMessageDialog = UserSystemMessageDialog::findFirst([
            'user_id=:user_id:',
            'bind' => [
                'user_id' => $nUserID,
            ]
        ]);

        if ( $oUserSystemMessageDialog == FALSE ) {
            $oUserSystemMessageDialog                             = new UserSystemMessageDialog();
            $oUserSystemMessageDialog->user_system_message_unread = 0;
        }

        $oUserSystemMessageDialog->user_id                                = $nUserID;
        $oUserSystemMessageDialog->system_message_id                      = $oSystemMessage->system_message_id;
        $oUserSystemMessageDialog->system_message_content                 = $sTitle;
        $oUserSystemMessageDialog->user_system_message_type               = UserSystemMessageDialog::TYPE_SYSTEM;
        $oUserSystemMessageDialog->user_system_message_unread             += 1;
        $oUserSystemMessageDialog->user_system_message_dialog_update_time = $oSystemMessage->system_message_create_time;
        return $oUserSystemMessageDialog->save();
    }

    /**
     * sendWithdrawMsg 发送提现系统消息
     *
     * @param  int $nUserID
     * @param  \app\models\UserWithdrawLog $oUserWithdrawLog
     * @return
     */
    public function sendWithdrawMsg($nUserID, \app\models\UserWithdrawLog $oUserWithdrawLog)
    {
        switch ( $oUserWithdrawLog->user_withdraw_log_check_status ) {
            case 'Y':
                $sTitle = '提现申请已经审核通过了，请及时关注支付宝到账记录哦';
                break;

            case 'N':
                $sTitle = '提现申请审核未通过，不通过原因：' . $oUserWithdrawLog->user_withdraw_log_remark;
                break;

            case 'C':
            default:
                $sTitle = '提现申请已提交，每周一0点到24点统一审核打款，遇节假日顺延，请耐心等待 ~';
                break;
        }

        $sMsg = json_encode([
            'type' => SystemMessage::TYPE_WITHDRAW,
            'data' => [
                'withdraw_log_id' => $oUserWithdrawLog->user_withdraw_log_id,
                'content'         => $sTitle,
            ],
        ], JSON_UNESCAPED_UNICODE);

        $oSystemMessage                           = new SystemMessage();
        $oSystemMessage->system_message_title     = $sTitle;
        $oSystemMessage->system_message_content   = $sMsg;
        $oSystemMessage->system_message_type      = SystemMessage::TYPE_WITHDRAW;
        $oSystemMessage->system_message_push_type = '1';
        // 这里的用户id只是为了后台的显示，不要使用这个字段查询用户的系统消息，用户的系统消息在UserSystemMessage中有
        $oSystemMessage->user_id = $nUserID;
        if ( $oSystemMessage->save() === FALSE ) {
            return FALSE;
        }

        $oUserSystemMessage                    = new UserSystemMessage();
        $oUserSystemMessage->user_id           = $nUserID;
        $oUserSystemMessage->system_message_id = $oSystemMessage->system_message_id;

        if ( $oUserSystemMessage->save() === FALSE ) {
            return FALSE;
        }

        $oUserSystemMessageDialog = UserSystemMessageDialog::findFirst([
            'user_id=:user_id:',
            'bind' => [
                'user_id' => $nUserID,
            ]
        ]);

        if ( $oUserSystemMessageDialog == FALSE ) {
            $oUserSystemMessageDialog                             = new UserSystemMessageDialog();
            $oUserSystemMessageDialog->user_system_message_unread = 0;
        }

        $oUserSystemMessageDialog->user_id                                = $nUserID;
        $oUserSystemMessageDialog->system_message_id                      = $oSystemMessage->system_message_id;
        $oUserSystemMessageDialog->system_message_content                 = $sTitle;
        $oUserSystemMessageDialog->user_system_message_type               = UserSystemMessageDialog::TYPE_SYSTEM;
        $oUserSystemMessageDialog->user_system_message_unread             += 1;
        $oUserSystemMessageDialog->user_system_message_dialog_update_time = $oSystemMessage->system_message_create_time;
        return $oUserSystemMessageDialog->save();
    }

    /**
     * sendCertificationMsg 发送实名认证系统消息
     *
     * @param  int $nUserID
     * @param  \app\models\UserCertification $oUserCertification
     * @return bool
     */
    public function sendCertificationMsg($nUserID, \app\models\UserCertification $oUserCertification)
    {
        if ( $oUserCertification->user_certification_status == 'C' && $oUserCertification->user_certification_video_status == 'C' && $oUserCertification->user_certification_image_status == 'C' ) {
            $sTitle = '认证已提交,审批流程大概需要1-2个工作日，请耐心等待~';
        } else {
            return;
        }
//		switch ($oUserCertification->user_certification_status) {
//			case 'Y':
//				$sTitle = '主播认证已经通过了，快去直播吧~';
//				break;
//
//			case 'N':
//				$sTitle = '主播认证被拒了，拒绝原因：'.$oUserCertification->user_certification_result;
//				break;
//
//			case 'C':
//			default:
//				$sTitle = '主播认证已提交,审批流程大概需要1-2个工作日，请耐心等待~';
//				break;
//		}

        $sMsg = json_encode([
            'type' => SystemMessage::TYPE_CERTIFICATION,
            'data' => [
                'content' => $sTitle,
                'status'  => $oUserCertification->user_certification_status,
            ],
        ], JSON_UNESCAPED_UNICODE);

        $oSystemMessage                           = new SystemMessage();
        $oSystemMessage->system_message_title     = $sTitle;
        $oSystemMessage->system_message_content   = $sMsg;
        $oSystemMessage->system_message_type      = SystemMessage::TYPE_CERTIFICATION;
        $oSystemMessage->system_message_push_type = '1';
        $oSystemMessage->system_message_status    = 'N';
        // 这里的用户id只是为了后台的显示，不要使用这个字段查询用户的系统消息，用户的系统消息在UserSystemMessage中有
        $oSystemMessage->user_id = $nUserID;
        if ( $oSystemMessage->save() === FALSE ) {
            return FALSE;
        }

        $oUserSystemMessage                    = new UserSystemMessage();
        $oUserSystemMessage->user_id           = $nUserID;
        $oUserSystemMessage->system_message_id = $oSystemMessage->system_message_id;

        if ( $oUserSystemMessage->save() === FALSE ) {
            return FALSE;
        }

        $oUserSystemMessageDialog = UserSystemMessageDialog::findFirst([
            'user_id=:user_id:',
            'bind' => [
                'user_id' => $nUserID,
            ]
        ]);

        if ( $oUserSystemMessageDialog == FALSE ) {
            $oUserSystemMessageDialog                             = new UserSystemMessageDialog();
            $oUserSystemMessageDialog->user_system_message_unread = 0;
        }


        $oUserSystemMessageDialog->user_id                                = $nUserID;
        $oUserSystemMessageDialog->system_message_id                      = $oSystemMessage->system_message_id;
        $oUserSystemMessageDialog->system_message_content                 = $sTitle;
        $oUserSystemMessageDialog->user_system_message_type               = UserSystemMessageDialog::TYPE_SYSTEM;
        $oUserSystemMessageDialog->user_system_message_unread             += 1;
        $oUserSystemMessageDialog->user_system_message_dialog_update_time = $oSystemMessage->system_message_create_time;
        $oUserSystemMessageDialog->save();

        // 队列进行
        $oTaskQueueService = new TaskQueueService();
        $oTaskQueueService->enQueue([
            'task'   => 'systemmessage',
            'action' => 'push',
            'param'  => [
                'system_message_id' => $oSystemMessage->system_message_id,
            ],
        ]);
        return TRUE;
    }

    /**
     * sendUserLevelUpMsg 发送用户等级升级消息
     *
     * @param  \app\models\User $oUser
     * @return bool
     */
    public function sendUserLevelUpMsg(\app\models\User $oUser)
    {
        $sContent = sprintf('恭喜升级至%s级', $oUser->user_level);
        return $this->sendGeneral($oUser->user_id, $sContent);
    }

    /**
     * sendUserLevelUpMsg 发送邀请奖励通知
     *
     * @param  \app\models\User $oUser
     * @return bool
     */
    public function sendRewardMsg(\app\models\User $oUser,$getUserId,$effactCount,$rewardValue,$rewardName)
    {
        $sContent = sprintf('您邀请的用户：%s，充值了价值%s元的金币，您获得邀请奖励%s%s，邀请多多奖励多多', $oUser->user_nickname,$effactCount,$rewardValue,$rewardName);
        return $this->sendGeneral($getUserId, $sContent,'',TRUE);
    }

    /**
     * sendUserLevelUpMsg 发送举报消息
     *
     * @param  \app\models\User $oUser
     * @return bool
     */
    public function sendReportMsg($getUserId)
    {
        $sContent = '您的举报已受理，我们会尽快核实处理。如有更多疑问请咨询我们客服TTbaby02';
        return $this->sendGeneral($getUserId, $sContent,'',TRUE);
    }

    /**
     * sendUserLevelUpMsg 发送反馈消息
     *
     * @param  \app\models\User $oUser
     * @return bool
     */
    public function sendFeedbackMsg($getUserId)
    {
        $sContent = '您的反馈已受理，我们会尽快核实处理。如有更多疑问请咨询我们客服TTbaby02';
        return $this->sendGeneral($getUserId, $sContent,'',TRUE);
    }

    /**
     * @param $nUserID
     * @param \app\models\User $oUser
     * @param $anchorUser
     * @return bool
     * 成为守护系统消息
     */
    public function sendBecomeGuardMsg($nUserID, \app\models\User $oAnchorUser,$thisCoin,$totalCoin)
    {
        $coinName = Kv::get(Kv::KEY_COIN_NAME);
        $sTitle = sprintf('恭喜您，消费了 %s %s成为了 %s 的守护者，您当前的守护值为：%s；您享有以下特权，点击查看。',$thisCoin,$coinName,$oAnchorUser->user_nickname,$totalCoin);
        $sMsg   = json_encode([
            'type' => SystemMessage::TYPE_BECOME_GUARD,
            'data' => [
                'user_id' => $oAnchorUser->user_id,
                'content' => $sTitle,
            ],
        ], JSON_UNESCAPED_UNICODE);

        $oSystemMessage                           = new SystemMessage();
        $oSystemMessage->system_message_title     = $sTitle;
        $oSystemMessage->system_message_content   = $sMsg;
        $oSystemMessage->system_message_type      = SystemMessage::TYPE_BECOME_GUARD;
        $oSystemMessage->system_message_push_type = '1';
        // 这里的用户id只是为了后台的显示，不要使用这个字段查询用户的系统消息，用户的系统消息在UserSystemMessage中有
        $oSystemMessage->user_id = $nUserID;
        if ( $oSystemMessage->save() === FALSE ) {
            return FALSE;
        }

        $oUserSystemMessage                    = new UserSystemMessage();
        $oUserSystemMessage->user_id           = $nUserID;
        $oUserSystemMessage->system_message_id = $oSystemMessage->system_message_id;

        if ( $oUserSystemMessage->save() === FALSE ) {
            return FALSE;
        }

        $oUserSystemMessageDialog = UserSystemMessageDialog::findFirst([
            'user_id=:user_id:',
            'bind' => [
                'user_id' => $nUserID,
            ]
        ]);

        if ( $oUserSystemMessageDialog == FALSE ) {
            $oUserSystemMessageDialog                             = new UserSystemMessageDialog();
            $oUserSystemMessageDialog->user_system_message_unread = 0;
        }

        $oUserSystemMessageDialog->user_id                                = $nUserID;
        $oUserSystemMessageDialog->system_message_id                      = $oSystemMessage->system_message_id;
        $oUserSystemMessageDialog->system_message_content                 = $sTitle;
        $oUserSystemMessageDialog->user_system_message_type               = UserSystemMessageDialog::TYPE_SYSTEM;
        $oUserSystemMessageDialog->user_system_message_unread             += 1;
        $oUserSystemMessageDialog->user_system_message_dialog_update_time = $oSystemMessage->system_message_create_time;
        return $oUserSystemMessageDialog->save();
    }


    /**
     * @param $nUserID
     * @param \app\models\User $oAnchorUser
     * @return bool\
     * 被抢走守护系统消息
     */
    public function sendGuardRobbedMsg($nUserID, \app\models\User $oAnchorUser)
    {
        $sTitle = sprintf('您好，刚刚您的守护对象 %s 有了新的守护者，您的守护地位被抢走了，点击查看详情。',$oAnchorUser->user_nickname);
        $sMsg   = json_encode([
            'type' => SystemMessage::TYPE_GUARD_ROBBED,
            'data' => [
                'user_id' => $oAnchorUser->user_id,
                'content' => $sTitle,
            ],
        ], JSON_UNESCAPED_UNICODE);

        $oSystemMessage                           = new SystemMessage();
        $oSystemMessage->system_message_title     = $sTitle;
        $oSystemMessage->system_message_content   = $sMsg;
        $oSystemMessage->system_message_type      = SystemMessage::TYPE_GUARD_ROBBED;
        $oSystemMessage->system_message_push_type = '1';
        // 这里的用户id只是为了后台的显示，不要使用这个字段查询用户的系统消息，用户的系统消息在UserSystemMessage中有
        $oSystemMessage->user_id = $nUserID;
        if ( $oSystemMessage->save() === FALSE ) {
            return FALSE;
        }

        $oUserSystemMessage                    = new UserSystemMessage();
        $oUserSystemMessage->user_id           = $nUserID;
        $oUserSystemMessage->system_message_id = $oSystemMessage->system_message_id;

        if ( $oUserSystemMessage->save() === FALSE ) {
            return FALSE;
        }

        $oUserSystemMessageDialog = UserSystemMessageDialog::findFirst([
            'user_id=:user_id:',
            'bind' => [
                'user_id' => $nUserID,
            ]
        ]);

        if ( $oUserSystemMessageDialog == FALSE ) {
            $oUserSystemMessageDialog                             = new UserSystemMessageDialog();
            $oUserSystemMessageDialog->user_system_message_unread = 0;
        }

        $oUserSystemMessageDialog->user_id                                = $nUserID;
        $oUserSystemMessageDialog->system_message_id                      = $oSystemMessage->system_message_id;
        $oUserSystemMessageDialog->system_message_content                 = $sTitle;
        $oUserSystemMessageDialog->user_system_message_type               = UserSystemMessageDialog::TYPE_SYSTEM;
        $oUserSystemMessageDialog->user_system_message_unread             += 1;
        $oUserSystemMessageDialog->user_system_message_dialog_update_time = $oSystemMessage->system_message_create_time;
        return $oUserSystemMessageDialog->save();
    }

    /**
     * @param $nUserID
     * @param \app\models\User $oAnchorUser
     * @param $thisCoin
     * @param $totalCoin
     * @return bool
     * 主播守护变更通知
     */
    public function sendAnchorGuardMsg($nUserID, \app\models\User $oUser, $nCoin, $nDot)
    {
        $coinName = Kv::get(Kv::KEY_COIN_NAME);
        $dotName = Kv::get(Kv::KEY_DOT_NAME);
        $sTitle = sprintf('您的守护榜有了新的变化，当前守护者是：%s。新一轮守护者本次消费 %s %s，您获得 %s %s，点击查看。',$oUser->user_nickname,$nCoin,$coinName,$nDot,$dotName);
        $sMsg   = json_encode([
            'type' => SystemMessage::TYPE_DOT_INCOME,
            'data' => [
                'user_id' => $nUserID,
                'content' => $sTitle,
            ],
        ], JSON_UNESCAPED_UNICODE);

        $oSystemMessage                           = new SystemMessage();
        $oSystemMessage->system_message_title     = $sTitle;
        $oSystemMessage->system_message_content   = $sMsg;
        $oSystemMessage->system_message_type      = SystemMessage::TYPE_DOT_INCOME;
        $oSystemMessage->system_message_push_type = '1';
        // 这里的用户id只是为了后台的显示，不要使用这个字段查询用户的系统消息，用户的系统消息在UserSystemMessage中有
        $oSystemMessage->user_id = $nUserID;
        if ( $oSystemMessage->save() === FALSE ) {
            return FALSE;
        }

        $oUserSystemMessage                    = new UserSystemMessage();
        $oUserSystemMessage->user_id           = $nUserID;
        $oUserSystemMessage->system_message_id = $oSystemMessage->system_message_id;

        if ( $oUserSystemMessage->save() === FALSE ) {
            return FALSE;
        }

        $oUserSystemMessageDialog = UserSystemMessageDialog::findFirst([
            'user_id=:user_id:',
            'bind' => [
                'user_id' => $nUserID,
            ]
        ]);

        if ( $oUserSystemMessageDialog == FALSE ) {
            $oUserSystemMessageDialog                             = new UserSystemMessageDialog();
            $oUserSystemMessageDialog->user_system_message_unread = 0;
        }

        $oUserSystemMessageDialog->user_id                                = $nUserID;
        $oUserSystemMessageDialog->system_message_id                      = $oSystemMessage->system_message_id;
        $oUserSystemMessageDialog->system_message_content                 = $sTitle;
        $oUserSystemMessageDialog->user_system_message_type               = UserSystemMessageDialog::TYPE_SYSTEM;
        $oUserSystemMessageDialog->user_system_message_unread             += 1;
        $oUserSystemMessageDialog->user_system_message_dialog_update_time = $oSystemMessage->system_message_create_time;
        return $oUserSystemMessageDialog->save();
    }


    /**
     * sendFollowMsg 发送购买微信系统消息
     *
     * @param  int $nUserID 接收用户id
     * @param  \app\models\User $oUser 关注的用户
     * @return bool
     */
    public function sendBuyWechatMsg($nUserID, \app\models\User $oUser,$randomKey)
    {
        $sTitle = sprintf('您好，用户【%s】成功购买你的微信，暗号为%s，请尽快通过微信备注为此暗号的好友申请，三日内未添加将取消订单！',$oUser->user_nickname ,$randomKey);
        $sMsg   = json_encode([
            'type' => SystemMessage::TYPE_BUY_WECHAT,
            'data' => [
                'user_id' => $oUser->user_id,
                'content' => $sTitle,
            ],
        ], JSON_UNESCAPED_UNICODE);

        $oSystemMessage                           = new SystemMessage();
        $oSystemMessage->system_message_title     = $sTitle;
        $oSystemMessage->system_message_content   = $sMsg;
        $oSystemMessage->system_message_type      = SystemMessage::TYPE_BUY_WECHAT;
        $oSystemMessage->system_message_push_type = '1';
        // 这里的用户id只是为了后台的显示，不要使用这个字段查询用户的系统消息，用户的系统消息在UserSystemMessage中有
        $oSystemMessage->user_id = $nUserID;
        if ( $oSystemMessage->save() === FALSE ) {
            return FALSE;
        }

        $oUserSystemMessage                    = new UserSystemMessage();
        $oUserSystemMessage->user_id           = $nUserID;
        $oUserSystemMessage->system_message_id = $oSystemMessage->system_message_id;

        if ( $oUserSystemMessage->save() === FALSE ) {
            return FALSE;
        }

        $oUserSystemMessageDialog = UserSystemMessageDialog::findFirst([
            'user_id=:user_id:',
            'bind' => [
                'user_id' => $nUserID,
            ]
        ]);

        if ( $oUserSystemMessageDialog == FALSE ) {
            $oUserSystemMessageDialog                             = new UserSystemMessageDialog();
            $oUserSystemMessageDialog->user_system_message_unread = 0;
        }

        $oUserSystemMessageDialog->user_id                                = $nUserID;
        $oUserSystemMessageDialog->system_message_id                      = $oSystemMessage->system_message_id;
        $oUserSystemMessageDialog->system_message_content                 = $sTitle;
        $oUserSystemMessageDialog->user_system_message_type               = UserSystemMessageDialog::TYPE_SYSTEM;
        $oUserSystemMessageDialog->user_system_message_unread             += 1;
        $oUserSystemMessageDialog->user_system_message_dialog_update_time = $oSystemMessage->system_message_create_time;
        return $oUserSystemMessageDialog->save();
    }
}