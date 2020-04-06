<?php
function array_random($arr, $num = 1) {
    shuffle($arr);

    $r = array();
    for ($i = 0; $i < $num; $i++) {
        if(isset($arr[$i])){
            $r[] = $arr[$i];
        }else{
            break;
        }
    }
    return $num == 1 ? $r[0] : $r;
}

if (!function_exists('createNoncestr')) {
    function createNoncestr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str   = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}


function add_querystring_var($url, $key, $value) {
    $url=preg_replace('/(.*)(?|&)'.$key.'=[^&]+?(&)(.*)/i','$1$2$4',$url.'&');
    $url=substr($url,0,-1);
    if(strpos($url,'?') === false){
        return ($url.'?'.$key.'='.$value);
    } else {
        return ($url.'&'.$key.'='.$value);
    }
}