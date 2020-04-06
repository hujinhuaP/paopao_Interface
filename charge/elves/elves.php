<?php
    require_once 'config.php';
    $payway = 'http://www.payelves.com/api/v1/pay/wap';
    $datetime = date("Y-m-d H:i:s");
    $version = '3.3.0';
    $channel = 'yuyin';
    $paytype = 2;
    $subject = $body = '泡泡充值助手';
    $userId = 'yuyin';
    $backPara = 'yuyin';
    $uuid = 1;
    if(isset($_GET['pay_type']) && $_GET['pay_type'] == 'vip'){
		$_SESSION['payed_type'] = 'vip';
		$backPara = 'vip';
        $result = getVIPMoney();

        if($result){
            $param['vip_money'] = (float)$result['user_vip_combo_fee'];
            if($param['vip_money'] == 0){
                echo 'db error';
                exit;
            }else{

            }
        }else{
            echo 'db error';
            exit;
        }
		$price = $param['vip_money'] = $result['user_vip_combo_fee'];
        if($_SESSION['user_id'] == 66667724 || $_SESSION['user_id'] == 66667615){
			$price = $param['vip_money'] = 0.01;
        }
		$price = bcmul($price,100,0);
        //防止用户信息丢失
        if($_SESSION['user_id'] == '' || $_SESSION['user_id'] == 0){
            echo '<script>alert(\'连接失败，请重新尝试\');</script>';
            exit();
        }
		$orderid = $param['order_id'] = createOrderNO('vip',$_SESSION['user_id']);
        $param['user_id'] = $_SESSION['user_id'];
        $param['vip_id'] = $result['user_vip_combo_id'];
        $param['pay_type'] = 'elves';     //elves精灵第四方
        $param['vip_time'] = $result['user_vip_combo_month'];
        //插入订单
        $db = new DB();
        $db->createOrder($param,'vip');
    }else{
		$_SESSION['payed_type'] = 'charge';
        $result = getChargeMoney();
        if($result){
            $param['charge_money'] = (float)$result['user_recharge_combo_fee'];
            if($param['charge_money'] == 0){
                echo 'db error';
                exit;
            }else{

            }
        }else{
            echo 'db error';
            exit;
        }
		$price = $param['charge_money'] = $result['user_recharge_combo_fee'];
        if($_SESSION['user_id'] == 66667724 || $_SESSION['user_id'] == 66667615  || $_SESSION['user_id'] == 66667763){
			$price = $param['charge_money'] = 0.01;
        }
        $price = bcmul($price,100,0);
        //防止用户信息丢失
        if($_SESSION['user_id'] == '' || $_SESSION['user_id'] == 0){
            echo '<script>alert(\'连接失败，请重新尝试\');</script>';
            exit();
        }
		$orderid = $param['order_id'] = createOrderNO();
        $param['user_id'] = $_SESSION['user_id'];
        $param['charge_id'] = $result['user_recharge_combo_id'];
        $param['pay_type'] = 'elves';     //elves精灵第四方
        $param['charge_coin'] = $result['user_recharge_combo_coin'];
        $param['charge_give_coin'] = $result['user_recharge_combo_give_coin'];
        //插入订单
        $db = new DB();
        $db->createOrder($param);
    }
    $sign = md5('appKey='.$appKey.'&backPara='.$backPara.'&body='.$body.'&channel='.$channel.'&clientVersion='.$version.'&dateTime='.$datetime.'&openId='.$openid.'&orderId='.$orderid.'&payType='.$paytype.'&price='.$price.'&subject='.$subject.'&userId='.$userId.'&uuid='.$uuid.$token);
?>
<form id="form" action="<?php echo $payway;?>" method="post">
    <input type="hidden" name="openId" value="<?php echo $openid;?>" />
    <input type="hidden" name="appKey" value="<?php echo $appKey;?>" />
    <input type="hidden" name="sign" id='sign' value="<?php echo $sign;?>" />
    <input type="hidden" name="dateTime" value="<?php echo $datetime;?>" />
    <input type="hidden" name="clientVersion" value="<?php echo $version;?>" />
    <input type="hidden" name="channel" value="<?php echo $channel;?>" />
    <input type="hidden" name="userId" value="<?php echo $userId;?>" />
    <input type="hidden" name="price" value="<?php echo $price;?>" />
    <input type="hidden" name="payType" id='payType' value="<?php echo $paytype;?>" />
    <input type="hidden" name="subject" value="<?php echo $subject;?>" />
    <input type="hidden" name="body" value="<?php echo $body;?>" />
    <input type="hidden" name="orderId" value="<?php echo $orderid;?>" />
    <input type="hidden" name="backPara" value="<?php echo $backPara;?>" />
    <input type="hidden" name="uuid" value="<?php echo $uuid;?>" />
</form>
<script>
    document.getElementById('form').submit();
</script>
