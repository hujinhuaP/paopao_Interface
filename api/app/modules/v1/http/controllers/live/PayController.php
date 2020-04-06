<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 |房间控制器                                                              |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\live;

use app\models\User;
use app\models\Anchor;
use app\http\controllers\ControllerBase;

/**
* PayController 支付
*/
class PayController extends ControllerBase
{

    /**
     * notifyAnchor
     *  
     * @param  int $nAnchorUserId
     */
    protected function notifyAnchor($nAnchorUserId=0)
    {
        $oAnchorUser  = User::findFirst($nAnchorUserId);
        $oAnchor  = Anchor::findFirst([
            'user_id=:user_id:',
            'bind' => [
                'user_id' => $nAnchorUserId,
            ]
        ]);

        // 主播数据
        $aPushMessage['anchor'] = [
            'anchor_ranking' => $oAnchor->anchor_ranking,
            // 总的收益，不是可提现的收益
            'user_dot'       => sprintf('%.2f', $oAnchorUser->user_collect_total),
            'user_id'        => $oAnchorUser->user_id,
            'user_avatar'    => $oAnchorUser->user_avatar,
            'user_level'     => $oAnchorUser->user_level,
            'user_nickname'  => $oAnchorUser->user_nickname,
        ];
        $this->timServer->setRid($nAnchorUserId);
        $this->timServer->setUid(0);
        $this->timServer->sendAnchorSignal($aPushMessage);
    }

    /**
     * notifyAnchorAction 给后台用的更新主播用户信息接口
     * 
     * @param  integer $nUserId 
     */
    public function notifyAnchorAction($nUserId=0)
    {
        $nAnchorUserId = $this->getParams('anchor_user_id', 'int', 0);
        $this->notifyAnchor($nAnchorUserId);
        $this->success();
    }

}