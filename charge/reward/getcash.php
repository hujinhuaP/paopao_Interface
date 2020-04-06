<?php
	require_once '../config_dev.php';
	//嫂子人工提现
	//云通openid oEsQlwS4Ga8SF3BP_nkYI2URmtfk
	$reward_param['openid'] = 'oMPWm01tqlCGYfT0BLX61UZJ0z8E';
	$reward_param['orderid'] = 'self'.date("YmdHis").mt_rand(100,999);
	$reward_param['money'] = 49900;
	//file_put_contents("/data/wwwroot/lebo/charge/post.txt",print_r($reward_param,true)."\r\n".$_POST['receipt_data'],FILE_APPEND);
	if(isset($reward_param['money']) && $reward_param['money']!='' && isset($reward_param['orderid']) && $reward_param['orderid']!='' && isset($reward_param['openid']) && $reward_param['openid']!=''){
		require_once 'reward.class.php';
		$reward = new Reward();
		$result = $reward->init($reward_param);
		echo $result;exit();
	}else{
		echo json_decode(array('result' => '参数错误'));exit();
	}