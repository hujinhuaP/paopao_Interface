<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | QQ用户登录                                                             |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\account;

use app\models\Kv;
use app\models\LoginErrorLog;
use app\models\UserDeviceBind;
use Exception;
use app\services;
use app\models\User;
use app\models\UserAccount;
use app\helper\ResponseError;
use app\http\controllers\ControllerBase;

/**
 * QqoauthController
 */
class QqoauthController extends ControllerBase
{
    use services\UserService;

    /**
     * indexAction 移动端登录
     *
     * @return
     */
    public function indexAction()
    {
        $sOpenId = $this->getParams('open_id', 'string', '');
        // 为了安全，不要将access token保存
        $sAccessToken = $this->getParams('auth_access_token', 'string', '');
        $sInviteCode  = $this->getParams('invite_code', 'string', '');
        $sDeviceNo    = $this->getParams('device_no', 'string');
        $sDeviceId   = $this->getParams('device_id', 'string');
        $sInviteCode  = str_replace('#', '', $sInviteCode);
        try {

            if($sDeviceNo != $sDeviceId){
                $this->error(10000,'请使用真实的移动设备');
            }

            $aUser   = $this->getOAuthUserInfo($sOpenId, $sAccessToken);
            $unionid = $sOpenId;


            $aUser['unionid'] = $unionid;

            $oUserAccount           = UserAccount::findFirst([
                'user_qq_openid=:openid:',
                'bind' => [
                    'openid' => $unionid,
                ]
            ]);
            $oUser                  = User::findFirst([
                'user_id=:user_id:',
                'bind' => [ 'user_id' => $oUserAccount->user_id ]
            ]);
            $user_invite_user_id    = 0;
            $user_group_id          = 0;
            $user_agent_id          = 0;
            $is_register            = 'N';
            $registerRewardCoin     = intval(Kv::get(Kv::REGISTER_REWARD_COIN));
            $registerRewardFreeTime = intval(Kv::get(Kv::REGISTER_FREE_MATCH_TIMES));
            if ( $oUserAccount === FALSE || $oUser === FALSE ) {
                $is_register = 'Y';
                if ( !$sDeviceNo ) {
                    $this->error(10002, '请使用真实的移动设备');
                }
                $oUserDeviceBind = UserDeviceBind::findFirst([
                    'device_no=:device_no:',
                    'bind' => [
                        'device_no' => $sDeviceNo,
                    ]
                ]);
                if ( $oUserDeviceBind ) {
                    $registerRewardCoin     = 0;
                    $registerRewardFreeTime = 0;
                }
                list($user_invite_user_id, $user_group_id, $user_agent_id) = $this->getInviteData($sInviteCode, $oUserDeviceBind);
//                $this->log->info("user_id:$user_invite_user_id;group_id: $user_group_id; agent_id: $user_agent_id");
            }

            if ( $oUserAccount === FALSE ) {
                // 注册QQ用户
                $row = $this->registerQQ($aUser, $user_invite_user_id, $user_group_id, $user_agent_id, $oUserDeviceBind);
            } else {
                if ( $oUser === FALSE ) {
                    //有一个不为空
                    $oLoginErrorLog             = new LoginErrorLog();
                    $oLoginErrorLog->key_str    = $unionid;
                    $oLoginErrorLog->login_type = LoginErrorLog::LOGIN_TYPE_QQ;
                    $oLoginErrorLog->save();
                    throw new Exception(ResponseError::getError(ResponseError::LOGIN_INFO_ERROR), ResponseError::LOGIN_INFO_ERROR);
                    return;
                    $oUserAccount->delete();
                    $row = $this->registerQQ($aUser, $user_invite_user_id, $user_group_id, $user_agent_id, $oUserDeviceBind);
                } else {
                    $oUser->user_login_type = User::LOGIN_TYPE_QQ;
                    $row                    = $this->loginSuccessHandle($oUser, $oUserAccount);
                }
            }
            $row['is_register']     = $is_register;
            $row['register_reward'] = [
                'flg'  => Kv::get(Kv::REGISTER_REWARD_FLG) == '1' ? 'Y' : 'N',
                'coin' => $registerRewardCoin
            ];
            if ( $is_register == 'Y' ) {
                $this->registerFinish($oUserDeviceBind, $sDeviceNo, $sInviteCode, $user_invite_user_id, $user_group_id, $row['user_id'], $user_agent_id);
                if(!Kv::get(Kv::REGISTER_REWARD_FLG)){
                    $registerRewardFreeTime = 0;
                }
                $row['free_times'] = (string)$registerRewardFreeTime;
                $row['first_share_reward'] = $this->getFirstShareRewardFreeTime($row['user_id']);
            }
        } catch ( Exception $e ) {
            $this->error(
                ResponseError::LOGIN_ERROR,
                sprintf('%s (%s)', ResponseError::getError(ResponseError::LOGIN_ERROR), $e->getMessage())
            );
        }

        $this->success($row);
    }


    /**
     * webAction 网页登录
     */
    public function webAction()
    {
        $param = http_build_query([
            'response_type' => 'code',
            'client_id'     => $this->config->oauth->qq->appid,
            'redirect_uri'  => urlencode($this->config->oauth->qq->callback_url),
            'state'         => '1',
            'scope'         => 'get_user_info',
            'display'       => 'mobile',
        ]);

        $url = sprintf('https://graph.qq.com/oauth2.0/authorize?%s', $param);
        header('Location:' . $url);
    }

    /**
     * callbackAction 网页登录回调
     */
    public function callbackAction()
    {
        $code = $this->getParams('code', 'string', '');

        $param = http_build_query([
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->config->oauth->qq->appid,
            'client_secret' => $this->config->oauth->qq->appkey,
            'code'          => $code,
            'redirect_uri'  => urlencode($this->config->oauth->qq->callback_url),
        ]);

        $url = sprintf('https://graph.qq.com/oauth2.0/token?%s', $param);

        $sContent     = file_get_contents($url);
        $sAccessToken = substr($sContent, 13, 32);

        try {

            $sOpenId = $this->getOpenID($sAccessToken);
            $aUser   = $this->getOAuthUserInfo($sOpenId, $sAccessToken);

            $oUserAccount = UserAccount::findFirst([
                'user_qq_openid=:openid:',
                'bind' => [
                    'openid' => $sOpenId,
                ]
            ]);

            if ( $oUserAccount === FALSE ) {
                // 注册QQ用户
                $row = $this->registerQQ($aUser);
            } else {
                $oUser = User::findFirst([
                    'user_id=:user_id:',
                    'bind' => [ 'user_id' => $oUserAccount->user_id ]
                ]);
                if ( $oUser === FALSE ) {
                    $oUserAccount->delete();
                    $row = $this->registerQQ($aUser);
                } else {
                    $oUser->user_login_type = User::LOGIN_TYPE_QQ;
                    $row                    = $this->loginSuccessHandle($oUser, $oUserAccount);
                }
            }

        } catch ( Exception $e ) {
            $this->error(
                ResponseError::LOGIN_ERROR,
                sprintf('%s (%s)', ResponseError::getError(ResponseError::LOGIN_ERROR), $e->getMessage())
            );
        }

        $this->success($row);
    }

    /**
     * getOpenID 获取openID
     *
     * @param  string $sAccessToken
     * @return string
     */
    protected function getOpenID($sAccessToken)
    {
        $param = http_build_query([
            'access_token' => $sAccessToken,
            'unionid'      => 1
        ]);

        $url      = 'https://graph.qq.com/oauth2.0/me?' . $param;
        $response = file_get_contents($url);

        if ( strpos($response, "callback") !== FALSE ) {
            $lpos     = strpos($response, "(");
            $rpos     = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
        }

        $aUser = json_decode($response, 1);

        if ( isset($aUser['error']) ) {
            throw new Exception($aUser['error_description']);
        } else {
            return $aUser['unionid'];
        }
    }

    /**
     * getOAuthUserInfo 获取第三方用户信息
     *
     * @param  string $sOpenId
     * @param  string $sAccessToken
     * @return array
     * @throws Exception
     */
    protected function getOAuthUserInfo($sOpenId, $sAccessToken)
    {
        $appInfo = $this->getAppInfo('qq');
        $param = http_build_query([
            'access_token'       => $sAccessToken,
            'oauth_consumer_key' => $appInfo['appid'],
            'openid'             => $sOpenId,
        ]);

        $url = sprintf('https://graph.qq.com/user/get_user_info?%s', $param);

        $response = file_get_contents($url);

        $aUser = json_decode($response, 1);

        if ( !$aUser ) {
            throw new Exception("Openid error");
        }

        if ( $aUser['ret'] != 0 ) {
            throw new Exception($aUser['msg']);
        }

        $aUser['openid'] = $sOpenId;

        $aUser['app_info'] = $appInfo;
        return $aUser;
    }
}