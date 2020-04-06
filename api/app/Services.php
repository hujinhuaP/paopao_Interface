<?php

namespace app;

use Phalcon\Crypt;
use Phalcon\Mvc\View;
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Mvc\Model\Metadata\Redis as MetaData;
use Phalcon\Cache\Frontend\Data as FrontendData;
use Phalcon\Cache\Backend\Factory as BackendMemcache;
use Phalcon\Logger\Adapter\File as Logger;
use Phalcon\Http\Response\Cookies;
use Redis;

class Services extends \Phalcon\DI\FactoryDefault
{

    public function __construct($config)
    {
        parent::__construct();
        $this->setShared('config', $config);
        $this->bindServices();
        date_default_timezone_set($config->application->timezone);
        unset($_REQUEST['_url']);
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
     * The URL component is used to generate all kind of urls in the application
     */
    protected function initUrl()
    {
        $url = new UrlProvider();
        $url->setBaseUri($this->get('config')->application->baseUri);
        return $url;
    }

    protected function initView()
    {
        $view = new View();

        $view->setViewsDir(APP_PATH . $this->get('config')->view->dir);

        $view->registerEngines([
            ".volt" => 'volt'
        ]);

        return $view;
    }

    /**
     * Setting up volt
     */
    protected function initSharedVolt($view, $di)
    {
        $class = $this->get('config')->view->engine;

        $viewEngine = new $class($view, $di);

        $viewEngine->setOptions([
            "compiledPath" => $this->get('config')->view->compiledDir,
        ]);

        $compiler = $viewEngine->getCompiler();
        $compiler->addFunction('is_a', 'is_a');

        return $viewEngine;
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
     * If the configuration specify the use of metadata adapter use it or use memory otherwise
     */
    protected function initModelsMetadata()
    {
        $config = $this->get('config')->toArray();

        $aAdapterConfig = $config['cache'][$config['cache']['adapter']];
        $aAdapterConfig['statsKey'] = '_PHCM_MM';
        return new MetaData($aAdapterConfig);
    }

    /**
     * Register the flash service with custom CSS classes
     */
    protected function initFlash()
    {
        return new FlashSession(array(
            'error'   => 'alert alert-danger',
            'success' => 'alert alert-success',
            'notice'  => 'alert alert-info',
            'warning' => 'alert alert-warning'
        ));
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
     * Register the Cookies service
     */
    public function initCookies()
    {
        // 使用这里应该注意，在代码里面不能使用die exit函数，否则cookie不会设置
        $cookies = new Cookies();
        $cookies->useEncryption(true);
        return $cookies;
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
     * Register the modelsCache service
     */
    public function initModelsCache()
    {
        $config = $this->get('config')->toArray();

        // Cache data for one day (default setting)
        $frontCache = new FrontendData(
            [
                'lifetime' => 86400,
            ]
        );

        $options = [
            "frontend" => $frontCache,
            "adapter"  => $config['cache']['adapter'],
        ];

        $aAdapterConfig = $config['cache'][$config['cache']['adapter']];

        $options = array_merge($options, $aAdapterConfig);

        $cache = BackendMemcache::load($options);
        return $cache;
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
