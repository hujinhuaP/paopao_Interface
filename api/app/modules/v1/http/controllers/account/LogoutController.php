<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 用户登出                                                               |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\account;

use app\models\Anchor;
use app\models\User;
use Exception;
use app\helper\ResponseError;
use app\http\controllers\ControllerBase;

/**
* LogoutController 用户登出
*/
class LogoutController extends ControllerBase
{
	/**
	 * indexAction 
	 * @param  int $nUserId
	 */
	public function indexAction($nUserId=0)
	{
	    // 将用户的在线状态改为离线
        $oUser = User::findfirst($nUserId);
        $oUser->user_online_status = User::USER_ONLINE_STATUS_OFFLINE;
        $oUser->update();

        if($oUser->user_is_anchor == 'Y'){
            $oAnchor = Anchor::findFirst([
                'user_id=:user_id:',
                'bind' => [
                    'user_id' => $nUserId,
                ],
            ]);
            if($oAnchor && $oAnchor->anchor_chat_status != Anchor::CHAT_STATUS_OFF){
                $oAnchor->anchor_chat_status = Anchor::CHAT_STATUS_OFFLINE;
                $oAnchor->update();
            }
        }
		$this->success();
	}
}