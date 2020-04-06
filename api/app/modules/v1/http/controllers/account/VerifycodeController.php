<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 验证码                                                                 |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\account;

use app\models\Kv;
use app\services\VerifycodeService;
use Exception;
use app\helper\Msg;
use app\models\VerifyCode;
use app\models\UserAccount;
use app\helper\ResponseError;
use app\http\controllers\ControllerBase;


/**
* VerifycodeController
*/
class VerifycodeController extends ControllerBase
{
    use \app\services\UserService;
    /**
     * registerAction 注册验证码
     */
    public function registerAction()
    {
        $sPhone = $this->getParams('phone', 'string', '');
        try {

            // 判断手机号码
            if ($sPhone == '') {
                throw new Exception(ResponseError::getError(ResponseError::ACCOUNT_ERROR), ResponseError::ACCOUNT_ERROR);
            }
            $forbidPhoneArr = [
                17635110645
            ];
            if(in_array($sPhone,$forbidPhoneArr)){
                throw new Exception(ResponseError::getError(ResponseError::USER_FORBID), ResponseError::USER_FORBID);
            }

            $oVerifycodeService = new VerifycodeService($sPhone,VerifycodeService::TYPE_REGISTER);
            if(!$oVerifycodeService->save()){
                throw new Exception(ResponseError::getError(ResponseError::FORBIDDEN_MESSAGE), ResponseError::FORBIDDEN_MESSAGE);
            }
            // IP 限制
            $oVerifycodeService = new VerifycodeService($sPhone,VerifycodeService::TYPE_REGISTER,ip2long($this->request->getClientAddress()));
            if(!$oVerifycodeService->save()){
                throw new Exception(ResponseError::getError(ResponseError::FORBIDDEN_MESSAGE), ResponseError::FORBIDDEN_MESSAGE);
            }
            // 设备限制
            $oVerifycodeService = new VerifycodeService($sPhone,VerifycodeService::TYPE_REGISTER,null,$this->getParams('device_id'));
            if(!$oVerifycodeService->save()){
                throw new Exception(ResponseError::getError(ResponseError::FORBIDDEN_MESSAGE), ResponseError::FORBIDDEN_MESSAGE);
            }
//            $oUserAccount = UserAccount::findFirst([
//                'user_phone=:user_phone:',
//                'bind' => [
//                    'user_phone' => $sPhone,
//                ]
//            ]);
//
//            // 判断账号是否已存在
//            if ($oUserAccount) {
//                throw new Exception(ResponseError::getError(ResponseError::ACCOUNT_EXISTS), ResponseError::ACCOUNT_EXISTS);
//            }

            // 判断时间是否过期
            // $row = VerifyCode::getVerify($sPhone, VerifyCode::TYPE_REGISTER);

            // if ($row && $row->verify_code_expire_time >= time()) {
            //     throw new Exception(ResponseError::getError(ResponseError::VERIFY_CODE_NOT_EXPIRE), ResponseError::VERIFY_CODE_NOT_EXPIRE);
            // }
            $sCode = mt_rand(100000, 999999);
            $appInfo = $this->getAppInfo();
            Msg::snedRegister($sPhone, $sCode,$appInfo['msg_register_template_id'],$appInfo['app_name']);
            VerifyCode::saveVerify($sPhone, $sCode, VerifyCode::TYPE_REGISTER);

        } catch (Exception $e) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success();
    }

    /**
     * bindPhoneAction 绑定手机
     */
    public function bindPhoneAction()
    {
        $sPhone = $this->getParams('phone', 'string', '');

        try {

            // 判断手机号码
            if ($sPhone == '') {
                throw new Exception(ResponseError::getError(ResponseError::ACCOUNT_ERROR), ResponseError::ACCOUNT_ERROR);
            }
            $oVerifycodeService = new VerifycodeService($sPhone,VerifycodeService::TYPE_BIND_PHONE);
            if(!$oVerifycodeService->save()){
                throw new Exception(ResponseError::getError(ResponseError::FORBIDDEN_MESSAGE), ResponseError::FORBIDDEN_MESSAGE);
            }
            // IP 限制
            $oVerifycodeService = new VerifycodeService($sPhone,VerifycodeService::TYPE_BIND_PHONE,ip2long($this->request->getClientAddress()));
            if(!$oVerifycodeService->save()){
                throw new Exception(ResponseError::getError(ResponseError::FORBIDDEN_MESSAGE), ResponseError::FORBIDDEN_MESSAGE);
            }
            // 设备限制
            $oVerifycodeService = new VerifycodeService($sPhone,VerifycodeService::TYPE_BIND_PHONE,null,$this->getParams('device_id'));
            if(!$oVerifycodeService->save()){
                throw new Exception(ResponseError::getError(ResponseError::FORBIDDEN_MESSAGE), ResponseError::FORBIDDEN_MESSAGE);
            }
            $oUserAccount = UserAccount::findFirst([
                'user_phone=:user_phone:',
                'bind' => [
                    'user_phone' => $sPhone,
                ]
            ]);

            // 判断手机号码是否存在
            if ($oUserAccount) {
                throw new Exception(ResponseError::getError(ResponseError::USER_HAS_BIND_PHONE), ResponseError::USER_HAS_BIND_PHONE);
            }


            // 判断时间是否过期
            // $row = VerifyCode::getVerify($sPhone, VerifyCode::TYPE_BIND_PHONE);

            // if ($row && $row->verify_code_expire_time >= time()) {
            //     throw new Exception(ResponseError::getError(ResponseError::VERIFY_CODE_NOT_EXPIRE), ResponseError::VERIFY_CODE_NOT_EXPIRE);
            // }

            $sCode = mt_rand(100000, 999999);
            $appInfo = $this->getAppInfo();
            Msg::snedBindPhone($sPhone, $sCode,$appInfo['msg_bind_template_id'],$appInfo['app_name']);
            VerifyCode::saveVerify($sPhone, $sCode, VerifyCode::TYPE_BIND_PHONE);

        } catch (Exception $e) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success();
    }

    /**
     * changePhoneAction 更换手机
     */
    public function changePhoneAction($nUserId=0)
    {
        try {

            $oUserAccount = UserAccount::findFirst($nUserId);

            if ($oUserAccount->user_phone == '') {
                throw new Exception(ResponseError::getError(ResponseError::USER_NOT_BIND_PHONE), ResponseError::USER_NOT_BIND_PHONE);
            }

            // 判断时间是否过期
            // $row = VerifyCode::getVerify($oUserAccount->user_phone, VerifyCode::TYPE_CHANGE_PHONE);

            // if ($row && $row->verify_code_expire_time >= time()) {
            //     throw new Exception(ResponseError::getError(ResponseError::VERIFY_CODE_NOT_EXPIRE), ResponseError::VERIFY_CODE_NOT_EXPIRE);
            // }
            $oVerifycodeService = new VerifycodeService($oUserAccount->user_phone,VerifycodeService::TYPE_CHANGE_PHONE);
            if(!$oVerifycodeService->save()){
                throw new Exception(ResponseError::getError(ResponseError::FORBIDDEN_MESSAGE), ResponseError::FORBIDDEN_MESSAGE);
            }
            // IP 限制
            $oVerifycodeService = new VerifycodeService($oUserAccount->user_phone,VerifycodeService::TYPE_CHANGE_PHONE,ip2long($this->request->getClientAddress()));
            if(!$oVerifycodeService->save()){
                throw new Exception(ResponseError::getError(ResponseError::FORBIDDEN_MESSAGE), ResponseError::FORBIDDEN_MESSAGE);
            }
            // 设备限制
            $oVerifycodeService = new VerifycodeService($oUserAccount->user_phone,VerifycodeService::TYPE_CHANGE_PHONE,null,$this->getParams('device_id'));
            if(!$oVerifycodeService->save()){
                throw new Exception(ResponseError::getError(ResponseError::FORBIDDEN_MESSAGE), ResponseError::FORBIDDEN_MESSAGE);
            }
            $sCode = mt_rand(100000, 999999);
            $appInfo = $this->getAppInfo();
            Msg::snedChangePhone($oUserAccount->user_phone, $sCode,$appInfo['msg_change_template_id'],$appInfo['app_name']);
            VerifyCode::saveVerify($oUserAccount->user_phone, $sCode, VerifyCode::TYPE_CHANGE_PHONE);

        } catch (Exception $e) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success();
    }

    /**
     * judgeChangePhoneAction 判断更换手机
     */
    public function judgeChangePhoneAction($nUserId=0)
    {
        $sCode = $this->getParams('code', 'string', '');

        try {
            $oUserAccount = UserAccount::findFirst($nUserId);
            VerifyCode::judgeVerify($oUserAccount->user_phone, VerifyCode::TYPE_CHANGE_PHONE, $sCode);

        } catch (Exception $e) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success();
    }

    /**
 * withdrawAction 发送提现验证码
 *
 * @param  int $nUserId
 */
    public function withdrawAction($nUserId=0)
    {
        try {
            $sCash    = $this->getParams('cash', 'string', '');
            $sPay     = $this->getParams('pay', 'string', '');
            $sAccount = $this->getParams('account', 'string', '');

//            if(date('D') != 'Sun' && $this->config->application->evn != 'dev'){
            if(date('Y-m-d') > '2019-04-16' && date('D') != 'Sun' && $this->config->application->evn != 'dev'){
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::WITHDRAW_ADD_DAY_ERROR)),
                    ResponseError::WITHDRAW_ADD_DAY_ERROR
                );
            }
            if ($sCash == '') {
                throw new Exception(
                    sprintf('cash %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }

            if ($sPay == '') {
                throw new Exception(
                    sprintf('pay %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }

            if ($sAccount == '') {
                throw new Exception(
                    sprintf('account %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }

            $oUserAccount = UserAccount::findFirst($nUserId);

            if ($oUserAccount->user_phone == '') {
                throw new Exception(ResponseError::getError(ResponseError::USER_NOT_BIND_PHONE), ResponseError::USER_NOT_BIND_PHONE);
            }

            // 判断时间是否过期
            // $row = VerifyCode::getVerify($oUserAccount->user_phone, VerifyCode::TYPE_WITHDRAW);

            // if ($row && $row->verify_code_expire_time >= time()) {
            //     throw new Exception(ResponseError::getError(ResponseError::VERIFY_CODE_NOT_EXPIRE), ResponseError::VERIFY_CODE_NOT_EXPIRE);
            // }

            $sCode = mt_rand(100000, 999999);

            $appInfo = $this->getAppInfo();
            Msg::snedWithdraw($oUserAccount->user_phone, $sCode, $sCash, $sPay, $sAccount,$appInfo['msg_withdraw_template_id'],$appInfo['app_name']);
            VerifyCode::saveVerify($oUserAccount->user_phone, $sCode, VerifyCode::TYPE_WITHDRAW);

        } catch (Exception $e) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success();
    }

    /**
     *  发送异常警告
     *
     */
    public function sendWarningAction()
    {
        try {
            $admin_phone = Kv::get(Kv::ADMIN_PHONE);
            $coin_name = Kv::get(Kv::KEY_COIN_NAME);
            Msg::sendWarning($admin_phone,$coin_name);

        } catch (Exception $e) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }
}