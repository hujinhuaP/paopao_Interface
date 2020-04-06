<?php

use Phalcon\Cli\Console as Application;
use Phalcon\Loader;
use Phalcon\Config;
use app\Services;

try {

     // App 路径
    define('BASE_PATH', dirname(__DIR__));
    define('APP_PATH', BASE_PATH . '/');
//    define('APP_PATH', realpath('..') . '/');

    if(getenv('APP_ENV') == 'dev'){
        define('APP_ENV', 'dev');
        define('APP_URL_PREFIX', 'dev.');
    }else{
        define('APP_ENV', 'product');
        define('APP_URL_PREFIX', '');
    }

    /**
     * Read the configuration
     */
    $sConfigPath     = APP_PATH.'app/configs';
    $aConfigFiles    = scandir($sConfigPath);
    $aConfig         = [];

    foreach ($aConfigFiles as &$sFile) {
        $sExt = '.'.pathinfo($sFile, PATHINFO_EXTENSION);
        if ($sExt === '.php') {
            $aConfig[str_replace($sExt, '', $sFile)] = require $sConfigPath.'/'.$sFile;
        }
    }

    $config = new Config($aConfig);

    // App api url
    define('APP_API_URL', $config->application->app_api_url);
    // App js url
    define('APP_JS_URL', APP_API_URL.'assets/js/');
    // App images url
    define('APP_IMG_URL', APP_API_URL.'assets/images/');
    // App css url
    define('APP_CSS_URL', APP_API_URL.'assets/css/');
    // App fonts url
    define('APP_FONTS_URL', APP_API_URL.'assets/fonts/');
    // App 分享出去的域名
    define('APP_WEB_URL', $config->application->app_web_url);


    /**
     * Auto-loader configuration
     */
    $loader = new Loader();

    /**
     * Auto-loader class
     */
    $loader->registerNamespaces([
        'app' => APP_PATH.'app',
    ])->register();

    /**
     * composer autoload
     */
    require APP_PATH.'/vendor/autoload.php';

    $di = new Services($config);

    $application = new Application($di);

    $arguments = [];

    /**
     * Process the console arguments
     */
    foreach ($argv as $k => $arg) {
        if ($k == 1) {
            $arguments['task'] = $arg;
        } elseif ($k == 2) {
            $arguments['action'] = $arg;
        } elseif ($k >= 3) {
            $arguments['params'][] = $arg;
        }
    }

    $di->getShared('dispatcher')->setNamespaceName('app\\tasks');

    /**
     * Handle
     */
    $application->handle($arguments);

} catch (\Phalcon\Exception $e) {
    fwrite(STDERR, $e . PHP_EOL);
    exit(1);
} catch (\Throwable $throwable) {
    fwrite(STDERR, $throwable . PHP_EOL);
    exit(1);
} catch (\Exception $exception) {
    fwrite(STDERR, $e . PHP_EOL);
    exit(1);
}