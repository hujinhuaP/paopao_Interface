<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | View配置信息                                                           |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

return [
	'engine'      => 'Phalcon\Mvc\View\Engine\Volt',
	'compiledDir' => APP_PATH.'cache/view/v1/',
	'dir'         => __DIR__.'/../views',
];