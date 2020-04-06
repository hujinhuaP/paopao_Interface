# PHP支付工具

目前完成支付宝支付和微信支付，以后有其他支付可以加进来，具体封装思路和现在的支付宝支付和微信支付一样。

## 支付宝
* Pay::alipay($config)->verify(); //验证
* Pay::alipay($config)->refund()；//退款
* Pay::alipay($config)->find()； //查询
* Pay::alipay($config)->cancel(); //取消
* Pay::alipay($config)->close(); //关闭
* Pay::alipay($config)->success(); //成功
* Pay::alipay($config)->app(); //app支付
* Pay::alipay($config)->web(); //web支付
* Pay::alipay($config)->pos(); //pos支付
* Pay::alipay($config)->transfer(); //转账
* Pay::alipay($config)->scan(); // 扫码支付
* Pay::alipay($config)->wap(); //wap支付


## 微信
* Pay::wechat($config)->verify(); //验证
* Pay::wechat($config)->refund()；//退款
* Pay::wechat($config)->find()； //查询
* Pay::wechat($config)->cancel(); //取消
* Pay::wechat($config)->close(); //关闭
* Pay::wechat($config)->success(); //成功
* Pay::wechat($config)->app(); //app支付
* Pay::wechat($config)->pos(); //pos支付
* Pay::wechat($config)->transfer(); //转账
* Pay::wechat($config)->scan(); // 扫码支付
* Pay::wechat($config)->wap(); //wap支付
* Pay::wechat($config)->mp(); //公众号支付
* Pay::wechat($config)->miniapp(); //小程序支付


```php
<php?
date_default_timezone_set('PRC');
error_reporting(E_ALL ^ E_NOTICE);

include 'autoload.php';

use payment\Pay;


// 支付宝
	
try	{

	$alipayConfig = [
		'mode'           => 'dev', // normal：正常模式，dev：沙箱模式
		'app_id'         => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', // app id
		'return_url'     => 'http://xxxxxxxxxxxxxxxxxxxxxxx', // 跳转页面
		'notify_url'     => 'http://xxxxxxxxxxxxxxxxxxxxxxx', // 通知回调
		'ali_public_key' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', // 公钥
		'private_key'    => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', // 私钥
	];

	$alipay = Pay::alipay($alipayConfig);
		//验证
	$alipay->verify(); 

	//退款
	$alipay->refund([
		'out_trade_no' => '123456',
		'refund_amount' => '0.01',
	]);

	//查询
	$alipay->find('123456');
	$alipay->find(['out_trade_no'=>'123456']);

	//取消
	$alipay->cancel('123456'); 
	$alipay->cancel(['out_trade_no'=>'123456']);

	//关闭
	$alipay->close('123456'); 
	$alipay->close(['out_trade_no'=>'123456']); 

	//成功
	$alipay->success(); 


	$order = [
		'out_trade_no' => time(),
	    'total_amount' => '1',
	    'subject'      => 'test subject - 测试',
	];

	$alipay->app($order);      //app支付
	$alipay->web($order);      //web支付
	$alipay->pos($order);      //pos支付
	$alipay->scan($order);     //扫码支付
	$alipay->wap($order);      //wap支付

	//转账
	$alipay->transfer([
		"out_biz_no"      => '3142321423432', // 商户转账唯一订单号
		"payee_type"      => 'ALIPAY_LOGONID', // 收款方账户类型
		"payee_account"   => 'spdhnf3246@sandbox.com', // 收款方账户
		"amount"          => '1', // 转账金额
		"payer_show_name" => '播呀', //付款方姓名
		"payee_real_name" => '沙箱环境', // 收款方真实姓名
		"remark"          => '转账测试', // 转账备注
	]); 

} catch (\Payment\Exceptions\Exception $e) {
	print_r($e);
} catch (Exception $e) {
	print_r($e);
}



// 微信

try {
	$wechatConfig = [
		'mode'        => 'dev', // normal：正常模式，dev：沙箱模式，hk：香港网关
		'app_id'      => 'xxxxxxxxxxxxxxxxxx', // APP APPID
		'mpapp_id'    => 'xxxxxxxxxxxxxxxxxx', // 公众号 APPID
		'miniapp_id'  => 'xxxxxxxxxxxxxxxxxx', // 小程序 APPID
		'mch_id'      => 'xxxxxxxxxxxxxxxxxx', // mch_id
		'key'         => 'xxxxxxxxxxxxxxxxxx', // key
		'notify_url'  => 'http://xxxxxxxxxxxxxxxxxx', // 通知回调
		'cert_client' => './config/apiclient_cert.pem', // optional, 退款，红包等情况时需要用到
		'cert_key'    => './config/apiclient_key.pem',  // optional, 退款，红包等情况时需要用到
		'rootca'      => './config/rootca.pem',         // optional, 退款，红包等情况时需要用到
	];

	$wechat = Pay::wechat($wechatConfig);

	//验证
	$wechat->verify(); 

	//退款
	$wechat->refund([
		'out_trade_no' => '123456',
	    'out_refund_no' => time(),
	    'total_fee' => '1',
	    'refund_fee' => '1',
	    'refund_desc' => '测试退款haha',
	]);

	//查询
	$wechat->find('123456');
	$wechat->find(['out_trade_no'=>'123456']);

	//关闭
	$wechat->close('123456'); 
	$wechat->close(['out_trade_no'=>'123456']); 

	//成功
	$wechat->success(); 


	$order = [
		'out_trade_no' => time(),
		'body'         => 'subject-测试',
		'total_fee'    => 0.01*100,
	];

	$wechat->app($order);      //app支付
	$wechat->pos($order);      //pos支付
	$wechat->scan($order);     //扫码支付
	$wechat->wap($order);      //wap支付
	$wechat->mp($order);       //公众号支付
	$wechat->miniapp($order);  //小程序支付

	//转账
	$wechat->transfer([
		'openid'       => '121231112312', // 用户openid
		'check_name'   => 'FORCE_CHECK', // 校验用户姓名选项 NO_CHECK：不校验真实姓名 FORCE_CHECK：强校验真实姓名
		're_user_name' => '张三', //收款用户姓名
		'amount'       => 2.01*100, // 金额
		'desc'         => '理赔', // 企业付款描述信息
	]);

} catch (\Payment\Exceptions\Exception $e) {
	print_r($e);
} catch (Exception $e) {
	print_r($e);
}


```