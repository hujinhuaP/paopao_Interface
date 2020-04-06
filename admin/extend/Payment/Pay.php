<?php 

/*
 +------------------------------------------------------------------------+
 | Payment                                                                |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | Pay                                                                    |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace Payment;

use Payment\Exceptions\ProvidersException;
use Payment\Contracts\PayProviderInterface;

class Pay 
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
     * Create a instance.
     *
     * @param string $method
     *
     * @return PayProviderInterface
     */
    protected function create($method)
    {
        $provider = __NAMESPACE__.'\\Providers\\'.ucfirst($method);
        if (class_exists($provider)) {
            return self::make($provider);
        }
        throw new ProvidersException("Pay provider [{$provider}] Not Exists", 1);
    }

    /**
     * Make a provider.
     *
     * @param string $provider
     *
     * @return PayProviderInterface
     */
    protected function make($provider)
    {
        $app = new $provider($this->config);
        if ($app instanceof PayProviderInterface) {
            return $app;
        }
        throw new ProvidersException("Pay provider [$provider] Must Be An Instance Of PayProviderInterface", 2);
    }

    /**
     * Magic static call.
     * 
     * @param string $method
     * @param array  $params
     *
     * @return PayProviderInterface
     */
    public static function __callStatic($method, $params)
    {
        $app = new self(...$params);
        return $app->create($method);
    }
}