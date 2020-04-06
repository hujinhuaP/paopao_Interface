<?php 

use Phalcon\Mvc\Router;

// Specify routes for modules
$di->set(
    'router',
    function () use ($application) {
        $router = new Router();

        $router->setDefaultModule('v1');

        foreach ($application->getModules() as $key => $module) {

            // http://api.xxxxx.com/[版本模块]/[控制器模块]/[控制器]/[方法]
            $router->add('/'.$key.'/:namespace/:controller/:action', [
                'module'     => $key,
                'namespace'  => 1,
                'controller' => 2,
                'action'     => 3,
            ]);

            // http://api.xxxxx.com/[版本模块]/[控制器]/[方法]
            $router->add('/'.$key.'/:controller/:action', [
                'module'     => $key,
                'controller' => 1,
                'action'     => 2,
            ]);

            // http://api.xxxxx.com/[版本模块]/[控制器]
            $router->add('/'.$key.'/:controller', [
                'module'     => $key,
                'controller' => 1,
                'action'     => 'index',
            ]);

            // http://api.xxxxx.com/[版本模块]
            $router->add('/'.$key, [
                'module'     => $key,
                'controller' => 'empty',
                'action'     => 'index',
            ]);

            // http://api.xxxxx.com
            $router->add('/', [
                'controller' => 'empty',
                'action'     => 'index',
            ]);
        }
        $router->handle();

        return $router;
    }
);

/**
 * Register the installed modules
 */
$application->registerModules([
    // 版本控制只针对已经上线的项目，开发项目不用版本控制
    'v1' => [
        'className' => 'app\\http\\Module',
        'path'      => APP_PATH . '/app/modules/v1/Module.php',
    ],
]);