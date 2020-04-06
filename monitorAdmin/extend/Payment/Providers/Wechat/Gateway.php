<?php 

/*
 +------------------------------------------------------------------------+
 | Payment                                                                |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | Gateway                                                                |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace Payment\Providers\Wechat;

use Payment\Contracts\PayInterface;
use Payment\Providers\Wechat\Support;

abstract class Gateway implements PayInterface
{
	/**
     * Config.
     *
     * @var Config
     */
    protected $config;

    /**
     * Bootstrap.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Pay an order.
     *
     * @param string $endpoint
     * @param array  $payload
     *
     * @return array
     */
    abstract public function pay($endpoint, array $payload);

    /**
     * Get trade type config.
     *
     * @return string
     */
    abstract protected function getTradeType();

    /**
     * Preorder an order.
     *
     * @param array $payload
     *
     * @return array
     */
    protected function preOrder($endpoint, $payload)
    {
        $payload['sign'] = Support::generateSign($payload, $this->config['key']);
        return Support::requestApi($endpoint, $payload, $this->config['key']);
    }
}