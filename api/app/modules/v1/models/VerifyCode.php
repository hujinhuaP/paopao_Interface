<?php 

namespace app\models;

use Exception;
use app\helper\Msg;
use app\helper\ResponseError;

/**
* VerifyCode 验证码
*/
class VerifyCode extends ModelBase
{
    /** @var int 过期时间 */
    const EXPIRE_TIME          = 1800;

    /** @var string 注册 */
    const TYPE_REGISTER        = 'register';
    /** @var string 绑定手机 */
    const TYPE_BIND_PHONE      = 'bindphone';
    /** @var string 更换手机 */
    const TYPE_CHANGE_PHONE    = 'changephone';
    /** @var string 忘记密码 */
    const TYPE_FORGET_PASSWORD = 'forgetpwd';
    /** @var string 提现 */
    const TYPE_WITHDRAW        = 'withdraw';

    public function beforeCreate()
    {
        $this->verify_code_update_time = time();
        $this->verify_code_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->verify_code_update_time = time();
    }

    /**
     * saveVerify 保存验证码
     * 
     * @param string $sPhone      手机号码
     * @param string $sCode       验证码
     * @param string $sVerifyType 验证类型
     * @return bool
     * @throws  Exception
     */
    public static function saveVerify($sPhone, $sCode, $sVerifyType)
    {
        $that = new static();
        $that->verify_code_phone       = $sPhone;
        $that->verify_code             = $sCode;
        $that->verify_code_type        = $sVerifyType;
        $that->verify_code_expire_time = time()+static::EXPIRE_TIME;
        $bool = $that->save();

        if ($bool === false) {
            $aMessage = $that->getMessages();
            throw new Exception(
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
                ResponseError::OPERATE_FAILED
            );
        }
        return $bool;
    }

    /**
     * getVerify 获取验证码
     * 
     * @param  string $sPhone      手机号码
     * @param  int    $sVerifyType 验证类型
     * @return app\models\VerifyCode
     */
    public static function getVerify($sPhone, $sVerifyType)
    {
        return static::findFirst([
            'verify_code_phone = :phone:
            and verify_code_type = :code_type:
            order by verify_code_id desc',
            'bind' => [
                'phone'     => $sPhone,
                'code_type' => $sVerifyType,
            ]
        ]);
    }

    /**
     * delVerify 删除验证码
     * 
     * @param  string $sPhone      手机号码
     * @param  int    $sVerifyType 验证类型
     * @return bool
     */
    public static function delVerify($sPhone, $sVerifyType)
    {
        return static::find([
            'verify_code_phone = :phone: and verify_code_type = :code_type:',
            'bind' => [
                'phone'     => $sPhone,
                'code_type' => $sVerifyType,
            ]
        ])->delete();
    }

    /**
     * judgeVerify 判断验证码
     * 
     * @param  string $sPhone     
     * @param  int    $sVerifyType
     * @param  string $sCode
     * @return bool
     */
    public static function judgeVerify($sPhone, $sVerifyType, $sCode)
    {
        $row = static::getVerify($sPhone, $sVerifyType);
        if ($row === false) {
            throw new Exception(ResponseError::getError(ResponseError::VERIFY_CODE_ERROR), ResponseError::VERIFY_CODE_ERROR);
        } elseif ($row->verify_code_expire_time < time()) {
            throw new Exception(ResponseError::getError(ResponseError::VERIFY_CODE_EXPIRE), ResponseError::VERIFY_CODE_EXPIRE);
        } elseif ($row->verify_code != $sCode) {
            throw new Exception(ResponseError::getError(ResponseError::VERIFY_CODE_ERROR), ResponseError::VERIFY_CODE_ERROR);
        } else {
            return true;
        }
    }
}