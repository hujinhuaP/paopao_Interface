<?php
    require_once '../config.php';
    $xmlData = file_get_contents('php://input');
    if($xmlData!=''){
        $data = (array)simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA);
        wf("success.txt",$data);
        if(true == verifyWxPublicData($data)){
            //发送订单成功数据请求
            $res = [
                'timestamp'=>time(),
                'order_no'=>$data['out_trade_no'],
                'third_order_no'=>$data['transaction_id'],
                'type'=>'vip'
            ];
            $res = json_encode($res);
            $encode_data = OpensslEncryptHelper::encryptWithOpenssl($res);
            $result = http_post(SERVICE_NOTIFY_URL, ['receipt_data'=>$encode_data]);
            wf("yuyin.txt",$data['out_trade_no'].$result);
            echo $result;exit();
        }
    }
    echo 'fail';

	function verifyWxPublicData($datas){
		if($datas['result_code'] == 'SUCCESS' && $datas['return_code'] == 'SUCCESS'){
			$key = "g5bcd1rn68u0hjreey0h4pchlc9zwhsl";
			$string = "appid=".$datas['appid']."&attach=".$datas['attach']."&bank_type=".$datas['bank_type']."&cash_fee=".$datas['cash_fee']."&fee_type=".$datas['fee_type']."&is_subscribe=".$datas['is_subscribe']."&mch_id=".$datas['mch_id']."&nonce_str=".$datas['nonce_str']."&openid=".$datas['openid']."&out_trade_no=".$datas['out_trade_no']."&result_code=".$datas['result_code']."&return_code=".$datas['return_code']."&time_end=".$datas['time_end']."&total_fee=".$datas['total_fee']."&trade_type=".$datas['trade_type']."&transaction_id=".$datas['transaction_id']."";
			$string .= "&key=".$key;
			$localsign = strtoupper(md5($string));
			if($localsign == $datas['sign']){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}