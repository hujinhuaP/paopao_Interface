<?php

/**

 * 简易的MYSQL类

 */

class DB{



    protected $db;



    function __construct(){

        $this->db = $this->pdoconn();

    }

    /**
     * @param $uid
     * @return bool
     * 如果没有充过值 返回true
     */
    function checkFirstPay($uid){
        $uid = intval($uid);
        $sqlstr = "SELECT user_total_coin FROM `user` WHERE user_id='".$uid."' LIMIT 1";

        $res = $this->getOne($sqlstr);

        if(!empty($res) && count($res)>0 && $res['user_total_coin'] == 0 ){
            return true;
        }else{
            return false;

        }
    }


    function pdoconn(){

        $pdo = new PDO("mysql:host=".WEB_HOST.";dbname=".WEB_DB."","".WEB_USER."","".WEB_PWD."",

            array(

                PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,							//返回一个索引为结果集列名的数组

                PDO::ATTR_ORACLE_NULLS=>PDO::CASE_LOWER,								//强制列名大写CASE_LOWER、CASE_UPPER

                PDO::ATTR_TIMEOUT=>3,													//连接等待时间

                PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES utf8mb4;'	//设置字符集

            )

        );

        return $pdo;

    }



    function getOne($sqlstr){

        $res = $this->db->query($sqlstr);

        return $res->fetch();

    }



    function returnData($sqlstr){

        $res = $this->db->query($sqlstr);

        return $res->fetchAll();

    }



    //查询充值套餐列表

    function getChargeList(){

        $sqlstr = "SELECT * FROM user_recharge_combo WHERE user_recharge_combo_apple_id='' ORDER BY user_recharge_combo_fee ASC";

        $res = $this->returnData($sqlstr);

        file_put_contents(WEBROOT."/charge.db",json_encode($res));    //记录价格

        return $res;

    }



    //查询VIP列表

    function getVipList(){

        $sqlstr = "SELECT * FROM user_vip_combo WHERE user_vip_combo_apple_id='' ORDER BY user_vip_combo_fee ASC";

        $res = $this->returnData($sqlstr);

        file_put_contents(WEBROOT."/vip.db",json_encode($res));    //记录价格

        return $res;

    }



    //创建订单

    public function createOrder($param,$order_type=''){

        if($order_type == 'vip'){

            $userinfo = $this->getUserInfo($param['user_id']);

            $param['expire_time'] = $userinfo['user_member_expire_time'];

            $sqlstr = "insert into user_vip_order(

                        user_vip_order_number,user_id,user_member_expire_time,user_vip_order_combo_fee,

                        user_vip_order_combo_month, user_vip_order_status,user_vip_order_create_time,user_vip_order_type,
                        
                        user_vip_order_update_time

                        )values(

                        '".$param['order_id']."','".$param['user_id']."','".$param['expire_time']."','".$param['vip_money']."',

                        '".$param['vip_time']."','N','".time()."','".$param['pay_type']."'
                        
                        ,'".time()."'
                        )";

            $this->db->query($sqlstr);

        }else{

            $sqlstr = "insert into user_recharge_order(

                        user_recharge_order_number,user_id,user_recharge_combo_id,user_recharge_combo_coin,

                        user_recharge_combo_give_coin,user_recharge_combo_fee,user_recharge_order_type,

                        user_recharge_order_coin,user_recharge_order_fee,user_recharge_order_create_time,
                        
                        user_recharge_order_update_time

                        )values(

                        '".$param['order_id']."','".$param['user_id']."','".$param['charge_id']."','".$param['charge_coin']."',

                        '".$param['charge_give_coin']."','".$param['charge_money']."','".$param['pay_type']."',

                        '".$param['charge_coin']."','".$param['charge_money']."','".time()."','".time()."'

                        )";

            //echo $sqlstr;die;

            $this->db->query($sqlstr);

        }

    }



    //获取帐户资料

    public function getUserInfo($uid){

        $sqlstr = "SELECT user_id,user_nickname,user_avatar,user_member_expire_time,truncate(user_coin,2) AS user_coin,user_free_coin FROM `user` WHERE user_id='".$uid."' LIMIT 1";

        $res = $this->getOne($sqlstr);

        if(!empty($res) && count($res)>0){

            return $res;

        }else{

            return false;

        }

    }



}



//初始化

$db = new DB();



function http_post($url, $data) {

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,$url);

    curl_setopt($ch, CURLOPT_HEADER,0);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_POST, 1);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $res = curl_exec($ch);

    curl_close($ch);

    return $res;

}



//输出调试笔记

function wf($file,$con){

    file_put_contents($file,print_r($con,true)."\r\n",FILE_APPEND);

}



//读取套餐价格

function getChargeMoney(){

    $charge_id = $_GET['chargemoney'];

    $charge_list = file_get_contents(WEBROOT."/charge.db");

    $charge_list = json_decode($charge_list,true);

    if(!is_array($charge_list) || empty($charge_list)){

        return false;

    }

    foreach($charge_list as $k=>$v){

        if($v['user_recharge_combo_id'] == $charge_id){

            return $v;

            break;

        }

    }

    return false;

}



//读取VIP价格

function getVIPMoney(){

    $vip_id = $_GET['chargemoney'];

    $vip_list = file_get_contents(WEBROOT."/vip.db");

    $vip_list = json_decode($vip_list,true);

    if(!is_array($vip_list) || empty($vip_list)){

        return false;

    }

    foreach($vip_list as $k=>$v){

        if($v['user_vip_combo_id'] == $vip_id){

            return $v;

            break;

        }

    }

    return false;

}



//生成订单号

function createOrderNO($type='',$uid=''){

    if($type == 'vip'){

        $sRechargeOrderNumber = time().$uid.mt_rand(10000,99999);

    }else{

        $aTime = explode('.', sprintf('%.10f', microtime(TRUE)));

        $sRechargeOrderNumber = date('YmdHis', $aTime[0]) . '000' . $aTime[1] . mt_rand(10000, 99999);

    }

    return $sRechargeOrderNumber;

}



//构造订单数据

function createWxOrder(){

    $result = getChargeMoney();

//    if($_SESSION['user_id'] == 66667724 || $_SESSION['user_id'] == 66667763){
//
//        $result['user_recharge_combo_fee'] = 0.01;//充值金额0.01
//
//    }

    //防止用户信息丢失
    if($_SESSION['user_id'] == '' || $_SESSION['user_id'] == 0){
        return json_encode(array('errmsg'=>'连接失败，请重新尝试'));exit();
    }

    if($result){

        $param['charge_money'] = bcmul($result['user_recharge_combo_fee'],100,0);

        if($param['charge_money'] == 0){

            return json_encode(array('errmsg'=>'db error'));exit();

        }else{



        }

    }else{

        return json_encode(array('errmsg'=>'db error'));exit();

    }

    $param['body'] = BODY;

    $param['scene_info'] ='{"h5_info":{"type":"Wap","wap_url":"'.WX_WAY.'","wap_name":"'.$param['body'].'"}}';//场景信息 必要参数

    $param['trade_type'] = 'MWEB';

    $param['order_id'] = createOrderNO();

    $param['nonce_str'] = md5($param['order_id']);

    $param['user_id'] = $_SESSION['user_id'];

    $param['charge_id'] = $result['user_recharge_combo_id'];

    $param['pay_type'] = 'wxh5';    //wxh5

    $param['charge_coin'] = $result['user_recharge_combo_coin'];

    $param['charge_give_coin'] = $result['user_recharge_combo_give_coin'];

    //$param['ip'] = $_SERVER["REMOTE_ADDR"];
    $param['ip'] = get_client_ip();

    $signA ="appid=".WXAPPID."&body=".$param['body']."&mch_id=".WXMCHID."&nonce_str=".$param['nonce_str']."&notify_url=".WX_NOTIFY_URL."&out_trade_no=".$param['order_id']."&scene_info=".$param['scene_info']."&spbill_create_ip=".$param['ip']."&total_fee=".$param['charge_money']."&trade_type=".$param['trade_type']."";

    $strSignTmp = $signA."&key=".WXKEY."";

    $sign = strtoupper(md5($strSignTmp));

    $post_data = "<xml>

                    <appid>".WXAPPID."</appid>

                    <body>".$param['body']."</body>

                    <mch_id>".WXMCHID."</mch_id>

                    <nonce_str>".$param['nonce_str']."</nonce_str>

                    <notify_url>".WX_NOTIFY_URL."</notify_url>

                    <out_trade_no>".$param['order_id']."</out_trade_no>

                    <scene_info>".$param['scene_info']."</scene_info>

                    <spbill_create_ip>".$param['ip']."</spbill_create_ip>

                    <total_fee>".$param['charge_money']."</total_fee>

                    <trade_type>".$param['trade_type']."</trade_type>

                    <sign>$sign</sign>

                </xml>";

    $dataxml = http_post(WX_SEND_URL,$post_data);

    //插入订单

    $db = new DB();

    $param['charge_money'] = $result['user_recharge_combo_fee'];

    $db->createOrder($param);

    $objectxml = (array)simplexml_load_string($dataxml, 'SimpleXMLElement', LIBXML_NOCDATA);

    //wf("/data/wwwroot/lebo/charge/test.txt",$objectxml);

    $objectxml['errmsg'] = '';

    return json_encode($objectxml);

}

function get_client_ip() {
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
}

//构造VIP订单

function createVIPWxOrder(){

    $result = getVIPMoney();

//    if($_SESSION['user_id'] == 66667724){
////
////        $result['user_vip_combo_fee'] = 0.01;//充值金额0.01
////
////    }

    //防止用户信息丢失
    if($_SESSION['user_id'] == '' || $_SESSION['user_id'] == 0){
        return json_encode(array('errmsg'=>'连接失败，请重新尝试'));exit();
    }

    if($result){

        $param['vip_money'] = bcmul($result['user_vip_combo_fee'],100,0);

        if($param['vip_money'] == 0){

            return json_encode(array('errmsg'=>'db error'));exit();

        }else{



        }

    }else{

        return json_encode(array('errmsg'=>'db error'));exit();

    }

    $param['body'] = BODY_VIP;

    $param['scene_info'] ='{"h5_info":{"type":"Wap","wap_url":"'.WX_WAY.'","wap_name":"'.$param['body'].'"}}';//场景信息 必要参数

    $param['trade_type'] = 'MWEB';

    $param['order_id'] = createOrderNO('vip',$_SESSION['user_id']);

    $param['nonce_str'] = md5($param['order_id']);

    $param['user_id'] = $_SESSION['user_id'];

    $param['charge_id'] = $result['user_vip_combo_id'];

    $param['pay_type'] = 'wxh5';    //wxh5

    $param['vip_time'] = $result['user_vip_combo_month'];

    //$param['ip'] = $_SERVER["REMOTE_ADDR"];
    $param['ip'] = get_client_ip();

    $signA ="appid=".WXAPPID."&body=".$param['body']."&mch_id=".WXMCHID."&nonce_str=".$param['nonce_str']."&notify_url=".WX_NOTIFY_VIP_URL."&out_trade_no=".$param['order_id']."&scene_info=".$param['scene_info']."&spbill_create_ip=".$param['ip']."&total_fee=".$param['vip_money']."&trade_type=".$param['trade_type']."";

    $strSignTmp = $signA."&key=".WXKEY."";

    $sign = strtoupper(md5($strSignTmp));

    $post_data = "<xml>

                    <appid>".WXAPPID."</appid>

                    <body>".$param['body']."</body>

                    <mch_id>".WXMCHID."</mch_id>

                    <nonce_str>".$param['nonce_str']."</nonce_str>

                    <notify_url>".WX_NOTIFY_VIP_URL."</notify_url>

                    <out_trade_no>".$param['order_id']."</out_trade_no>

                    <scene_info>".$param['scene_info']."</scene_info>

                    <spbill_create_ip>".$param['ip']."</spbill_create_ip>

                    <total_fee>".$param['vip_money']."</total_fee>

                    <trade_type>".$param['trade_type']."</trade_type>

                    <sign>$sign</sign>

                </xml>";

    $dataxml = http_post(WX_SEND_URL,$post_data);

    //插入订单

    $db = new DB();

    $param['vip_money'] = $result['user_vip_combo_fee'];

    $db->createOrder($param,'vip');

    $objectxml = (array)simplexml_load_string($dataxml, 'SimpleXMLElement', LIBXML_NOCDATA);

    $objectxml['errmsg'] = '';

    return json_encode($objectxml);

}



function verifyWxData($data){

    //$xmlData = file_get_contents('php://input');

    //libxml_disable_entity_loader(true);

    //$data = json_decode(json_encode(simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

    //效验签名

    $sign = "appid=".$data['appid']."&bank_type=".$data['bank_type']."&cash_fee=".$data['cash_fee']."&fee_type=".$data['fee_type']."&is_subscribe=".$data['is_subscribe']."&mch_id=".$data['mch_id']."&nonce_str=".$data['nonce_str']."&openid=". $data['openid']."&out_trade_no=".$data['out_trade_no']."&result_code=".$data['result_code']."&return_code=".$data['return_code']."&time_end=".$data['time_end']."&total_fee=".$data['total_fee']."&trade_type=".$data['trade_type']."&transaction_id=".$data['transaction_id']."&key=".WXKEY."";

    if(strtoupper(md5($sign))!=$data['sign']) {

        return false;

    }else{

        return true;

    }

}



function cnMonth($num){

    switch($num){

        case "1":

            return '月卡会员';

            break;

        case "3":

            return '季度会员';

            break;

        case "6":

            return '半年会员';

            break;

        case "12":

            return '年卡会员';

            break;

        default:

            return '';

            break;

    }

}


