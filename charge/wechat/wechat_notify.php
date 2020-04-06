<?php
    require_once '../config.php';
    $xmlData = file_get_contents('php://input');
    if($xmlData!=''){
        $data = (array)simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA);
        wf("success.txt",$data);
        if(true == verifyWxData($data)){
            //发送订单成功数据请求
            $res = [
                'timestamp'=>time(),
                'order_no'=>$data['out_trade_no'],
                'third_order_no'=>$data['transaction_id']
            ];
            $res = json_encode($res);
            $encode_data = OpensslEncryptHelper::encryptWithOpenssl($res);
            $result = http_post(SERVICE_NOTIFY_URL, ['receipt_data'=>$encode_data]);
            wf("yuyin.txt",$data['out_trade_no'].$result);
            echo $result;exit();
        }
    }
    echo 'fail';