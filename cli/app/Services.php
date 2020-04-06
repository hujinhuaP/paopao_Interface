<?php

namespace app;

use Redis;
use Phalcon\Crypt;
use Phalcon\Logger\Adapter\File as Logger;
use Phalcon\Di\FactoryDefault\Cli as FactoryDefault;

class Services extends FactoryDefault
{

    public function __construct($config)
    {
        parent::__construct();
        $this->setShared('config', $config);
        $this->bindServices();
        date_default_timezone_set($config->application->timezone);
    }
    
    /**
     * Bind services
     */
    protected function bindServices()
    {
        $reflection = new \ReflectionObject($this);
        $methods = $reflection->getMethods();
        
        foreach ($methods as $method) {

            if ((strlen($method->name) > 10) && (strpos($method->name, 'initShared') === 0)) {
                $this->set(lcfirst(substr($method->name, 10)), $method->getClosure($this));
                continue;
            }
            
            if ((strlen($method->name) > 4) && (strpos($method->name, 'init') === 0)) {
                $this->set(lcfirst(substr($method->name, 4)), $method->getClosure($this));
            }

        }
        
    }

    /**
     * Database connection is created based in the parameters defined in the configuration file
     */
    protected function initDb()
    {
        $config = $this->get('config')->get('database')->toArray();

        $dbClass = 'Phalcon\Db\Adapter\Pdo\\' . $config['adapter'];
        unset($config['adapter']);

        return new $dbClass($config);
    }

    /**
     * Register the Crypt service
     */
    public function initCrypt()
    {
        $crypt = new Crypt();
        // Set a global encryption key
        $crypt->setKey($this->get('config')->application->crypt_key);
        return $crypt;
    }

    /**
     * Register the redis service
     */
    public function initRedis()
    {
        $redis = new Redis();
        $config = $this->get('config');
        
        if ($config->redis->pconnect) {
            $redis->pconnect($config->redis->host, $config->redis->port);
        } else {
            $redis->connect($config->redis->host, $config->redis->port);
        }

        $redis->auth($config->redis->auth);
        $redis->select($config->redis->db);
        return $redis;
    }

    /**
     * Register the log service
     */
    public function initLog()
    {
        $config = $this->get('config')->toArray();
        return new Logger(
            sprintf('%s_%s.log', $config['log']['app'], date('Ymd'))
        );
    }
}
