<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 系统消息服务                                                           |
 +------------------------------------------------------------------------+                              |
 +------------------------------------------------------------------------+
 */

namespace app\admin\library\traits;

use app\admin\library\TaskQueueService;
use app\admin\model\api\SystemMessage;
use app\admin\model\api\Agent;
use app\admin\model\api\UserSystemMessage;
use app\admin\model\api\UserSystemMessageDialog;


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
    public function sendGeneral($nUserID = 0, $sContent, $sUrl = '',$isPush = '')
    {
        $sTitle = $sContent;
        $sMsg   = json_encode([
            'type' => SystemMessage::TYPE_GENERAL,
            'data' => [
                'content' => $sContent,
                'url'     => $sUrl,
            ],
        ], JSON_UNESCAPED_UNICODE);

        $oSystemMessage                           = new SystemMessage();
        $oSystemMessage->system_message_title     = $sTitle;
        $oSystemMessage->system_message_content   = $sMsg;
        $oSystemMessage->system_message_type      = SystemMessage::TYPE_GENERAL;
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
                return TRUE;
            }
            $oUserSystemMessage                    = new UserSystemMessage();
            $oUserSystemMessage->user_id           = $nUserID;
            $oUserSystemMessage->system_message_id = $oSystemMessage->system_message_id;

            if ( $oUserSystemMessage->save() === FALSE ) {
                return FALSE;
            }

            $oUserSystemMessageDialog = UserSystemMessageDialog::where('user_id', $nUserID)->find();

            if ( $oUserSystemMessageDialog == FALSE ) {
                $oUserSystemMessageDialog                             = new UserSystemMessageDialog();
                $oUserSystemMessageDialog->user_system_message_unread = 0;
            }

            $oUserSystemMessageDialog->user_id                    = $nUserID;
            $oUserSystemMessageDialog->system_message_id          = $oSystemMessage->system_message_id;
            $oUserSystemMessageDialog->system_message_content     = $sTitle;
            $oUserSystemMessageDialog->user_system_message_type   = UserSystemMessageDialog::TYPE_SYSTEM;
            $oUserSystemMessageDialog->user_system_message_unread += 1;
            $oUserSystemMessageDialog->save();

            return TRUE;
        }

        return TRUE;
    }

	/**
	 * sendCertificationMsg 发送代理商生成主播消息
	 * 
	 * @param  int                                   $nUserID            
	 * @return bool
	 */
	public function sendBecomeAnchorMsg($nUserID,$agentName)
	{
        $sTitle = sprintf('您已成为 【%s】 旗下的主播，将无法提现~|You have become the anchor of [%s] and will not be able to cash in.',$agentName,$agentName);
		$sMsg = json_encode([
			'type' => SystemMessage::TYPE_AGENT,
			'data' => [
				'content' => $sTitle,
				'anchor_status' => Agent::ANCHOR_STATUS_BECOME,
			],
		], JSON_UNESCAPED_UNICODE);

		$oSystemMessage = new SystemMessage();
		$oSystemMessage->system_message_title     = $sTitle;
		$oSystemMessage->system_message_content   = $sMsg;
		$oSystemMessage->system_message_type      = SystemMessage::TYPE_AGENT;
		$oSystemMessage->system_message_push_type = '1';
		// 这里的用户id只是为了后台的显示，不要使用这个字段查询用户的系统消息，用户的系统消息在UserSystemMessage中有
		$oSystemMessage->user_id                  = $nUserID;
		if($oSystemMessage->save() === FALSE) {
			return FALSE;
		}

		$oUserSystemMessage = new UserSystemMessage();
		$oUserSystemMessage->user_id = $nUserID;
		$oUserSystemMessage->system_message_id = $oSystemMessage->system_message_id;
		
		if ($oUserSystemMessage->save() === FALSE) {
			return FALSE;
		}

		$oUserSystemMessageDialog = UserSystemMessageDialog::where('user_id', $nUserID)->find();

		if ($oUserSystemMessageDialog == FALSE) {
			$oUserSystemMessageDialog = new UserSystemMessageDialog();
			$oUserSystemMessageDialog->user_system_message_unread = 0;
		}
		
		$oUserSystemMessageDialog->user_id = $nUserID;
		$oUserSystemMessageDialog->system_message_id = $oSystemMessage->system_message_id;
		$oUserSystemMessageDialog->system_message_content = $sTitle;
		$oUserSystemMessageDialog->user_system_message_type = UserSystemMessageDialog::TYPE_SYSTEM;
		$oUserSystemMessageDialog->user_system_message_unread += 1;
		return $oUserSystemMessageDialog->save();
	}


    /**
     * sendCertificationMsg 发送实名认证系统消息
     *
     * @param  int $nUserID
     * @param  app\admin\model\api\UserCertification $oUserCertification
     * @return bool
     */
    public function sendCertificationMsg($nUserID, \app\admin\model\api\UserCertification $oUserCertification, $refuseResult = '')
    {
        //判断是否所有提交的东西已审核
        $isRefuse = false;
        if ( $oUserCertification->user_certification_status == 'D' ) {
            $sTitle = $content = '主播认证被禁止了，请联系客服人员';
        } else {
            if ( $oUserCertification->user_certification_status == 'Y' && $oUserCertification->user_certification_video_status == 'Y' && $oUserCertification->user_certification_image_status == 'Y' ) {
                $sTitle = $content = '亲爱的小姐姐，恭喜您完成主播认证，赶快开启接单抢单之旅吧~';
                // 公会审核通过 需要给官方再审核
                return;
            } else {
                if ( $oUserCertification->user_certification_status == 'C' || $oUserCertification->user_certification_video_status == 'C' || $oUserCertification->user_certification_image_status == 'C' ) {
                    //存在审核中的类型  则不需要发通知
                    return TRUE;
                }
                $passArr         = [];
                $notArr          = [];
                $refuseArr       = [];
                $refuseResultArr = [];
                $sTitle          = '亲爱的小姐姐,您有新的主播审核结果哦';
                switch ( $oUserCertification->user_certification_status ) {
                    case 'Y':
                        $passArr[] = '实名认证';
                        break;
                    case 'N':
                        $refuseArr[]       = '实名认证';
                        $refuseResultArr[] = '实名认证未通过原因：【' . $oUserCertification->user_certification_result .'】';
                        $isRefuse = TRUE;
                        break;
                    case 'NOT':
                    default:
                        $notArr[] = '实名认证';
                }

                switch ( $oUserCertification->user_certification_video_status ) {
                    case 'Y':
                        $passArr[] = '宣传视频';
                        break;
                    case 'N':
                        $refuseArr[]       = '宣传视频';
                        $refuseResultArr[] = '宣传视频未通过原因：【' . $oUserCertification->user_certification_video_result .'】';
                        $isRefuse = TRUE;
                        break;
                    case 'NOT':
                    default:
                        $notArr[] = '宣传视频';
                }

                switch ( $oUserCertification->user_certification_image_status ) {
                    case 'Y':
                        $passArr[] = '展示图片';
                        break;
                    case 'N':
                        $refuseArr[]       = '展示图片';
                        $refuseResultArr[] = '展示图片未通过原因：【' . $oUserCertification->user_certification_image_result .'】';
                        $isRefuse = TRUE;
                        break;
                    case 'NOT':
                    default:
                        $notArr[] = '展示图片';
                }

                $passMsg = '';
                if ( $passArr ) {
                    $passStr = implode('、', $passArr);
                    $passMsg = sprintf(',您的%s已经通过', $passStr);
                }

                $refuseMsg = '';
                if ( $refuseArr ) {
                    $passStr = implode('、', $refuseArr);
                    $refuseResultStr = implode(';', $refuseResultArr);
                    $refuseMsg = sprintf(',您的%s未审核通过，请按照对应规则重新完善您的%s,%s', $passStr, $passStr, $refuseResultStr);
                    $isRefuse = TRUE;
                }

                $notMsg = '';
                if ( $notArr ) {
                    $notStr = implode('、', $notArr);
                    $notMsg = sprintf(',您的%s暂未提交，请按照对应规则完善您的%s', $notStr,$notStr);
                }

                $content = sprintf('亲爱的小姐姐%s%s%s', $passMsg,$refuseMsg,$notMsg);

            }
        }

        $sMsg = json_encode([
            'type' => SystemMessage::TYPE_CERTIFICATION,
            'data' => [
                'content' => $content,
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
//        在推送的过程中 生成记录
//        $oUserSystemMessage                    = new UserSystemMessage();
//        $oUserSystemMessage->user_id           = $nUserID;
//        $oUserSystemMessage->system_message_id = $oSystemMessage->system_message_id;
//
//        if ( $oUserSystemMessage->save() === FALSE ) {
//            return FALSE;
//        }

//        $oUserSystemMessageDialog = UserSystemMessageDialog::where('user_id', $nUserID)->find();
//
//        if ( $oUserSystemMessageDialog == FALSE ) {
//            $oUserSystemMessageDialog                             = new UserSystemMessageDialog();
//            $oUserSystemMessageDialog->user_system_message_unread = 0;
//        }
//
//        $oUserSystemMessageDialog->user_id                    = $nUserID;
//        $oUserSystemMessageDialog->system_message_id          = $oSystemMessage->system_message_id;
//        $oUserSystemMessageDialog->system_message_content     = $sTitle;
//        $oUserSystemMessageDialog->user_system_message_type   = UserSystemMessageDialog::TYPE_SYSTEM;
//        $oUserSystemMessageDialog->user_system_message_unread += 1;
//        $oUserSystemMessageDialog->save();
        $oTaskQueueService = new TaskQueueService();
        $oTaskQueueService->enQueue([
            'task'   => 'systemmessage',
            'action' => 'push',
            'param'  => [
                'system_message_id' => $oSystemMessage->system_message_id,
            ],
        ]);

        if($isRefuse){
            $msg = '您好，您的视频和封面审核不规范，请参考平台规范进行修改，规范请点击请点击http://static.sxypaopao.com/site/anchorstandard.html。如有疑。如有疑问，请加平台客服v：TTbaby02';
            $url = 'http://static.sxypaopao.com/site/anchorstandard.html';
            $this->sendGeneral($nUserID,$msg,$url,TRUE);
        }
        return TRUE;
    }

}