<?php
    require_once 'config.php';
	$amount = post('amount');
	$backPara = post('backPara');
	$dateTime = post('dateTime');
	$payUserId = post('payUserId');
	$payType = post('payType');
	$orderId = post('orderId');
	$outTradeNo = post('outTradeNo');
	$sign = post('sign');
	$version = post('version');
	$status = post('status');
	$verify_sign = md5('amount='.$amount.'&appKey='.$appKey.'&backPara='.$backPara.'&dateTime='.$dateTime.'&openId='.$openid.'&orderId='.$orderId.'&outTradeNo='.$outTradeNo.'&payType='.$payType.'&payUserId='.$payUserId.'&status='.$status.'&version='.$version.$token);
    if($verify_sign == $sign && $status == 2) {
		wf("success.txt",$_POST);
    	if($backPara == 'vip'){
			//发送订单成功数据请求
			$res = [
				'timestamp'=>time(),
				'order_no'=>$orderId,
				'third_order_no'=>$outTradeNo,
				'type'=>'vip'
			];
		}else{
			//发送订单成功数据请求
			$res = [
				'timestamp'=>time(),
				'order_no'=>$orderId,
				'third_order_no'=>$outTradeNo
			];
		}
		$res = json_encode($res);
		wf("res.txt",$res);
    	$encode_data = OpensslEncryptHelper::encryptWithOpenssl($res);
		$result = http_post(SERVICE_NOTIFY_URL, ['receipt_data'=>$encode_data]);
		wf("result.txt",$result);
		echo $result;exit();
	}else{
		echo 'fail';
	}
