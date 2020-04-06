<?php

namespace app\helper;

class WechatPay
{
    //微信开放平台移动端 appid
    protected static $openappid = 'wx15e7ae5e8498aca5';
    //微信商户平台商户号
    protected static $merchant_id = '1547035631';
    //微信商户平台API秘钥
    protected static $api_key = "qXfGNqdbD8ZGb5NxCjUEufG8vVprOmtW";
    //公众号appid
    protected static $public_app_id = 'wx9b2c3de5e1a0119c';
    //公众号对应的商户号
    protected static $public_merchant_id = '1511147451';
    //公众号对应的商户平台API秘钥
    protected static $public_api_key = "pti0tzR8Ilr5smoGHJeSfMMJqMBZr9ep";

    public function setPublicAppId( $appId )
    {
        self::$public_app_id = $appId;
    }

    public function setPublicMerchantId( $merchantId )
    {
        self::$public_merchant_id = $merchantId;
    }

    public function setPublicApiKey( $apiKey )
    {
        self::$public_api_key = $apiKey;
    }

    /**
     * Pay 微信支付
     * @param $money  支付金额以 分 为单位
     * @param $order_no 订单号
     * @param $body 商品购买描述
     * @return array|bool
     */
    public static function createOrder( $money, $order_no, $body, $notify_url, $trade_type = 'APP', $product_id = '', $ip = '', $attach = 'RECHARGE', $wap_url = '' )
    {
        $app_id = self::$openappid;
        $mch_id = self::$merchant_id;
        if ( $trade_type != 'APP' ) {
            $app_id = self::$public_app_id;
            $mch_id = self::$public_merchant_id;
        }
        $url  = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $data = [
            'appid'            => $app_id,
            'mch_id'           => $mch_id,
            'body'             => $body,
            'nonce_str'        => self::rand_num(10),
            'total_fee'        => $money * 100,
            'spbill_create_ip' => self::get_client_ip(),
            'trade_type'       => $trade_type,
            'notify_url'       => $notify_url,
            'out_trade_no'     => $order_no,
            'attach'           => $attach,
        ];
        if ( $trade_type == 'NATIVE' ) {
            $data['spbill_create_ip'] = $ip;
            $data['product_id']       = $product_id;
        } else if ( $trade_type == 'MWEB' ) {
            if ( !$wap_url ) {
                $wap_url = 'http://charge.sxypaopao.com';
            }
            $data['scene_info'] = '{"h5_info":{"type":"Wap","wap_url":"' . $wap_url . '","wap_name":"' . $body . '"}}';//场景信息 必要参数;
        }

        $data['sign'] = self::getSign($data, $trade_type);
        $xml          = self::array_to_xml($data);
        $res          = self::postXmlCurl($url, $xml);
        $res          = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ( $res->return_code == "SUCCESS" ) {
            if ( $trade_type == 'APP' ) {
                $pay_data         = [
                    'appid'     => $app_id,
                    'partnerid' => $mch_id,
                    'prepayid'  => (string)$res->prepay_id,
                    'package'   => 'Sign=WXpay',
                    'noncestr'  => self::rand_num(10),
                    'timestamp' => time()
                ];
                $pay_data['sign'] = self::getSign($pay_data, $trade_type);
                return $pay_data;
            } else if ( $trade_type == 'MWEB' ) {
                $pay_data = [
                    'code_url' => sprintf('%s&redirect_url=%s', $res->mweb_url, urlencode(str_replace('http://', '', $wap_url))),
                    'rule_url' => $wap_url,
                    'attach'   => $attach,
                ];
                return $pay_data;
            } else {
                $pay_data = [
                    'code_url' => (string)$res->code_url
                ];
                return $pay_data;
            }
        } else {
            return FALSE;
        }

    }

    //订单查询
    public static function orderQuery( $transaction_id, $trade_type = 'APP' )
    {
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        if ( $trade_type == 'APP' ) {
            $app_id = self::$openappid;
            $mch_id = self::$merchant_id;
        } else {
            $app_id = self::$public_app_id;
            $mch_id = self::$public_merchant_id;
        }
        $data         = [
            'appid'          => $app_id,
            'mch_id'         => $mch_id,
            'transaction_id' => $transaction_id,
            'nonce_str'      => self::rand_num(10),
        ];
        $data['sign'] = self::getSign($data, $trade_type);
        $xml          = self::array_to_xml($data);
        $res          = self::postXmlCurl($url, $xml);
        $res          = json_decode(json_encode(simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA)), TRUE);
        if ( $res['return_code'] == "SUCCESS" && isset($res['result_code']) && $res['result_code'] == 'SUCCESS' && $res['trade_state'] == 'SUCCESS' ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * getSign 获取签名
     * @param $data
     * @return string
     */
    protected static function getSign( $data, $trade_type )
    {
        ksort($data);
        $str = self::BuildQuery($data);
        $key = self::$api_key;
        if ( $trade_type != 'APP' ) {
            $key = self::$public_api_key;
        }
        $str  .= 'key=' . $key;
        $sign = strtoupper(md5($str));
        return $sign;
    }

    public static function array_to_xml( $arr )
    {

        $xml = new \SimpleXMLElement('<xml/>');
        foreach ( $arr as $k => $v ) {
            $xml->addChild($k, $v);
        }
        return $xml->asXML();
    }

    public static function postXmlCurl( $url, $xml, $second = 30 )
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    private static function BuildQuery( $paraMap )
    {
        $str = "";
        ksort($paraMap);
        foreach ( $paraMap as $k => $v ) {
            $str .= $k . "=" . $v . "&";
        }
        return $str;
    }

    public static function get_client_ip( $type = 0, $adv = FALSE )
    {
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if ( $ip !== NULL ) return $ip[ $type ];
        if ( $adv ) {
            if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if ( FALSE !== $pos ) unset($arr[ $pos ]);
                $ip = trim($arr[0]);
            } else if ( isset($_SERVER['HTTP_CLIENT_IP']) ) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } else if ( isset($_SERVER['REMOTE_ADDR']) ) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } else if ( isset($_SERVER['REMOTE_ADDR']) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip   = $long ? [
            $ip,
            $long
        ] : [
            '0.0.0.0',
            0
        ];
        return $ip[ $type ];
    }

    /**
     * 随机数
     * @param int $num
     * @return int
     */
    public static function rand_num( $num = 4 )
    {
        $authnum = "";
        srand((double)microtime() * 1000000);//create a random number feed.
        for ( $i = 0; $i < $num; $i++ ) {
            $randnum = rand(1, 9);
            $authnum .= $randnum;
        }
        return $authnum;
    }


}
