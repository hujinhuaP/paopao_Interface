<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 应用配置信息                                                           |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

return [
	// APP名字
	'app_name'   => '泡泡',
	// 时区
	'timezone'   => 'PRC',
	// h5 web域名
	'app_api_url' => sprintf('http://%sapi.sxypaopao.com/v1/',APP_URL_PREFIX),
	'app_web_url' => sprintf('http://%sh5.sxypaopao.com/v1/',APP_URL_PREFIX),
	// IM服务
	'im' => [
		'ws_url'      => '',
		// IM服务API
		'api_url'     => '',
		// 全局IM房间号
		'global_id'   => '',
	],
	// 游戏服务器 API URL
	'game_api_url' => '',
	// Crypt key
	'crypt_key' => '%31.1e$i86e$f!8jz',
	// TIM服务(腾讯云通信服务)
	'tim' => [
		// 应用的appid
		'app_id'       => '1400056182',
		// 应用的类型
		'account_type'     => '20760',
		// 私钥
		'private_pem_path' => APP_PATH.'app/configs/tim_keys/private_key',
		// 公钥
		'public_pem_path'  => APP_PATH.'app/configs/tim_keys/public_key',
		// 账号管理员
		'identifier' => 'leboadmin',
	],
    'cli_api_key' =>  'hzjkb24kWDI8ukoexOiP87j'
];