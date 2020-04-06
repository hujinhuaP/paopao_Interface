<?php
    require_once '../config.php';
    if(isset($_GET['pay_type']) && $_GET['pay_type'] == 'vip'){
        //创建VIP订单
        echo createVIPWxOrder();
    }else{
        //创建充值订单
        echo createWxOrder();
    }