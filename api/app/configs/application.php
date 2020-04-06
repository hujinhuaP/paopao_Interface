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
    // 系统环境
    'evn'              => APP_ENV,
    // APP名字
    'app_name'         => 'JK',
    // 时区
    'timezone'         => 'PRC',
    // h5 web域名
    'app_web_url'      => sprintf('http://%sh5.sxypaopao.com', APP_URL_PREFIX),
    // 下载域名
    'app_download_url' => 'http://h5.mastercode.top/register',
    // api域名
    'api_url'          => sprintf('http://%sapi.sxypaopao.com', APP_URL_PREFIX),
    //pc端域名
    'pc_web_url'       => 'http://pc.sxypaopao.com',
    //pc端登录地址
    'pc_login_url'     => 'http://dev.pc.sxypaopao.com',

    // h5 充值域名
    'h5_charge_url'    => sprintf('http://%scharge.sxypaopao.com', APP_URL_PREFIX),

    // 活动页面
    'activity_url'     => sprintf('http://%sactivity.sxypaopao.com', APP_URL_PREFIX),
    // IM服务
    'im'               => [
        'ws_url'    => '',
        // IM服务API
        'api_url'   => '',
        // 全局IM房间号
        'global_id' => '',
    ],
    // 游戏服务器 API URL
    'game_api_url'     => '',
    // Crypt key
    'crypt_key'        => '%31.1e$i86e$f!8jz',
    // TIM服务(腾讯云通信服务)
    // 新账号
    'tim'              => [
        // 加密类型
        'sign_version'             => 'v1',
        // 应用的appid
        'app_id'           => '1400215699',
        // 应用的类型
        'account_type'     => '34300',
        // 私钥
        'private_pem_path' => APP_PATH . 'app/configs/tim_keys/private_key',
        // 公钥
        'public_pem_path'  => APP_PATH . 'app/configs/tim_keys/public_key',
        // 账号管理员
        'identifier'       => 'chuangshi_admin',
    ],
    'tim_dev'          => [
        'sign_version'         => 'v2',
        // 应用的appid
        'app_id'       => '1400293486',
        // key
        'key'          => '217d59a39f314ad224704880dfac3549ea2635e5e68ec9f5292d710d07d4af00',
        // 账号管理员
        'identifier'   => 'administrator',
        // 私钥  为了兼容
        'private_pem_path' => APP_PATH . 'app/configs/tim_keys/private_key',
        // 公钥  为了兼容
        'public_pem_path'  => APP_PATH . 'app/configs/tim_keys/public_key',
    ],
    //    老账号
    //    'tim'           => [
    //        // 应用的appid
    //        'app_id'           => '1400118296',
    //        // 应用的类型32753
    //        'account_type'     => '32753',
    //        // 私钥
    //        'private_pem_path' => APP_PATH . 'app/configs/tim_keys/private_key',
    //        // 公钥
    //        'public_pem_path'  => APP_PATH . 'app/configs/tim_keys/public_key',
    //        // 账号管理员
    //        'identifier'       => 'yuyinadmin',
    //    ],
    //  新增测试账号   2652565926@qq.com
    //                  ly19930316ly..
    //    'tim'           => [
    //        // 应用的appid
    //        'app_id'           => '1400156080',
    //        // 应用的类型32753
    //        'account_type'     => '36862',
    //        // 私钥
    //        'private_pem_path' => APP_PATH . 'app/configs/tim_keys/private_key',
    //        // 公钥
    //        'public_pem_path'  => APP_PATH . 'app/configs/tim_keys/public_key',
    //        // 账号管理员
    //        'identifier'       => 'yuyinadmin',
    //    ],
    'pay'              => [
        'alipay' => [
            'return_url' => 'http://sxypaopao.com/recharge',
            'notify_url' => '/v1/notify/notify/alipayNotify'
        ],
        'wechat' => [
            'notify_url' => '/v1/notify/notify/wxNotify',
            'ip'         => '47.94.172.201'
            //扫码支付要用服务器ip
        ]
    ],
    'apple_sandbox'    => TRUE,

    // php请求秘钥
    'cli_api_key'      => 'hzjkb24kWDI8ukoexOiP87j',
    // APP 签名秘钥
    'api_key'          => 'CUjzAR7BhuRDwK6b',

    'zego'     => [
        // 是否是生产环境
        'product'          => 'Y',
        'app_id'           => '968148240',
        'app_sign_android' => '92,30,228,235,147,168,94,69,68,194,35,253,247,187,58,16,29,132,124,163,250,17,138,39,120,219,176,252,100,118,55,183',
        'app_sign'         => '0x5c,0x1e,0xe4,0xeb,0x93,0xa8,0x5e,0x45,0x44,0xc2,0x23,0xfd,0xf7,0xbb,0x3a,0x10,0x1d,0x84,0x7c,0xa3,0xfa,0x11,0x8a,0x27,0x78,0xdb,0xb0,0xfc,0x64,0x76,0x37,0xb7',
        'callback_secret'  => '5c1ee4eb93a85e4544c223fdf7bb3a10',
        'server_secret'    => '7c71ec3c173ff8caed896dc8f011fa72',
    ],
    'zego_dev' => [
        // 是否是生产环境
        'product'          => 'N',
        'app_id'           => '968148240',
        'app_sign_android' => '92,30,228,235,147,168,94,69,68,194,35,253,247,187,58,16,29,132,124,163,250,17,138,39,120,219,176,252,100,118,55,183',
        'app_sign'         => '0x5c,0x1e,0xe4,0xeb,0x93,0xa8,0x5e,0x45,0x44,0xc2,0x23,0xfd,0xf7,0xbb,0x3a,0x10,0x1d,0x84,0x7c,0xa3,0xfa,0x11,0x8a,0x27,0x78,0xdb,0xb0,0xfc,0x64,0x76,0x37,0xb7',
        'callback_secret'  => '5c1ee4eb93a85e4544c223fdf7bb3a10',
        'server_secret'    => '7c71ec3c173ff8caed896dc8f011fa72',
    ]
];