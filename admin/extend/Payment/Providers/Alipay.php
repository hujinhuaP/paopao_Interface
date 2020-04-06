<?php 

/*
 +------------------------------------------------------------------------+
 | Payment                                                                |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | Alipay                                                                 |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace Payment\Providers;

use Payment\Contracts\PayInterface;
use Payment\Providers\Alipay\Support;
use Payment\Exceptions\GatewayException;
use Payment\Contracts\PayProviderInterface;
use Payment\Exceptions\InvalidSignException;

class Alipay implements PayProviderInterface
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

	public function __construct($config)
	{
		$this->config = $config;
		$this->gateway = Support::baseUri($this->config['mode']);
		$this->payload = [
            'app_id'      => $config['app_id'],
            'method'      => '',
            'format'      => 'JSON',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'version'     => '1.0',
            'return_url'  => $config['return_url'],
            'notify_url'  => $config['notify_url'],
            'timestamp'   => date('Y-m-d H:i:s'),
            'sign'        => '',
            'biz_content' => '',
        ];
	}

	/**
     * Pay an order.
     *
     * @param string $gateway
     * @param array  $params
     *
     * @return Response
     */
    public function pay($gateway, $params = [])
    {
        $this->payload['biz_content'] = json_encode($params);
        $gateway = get_class($this).'\\'.ucfirst($gateway).'Gateway';
        if (class_exists($gateway)) {
            return $this->makePay($gateway);
        }
        throw new GatewayException("Pay Gateway [{$gateway}] not exists", 1);
    }

    /**
     * Verfiy sign.
     */
    public function verify()
    {
        $data = count($_POST) > 0 ? $_POST : $_GET;
        $data = Support::encoding($data, 'utf-8', $data['charset'] ?: 'gb2312');
        if (Support::verifySign($data, $this->config['ali_public_key'])) {
            return $data;
        }
        throw new InvalidSignException('Alipay Sign Verify FAILED', 3, []);
    }

    /**
     * Query an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     *
     * @return array
     */
    public function find($order)
    {
        $this->payload['method'] = 'alipay.trade.query';
        $this->payload['biz_content'] = json_encode(is_array($order) ? $order : ['out_trade_no' => $order]);
        $this->payload['sign'] = Support::generateSign($this->payload, $this->config['private_key']);
        return Support::requestApi($this->payload, $this->config['ali_public_key']);
    }
    /**
     * Refund an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $order
     *
     * @return array
     */
    public function refund($order)
    {
        $this->payload['method'] = 'alipay.trade.refund';
        $this->payload['biz_content'] = json_encode($order);
        $this->payload['sign'] = Support::generateSign($this->payload, $this->config['private_key']);
        return Support::requestApi($this->payload, $this->config['ali_public_key']);
    }
    /**
     * Cancel an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     *
     * @return array
     */
    public function cancel($order)
    {
        $this->payload['method'] = 'alipay.trade.cancel';
        $this->payload['biz_content'] = json_encode(is_array($order) ? $order : ['out_trade_no' => $order]);
        $this->payload['sign'] = Support::generateSign($this->payload, $this->config['private_key']);
        return Support::requestApi($this->payload, $this->config['ali_public_key']);
    }
    /**
     * Close an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     *
     * @return array
     */
    public function close($order)
    {
        $this->payload['method'] = 'alipay.trade.close';
        $this->payload['biz_content'] = json_encode(is_array($order) ? $order : ['out_trade_no' => $order]);
        $this->payload['sign'] = Support::generateSign($this->payload, $this->config['private_key']);
        return Support::requestApi($this->payload, $this->config['ali_public_key']);
    }
    /**
     * Reply success to alipay.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return array
     */
    public function success()
    {
        return 'success';
    }
    /**
     * Make pay gateway.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $gateway
     *
     * @return array
     */
    protected function makePay($gateway)
    {
        $app = new $gateway($this->config);
        if ($app instanceof PayInterface) {
            return $app->pay($this->gateway, $this->payload);
        }
        throw new GatewayException("Pay Gateway [{$gateway}] Must Be An Instance Of GatewayInterface", 2);
    }
    /**
     * Magic pay.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     * @param array  $params
     *
     * @return array
     */
    public function __call($method, $params)
    {
        return $this->pay($method, ...$params);
    }
}