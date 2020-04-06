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

}