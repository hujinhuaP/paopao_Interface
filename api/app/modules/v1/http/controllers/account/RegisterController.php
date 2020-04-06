<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 用户注册                                                               |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\account;

use app\services;
use app\http\controllers\ControllerBase;

/**
* RegisterController
*/
class RegisterController extends ControllerBase
{
	use services\UserService;

    public function getSignAction(){
        return;
        $identifier = 'visitor_'.time().rand(1000,9999);
        $tim = [
            // 私钥签名
            'sign'         => $this->timServer->genSig($identifier),
            // 用户帐号
            'account'      => $identifier,
            // 应用的类型
            'account_type' => $this->config->application->tim->account_type,
            // APP ID
            'app_id'       => $this->config->application->tim->app_id,
        ];
        $this->success($tim);
    }
}