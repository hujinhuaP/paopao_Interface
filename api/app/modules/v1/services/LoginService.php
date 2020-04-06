<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 登录服务                                                               |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\services;
use Exception;
use app\models\User;

use app\models\VerifyCode;

use app\models\UserAccount;
use app\helper\ResponseError;



/**
* UserService
*/
trait LoginService
{
	/**
	 * createNickname 创建一个用户昵称
	 *
	 * @return string
	 */
	protected function createNickname($sNickname='')
	{

		if ($sNickname == '') {
			$sOnlyNickname = $this->config->application->app_name.date('mdHis').mt_rand(0, 9);
			if (User::findFirst(['user_nickname=:user_nickname:', 'bind'=>['user_nickname'=>$sOnlyNickname]])) {
				$this->createNickname();
			}
		} else {

			if (User::findFirst(['user_nickname=:user_nickname:', 'bind'=>['user_nickname'=>$sNickname]])) {
				return $this->createNickname($sNickname.date('mdHis').mt_rand(0, 9));
			} else {
				$sOnlyNickname = $sNickname;
			}
		}

		return $sOnlyNickname;
	}

	/**
	 * registerPhone 注册手机
	 * 
	 * @param  string $sPhone   
	 * @param  string $sPassword
	 * @param  string $sCode    
	 * @return 
	 */
	protected function registerPhone($sPhone, $sPassword, $sCode)
	{
		$oUserAccount = UserAccount::findFirst([
			'user_phone=:phone:',
			'bind' => [
				'phone' => $sPhone
			]
		]);

		if ($oUserAccount) {
			throw new Exception(ResponseError::getError(ResponseError::ACCOUNT_EXISTS), ResponseError::ACCOUNT_EXISTS);
		}

		$oVerifyCode = new VerifyCode();
		$bool = $oVerifyCode->judgeVerify($sPhone, VerifyCode::TYPE_REGISTER, $sCode);

		if ($bool === FALSE) {
			throw new Exception(ResponseError::getError(ResponseError::VERIFY_CODE_ERROR), ResponseError::VERIFY_CODE_ERROR);
		}

		$oUser = new User();
		$oUser->user_nickname      = $this->createNickname();
		$oUser->user_login_type    = User::LOGIN_TYPE_PHONE;
		$oUser->user_register_type = User::REGISTER_TYPE_PHONE;
		$oUser->user_register_ip   = ip2long($this->request->getClientAddress());
		$oUser->user_login_ip      = ip2long($this->request->getClientAddress());
		$oUser->user_invite_code   = $this->getInviteCode();
		$bool = $oUser->save();

		if ($bool !== true) {
			$aMessage = $oUser->getMessages();
			throw new Exception(
				sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
				ResponseError::OPERATE_FAILED
			);
		}

		$oUser = User::findFirst($oUser->user_id);
		$oUserAccount = new UserAccount();
		$oUserAccount->user_id                = $oUser->user_id;
		$oUserAccount->user_phone             = $sPhone;
		$oUserAccount->user_password          = md5($sPassword);
		$bool = $oUserAccount->save();

		if ($bool !== true) {
			$aMessage = $oUserAccount->getMessages();
			throw new Exception(
				sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
				ResponseError::OPERATE_FAILED
			);
		}
		$oUserAccount = UserAccount::findFirst($oUser->user_id);

		$oVerifyCode->delVerify($sPhone, VerifyCode::TYPE_REGISTER);

		return $this->loginSuccessHandle($oUser, $oUserAccount);
	}

	/**
	 * registerQQ 注册QQ
	 * 
	 * @param  array $QQUser   
	 * @return 
	 */
	protected function registerQQ($QQUser)
	{
		//进行注册
		$oUser = new User();
		$oUser->user_nickname      = $this->createNickname($QQUser['nickname']);
		$oUser->user_avatar        = isset($QQUser['figureurl_qq_2']) ? $QQUser['figureurl_qq_2'] : $QQUser['figureurl_qq_1'];
		$oUser->user_login_type    = User::LOGIN_TYPE_QQ;
		$oUser->user_register_type = User::REGISTER_TYPE_QQ;
		$oUser->user_register_ip   = ip2long($this->request->getClientAddress());
		$oUser->user_login_ip      = ip2long($this->request->getClientAddress());
		$oUser->user_invite_code   = $this->getInviteCode();
		$bool = $oUser->save();

		if ($bool !== true) {
			$aMessage = $oUser->getMessages();
			throw new Exception(
				sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
				ResponseError::OPERATE_FAILED
			);
		}
		
		$oUser = User::findFirst($oUser->user_id);
		$oUserAccount = new UserAccount();
		$oUserAccount->user_id                = $oUser->user_id;
		$oUserAccount->user_qq_openid         = $QQUser['unionid'];
		$bool = $oUserAccount->save();

		if ($bool !== true) {
			$aMessage = $oUserAccount->getMessages();
			throw new Exception(
				sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
				ResponseError::OPERATE_FAILED
			);
		}
		$oUserAccount = UserAccount::findFirst($oUser->user_id);

		return $this->loginSuccessHandle($oUser, $oUserAccount);
	}

	/**
	 * registerWX 注册微信
	 * 
	 * @param  array $WXUser   
	 * @return 
	 */
	protected function registerWX($WXUser)
	{
		//进行注册
		$oUser = new User();
		$oUser->user_nickname      = $this->createNickname($WXUser['nickname']);
		$oUser->user_avatar        = $WXUser['headimgurl'];
		$oUser->user_login_type    = User::LOGIN_TYPE_WX;
		$oUser->user_register_type = User::REGISTER_TYPE_WX;
		$oUser->user_register_ip   = ip2long($this->request->getClientAddress());
		$oUser->user_login_ip      = ip2long($this->request->getClientAddress());
		$oUser->user_invite_code   = $this->getInviteCode();
		$bool = $oUser->save();

		if ($bool !== true) {
			$aMessage = $oUser->getMessages();
			throw new Exception(
				sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
				ResponseError::OPERATE_FAILED
			);
		}

		$oUser = User::findFirst($oUser->user_id);
		$oUserAccount = new UserAccount();
		$oUserAccount->user_id                = $oUser->user_id;
		$oUserAccount->user_wx_openid         = $WXUser['unionid'];
		$bool = $oUserAccount->save();

		if ($bool !== true) {
			$aMessage = $oUserAccount->getMessages();
			throw new Exception(
				sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
				ResponseError::OPERATE_FAILED
			);
		}
		$oUserAccount = UserAccount::findFirst($oUser->user_id);

		return $this->loginSuccessHandle($oUser, $oUserAccount);
	}

	/**
	 * registerWB 注册微博
	 * 
	 * @param  array $WBUser   
	 * @return 
	 */
	protected function registerWB($WBUser)
	{
		//进行注册
		$oUser = new User();
		$oUser->user_nickname      = $this->createNickname($WBUser['screen_name']);
		$oUser->user_avatar        = $WBUser['avatar_large'];
		$oUser->user_login_type    = User::LOGIN_TYPE_WB;
		$oUser->user_register_type = User::REGISTER_TYPE_WB;
		$oUser->user_register_ip   = ip2long($this->request->getClientAddress());
		$oUser->user_login_ip      = ip2long($this->request->getClientAddress());
		$oUser->user_invite_code   = $this->getInviteCode();
		$bool = $oUser->save();

		if ($bool !== true) {
			$aMessage = $oUser->getMessages();
			throw new Exception(
				sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
				ResponseError::OPERATE_FAILED
			);
		}

		$oUser = User::findFirst($oUser->user_id);
		$oUserAccount = new UserAccount();
		$oUserAccount->user_id                = $oUser->user_id;
		$oUserAccount->user_wb_openid         = $WBUser['openid'];
		$bool = $oUserAccount->save();

		if ($bool !== true) {
			$aMessage = $oUserAccount->getMessages();
			throw new Exception(
				sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
				ResponseError::OPERATE_FAILED
			);
		}
		$oUserAccount = UserAccount::findFirst($oUser->user_id);

		return $this->loginSuccessHandle($oUser, $oUserAccount);
	}

	/**
	 * loginSuccessHandle 登录成功后处理数据
	 * 
	 * @param  app\models\User        $oUser        
	 * @param  app\models\UserAccount $oUserAccount 
	 * @return array
	 * @throws Exception
	 */
	protected function loginSuccessHandle(\app\models\User $oUser, \app\models\UserAccount $oUserAccount)
	{
		
		if($oUser->user_is_forbid == 'Y') {
			throw new Exception(ResponseError::getError(ResponseError::USER_FORBID), ResponseError::USER_FORBID);
		}

		$oUser->user_login_ip = ip2long($this->request->getClientAddress());
		$oUser->user_login_time = time();
		$oUser->save();
        $token = $this->encryptAccessToken($oUser->user_id,$this->createToken());
		$this->redis->set($token,$oUser->user_id);
		$this->redis->expire($token,UserAccount::TOKEN_EXPIRE_TIME);
        return array('user_id' =>$oUser->user_id,'user_nickname'=>$oUser->user_nickname,'avatar'=>$oUser->user_avatar,'level'=>$oUser->user_level,'token' =>$token);
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
        $sCode = $sLetter{rand(0, 25)};
        for ($i = 0; $i < 5; $i ++) {
            $sCode .= $sNumber{rand(0, 9)};
        }

        //检查邀请码是否存在
        $isExsit = User::findFirst([
        	'user_invite_code=:user_invite_code:',
        	'bind' => [
        		'user_invite_code' => $sCode
        	]
        ]);

        if (! empty($isExsit)) {
            return $this->getInviteCode();
        }

        return $sCode;
	}

    /**
     * encryptAccessToken 加密访问token
     *
     * @param  int    $nUserId
     * @param  string $sUserToken
     * @return string
     */
    protected function encryptAccessToken($nUserId, $sUserToken)
    {
        return str_replace(['+', '/', '='], ['.', '_', ''], $this->crypt->encryptBase64($nUserId.'-'.$sUserToken.'-'.$this->createTokenExpireTime()));
    }

    /**
     * createToken 创建token
     *
     * @return string
     */
    protected function createToken()
    {
        return md5(uniqid().time());
    }

    /**
     * createTokenExpireTime 创建token过期时间
     *
     * @return int
     */
    public function createTokenExpireTime()
    {
        return time()+UserAccount::TOKEN_EXPIRE_TIME;
    }

}