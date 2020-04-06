<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 第三方配置信息                                                         |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

return [
    //QQ移动应用
	'qq' => [
		'appid'        => '1107917735',
		'appkey'       => 'tf72MPIc0DL0adIb'
	],
    //QQ网页应用
	'web_qq' => [
		'appid'        => 'xxxx',
		'appkey'       => 'xxxxx',
        'callback_url' => 'http://api.xxxxxx.com/v1/pc/qqoauth/callback'
	],
    //微信移动应用
	'wx' => [
		'appid'        => 'xxxxxxxx',
		'appkey'       => 'xxxxxxxxxxx'
	],
    //微信网页应用
    'web_wx' => [
        'appid'        => 'xxxxxx',
        'appkey'       => 'xxxxxxxxx',
        'callback_url' => 'http://api.xxxxxx.com/v1/pc/wxoauth/callback'
    ],
    //微博移动用用
	'wb' => [
		'appid'        => 'xxxxxxx',
		'appkey'       => 'xxxxxxxxxx'
	],
    //微博网页应用
    'web_wb' => [
        'appid'        => 'xxxxxxxxxxx',
        'appkey'       => 'xxxxxxxxx',
        'callback_url' => 'http://api.xxxxxx.com/v1/pc/wboauth/callback'
    ],
    //微信公众号
    'wx_public' => [
        'appid'        => 'xxxxxxxx',
        'appkey'       => 'xxxxxxxxxxxx',
        'callback_url' => 'http://api.xxxxxx.com/v1/pc/wxpublic/callback'
    ]
];