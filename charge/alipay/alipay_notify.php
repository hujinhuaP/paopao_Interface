<?php
    require_once '../config.php';
    require_once dirname ( __FILE__ ).'/wappay/service/AlipayTradeService.php';
    require_once dirname ( __FILE__ ).'/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
    require_once 'config.php';
//    $_POST = array(
//        'gmt_create'=>'2018-07-17 19:16:28',
//        'charset'=>'UTF-8',
//        'seller_email'=>'2158341618@qq.com',
//        'subject'=>'泡泡直播充值',
//        'sign'=>'KQjZLGAoMk0vj/2kmubleIt2DTqvYJTJhMd1B3DLVJ1MavzIWOfnJyMdvhvYdSBqcF2R6JdellGJFEvEBXGJAjc2dzbovRRxXpvYM6vjfkIWFzk8tojQVhqoodWt0jplyTMhQmS7edRH62g6eL0P3moeL7RaDxZgyfUyRywRqWJ5XQfyb28yjvPjkkLVbdxpkknE3mU3JlU56MNgHWTzqozGwcYakuoNe3dDpS4F9oiPwrG19qw3iuwlKYyuMlxJ0KXisDDyzNgAwL2CAkaVHcXhun8RfgMeQi2aveEFw9QaxZgSnqYuJtpDvGEEiW8pD4ei1QHBcYX30tpc1XkdaA==',
//        'body'=>'泡泡直播充值',
//        'buyer_id'=>'2088302803976819',
//        'invoice_amount'=>'0.00',
//        'notify_id'=>'897280a45fdcd0e86d12c9958e997f5m95',
//        'fund_bill_list'=>'[{"amount":"0.01","fundChannel":"POINT"}]',
//        'notify_type'=>'trade_status_sync',
//        'trade_status'=>'TRADE_SUCCESS',
//        'receipt_amount'=>'0.01',
//        'buyer_pay_amount'=>'0.01',
//        'app_id'=>'2018071760684284',
//        'sign_type'=>'RSA2',
//        'seller_id'=>'2088031024080022',
//        'gmt_payment'=>'2018-07-17 19:16:29',
//        'notify_time'=>'2018-07-17 19:16:29',
//        'version'=>'1.0',
//        'out_trade_no'=>'20180717191617258274',
//        'total_amount'=>'0.01',
//        'trade_no'=>'2018071721001004810554517111',
//        'auth_app_id'=>'2018071760684284',
//        'buyer_logon_id'=>'501***@qq.com',
//        'point_amount'=>'0.01',
//    );
    //wf("success.txt",$_POST);
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
                    'third_order_no'=>$_POST['trade_no']
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