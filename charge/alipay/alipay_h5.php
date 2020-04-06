<?php
    require_once '../config.php';
    require_once dirname ( __FILE__ ).'/wappay/service/AlipayTradeService.php';
    require_once dirname ( __FILE__ ).'/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
    require 'config.php';
    if(isset($_GET['pay_type']) && $_GET['pay_type'] == 'vip'){
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
        $param['vip_money'] = $result['user_vip_combo_fee'];
//        if($_SESSION['user_id'] == 66667724 || $_SESSION['user_id'] == 66667615){
//            $param['vip_money'] = 0.01;
//        }
		//防止用户信息丢失
		if($_SESSION['user_id'] == '' || $_SESSION['user_id'] == 0){
			echo '<script>alert(\'连接失败，请重新尝试\');</script>';
			exit();
		}
        $param['order_id'] = createOrderNO('vip',$_SESSION['user_id']);
        $param['user_id'] = $_SESSION['user_id'];
        $param['vip_id'] = $result['user_vip_combo_id'];
        $param['pay_type'] = 'alipayh5';     //alipayh5
        $param['vip_time'] = $result['user_vip_combo_month'];
        //插入订单
        $db = new DB();
        $db->createOrder($param,'vip');
        //超时时间
        $timeout_express="1m";
        $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody(BODY_VIP);
        $payRequestBuilder->setSubject(BODY_VIP);
        $payRequestBuilder->setOutTradeNo($param['order_id']);
        $payRequestBuilder->setTotalAmount($param['vip_money']);
        $payRequestBuilder->setTimeExpress($timeout_express);
        $payResponse = new AlipayTradeService($config);
        $payResponse->wapPay($payRequestBuilder,$config['return_vip_url']."?mod=return&uid=".$_SESSION['user_id'],$config['notify_vip_url']);
        exit;
    }else{
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
        $param['charge_money'] = $result['user_recharge_combo_fee'];
//		if($_SESSION['user_id'] == 66667724 || $_SESSION['user_id'] == 66667615  || $_SESSION['user_id'] == 66667763){
//			$param['charge_money'] = 0.01;
//		}
		//防止用户信息丢失
		if($_SESSION['user_id'] == '' || $_SESSION['user_id'] == 0){
			echo '<script>alert(\'连接失败，请重新尝试\');</script>';
			exit();
		}
        $param['order_id'] = createOrderNO();
        $param['user_id'] = $_SESSION['user_id'];
        $param['charge_id'] = $result['user_recharge_combo_id'];
        $param['pay_type'] = 'alipayh5';     //alipayh5
        $param['charge_coin'] = $result['user_recharge_combo_coin'];
        $param['charge_give_coin'] = $result['user_recharge_combo_give_coin'];
        //插入订单
        $db = new DB();
        $db->createOrder($param);
        //超时时间
        $timeout_express="1m";
        $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody(BODY);
        $payRequestBuilder->setSubject(BODY);
        $payRequestBuilder->setOutTradeNo($param['order_id']);
        $payRequestBuilder->setTotalAmount($param['charge_money']);
        $payRequestBuilder->setTimeExpress($timeout_express);
        $payResponse = new AlipayTradeService($config);
        $payResponse->wapPay($payRequestBuilder,$config['return_url']."?mod=return&uid=".$_SESSION['user_id'],$config['notify_url']);
        exit;
    }

