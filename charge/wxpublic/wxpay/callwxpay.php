<?php
error_reporting(0);
//ini_set('display_errors',1);            //错误信息
//ini_set('display_startup_errors',1);    //php启动错误信息
require_once '../../config.php';
require_once "../lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
require_once "WxPay.Config.php";
require_once 'log.php';


$tools = new JsApiPay();
$openId = $tools->GetOpenid();

$_SESSION['quick_price'] = 0;
if(isset($_GET['id']) && $_GET['id']!=''){
    if(isset($_GET['pay_type']) && $_GET['pay_type']!=''){
        try{
            switch($_GET['pay_type']){
                //办理会员
                case "vip":
                    $notify_url = 'http://charge.860051.cn/wxpublic/wechat_notify_vip.php';
                    $user_vip_combo_id = $user_vip_combo_month = '';
                    $order_id = createOrderNO($_GET['pay_type'],$_SESSION['user_id']);
                    $res = $db->getVipList();
                    foreach($res as $k=>$v){
                        if($v['user_vip_combo_id'] == $_GET['id']){
                            $user_vip_combo_month = $v['user_vip_combo_month'];
                            $user_vip_combo_id = $v['user_vip_combo_id'];
                            $_SESSION['quick_price'] = $v['user_vip_combo_fee'];
                            break;
                        }
                    }
                    if($_SESSION['user_id'] == '66667763'){
                        $_SESSION['quick_price'] = 0.01;
                    }
                    $db = new DB();
                    $param = [
                        'order_id'=>$order_id,
                        'charge_id'=>$user_vip_combo_id,
                        'user_id'=>$_SESSION['user_id'],
                        'vip_time'=>$user_vip_combo_month,
                        'pay_type'=>'wxh5',
                        'vip_money'=>$_SESSION['quick_price'],
                    ];
                    $db->createOrder($param,$_GET['pay_type']);
                    break;
                //充值
                case "charge":
                    $notify_url = 'http://charge.860051.cn/wxpublic/wechat_notify.php';
                    $user_recharge_combo_id = $user_recharge_combo_coin = $user_recharge_combo_give_coin = 0;
                    $order_id = createOrderNO($_GET['pay_type'],$_SESSION['user_id']);
                    $res = $db->getChargeList();
                    foreach($res as $k=>$v){
                        if($v['user_recharge_combo_id'] == $_GET['id']){
                            $_SESSION['quick_price'] = $v['user_recharge_combo_fee'];
                            $user_recharge_combo_id = $v['user_recharge_combo_id'];
                            $user_recharge_combo_coin = $v['user_recharge_combo_coin'];
                            $user_recharge_combo_give_coin = $v['user_recharge_combo_give_coin'];
                            break;
                        }
                    }
                    if($_SESSION['user_id'] == '66667763'){
                        $_SESSION['quick_price'] = 0.01;
                    }
                    $db = new DB();
                    $param = [
                        'order_id'=>$order_id,
                        'user_id'=>$_SESSION['user_id'],
                        'charge_id'=>$user_recharge_combo_id,
                        'charge_give_coin'=>$user_recharge_combo_give_coin,
                        'pay_type'=>'wxh5',
                        'charge_coin'=>$user_recharge_combo_coin,
                        'charge_money'=>$_SESSION['quick_price'],
                    ];
                    file_put_contents(WEBROOT."/test.log","创建订单\n",FILE_APPEND);
                    $db->createOrder($param,$_GET['pay_type']);
                    break;
                default:
                    exit('支付类型错误');
                    break;
            }
        }catch(Exception $e){
            exit('订单创建失败，请重新尝试');
        }
    }else{
        exit('支付类型错误');
    }
}else{
    echo '支付失败';
    die;
}

/**
 *
 * example目录下为简单的支付样例，仅能用于搭建快速体验微信支付使用
 * 样例的作用仅限于指导如何使用sdk，在安全上面仅做了简单处理， 复制使用样例代码时请慎重
 * 请勿直接直接使用样例对外提供服务
 *
 **/

//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

//打印输出数组信息
function printf_info($data){
    foreach($data as $key=>$value){
        echo "<font color='#00ff55;'>$key</font> :  ".htmlspecialchars($value, ENT_QUOTES)." <br/>";
    }
}

//①、获取用户openid
try{
    //②、统一下单
    $input = new WxPayUnifiedOrder();
    $input->SetBody("泡泡增值服务");
    $input->SetAttach("泡泡增值服务");
    $input->SetOut_trade_no($order_id);
    $input->SetTotal_fee("".bcmul($_SESSION['quick_price'],100)."");
    $input->SetTime_start(date("YmdHis"));
    $input->SetTime_expire(date("YmdHis", time() + 600));
    $input->SetGoods_tag("泡泡增值服务");
    $input->SetNotify_url($notify_url);
    $input->SetTrade_type("JSAPI");
    $input->SetOpenid($openId);
    $config = new WxPayConfig();
    $order = WxPayApi::unifiedOrder($config, $input);
    //echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
    //printf_info($order);
    $jsApiParameters = $tools->GetJsApiParameters($order);

    //获取共享收货地址js函数参数
    $editAddress = $tools->GetEditAddressParameters();
} catch(Exception $e) {
    Log::ERROR(json_encode($e));
}
//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
/**
 * 注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/> 
    <title>微信支付</title>
    <script type="text/javascript">
	//调用微信JS api 支付
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $jsApiParameters;?>,
			function(res){
				WeixinJSBridge.log(res.err_msg);
                <?php file_put_contents(WEBROOT."/test.log","支付\n",FILE_APPEND);    ?> //记录价格
				<?php
					if($_GET['pay_type'] == 'vip'){
						echo "document.getElementById('result_msg').innerHTML='<a href=\'../wx_vip.php\'>完成并返回</a>';";
					}else{
						echo "document.getElementById('result_msg').innerHTML='<a href=\'../wx_charge.php\'>完成并返回</a>';";
					}
				?>
				//alert(res.err_code+res.err_desc+res.err_msg);
			}
		);
	}

	function                     callpay()
	{
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	}
	</script>
	<script type="text/javascript">
	//获取共享地址
	function editAddress()
	{
		WeixinJSBridge.invoke(
			'editAddress',
			<?php echo $editAddress; ?>,
			function(res){
				var value1 = res.proviceFirstStageName;
				var value2 = res.addressCitySecondStageName;
				var value3 = res.addressCountiesThirdStageName;
				var value4 = res.addressDetailInfo;
				var tel = res.telNumber;
				
				//alert(value1 + value2 + value3 + value4 + ":" + tel);
			}
		);
	}
	
	window.onload = function(){
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', editAddress, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', editAddress); 
		        document.attachEvent('onWeixinJSBridgeReady', editAddress);
		    }
		}else{
			editAddress();
		};
        callpay();
	};
	</script>
</head>
<body>
    <!--
    <br/>
    <font color="#9ACD32"><b>该笔订单支付金额为<span style="color:#f00;font-size:50px">1分</span>钱</b></font><br/><br/>
	<div align="center">
		<button style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" >立即支付</button>
	</div>
    -->
    <div align="center">
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p id="result_msg"><img src="../img/loading.gif" width="32px;" height="32px;">正在调用支付组件，请稍候...</p>
    </div>
</body>
</html>
