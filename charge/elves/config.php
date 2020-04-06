<?php
    require_once '../config.php';
    $appKey = '7599127924768769';
    $openid = 'yNkzQYOnF';
    $token = '3b84809f8ab74d04b888c2f728b3b729';
    function post($str,$default_val=''){
        if(isset($_POST[$str]) && $_POST[$str]!=''){
            return $_POST[$str];
        }else{
            return $default_val;
        }
    }