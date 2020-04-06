<?php

use Phalcon\Loader;

/**
 * Auto-loader configuration
 */
$loader = new Loader();

/**
 * Auto-loader class
 */
$loader->registerNamespaces([
	'app'       => APP_PATH.'app',
])->register();

/**
 * composer autoload
 */
require APP_PATH.'/vendor/autoload.php';

return $loader;