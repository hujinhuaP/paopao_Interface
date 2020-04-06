<?php 

namespace Payment\Providers\Wechat;

use Payment\Exceptions\GatewayException;
use Payment\Exceptions\InvalidConfigException;
use Payment\Exceptions\InvalidSignException;
use Payment\Exceptions\InvalidArgumentException;

class Support
{
	use \Payment\Traits\Http;

    /**
     * Instance.
     *
     * @var Support
     */
    private static $instance;

    /**
     * Wechat gateway.
     *
     * @var string
     */
    protected $baseUri = 'https://api.mch.weixin.qq.com/';

    /**
     * Get instance.
     *
     * @return Support
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Request wechat api.
     *
     * @param string      $endpoint
     * @param array       $data
     * @param string|null $key
     * @param string      $certClient
     * @param string      $certKey
     * @param string      $ca
     *
     * @return array
     */
    public static function requestApi($endpoint, $data, $key = null, $certClient = null, $certKey = null, $rootca=null)
    {
        $result = self::getInstance()->post(
            $endpoint,
            self::toXml($data),
            ($certClient !== null && $certKey !== null && $rootca !== null) ? [
				CURLOPT_SSL_VERIFYHOST => 1,
				CURLOPT_SSLCERTTYPE    => 'pem',
				CURLOPT_SSLCERT        => $certClient,
				CURLOPT_SSLKEYTYPE     => 'pem',
				CURLOPT_SSLKEY         => $certKey,
				CURLOPT_CAINFO         => $rootca,
            ] : []
        );
        $result = is_array($result) ? $result : self::fromXml($result);
        if (!isset($result['return_code']) || $result['return_code'] != 'SUCCESS' || $result['result_code'] != 'SUCCESS') {
            throw new GatewayException(
                'Get Wechat API Error:'.$result['return_msg'].' '.($result['err_code_des'] ?: ''),
                20000,
                $result
            );
        }
        if (self::generateSign($result, $key) === $result['sign']) {
            return $result;
        }
        throw new InvalidSignException('Wechat Sign Verify FAILED', 3, $result);
    }

    /**
     * Generate wechat sign.
     *
     * @param array $data
     *
     * @return string
     */
    public static function generateSign($data, $key = null)
    {
        if (is_null($key)) {
            throw new InvalidArgumentException('Missing Wechat Config -- [key]', 1);
        }
        ksort($data);
        $string = md5(self::getSignContent($data).'&key='.$key);
        return strtoupper($string);
    }

    /**
     * Generate sign content.
     *
     * @param array $data
     *
     * @return string
     */
    public static function getSignContent($data)
    {
        $buff = '';
        foreach ($data as $k => $v) {
            $buff .= ($k != 'sign' && $v != '' && !is_array($v)) ? $k.'='.$v.'&' : '';
        }
        return trim($buff, '&');
    }

    /**
     * Convert array to xml.
     *
     * @param array $data
     *
     * @return string
     */
    public static function toXml($data)
    {
        if (!is_array($data) || count($data) <= 0) {
            throw new InvalidArgumentException('Convert To Xml Error! Invalid Array!', 2);
        }
        $xml = '<xml>';
        foreach ($data as $key => $val) {
            $xml .= is_numeric($val) ? '<'.$key.'>'.$val.'</'.$key.'>' :
                                       '<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
        }
        $xml .= '</xml>';
        return $xml;
    }
    /**
     * Convert xml to array.
     *
     * @param string $xml
     *
     * @return array
     */
    public static function fromXml($xml)
    {
        if (!$xml) {
            throw new InvalidArgumentException('Convert To Array Error! Invalid Xml!', 3);
        }
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * Wechat gateway.
     *
     * @param string $mode
     *
     * @return string
     */
    public static function baseUri($mode = null)
    {
        switch ($mode) {
            case 'dev':
                self::getInstance()->baseUri = 'https://api.mch.weixin.qq.com/sandboxnew/';
                break;
            case 'hk':
                self::getInstance()->baseUri = 'https://apihk.mch.weixin.qq.com/';
                break;
            default:
                break;
        }
        return self::getInstance()->baseUri;
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param  int  $length
     * @return string
     */
	public static function random($length = 16)
    {
        $string = '';
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $string .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $string;
    }
}