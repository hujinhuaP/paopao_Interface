<?php

namespace app\helper;

class JiGuangApi{

    private $app_key = '';            //appKey。
    private $master_secret = '';      //主密码
    private $url = "https://api.jpush.cn/v3/push";            //推送的地址
    private $iOSIsPublish = FALSE;

    //若实例化的时候传入相应的值则按新的相应值进行
    public function __construct($app_key = null, $master_secret = null, $url = null,$isPublish = FALSE){
        if ($app_key)
            $this->app_key = $app_key;
        if ($master_secret)
            $this->master_secret = $master_secret;
        if ($url)
            $this->url = $url;
        $this->iOSIsPublish = $isPublish;
    }

    /*  $receiver 接收者的信息
        all 字符串 该产品下面的所有用户. 对app_key下的所有用户推送消息
        tag(20个)Array标签组(并集): tag=>array('昆明','北京','曲靖','上海');
        tag_and(20个)Array标签组(交集): tag_and=>array('广州','女');
        alias(1000)Array别名(并集): alias=>array('1','2');
        registration_id(1000)注册ID设备标识(并集): registration_id=>array('20effc07746e1ff2001bd80308a467d800bed39e');
        $content 推送的内容。
        $extras  附加字段  array类型
        $m_time 保存离线时间的秒数默认为一天(可不传)单位为秒
    */
    public function push($receiver = 'all', $title = '', $content = '', $extras = array() ,$m_time = '86400'){
        $base64 = base64_encode("$this->app_key:$this->master_secret");
        $header = array(
            "Authorization:Basic $base64",
            "Content-Type:application/json"
        );
        $data = array();
        $data['platform'] = ["android", "ios"];          //目标用户终端手机的平台类型android,ios,winphone
        $data['audience'] = $receiver;          //目标用户
        //发送通知
        $data['notification'] = array(
            //统一的模式--标准模式
            "alert" => $content,
            //安卓自定义
            "android" => array(
                "alert" => $content,
                "title" => $title,
                "builder_id" => 1,
                'extras' => $extras
            ),
            //ios的自定义
            "ios" => array(
                "alert" => $content,
                "badge" => "+1",
                "sound" => "default",
                'mutable-content' => true, //设置true 支持ios10的推送
                'extras' => $extras
            ),
        );
        //自定义信息
        $data['message'] = array(
            "msg_content" => $content,
            "title" => $title,
            "extras" => $extras
        );

        //附加选项
        $data['options'] = array(
            "sendno" => time(),
            "apns_production" => $this->iOSIsPublish       //指定 APNS 通知发送环境：0开发环境，1生产环境。
        );
        $param = json_encode($data);
        $res = $this->push_curl($param, $header);

        if ($res) {       //得到返回值--成功已否后面判断
            return $res;
        } else {          //未得到返回值--返回失败
            return false;
        }
    }

    //推送的Curl方法
    public function push_curl($param = "", $header = ""){
        if (empty($param)) {
            return false;
        }
        $postUrl = $this->url;
        $curlPost = $param;
        $ch = curl_init();                                      //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl);                 //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);           // 增加 HTTP Header（头）里的字段
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);                                 //运行curl
        curl_close($ch);
        return $data;
    }

}
