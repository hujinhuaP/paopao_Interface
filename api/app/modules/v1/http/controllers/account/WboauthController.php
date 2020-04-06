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

use app\models\LoginErrorLog;
use app\models\UserDeviceBind;
use Exception;
use app\services;
use app\models\User;
use app\models\UserAccount;
use app\helper\ResponseError;
use app\http\controllers\ControllerBase;
use app\models\Kv;

class WboauthController extends ControllerBase
{

    use services\UserService;

    /**
     * indexAction 移动端登录
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
//            $this->log->info("$sOpenId||$sAccessToken||$sInviteCode||$sDeviceNo");
            $aUser = $this->getOAuthUserInfo($sOpenId, $sAccessToken);

            $oUserAccount           = UserAccount::findFirst([
                'user_wb_openid=:openid:',
                'bind' => [
                    'openid' => $sOpenId,
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
                // 注册微博用户
                $row = $this->registerWB($aUser, $user_invite_user_id, $user_group_id, $user_agent_id, $oUserDeviceBind);
            } else {
                if ( $oUser === FALSE ) {
                    //有一个不为空
                    $oLoginErrorLog             = new LoginErrorLog();
                    $oLoginErrorLog->key_str    = $sOpenId;
                    $oLoginErrorLog->login_type = LoginErrorLog::LOGIN_TYPE_WB;
                    $oLoginErrorLog->save();
                    throw new Exception(ResponseError::getError(ResponseError::LOGIN_INFO_ERROR), ResponseError::LOGIN_INFO_ERROR);
                    return;
                    $oUserAccount->delete();
                    $row = $this->registerWB($aUser, $user_invite_user_id, $user_group_id, $user_agent_id, $oUserDeviceBind);
                } else {
                    $oUser->user_login_type = User::LOGIN_TYPE_WB;
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
     * webAction 通过网页版使用微信登录的时候用到
     */
    public function webAction()
    {
        $param = http_build_query([
            'client_id'     => $this->config->oauth->wb->appid,
            'redirect_uri'  => urlencode($this->config->oauth->wb->callback_url),
            'response_type' => 'code',
            'state'         => '1',
            'display'       => 'mobile',
        ]);

        $url = sprintf('https://api.weibo.com/oauth2/authorize?%s', $param);

        header('Location:' . $url);
    }

    /**
     * callbackAction 网页登录回调
     */
    public function callbackAction()
    {
        $code = $this->getParams('code', 'string', '');

        $param = [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->config->oauth->wx->appid,
            'client_secret' => $this->config->oauth->wx->appkey,
            'code'          => $code,
            'redirect_uri'  => urlencode($this->config->oauth->wb->callback_url),
        ];

        $url = 'https://api.weibo.com/oauth2/access_token';

        $sContent = $this->httpRequest($url, $param);
        $aUser    = json_decode($sContent, 1);

        try {

            if ( !$aUser || $aUser['uid'] == "" ) {
                throw new Exception($aUser['error']);
            }

            $sOpenId      = $aUser['uid'];
            $sAccessToken = $aUser['access_token'];
            $aUser        = $this->getOAuthUserInfo($sOpenId, $sAccessToken);

            $oUserAccount = UserAccount::findFirst([
                'user_wx_openid=:openid:',
                'bind' => [
                    'openid' => $sOpenId,
                ]
            ]);

            if ( $oUserAccount === FALSE ) {
                // 注册微博用户
                $row = $this->registerWB($aUser);
            } else {
                $oUser = User::findFirst([
                    'user_id=:user_id:',
                    'bind' => [ 'user_id' => $oUserAccount->user_id ]
                ]);
                if ( $oUser === FALSE ) {
                    $oUserAccount->delete();
                    $row = $this->registerWB($aUser);
                } else {
                    $oUser->user_login_type = User::LOGIN_TYPE_WB;
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
     * getOAuthUserInfo 获取第三方用户信息
     *
     * @param  string $sOpenId
     * @param  string $sAccessToken
     * @return array
     * @throws Exception
     */
    protected function getOAuthUserInfo($sOpenId, $sAccessToken)
    {

        $param = [
            'access_token' => $sAccessToken,
            'uid'          => $sOpenId,
        ];

        $url = 'https://api.weibo.com/2/users/show.json';

        $response = $this->httpRequest($url, $param);

        $aUser = json_decode($response, 1);

        if ( !$aUser ) {
            throw new Exception("Openid error");
        }

        if ( isset($aUser['error']) ) {
            throw new Exception($aUser['error'], $aUser['error_code']);
        }

        $aUser['openid'] = $sOpenId;

        return $aUser;
    }

    /**
     * httpRequest
     *
     * @param  string $url
     * @param  array $param
     * @param  string $mothod
     * @return string
     */
    protected function httpRequest($url, $param = [], $mothod = 'GET')
    {
        $ch = curl_init();
        if ( strtoupper($mothod) == 'POST' ) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        } else {
            $url .= '?' . http_build_query($param);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

}