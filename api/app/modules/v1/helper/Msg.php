<?php 

namespace app\helper;

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 短息工具类                                                             |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */
class Msg
{

//    const REGISTER_TEMPLATE_TEXT = '【泡泡语音交友】验证码%s 您正在使用验证码登陆注册，请勿将验证码泄露给其他人，5分钟内有效，感谢使用泡泡语音交友！';
//    const BIND_TEMPLATE_TEXT     = '【泡泡语音交友】验证码%s 您正在使用验证码绑定手机号码，请勿将验证码泄露给其他人，5分钟内有效，感谢使用泡泡语音交友！';
//    const CHANGE_TEMPLATE_TEXT   = '【泡泡语音交友】验证码%s 您正在使用验证码更换手机号码，请勿将验证码泄露给其他人，5分钟内有效，感谢使用泡泡语音交友！';
//    const WITHDRAW_TEMPLATE_TEXT = '【泡泡语音交友】验证码%s 您正在使用验证码提现,请勿将验证码泄露给其他人，5分钟内有效，感谢使用泡泡语音交友！';



	/** @var string APP KEY */private static $apikey = "9a7c26d50b29249934409ffcedb2bc9e";

    /** @var string 签名 */
   // private static $sign = "泡泡语音交友";

    /**
     * snedRegister 发送注册验证码短信
     * 
     * @param  string $sPhone  
     * @param  string $sCode
     * @return bool
     * @throws Exception
     */
    public static function snedRegister($sPhone, $sCode,$tpl_id,$app = '甜蜜')
    {
        if(strpos($sPhone,'86') === 0){
            $sPhone = substr($sPhone,2);
        }else{
            $sPhone = '+'.$sPhone;
        }
        $data = [
            'apikey' => self::$apikey,
            'mobile' => $sPhone,
            'tpl_id' => $tpl_id,
            "tpl_value"   => urlencode('#code#').'='.urlencode($sCode).'&'.
                urlencode('#app#').'='.urlencode($app),
        ];
        $result = json_decode(static::curl('https://sms.yunpian.com/v2/sms/tpl_single_send.json', $data), true);

        if ($result['code'] != 0) {
            throw new \Exception($result['msg']?:$result['detail'], $result['code']);
        }

        return true;
    }


    /**
     * snedBindPhone 发送绑定手机验证码短信
     * 
     * @param  string $sPhone  
     * @param  string $sCode
     * @return bool
     * @throws Exception
     */
    public static function snedBindPhone($sPhone, $sCode,$tpl_id,$app = '甜蜜')
    {
        if(strpos($sPhone,'86') === 0){
            $sPhone = substr($sPhone,2);
        }else{
            $sPhone = '+'.$sPhone;
        }
        $data = [
            'apikey' => self::$apikey,
            'mobile' => $sPhone,
//            'text' => sprintf(self::BIND_TEMPLATE_TEXT,$sCode)
            'tpl_id' => $tpl_id,
            "tpl_value"   => urlencode('#code#').'='.urlencode($sCode).'&'.
                urlencode('#app#').'='.urlencode($app),
        ];
//        $result = json_decode(static::curl('https://sms.yunpian.com/v2/sms/single_send.json', $data), true);
        $result = json_decode(static::curl('https://sms.yunpian.com/v2/sms/tpl_single_send.json', $data), true);

        if ($result['code'] != 0) {
            throw new \Exception($result['msg']?:$result['detail'], $result['code']);
        }
        return true;
    }

    /**
     * snedChangePhone 发送更换手机验证码短信
     * 
     * @param  string $sPhone  
     * @param  string $sCode
     * @return bool
     * @throws Exception
     */
    public static function snedChangePhone($sPhone, $sCode,$tpl_id,$app = '甜蜜')
    {
        if(strpos($sPhone,'86') === 0){
            $sPhone = substr($sPhone,2);
        }else{
            $sPhone = '+'.$sPhone;
        }
        $data = [
            'apikey' => self::$apikey,
            'mobile' => $sPhone,
//            'text' => sprintf(self::CHANGE_TEMPLATE_TEXT,$sCode)
            'tpl_id' => $tpl_id,
            "tpl_value"   => urlencode('#code#').'='.urlencode($sCode).'&'.
    urlencode('#app#').'='.urlencode($app),
        ];
//        $result = json_decode(static::curl('https://sms.yunpian.com/v2/sms/single_send.json', $data), true);
        $result = json_decode(static::curl('https://sms.yunpian.com/v2/sms/tpl_single_send.json', $data), true);

        if ($result['code'] != 0) {
            throw new \Exception($result['msg']?:$result['detail'], $result['code']);
        }
        return true;
    }

    /**
     * sned 发送提现验证码短信
     * 
     * @param  string $sPhone  
     * @param  string $sCode
     * @return bool
     * @throws Exception
     */
    public static function snedWithdraw($sPhone, $sCode, $sCash, $sPay, $sAccount,$tpl_id,$app = '甜蜜')
    {
        if(strpos($sPhone,'86') === 0){
            $sPhone = substr($sPhone,2);
        }else{
            $sPhone = '+'.$sPhone;
        }
        $data = [
            'apikey' => self::$apikey,
            'mobile' => $sPhone,
//            'text' => sprintf(self::WITHDRAW_TEMPLATE_TEXT,$sCode),
            'tpl_id' => $tpl_id,
            "tpl_value"   => urlencode('#code#').'='.urlencode($sCode)
//                .'&'. urlencode('#app#').'='.urlencode($app),
        ];
//        $result = json_decode(static::curl('https://sms.yunpian.com/v2/sms/single_send.json', $data), true);
        $result = json_decode(static::curl('https://sms.yunpian.com/v2/sms/tpl_single_send.json', $data), true);
        
        if ($result['code'] != 0) {
            throw new \Exception($result['msg']?:$result['detail'], $result['code']);
        }
        return true;
    }

	/**
	 * curl 发送http请求
	 * 
	 * @param  string $url  
	 * @param  string $data 
	 * @return string
	 */
    protected static function curl($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * response 响应数据
     * 
     * @param  string $code
     * @return string
     */
    protected static function response($code)
    {
        $response = [
            '0'   => '提交成功',
            '101' => '无此用户',
            '102' => '密码错误',
            '103' => '提交过快（提交速度超过流速限制）',
            '104' => '短信平台系统繁忙',
            '105' => '短信内容包含敏感词',
            '106' => '消息长度错误',
            '107' => '包含错误的手机号码',
            '108' => '手机号码个数错',
            '109' => '可用短信数已使用完',
            '110' => '不在发送时间内',
            '111' => '超出该账户当月发送额度限制',
            '112' => '无此产品，用户没有订购该产品',
            '113' => 'extno格式错',
            '115' => '自动审核驳回',
            '116' => '签名不合法，未带签名',
            '117' => 'IP地址认证错,请求调用的IP地址不是系统登记的IP地址',
            '118' => '用户没有相应的发送权限',
            '119' => '用户已过期',
            '120' => '测试内容不是白名单'
        ];
        return $response[$code];
    }
}