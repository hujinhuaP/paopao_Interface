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

namespace Payment\Providers\Alipay;

use Payment\Contracts\PayInterface;
use Payment\Providers\Alipay\Support;

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
     * Get method config.
     *
     * @return string
     */
    abstract protected function getMethod();

    /**
     * Get product code config.
     *
     * @return string
     */
    abstract protected function getProductCode();
}