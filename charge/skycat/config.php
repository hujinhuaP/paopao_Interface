<?php
    require_once '../config.php';
    $payway = 'http://love.pcheng-shop.com/charge/unifiedorder.php';   //提交地址
    $app_id = '1001';      //商户号
    $key = 'FTli7lkqSeC2qtEXwDhQidmJsYQAXdPc';      //密钥
    function post($str,$default_val=''){
        if(isset($_POST[$str]) && $_POST[$str]!=''){
            return $_POST[$str];
        }else{
            return $default_val;
        }
    }
