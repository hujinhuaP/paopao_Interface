<?php 

namespace Payment\Providers\Alipay;

use Payment\Exceptions\GatewayException;
use Payment\Exceptions\InvalidConfigException;
use Payment\Exceptions\InvalidSignException;

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
     * Alipay gateway.
     *
     * @var string
     */
    protected $baseUri = 'https://openapi.alipay.com/gateway.do';

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
     * Get Alipay API result.
     *
     * @param array  $data
     * @param string $publicKey
     *
     * @return array
     */
    public static function requestApi(array $data, $publicKey)
    {
        $method = str_replace('.', '_', $data['method']).'_response';
        $result = mb_convert_encoding(self::getInstance()->post('', $data), 'utf-8', 'gb2312');
        $result = json_decode($result, true);
        if (isset($result[$method]['code']) && $result[$method]['code'] == '10000') {
            if (!self::verifySign($result[$method], $publicKey, true, $result['sign'])) {
                throw new InvalidSignException('Alipay Sign Verify FAILED', 3, $result);
            }
            return $result[$method];
        }
        throw new GatewayException(
            'Get Alipay API Error:'.$result[$method]['msg'].' - '.$result[$method]['sub_code'],
            $result[$method]['code'],
            $result
        );
    }

    /**
     * Generate sign.
     *
     * @param array  $parmas
     * @param string $privateKey
     *
     * @return string
     */
    public static function generateSign(array $parmas, $privateKey = null)
    {
        if (is_null($privateKey)) {
            throw new InvalidConfigException('Missing Alipay Config -- [private_key]', 1);
        }
        $extend=pathinfo($privateKey);
        if (isset($extend['extension']) && $extend['extension'] == 'pem') {
            $privateKey = openssl_pkey_get_private($privateKey);
        } else {
            $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n".
                wordwrap($privateKey, 64, "\n", true).
                "\n-----END RSA PRIVATE KEY-----";
        }
        openssl_sign(self::getSignContent($parmas), $sign, $privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($sign);
    }

    /**
     * Verfiy sign.
     *
     * @param array       $data
     * @param string      $publicKey
     * @param bool        $sync
     * @param string|null $sign
     *
     * @return bool
     */
    public static function verifySign(array $data, $publicKey = null, $sync = false, $sign = null)
    {
        if (is_null($publicKey)) {
            throw new InvalidConfigException('Missing Alipay Config -- [ali_public_key]', 2);
        }
        $extend=pathinfo($publicKey);
        if (isset($extend['extension']) && $extend['extension'] == 'pem') {
            $publicKey = openssl_pkey_get_public($publicKey);
        } else {
            $publicKey = "-----BEGIN PUBLIC KEY-----\n".
                wordwrap($publicKey, 64, "\n", true).
                "\n-----END PUBLIC KEY-----";
        }
        $sign = $sign ?: $data['sign'];
        $toVerify = $sync ? mb_convert_encoding(json_encode($data, JSON_UNESCAPED_UNICODE), 'gb2312', 'utf-8') :
                            self::getSignContent($data, true);
        return openssl_verify($toVerify, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    /**
     * Get signContent that is to be signed.
     *
     * @param array $data
     * @param bool  $verify
     *
     * @return string
     */
    public static function getSignContent(array $data, $verify = false)
    {
        $data = self::encoding($data, $data['charset'] ?: 'gb2312', 'utf-8');
        ksort($data);
        $stringToBeSigned = '';
        foreach ($data as $k => $v) {
            if ($verify && $k != 'sign' && $k != 'sign_type') {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
            if (!$verify && $v !== '' && !is_null($v) && $k != 'sign' && '@' != substr($v, 0, 1)) {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
        }
        return trim($stringToBeSigned, '&');
    }

    /**
     * Convert encoding.
     *
     * @param string|array $data
     * @param string       $to
     * @param string       $from
     *
     * @return array
     */
    public static function encoding($data, $to = 'utf-8', $from = 'gb2312')
    {
        $encoded = [];
        foreach ($data as $key => $value) {
            $encoded[$key] = is_array($value) ? self::encoding($value, $to, $from) :
                                                mb_convert_encoding($value, $to, $from);
        }
        return $encoded;
    }

    /**
     * Alipay gateway.
     *
     * @param string $mode
     *
     * @return string
     */
    public static function baseUri($mode = null)
    {
        switch ($mode) {
            case 'dev':
                self::getInstance()->baseUri = 'https://openapi.alipaydev.com/gateway.do';
                break;
            default:
                break;
        }
        return self::getInstance()->baseUri;
    }
}