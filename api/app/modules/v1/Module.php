<?php

namespace app\http; 

use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Config;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Dispatcher;

use app\plugins\SecurityPlugin;
use app\plugins\NotFoundPlugin;

class Module implements ModuleDefinitionInterface
{
    /**
     * Registers an autoloader related to the module
     *
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        /**
         * include functions
         */
        include __DIR__.'/./helper/functions.php';

        /**
         * Auto-loader class
         */
        $loader->registerNamespaces([
            'app' => __DIR__,
        ])->register();
    }

    /**
     * Registers services related to the module
     *
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {

        /**
         * Merge config
         */
        $di['config']->merge($this->initConfig());

        $that = $this;

        $di['dispatcher'] = function () use ($that, $di) {
            return $that->initDispatcher($di);
        };

        /**
         * Database connection is created based in the parameters defined in the configuration file
         */
        $di['liveServer'] = function () use ($that, $di){
            return $that->initLiveServer($di);
        };

        /**
         * Database connection is created based in the parameters defined in the configuration file
         */
        $di['imServer'] = function () use ($that, $di){
            return $that->initImServer($di);
        };

        /**
         * Database connection is created based in the parameters defined in the configuration file
         */
        $di['timServer'] = function () use ($that, $di){
            return $that->initTimServer($di);
        };
        
    }

    /**
     * We register the events manager
     */
    protected function initDispatcher(DiInterface $di)
    {
        $eventsManager = new EventsManager;

        /**
         * Check if the user is allowed to access certain action using the SecurityPlugin
         */
        $eventsManager->attach('dispatch:beforeDispatch', new SecurityPlugin);

        /**
         * Handle exceptions and not-found exceptions using NotFoundPlugin
         */
        $eventsManager->attach('dispatch:beforeException', new NotFoundPlugin);

        /**
         * 根据命名空间加载控制器模块
         *   http://api.xxxxx.com/[版本模块]/[控制器模块]/[控制器]/[方法]
         *   http://api.xxxxx.com/[版本模块]/[控制器]/[方法]
         *   http://api.xxxxx.com/[版本模块]/[控制器]
         *   http://api.xxxxx.com/[版本模块]
         *   http://api.xxxxx.com
         */
        $eventsManager->attach('dispatch:beforeDispatchLoop',
            function ($event, $dispatcher) use ($di) {
                $router = $di->getRouter();
                $namespace = $router->getNamespaceName();
                $dispatcher->setNamespaceName('app\\http\\controllers\\'.$namespace);
            }
        );

        $dispatcher = new Dispatcher;
        $dispatcher->setEventsManager($eventsManager);

        return $dispatcher;
    }

    protected function initConfig()
    {
        /**
         * Read the configuration
         */
        $sConfigPath     = __DIR__.'/configs';
        $sConfigDevPath  = __DIR__.'/configs/dev';
        $aConfig         = [];
        $aConfigFiles    = [];
        $aConfigDevFiles = [];

        if (is_dir($sConfigPath)) {
            $aConfigFiles    = scandir($sConfigPath);
        }

        if (is_dir($sConfigDevPath)) {
            $aConfigDevFiles = scandir($sConfigDevPath);
        }
        
        foreach ($aConfigFiles as &$sFile) {
            $sExt = '.'.pathinfo($sFile, PATHINFO_EXTENSION);
            if ($sExt === '.php') {
                $aConfig[str_replace($sExt, '', $sFile)] = require $sConfigPath.'/'.$sFile;
            }
        }

        foreach ($aConfigDevFiles as &$sFile) {
            $sExt = '.'.pathinfo($sFile, PATHINFO_EXTENSION);
            if ($sExt === '.php') {
                $aConfig[str_replace($sExt, '', $sFile)] = require $sConfigPath.'/'.$sFile;
            }
        }
        
        return new Config($aConfig);
    }

    /**
     * Register the live server service
     */
    protected function initLiveServer($di) {
        $config      = $di->getConfig();
        $oLiveServer = new \app\helper\LiveService();
        $oLiveServer->setDomainName($config->live->domainName);
        $oLiveServer->setPushDomainName($config->live->pushDomainName);
        $oLiveServer->setBizid($config->live->bizid);
        $oLiveServer->setPushAuthKey($config->live->pushAuthKey);
        $oLiveServer->setAppName($config->live->appName);
        return $oLiveServer;
    }

    /**
     * Register the im server service
     */
    protected function initImServer($di) {
        $config     = $di->getConfig();
        $oIMService = new \app\helper\IMService();
        $oIMService->setApiUrl($config->application->im->api_url);
        $oIMService->setWsUrl($config->application->im->ws_url);
        return $oIMService;
    }

    /**
     * Register the im server service
     */
    protected function initTimServer($di) {
        $config = $di->getConfig();
        $oTIM = new \app\helper\TIM();
        if(APP_ENV == 'dev'){
            $oTIM->setIdentifier($config->application->tim_dev->identifier);
            $oTIM->setAppid($config->application->tim_dev->app_id);
            $oTIM->setSignVersion($config->application->tim_dev->sign_version);
            $oTIM->setKey($config->application->tim_dev->key);

            if (file_exists($config->application->tim->private_pem_path)) {
                $oTIM->setPrivateKey(file_get_contents($config->application->tim->private_pem_path));
            }

            if (file_exists($config->application->tim->public_pem_path)) {
                $oTIM->setPublicKey(file_get_contents($config->application->tim->public_pem_path));
            }
        }else{
            $oTIM->setIdentifier($config->application->tim->identifier);
            $oTIM->setAppid($config->application->tim->app_id);
            $oTIM->setSignVersion($config->application->tim->sign_version);

            if (file_exists($config->application->tim->private_pem_path)) {
                $oTIM->setPrivateKey(file_get_contents($config->application->tim->private_pem_path));
            }

            if (file_exists($config->application->tim->public_pem_path)) {
                $oTIM->setPublicKey(file_get_contents($config->application->tim->public_pem_path));
            }
        }
        return $oTIM;
    }

}
