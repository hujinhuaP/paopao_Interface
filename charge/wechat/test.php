<?php
    require_once '../config.php';
    //发送订单成功数据请求
    if($_GET['act'] == 'one'){
        $res = [
            'timestamp'=>time(),
            'order_no'=>'15407126716679731986151',
            'third_order_no'=>'4200000203201810289207144505',
            'type'=>'vip'
        ];
    }else{
        $res = [
            'timestamp'=>time(),
            'order_no'=>'15407125196679731920108',
            'third_order_no'=>'4200000194201810284705407502',
            'type'=>'vip'
        ];
    }
    $res = json_encode($res);
    $encode_data = OpensslEncryptHelper::encryptWithOpenssl($res);
    $result = http_post(SERVICE_NOTIFY_URL, ['receipt_data'=>$encode_data]);
    wf("yuyin.txt",$result);
    echo $result;exit();