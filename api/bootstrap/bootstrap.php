<?php 
ini_set("display_errors", "On");
error_reporting(E_ALL ^ E_NOTICE);
$origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
if ($origin) {
    header("Content-Type: text/html; charset=utf-8");
    header('Access-Control-Allow-Origin:'.$origin);
    header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
} else {
    header("Content-Type: text/html; charset=utf-8");
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
}


use app\Services;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Logger\Adapter\File as Logger;
use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Response\Exception as HttpResponseException;

try {
    // App 路径
    define('APP_PATH', realpath('..') . '/');
    if(getenv('APP_ENV') == 'dev'){
        define('APP_ENV', 'dev');
        define('APP_URL_PREFIX', 'dev.');
    }else{
        define('APP_ENV', 'product');
        define('APP_URL_PREFIX', '');
    }

    /**
     * require config
     */
    $config = require 'config.php';

    // App api url
    define('APP_API_URL', sprintf('http://%s/', $_SERVER['HTTP_HOST']));
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
    // App 分享出去的下载域名
    define('APP_DOWNLOAD_URL', $config->application->app_download_url);


    /**
     * require config
     */
    $loader = require 'autoload.php';

    $di = new Services($config);

    $application = new Application($di);

    /**
     * require version
     */
    require 'version.php';
    // NGINX - PHP-FPM already set PATH_INFO variable to handle route
    echo $application->handle(!empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : null)->getContent();

} catch (HttpResponseException $e) {
    
} catch (Exception $e){
    $request = new Request();
    $response = new Response();

    $format = $request->get('format', 'string', 'json');

    switch ( strtolower($format) ) {
        case 'josnp':
            $callback = $this->getParams('callback', 'string', 'JOSNP');
            $result = [
                'code'        => $e->getCode(),
                'error'       => get_class($e),
                "msg"         => $e->getMessage(),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
                'detail'      => $e->getTraceAsString(),
                'request_uri' => $_SERVER['REQUEST_URI'],
            ];

            $data = json_encode([
                "c"       => 502,
                "m"       => 'Server error',
                "d"       => $result,
                "comsume" => 0,
            ], JSON_UNESCAPED_UNICODE);

            $data = sprintf('%s(%s)', $callback, $data);

            $response->setHeader('Content-Type', 'text/html');
            break;
        case 'json':
            $result = [
                'code'        => $e->getCode(),
                'error'       => get_class($e),
                "msg"         => $e->getMessage(),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
                'detail'      => $e->getTraceAsString(),
                'request_uri' => $_SERVER['REQUEST_URI'],
            ];

            $data = json_encode([
                "c"       => 502,
                "m"       => 'Server error',
                "d"       => $result,
                "comsume" => 0,
            ], JSON_UNESCAPED_UNICODE);

            $response->setHeader('Content-Type', 'text/html');
            break;

        case '':
            $response->setHeader('Content-Type', 'text/html');
            $data = sprintf('%s<br/><pre>%s</pre>', $e->getMessage(), $e->getTraceAsString());
            break;
    }
    $data .= json_encode($_REQUEST);
    $log = new Logger(
        sprintf('%s_%s.log', $config->log->error, date('Ymd'))
    );
    $log->info($data . "\n\n");
    $response->setContent($data);
    $response->send();
}