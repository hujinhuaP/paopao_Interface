<?php
    ini_set('display_errors',1);            //错误信息
    ini_set('display_startup_errors',1);    //php启动错误信息
    error_reporting(0);                                //打印出所有的 错误信息
    session_start();
    define("WEB_HOST","127.0.0.1");
    define("WEB_USER","root");
    define("WEB_PWD","Mysql123456@");           //Mysql123456@
    define("WEB_DB","yuyin_live");
    define('BODY','泡泡直播充值');
    define('BODY_VIP','泡泡VIP充值');
    define('WEBROOT',dirname(__FILE__));
    /* 泡泡直播公众号T+7 */
    define('WXAPPID','wx54e1dcd14c22ff22');
    define('WXMCHID','1519576351');
    define('WXKEY','pti0tzR8Ilr5smoGHJeSfMMJqMBZr9ep');
    /* 泡泡直播T+7 */
	//define('WXAPPID','wx21222e437c4d050d');
	//define('WXMCHID','1500204832');
	//define('WXKEY','n9q5eku8draVpMbJkazfjKEMCwBE6o7F');
    /* 泡泡科技T+1 强制改T+7，已冻结 */
    //define('WXAPPID','wxdc8664fd2c614cb7');
    //define('WXMCHID','1511147451');
    //define('WXKEY','eM7AW3fQSUQZhl1nwvR35I2PElAqvUnc');
	/* 泡泡网T+1 原云通 已被警告 */
	//define('WXAPPID','wx4960c91e7571476f');
	//define('WXMCHID','1393061002');
	//define('WXKEY','71fa63c9702ca9046a0eb5a02154d76f');
	define('WX_WAY','http://charge.860051.cn');                                  //网关
    define('WX_SEND_URL','https://api.mch.weixin.qq.com/pay/unifiedorder');         //微信传参地址
    define('WX_NOTIFY_URL','http://charge.860051.cn/wechat/wechat_notify.php');  //回调地址
    define('WX_NOTIFY_VIP_URL','http://charge.860051.cn/wechat/wechat_notify_vip.php');  //VIP回调地址
    //  http://charge.860051.cn/test.php 回调地址
    define('SERVICE_NOTIFY_URL','http://api.sxypaopao.com/v1/notify/notify/h5Pay');  //平台服务端回调地址
    if(isset($_GET['user_id']) && $_GET['user_id']!=''){
        $_SESSION['user_id'] = $_GET['user_id'];
    }
    require_once WEBROOT.'/common.php';
    require_once WEBROOT.'/OpensslEncryptHelper.php';