<?php
    require_once 'config.php';
    $params = $_POST;
    $attach = $params['attach'];
    $merid = $params['merid'];
    $trade_no = $params['trade_no'];
    $out_trade_no = $params['out_trade_no'];
    $result_code = $params['result_code'];
    $return_code = $params['return_code'];
    $total_fee = $params['total_fee'];
    $sign = $params['sign'];
    $str = strtoupper(md5($merid.$total_fee.$attach.$out_trade_no.$trade_no.$result_code.$return_code.$key));
    if($sign != $str){
        wf("fail.txt",$params);
        echo 'sign Error';
    }else{
        //获取传递的值
        if($result_code == 'success' && $return_code == 'success'){
            wf("success.txt",$_REQUEST);
            if($attach == 'vip'){
                //发送订单成功数据请求
                $res = [
                    'timestamp'=>time(),
                    'order_no'=>$out_trade_no,
                    'third_order_no'=>$trade_no,
                    'type'=>'vip'
                ];
            }else{
                //发送订单成功数据请求
                $res = [
                    'timestamp'=>time(),
                    'order_no'=>$out_trade_no,
                    'third_order_no'=>$trade_no
                ];
            }
            $res = json_encode($res);
            wf("res.txt",$res);
            $encode_data = OpensslEncryptHelper::encryptWithOpenssl($res);
            $result = http_post(SERVICE_NOTIFY_URL, ['receipt_data'=>$encode_data]);
            wf("result.txt",$result);
            echo $result;exit();
        }
    }
