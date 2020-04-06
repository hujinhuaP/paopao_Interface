<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 缓存配置信息                                                           |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

return [
	
	// 默认使用redis
	'adapter' => 'redis',
	
	// 内存
	'memory' => [],

	// redis
	'redis' => [
		'prefix'     => 'caches_',
		'host'       => '127.0.0.1',
		'port'       => 6379,
		'auth'       => 'redis123456',
		'index'      => 10,
		'persistent' => FALSE,
	],

	// mongo
	'mongo' => [
		'prefix'     => "caches_",
		"server"     => "mongodb://127.0.0.1",
		"db"         => "0",
		'collection' => 'caches',
	],

	// memcache
	'memcache' => [
		'prefix'     => 'caches_',
		"host"       => "127.0.0.1",
		"port"       => 11211,
		"persistent" => false,
	],

	// 文件
	'file' => [
		'prefix'   => 'caches_',
		'cacheDir' => APP_PATH.'cache/data/',
	],

	// apc
	'apc' => [
		'prefix' => 'caches_',
	],

	// apcu
	'apcu' => [
		'prefix' => 'caches_',
	],

	// xcache
	'xcache' => [
		'prefix' => 'caches_',
	],
];