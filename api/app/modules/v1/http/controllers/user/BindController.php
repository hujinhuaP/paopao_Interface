<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 用户绑定控制器                                                         |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\user;

use Exception;
use app\models\UserAccount;
use app\models\VerifyCode;
use app\helper\ResponseError;
use app\http\controllers\ControllerBase;

/**
* BindController 用户绑定
*/
class BindController extends ControllerBase
{
	
	/**
	 * phoneAction 绑定手机
	 * 
	 * @param  int $nUserId
	 */
	public function phoneAction($nUserId=0)
	{
		$sPhone    = $this->getParams('phone', 'string', '');
		$sCode     = $this->getParams('code', 'string', '');

		try {

			if ($sPhone == '') {
				throw new Exception(
					sprintf('phone %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
					ResponseError::PARAM_ERROR
				);
			}

			if ($sCode == '') {
				throw new Exception(
					sprintf('code %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
					ResponseError::PARAM_ERROR
				);
			}
			
			$row = UserAccount::findFirst($nUserId);

			if ($row->user_phone != '') {
				throw new Exception(ResponseError::getError(ResponseError::ACCOUNT_EXISTS), ResponseError::ACCOUNT_EXISTS);
			}

			$oVerifyCode = new VerifyCode();
			if (!$oVerifyCode->judgeVerify($sPhone, VerifyCode::TYPE_BIND_PHONE, $sCode)) {
				throw new Exception(ResponseError::getError(ResponseError::VERIFY_CODE_ERROR), ResponseError::VERIFY_CODE_ERROR);
			}

			$row->user_phone    = $sPhone;
			$bool = $row->save();

			if ($bool === FALSE) {
				throw new Exception(
					sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $row->getMessages()),
					ResponseError::OPERATE_FAILED
				);
			}

		} catch (Exception $e) {
			$this->error($e->getCode(), $e->getMessage());
		}

		$this->success();
	}

	/**
	 * changePhoneAction 更换绑定手机
	 * 
	 * @param  int $nUserId
	 */
	public function changePhoneAction($nUserId=0)
	{
		$aParam = $this->getParams();

		$sPhone        = $this->getParams('new_phone', 'string', '');
		$sCode         = $this->getParams('new_phone_code', 'string', '');
		$sOldPhoneCode = $this->getParams('code', 'string', '');

		try {

			if ($sPhone == '') {
				throw new Exception(
					sprintf('phone %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
					ResponseError::PARAM_ERROR
				);
			}

			if ($sCode == '') {
				throw new Exception(
					sprintf('code %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
					ResponseError::PARAM_ERROR
				);
			}

			if ($sOldPhoneCode == '') {
				throw new Exception(
					sprintf('old_phone_code %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
					ResponseError::PARAM_ERROR
				);
			}

			$bool = UserAccount::findFirst(['user_phone=:user_phone:', 'bind'=>['user_phone'=>$sPhone]]);
			
			// 判断新手机是否存在
			if ($bool) {
				throw new Exception(ResponseError::getError(ResponseError::ACCOUNT_EXISTS), ResponseError::ACCOUNT_EXISTS);
			}
			

			// 判断新手机的验证码
			if (!VerifyCode::judgeVerify($sPhone, VerifyCode::TYPE_BIND_PHONE, $sCode)) {
				throw new Exception(ResponseError::getError(ResponseError::VERIFY_CODE_ERROR), ResponseError::VERIFY_CODE_ERROR);
			}

			$row = UserAccount::findFirst($nUserId);

			// 判断旧手机的验证码
			if (!VerifyCode::judgeVerify($row->user_phone, VerifyCode::TYPE_CHANGE_PHONE, $sOldPhoneCode)) {
				throw new Exception(ResponseError::getError(ResponseError::VERIFY_CODE_ERROR), ResponseError::VERIFY_CODE_ERROR);
			}
			$sOldPhone = $row->user_phone;
			$row->user_phone = $sPhone;
			$bool = $row->save();

			if ($bool === FALSE) {
				throw new Exception(
					sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $row->getMessages()),
					ResponseError::OPERATE_FAILED
				);
			}

			VerifyCode::delVerify($sPhone, VerifyCode::TYPE_BIND_PHONE);
			VerifyCode::delVerify($sOldPhone, VerifyCode::TYPE_CHANGE_PHONE);

		} catch (Exception $e) {
			$this->error($e->getCode(), $e->getMessage());
		}

		$this->success();
	}
}