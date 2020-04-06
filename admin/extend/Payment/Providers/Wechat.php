<?php 

/*
 +------------------------------------------------------------------------+
 | Payment                                                                |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | Wechat                                                                 |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace Payment\Providers;

use Payment\Contracts\PayInterface;
use Payment\Providers\Wechat\Support;
use Payment\Exceptions\GatewayException;
use Payment\Contracts\PayProviderInterface;
use Payment\Exceptions\InvalidSignException;

class Wechat implements PayProviderInterface
{
	/**
     * Config.
     *
     * @var Config
     */
    protected $config;

    /**
     * Alipay payload.
     *
     * @var array
     */
    protected $payload;

    /**
     * Alipay gateway.
     *
     * @var string
     */
    protected $gateway;

    /**
     * sandBoxSignKey 
     * 
     * @var string
     */
    protected $sandBoxSignKey;

	public function __construct($config)
	{
        $this->config = $config;
        $this->gateway = Support::baseUri($this->config['mode']);
        $this->payload = [
            'appid'            => $this->config['app_id'],
            'mch_id'           => $this->config['mch_id'],
            'nonce_str'        => Support::random(),
            'notify_url'       => $this->config['notify_url'],
            'sign'             => '',
            'trade_type'       => '',
            'spbill_create_ip' => Support::getClientIP(),
        ];
        if ($this->config['mode'] == 'dev') {
        	$this->config['key'] = $this->getSandBoxSignKey();
        }
	}

	/**
	 * pay
	 * 
	 * @param  string $gateway
	 * @param  array $params
	 * @return PayProviderInterface
	 */
    public function pay($gateway, $params)
    {
    	$this->payload = array_merge($this->payload, $params);
        $gateway = get_class($this).'\\'.ucfirst($gateway).'Gateway';
        if (class_exists($gateway)) {
            return $this->makePay($gateway);
        }
        throw new GatewayException("Pay Gateway [{$gateway}] Not Exists", 1);
    }

    /**
     * verify  Verify a request.
     * 
     * @return array
     */
    public function verify()
    {
        $data = Support::fromXml(Support::getRawBody());
        if (Support::generateSign($data, $this->config['key']) === $data['sign']) {
            return $data;
        }
        throw new InvalidSignException('Wechat Sign Verify FAILED', 3, $data);
    }

    /**
     * find    Query an order.
     * 
     * @param  array $order
     * @return array
     */
    public function find($order)
    {
    	if (is_array($order)) {
            $this->payload = array_merge($this->payload, $order);
        } else {
            $this->payload['out_trade_no'] = $order;
        }
        unset($this->payload['notify_url'], $this->payload['trade_type']);
        $this->payload['sign'] = Support::generateSign($this->payload, $this->config['key']);
        return Support::requestApi('pay/orderquery', $this->payload, $this->config['key']);
    }

    /**
     * refund  Refund an order.
     * 
     * @param  array $order
     * @return
     */
    public function refund($order)
    {
    	if (isset($order['miniapp'])) {
            $this->payload['appid'] = $this->config['miniapp_id'];
            unset($order['miniapp']);
        }
        $this->payload = array_merge($this->payload, $order);
        unset($this->payload['notify_url'], $this->payload['trade_type']);
        $this->payload['sign'] = Support::generateSign($this->payload, $this->config['key']);
        return Support::requestApi(
            'secapi/pay/refund',
            $this->payload,
            $this->config['key'],
            $this->config['cert_client'],
            $this->config['cert_key'],
            $this->config['rootca']
        );
    }

    /**
     * cancel  Cancel an order.
     * 
     * @param  string|array $order
     * @return
     */
    public function cancel($order)
    {
    	throw new GatewayException('Wechat Do Not Have Cancel API! Plase use Close API!', 3);
    }

    /**
     * close   Close an order.
     * 
     * @param  string|array $order
     * @return 
     */
    public function close($order)
    {
    	if (is_array($order)) {
            $this->payload = array_merge($this->payload, $order);
        } else {
            $this->payload['out_trade_no'] = $order;
        }
        unset($this->payload['notify_url'], $this->payload['trade_type']);
        $this->payload['sign'] = Support::generateSign($this->payload, $this->config['key']);
        return Support::requestApi('pay/closeorder', $this->payload, $this->config['key']);
    }

    /**
     * Make pay gateway.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $gateway
     *
     * @return Response
     */
    protected function makePay($gateway)
    {
        $app = new $gateway($this->config);
        if ($app instanceof PayInterface) {
            return $app->pay($this->gateway, $this->payload);
        }
        throw new GatewayException("Pay Gateway [{$gateway}] Must Be An Instance Of PayInterface", 2);
    }

    /**
     * getSandBoxSignKey 获取沙箱密钥
     * 
     * @return string
     */
    protected function getSandBoxSignKey()
    {
    	if (!empty($this->sandBoxSignKey)) {
    		return $this->sandBoxSignKey;
    	}
    	$payload = [
			'mch_id'    => $this->payload['mch_id'],
			'nonce_str' => $this->payload['mch_id'],
    	];

    	$payload['sign'] = Support::generateSign($payload, $this->config['key']);

    	$result = Support::getInstance()->post(
            'pay/getsignkey',
            Support::toXml($payload));
        $result = is_array($result) ? $result : Support::fromXml($result);

        if (!isset($result['return_code']) || $result['return_code'] != 'SUCCESS') {
        	throw new GatewayException(
                'Get Wechat API Error:'.$result['return_msg'].' '.($result['err_code_des'] ?: ''),
                20000,
                $result
            );
        }
        
        $this->sandBoxSignKey = $result['sandbox_signkey'];
        return $this->sandBoxSignKey;
    }

    /**
     * getSignKey 
     * 
     * @return string
     */
    public function getSignKey()
    {
    	return $this->sandBoxSignKey;
    }

    /**
     * success Echo success to server.
     *
     * @return 
     */
    public function success()
    {
    	header(sprintf('%s %s %s', $_SERVER['SERVER_PROTOCOL'], 200, 'OK'), true, 200);
    	header('Content-Type: application/xml');
    	return Support::toXml(['return_code' => 'SUCCESS']);

    }

    /**
     * Magic pay.
     *
     * @param string $method
     * @param string $params
     *
     * @return 
     */
    public function __call($method, $params)
    {
        return self::pay($method, ...$params);
    }
}