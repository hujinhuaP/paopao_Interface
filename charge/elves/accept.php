<?php
	session_start();
	if($_SESSION['payed_type'] == 'vip'){
		echo '<script>window.location.href="http://charge.860051.cn/vip.php?mod=return&uid='.$_SESSION['user_id'].'";</script>';exit;
	}else{
		echo '<script>window.location.href="http://charge.860051.cn/pay_new.php?mod=return&uid='.$_SESSION['user_id'].'";</script>';exit;
	}
