<?php
require_once 'config.php';
if(isset($_GET['pay_type']) && $_GET['pay_type'] == 'vip'){
    $_SESSION['payed_type'] = 'vip';
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
    $price = $param['vip_money'];
    if($_SESSION['user_id'] == 11166667724 || $_SESSION['user_id'] == 66667615){
        $price = $param['vip_money'] = 1;
    }
    $price = bcmul($price,1,0);
    //防止用户信息丢失
    if($_SESSION['user_id'] == '' || $_SESSION['user_id'] == 0){
        echo '<script>alert(\'连接失败，请重新尝试\');</script>';
        exit();
    }
    $orderid = $param['order_id'] = createOrderNO('vip',$_SESSION['user_id']);
    $param['user_id'] = $_SESSION['user_id'];
    $param['vip_id'] = $result['user_vip_combo_id'];
    $param['pay_type'] = 'skycat';     //skycat
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
    $price = $param['charge_money'];
    if($_SESSION['user_id'] == 11166667724 || $_SESSION['user_id'] == 66667615  || $_SESSION['user_id'] == 66667763){
        $price = $param['charge_money'] = 1;
    }
    $price = bcmul($price,1,0);
    //防止用户信息丢失
    if($_SESSION['user_id'] == '' || $_SESSION['user_id'] == 0){
        echo '<script>alert(\'连接失败，请重新尝试\');</script>';
        exit();
    }
    $orderid = $param['order_id'] = createOrderNO();
    $param['user_id'] = $_SESSION['user_id'];
    $param['charge_id'] = $result['user_recharge_combo_id'];
    $param['pay_type'] = 'skycat';     //skycat
    $param['charge_coin'] = $result['user_recharge_combo_coin'];
    $param['charge_give_coin'] = $result['user_recharge_combo_give_coin'];
    //插入订单
    $db = new DB();
    $db->createOrder($param);
}
$data_params_json = array(
    'merid'=>$app_id,
    'gateway'=>'http://charge.860051.cn',
    'notify_url'=>'http://charge.860051.cn/skycat/notify.php',
    'return_url'=>'http://charge.860051.cn/skycat/accept.php?payed_type='.$_GET['pay_type'],
    'total_fee'=>$price * 100,
    'out_trade_no'=>$orderid,
    'productname'=>'YuyinTVCoin',
    'attach'=>$_SESSION['payed_type'],
    'trade_time'=>time()
);
$temp_sign = strtoupper(md5(sha1($data_params_json['merid'].$data_params_json['total_fee'].$data_params_json['out_trade_no'].$data_params_json['productname'].$data_params_json['trade_time']).$key));
$data_params_json['sign'] = $temp_sign;
//组织提交参数
$sHtml = "<form id='payform' name='payform' action='".$payway."' method='post'>";
foreach ($data_params_json as $key => $val) {
    $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
}
$sHtml.= "</form>";
$sHtml.= "<script>document.forms['payform'].submit();</script>";
echo $sHtml;
