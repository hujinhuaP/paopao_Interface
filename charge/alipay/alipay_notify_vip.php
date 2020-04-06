<?php
    require_once '../config.php';
    require_once dirname ( __FILE__ ).'/wappay/service/AlipayTradeService.php';
    require_once dirname ( __FILE__ ).'/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
    require_once 'config.php';
    wf("success.txt",$_POST);
    $notify = new AlipayTradeService($config);
    $verify_result = $notify->check($_POST);
    if($verify_result) {
        if(!empty($_POST['out_trade_no'])) {
            $trade_status=$_POST['trade_status'];
            if ($trade_status == 'TRADE_SUCCESS' || $trade_status == 'TRADE_FINISHED'){
                //发送订单成功数据请求
                $res = [
                    'timestamp'=>time(),
                    'order_no'=>$_POST['out_trade_no'],
                    'third_order_no'=>$_POST['trade_no'],
                    'type'=>'vip'
                ];
                $res = json_encode($res);
                //wf("success.txt",$res);
                $encode_data = OpensslEncryptHelper::encryptWithOpenssl($res);
                //wf("success.txt",$encode_data);
                $result = http_post(SERVICE_NOTIFY_URL, ['receipt_data'=>$encode_data]);
                //wf("success.txt",$result);
                echo $result;exit();
            }
        }
    }else{
        echo 'fail';
    }
