<?php
namespace app\helper;

class AlipayServer {

    public function __construct(){
        $dir = __DIR__.'/alipay-php-sdk';
        $this->requireFiles($dir);
    }
    private function requireFiles($dir){
        $file = scandir($dir);
        foreach($file as $item){
            if(!($item == '.' || $item =='..')){
                if(is_file($dir.'/'.$item)){
                    require_once $dir .'/'.$item;
                }else{
                    $this->requireFiles($dir.'/'.$item);
                }
            }
        }
    }

    //应用id
    private static $appId = '2018102461815486';//appId
    //开发者私钥
    private static $privateKey = 'MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQDOkDp6mAhsMbAcT2iioBxCt3U0oyG89VdSHfAigoDxoV3L156Mya4csP6wHCMmdGOSBHSqXBFMjscNLJHj/OSNn45wvDJYZP8OtJp/hpjmkB91PiBSmJrDVVgHOfdJlLCcDeIMeefo3WcF5OrXsiRosmbFSXzUnZksH4MJu7frXfdM3+0xfcvEkmgot/yE/o6JQVzB57Gq2tDxs/WbTye4OcNRlpFF8jEzpFneXLiAykNQilI9f3P5xqxThkF/A85jT/DfMiEeJU2C6JhCoXMz70cf6X5tLrn9xWYSSrvY//j7l5GB2b3EkUbOZUYXqz9QgaIoT8Vw8w/NW2I/hpwxAgMBAAECggEBAMf10sZuemjSSNt++5nCSNlE408LRFO5ZMh3dsjRcKV4QmZb2n4LlmLr7ADrnBNTxDfL3Gw2KADmjkZwiOIdI9r9RFRZuprbWhUQPCeLUmSPzAQhGgUa+WZyLX8BXCN8ruLChbryH8/K1DpeegBH0PsRCG+fTho8XdTaxG0drVNHq6euvmxMs3jBkoz7vweBoDKxpQbhNh5r98B2FPUph855JbUGDEejFSwSXFZHOB1g9+Y9tupk8W4upkOXlYO6ItsXgSdEIbX7v4t1+qgoLuRIgT+yUwXBvn1IX6g8E9ddlMMyjW52oCrwQ0P2N3MvF7YMY9ITzIaU5Tvyij6kGikCgYEA8X8TqmZoX1TkrRaC1+H2l/IDYir2VV/9Q2ypcEk6x1rvEosuJF2qZrXreOhZ51UEnDGc8V+KpX0/FxN124opTSKWnrPKyexi6scZjLP/eW7jz3ef2XiWoWqhOP5bfd1UAw6vg6wB7mQEp/yMNmAKN5VMhkiljhNMYSQsVVNlR/MCgYEA2vgRoBD8EUJshFXu0WLQBUswCM63VKCIMR7QgVphzLFhj6tyIPl9MHarqnmyaRO30KdydDo1jDlmZ2jcQE2MWWFZ1up0zlxjoBiF5RffudF5wDWJchygy3uGIV+WUpKldqcTjP72Fmf6+UV9f1dGVwSbZKiNzmB+hE3gaX4lWEsCgYEAmBFhHMfnqUAXzzaBpddQJFXs84ACJbiQDkj6WQ6DyMzmBlNF9vhUOOENKdGF6zmJ8aD8JrH26EZ519oVOO1DHKNPHRgx9fy4PQaqfANMN/cv1JCLQ7G/iF1QsEba7eLU6CfzNYK2pJquo+lPkV3gkSeeTGCqf1B/pBvXHtOozykCgYBBNLYq8GPfz+P41I41lDNWIDnBpa06akOkPQTiQEP3bKsc2XU3FJSPJgeg0HSsjc6jN/oBWoQvqbgw+yz7iRxOUYsrUM5P1XtlZWgZ/K4G67ZR4p93d8b6UWJz9b8R/9F+L+rGhfZKXdSC/oqMrTSpHRoZM4hm+J00UOyO/Z2pWQKBgQDr80ihXq1QSq5quvtS+QmTwL600L6jZrLcKySKLFE+YRUhZFKbp2x3rudFdhQ4qBu1RNJhrocWHHh27HgGE0AbOKyW8j8hNfWNPIffs/H1d7Tcrsjjk6aUfwckSS07+fZbHnFwWEwTFjbS/S9I5y4tRIqjG/NEEQ4wUnUcFHEFPw==';
    //支付宝公钥
    private static $publicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnGCFncJqrxlBheiieR2uQeAppOnRiNcNgGLbRfQ1ULaIU4hxf7rwclQh+fBDgyFfLPCdZSd40n8hONT6jP47EUL15immaimhf4So9NH7VsTekouUd9aDUFt9jjnV1+7tBWfHiguK6c80dTZv8mFBLXjcoq5MkGzmWAZO18XuAap9WAytcH+LqfxTiEwRMVHy1KOCND7bjsbBNsYa16FREIpf+eTRqs40KcNgZdQBtQ9rYI+6JkhikX6uRc+6zag0KY6w0OMFRHhOvGa1gu4Lvsyszykh5v7uyT+s4K9JKWCI4IWIXFSX5X1btmKXAm96oYEIyV0tnWcs5itVLpnO8wIDAQAB';
    private static $gatewayUrl = 'https://openapi.alipay.com/gateway.do';
    //沙箱环境
    //private static $gatewayUrl = 'https://openapi.alipaydev.com/gateway.do';
    /**
     * 创建预支付订单
     * @see https://doc.open.alipay.com/docs/doc.htm?spm=a219a.7629140.0.0.Bydc4A&treeId=204&articleId=105465&docType=1
     * @param $body 交易描述
     * @param $subject  商品标题,订单关键字
     * @param $out_trade_no 商户网站唯一订单号
     * @param $total_amount 订单总金额，单位为元，精确到小数点后两位，
     * @param $notify_url 商户外网可以访问的异步地址
     * @param $timeout_express 设置未付款支付宝交易的超时时间，取值范围：1m～15d:m-分钟，h-小时，d-天
     */
    public static function createOrder( $body, $subject, $out_trade_no, $total_amount, $notify_url ,$type ,$timeout_express = '30m'){
        $aop = new AopClient;
        $aop->gatewayUrl = self::$gatewayUrl;
        $aop->appId = self::$appId;
        $aop->rsaPrivateKey = self::$privateKey;
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";//支持RSA和RSA2推荐使用RSA2
        $aop->alipayrsaPublicKey = self::$publicKey;
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = json_encode(array(
           'body' => $body,
           'passback_params' => $type,
           'subject' => $subject,
           'out_trade_no' => $out_trade_no,
           'timeout_express' => $timeout_express,
           'total_amount' => $total_amount,
           'product_code' => 'QUICK_MSECURITY_PAY',//销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY
        ));
        $request->setNotifyUrl($notify_url);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        return $response;

    }
    /**
     * 验证支付宝回调异步通知
     * @see https://doc.open.alipay.com/docs/doc.htm?spm=a219a.7629140.0.0.3Hrc1F&treeId=204&articleId=105301&docType=1
     * @param $arr 数组 回调的参数
     */

    public static function confirmParams($arr){
        $aop = new AopClient;
        $aop->alipayrsaPublicKey = self::$publicKey;
        $flag = $aop->rsaCheckV1($arr, NULL, "RSA2");
        if(!$flag){
            return false;
        }
        $query = self::queryOrder($arr['out_trade_no']);
        if($query){
            return true;
        }
        return false;
    }

    /**
     * 电脑网站支付
     */
    public function getPage($return_url,$notify_url,$body,$subject,$out_trade_no,$total_amount,$type){
        $aop = new AopClient ();
        $aop->gatewayUrl = self::$gatewayUrl;
        $aop->appId = self::$appId;
        $aop->rsaPrivateKey = self::$privateKey;
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset= 'utf-8';
        $aop->format='json';
        $request = new AlipayTradePagePayRequest ();
        $request->setReturnUrl($return_url);
        $request->setNotifyUrl($notify_url);
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = json_encode(array(
            'passback_params' => $type,
            'body' => $body,
            'subject' => $subject,
            'out_trade_no' => $out_trade_no,
            'total_amount' => $total_amount,
            'product_code' => 'FAST_INSTANT_TRADE_PAY',//销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY
        ));
        $request->setBizContent($bizcontent);
        $result = $aop->pageExecute ($request);
        return $result;
    }

    public static function queryOrder($out_trade_no){
        $aop = new AopClient ();
        $aop->gatewayUrl = self::$gatewayUrl;
        $aop->appId = self::$appId;
        $aop->rsaPrivateKey = self::$privateKey;
        $aop->alipayrsaPublicKey = self::$publicKey;
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'utf-8';
        $aop->format='json';
        $request = new AlipayTradeQueryRequest ();
        $request->setBizContent(json_encode(array('out_trade_no'=>$out_trade_no)));
        $result = $aop->execute ( $request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            if($result->$responseNode->trade_status == 'TRADE_CLOSED' || $result->$responseNode->trade_status == 'TRADE_SUCCESS' ){
                return true;
            }
        }
        return false;
    }

    public static function createOrderH5( $body, $subject, $out_trade_no, $total_amount, $notify_url ,$type ,$timeout_express = '30m'){
        $aop = new AopClient;
        $aop->gatewayUrl = self::$gatewayUrl;
        $aop->appId = self::$appId;
        $aop->rsaPrivateKey = self::$privateKey;
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";//支持RSA和RSA2推荐使用RSA2
        $aop->alipayrsaPublicKey = self::$publicKey;
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new AlipayTradeWapPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = json_encode(array(
            'body' => $body,
            'passback_params' => $type,
            'subject' => $subject,
            'out_trade_no' => $out_trade_no,
            'timeout_express' => $timeout_express,
            'total_amount' => $total_amount,
            'product_code' => 'QUICK_WAP_WAY',//销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY
        ));
        $request->setNotifyUrl($notify_url);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->pageExecute($request,'GET');
        return $response;

    }

}