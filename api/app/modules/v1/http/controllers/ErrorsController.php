<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 错误控制器                                                             |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers;

use app\helper\ResponseError;

/**
* ErrorsController 
*/
class ErrorsController extends ControllerBase
{

    public function show404Action()
    {

    }

    public function show401Action()
    {

    }

    public function show500Action()
    {

    }

    public function showAccessTokenInvalidAction()
    {
    	$this->error(ResponseError::ACCESS_TOKEN_INVALID, ResponseError::getError(ResponseError::ACCESS_TOKEN_INVALID));
    }

    public function showLogoutAction()
    {
        $this->error(ResponseError::ACCESS_TOKEN_INVALID, sprintf('您的账号于 %s在另一台手机登录。如非本人操作，则密码可能泄露，建议修改密码', date('Y-m-d H:i')));
    }

    public function showSignInvalidAction()
    {
        $this->error(ResponseError::ACCESS_TOKEN_INVALID, '签名错误');
    }
    //pc端未登录，去登录页
    public function showPcLoginAction(){
        header("Location:".$this->config->application->pc_login_url);
    }
}
