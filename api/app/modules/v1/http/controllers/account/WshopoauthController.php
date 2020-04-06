<?php

/**
 * 微信商城登录注册
 */

namespace app\http\controllers\account;

use app\helper\OpensslEncryptHelper;
use app\models\LoginErrorLog;
use app\models\UserDeviceBind;
use Exception;
use app\services;
use app\models\User;
use app\models\UserAccount;
use app\helper\ResponseError;
use app\http\controllers\ControllerBase;
use app\models\Kv;

class WshopoauthController extends ControllerBase
{

    /**
     */
    public function indexAction()
    {
        $receiptData = $this->getParams("receipt_data");
        try {
            $decodeStr = OpensslEncryptHelper::decryptWithOpenssl($receiptData);
            $decodeArr = json_decode($decodeStr, TRUE);
            if ( !$decodeArr ) {
                throw new Exception(
                    sprintf('%s %s',ResponseError::getError(ResponseError::PARAM_ERROR),'receipt_data'),
                    ResponseError::PARAM_ERROR
                );
            }
            $sUnionId    = $decodeArr['union_id'] ?? '';
            $sHeadimgurl = $decodeArr['head_img_url'] ?? '';
            $sNickname   = $decodeArr['nickname'] ?? '';
            if(empty($sUnionId)){
                throw new Exception(
                    sprintf('%s %s',ResponseError::getError(ResponseError::PARAM_ERROR),'union_id'),
                    ResponseError::PARAM_ERROR
                );
            }
            $oUserAccount = UserAccount::findFirst([
                'user_wx_openid=:openid:',
                'bind' => [
                    'openid' => $sUnionId,
                ]
            ]);
            $oUser        = User::findFirst([
                'user_id=:user_id:',
                'bind' => [ 'user_id' => $oUserAccount->user_id ]
            ]);

//            $is_register = 'N';
//            if ( $oUserAccount === FALSE || $oUser === FALSE ) {
//                $is_register = 'Y';
//            }
            $aUser = [
                'unionid'    => $sUnionId,
                'nickname'   => $sNickname,
                'headimgurl' => $sHeadimgurl,
            ];
            if ( $oUserAccount === FALSE ) {
                // 注册微信用户
                list($oUser, $oUserAccount) = $this->registerWXShop($aUser);
            } else {
                if ( $oUser === FALSE ) {
                    //有一个不为空
                    $oLoginErrorLog             = new LoginErrorLog();
                    $oLoginErrorLog->key_str    = $aUser['unionid'];
                    $oLoginErrorLog->login_type = LoginErrorLog::LOGIN_TYPE_WX;
                    $oLoginErrorLog->save();
                    throw new Exception(ResponseError::getError(ResponseError::LOGIN_INFO_ERROR), ResponseError::LOGIN_INFO_ERROR);
                } else {
                    $oUser->user_login_type = User::LOGIN_TYPE_WX;
                    $oUser->user_login_ip   = ip2long($this->request->getClientAddress());
                    $oUser->user_login_time = time();
                    $oUser->save();

                    $oUserAccount->user_device_id         = '';
                    $oUserAccount->user_os_type           = 'wxshop';
                    $oUserAccount->user_token             = $this->createToken();
                    $oUserAccount->user_token_expire_time = $this->createTokenExpireTime();
                    $oUserAccount->save();
                }
            }
            $row = [
                'user_id'    => $oUser->user_id,
                'user_phone' => $oUserAccount->user_phone,
            ];
        } catch ( Exception $e ) {
            $this->error(
                ResponseError::LOGIN_ERROR,
                sprintf('%s (%s)', ResponseError::getError(ResponseError::LOGIN_ERROR), $e->getMessage())
            );
        }

        $this->success($row);
    }

    /**
     * @param $aUser
     * 微商城 注册到主app
     */
    private function registerWXShop($aUser)
    {
        $oUser                     = new User();
        $oUser->user_nickname      = $this->createNickname($aUser['nickname']);
        $oUser->user_avatar        = $aUser['headimgurl'];
        $oUser->user_login_type    = User::LOGIN_TYPE_WX;
        $oUser->user_register_type = User::REGISTER_TYPE_WX;
        $oUser->user_register_ip   = ip2long($this->request->getClientAddress());
        $oUser->user_login_ip      = ip2long($this->request->getClientAddress());
        $oUser->user_login_time    = time();
        $oUser->user_invite_code   = $this->getInviteCode();
        $bool                      = $oUser->save();

        if ( $bool !== TRUE ) {
            $aMessage = $oUser->getMessages();
            throw new Exception(
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
                ResponseError::OPERATE_FAILED
            );
        }

        $oUser                                = User::findFirst($oUser->user_id);
        $oUserAccount                         = new UserAccount();
        $oUserAccount->user_id                = $oUser->user_id;
        $oUserAccount->user_wx_openid         = $aUser['unionid'];
        $oUserAccount->user_token             = $this->createToken();
        $oUserAccount->user_token_expire_time = $this->createTokenExpireTime();
        $oUserAccount->user_os_type           = 'wxshop';
        $bool                                 = $oUserAccount->save();

        if ( $bool !== TRUE ) {
            $aMessage = $oUserAccount->getMessages();
            throw new Exception(
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
                ResponseError::OPERATE_FAILED
            );
        }

        return [
            $oUser,
            $oUserAccount
        ];

    }

    /**
     * createNickname 创建一个用户昵称
     *
     * @return string
     */
    protected function createNickname($sNickname = '')
    {

        if ( $sNickname == '' ) {
            $sOnlyNickname = $this->config->application->app_name . date('mdHis') . mt_rand(0, 9);
            if ( User::findFirst([
                'user_nickname=:user_nickname:',
                'bind' => [ 'user_nickname' => $sOnlyNickname ]
            ]) ) {
                $this->createNickname();
            }
        } else {

            if ( User::findFirst([
                'user_nickname=:user_nickname:',
                'bind' => [ 'user_nickname' => $sNickname ]
            ]) ) {
                return $this->createNickname($sNickname . date('mdHis') . mt_rand(0, 9));
            } else {
                $sOnlyNickname = $sNickname;
            }
        }

        return $sOnlyNickname;
    }

    /**
     * getInviteCode 获取邀请码
     *
     * @return string
     */
    protected function getInviteCode()
    {
        $sLetter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $sNumber = '0123456789';
        $sCode   = $sLetter{rand(0, 25)};
        for ( $i = 0; $i < 5; $i++ ) {
            $sCode .= $sNumber{rand(0, 9)};
        }

        //检查邀请码是否存在
        $isExsit = User::findFirst([
            'user_invite_code=:user_invite_code:',
            'bind' => [
                'user_invite_code' => $sCode
            ]
        ]);

        if ( !empty($isExsit) ) {
            return $this->getInviteCode();
        }

        return $sCode;
    }

    /**
     * createToken 创建token
     *
     * @return string
     */
    protected function createToken()
    {
        return md5(uniqid() . time());
    }

    /**
     * createTokenExpireTime 创建token过期时间
     *
     * @return int
     */
    public function createTokenExpireTime()
    {
        return time() + UserAccount::TOKEN_EXPIRE_TIME;
    }
}