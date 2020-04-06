<?php
    //header("location:http://charge.860051.cn/pay.php?uid=".$_GET['uid']."&mod=return");
    require_once 'config.php';
    $res = $db->getChargeList();
    $uid = isset($_GET['uid']) && $_GET['uid']!='' ? $_GET['uid'] : '';
    $user_info = [
        'user_avatar'=>'',
        'user_nickname'=>'',
        'user_id'=>''
    ];

    $_GET['error'] = '';
    if($uid == ''){
        $_GET['mod'] = 'nouid';
    }else{
        $user_info = $db->getUserInfo($uid);
        if($user_info == false){
            $_GET['mod'] = 'nouid';
            $_GET['error'] = '!!!用户编号不存在';
        }
        $_SESSION['user_id'] = $uid;
    }
    $noPay = $db->checkFirstPay($uid);
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>充值</title>
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0" />
	<!--<link rel="stylesheet" type="text/css" href="./css/main.css">-->
	<script type="text/javascript">
		// 高度适配说明：通过设置一个方便计算的数值，来设置根元素的font-size,所有元素通过rem为单位达到适配需求；
		//具体使用：
		//通过视觉稿宽度除以一个数获得一个方便计算的值100；
		//所有元素的高度可用{(视觉稿量的的高度)/100}rem获得适配的高度；
		!function () { function a() { document.documentElement.style.fontSize = document.documentElement.clientWidth / 7.5 + "px" } var b = null; window.addEventListener("resize", function () { clearTimeout(b), b = setTimeout(a, 300) }, !1), a() }(window);
	</script>
    <style>
        *{margin:0;padding:0}
        body{min-height:100%;background-color:#fffcfc;color:#fff}
        header{padding:0 .3rem;padding-bottom:.35rem}
        header>p{font-size:.2rem;color:#333;text-align:center}
        .headerdesc{padding:.38rem 0;display:flex;justify-content:space-between;align-items:center}
        .box{z-index:99999999999999;width:100%;height:100%;background:0 0;background:rgba(0,0,0,.55);border-radius:8px;position:fixed;margin:auto;left:0;right:0;top:0;bottom:0;z-index:1}
        .box>div{width:80%;height:2rem;overflow:auto;margin:auto;position:absolute;top:0;left:0;bottom:0;right:0}
        .box>div>li{float:left;width:50%;list-style-type:none;text-align:center}
        .box>div>li>span{width:50%;background:#999;border-radius:8px;padding:.2rem .2rem .2rem .2rem;font-size:.3rem}
        .rechange{width:1.45rem;height:.6rem;border:1px solid #ff6687;font-size:.24rem;color:#ff6687;text-align:center;line-height:.6rem;border-radius:.1rem}
        .headerdesc>div{display:flex;align-items:center;width:100%;height:3.6rem;background:url(./res/new_charge/headerbg.png) no-repeat;background-size:cover;flex-direction:column}
        .headerimg{width:0.9rem;height:0.9rem;border-radius:0.9rem;border:1px solid #fff;margin-top:.28rem;margin-bottom:.07rem}
        .headerimg>img{width:0.9rem;height:0.9rem;border-radius:0.9rem;margin-bottom: 0.2rem;}
        .desc{font-size:.28rem;margin-top:0.01rem}
        .desc>p:last-child{margin-top:.08rem;color:#fff}
        .coindesc{font-size:.28rem;margin-top:0.1rem}
        .coindesc>p:last-child{margin-top:.08rem;color:#fff}
        .paytip{line-height:.88rem;font-size:.3rem;color:#999;padding:0 .3rem;margin-top:.3rem;border-top:1px solid #e9e9e9}
        .item{line-height:1rem;border-bottom:1px solid #e9e9e9;display:flex;justify-content:space-between;align-items:center;padding:0 .32rem;font-size:.3rem;background-color:#fffcfc;color:#333;position:relative}
        .item>div{display:flex;align-items:center}
        .item>div>img{width:.44rem;height:.44rem;margin-right:.2rem}
        .item>span{width:1.84rem;height:.58rem;display:inline-block;text-align:center;line-height:.58rem;background-color:#999;border-radius:.24rem;color:#fff}
        .firstrecharge:after{position:absolute;content:'';top:0;right:0;width:.44rem;height:.36rem;background:url(./res/new_charge/firstrecharge.png) no-repeat;background-size:contain}
        .list>.active>span{background-color:#ff6687}
        .fixedbottom{position:absolute;height:.98rem;width:100%}
        footer{position:fixed;width:100%;height:.58rem;padding:.2rem 0;bottom:0;left:0;display:flex;justify-content:space-around;align-items:center;background-color:rgba(0,0,0,.8)}
        footer>div{height:100%;font-size:.3rem;display:flex;align-items:center;flex:1}
        footer>div:first-child{border-right:1px solid #fff}
        footer>div>img{width:.6rem;height:.6rem;margin:0 .16rem 0 .8rem}
        .pay-type{height:.6rem;display:flex;align-items:center;justify-content:center;text-align:center}
        .pay-type div{width:2rem;height:.6rem;color:#ccc;background-color:#999;display:flex;align-items:center;justify-content:center;font-size:.3rem}
        .pay-type div img{width:.4rem;height:.4rem;margin-right:.08rem}
        .pay-type div:first-child{border-top-left-radius:.6rem;border-bottom-left-radius:.6rem;background:url(./res/new_charge/alipayBtn.png) no-repeat;background-size:contain}
        .pay-type div:last-child{border-top-right-radius:.6rem;border-bottom-right-radius:.6rem;background:url(./res/new_charge/wechatBtn2.png) no-repeat;background-size:contain}
        /*.pay-type div:last-child{border-top-right-radius:.6rem;border-bottom-right-radius:.6rem;background:url(./res/new_charge/alipayBtn2.png) no-repeat;background-size:contain}*/
        .item .more{align-items:center;margin-bottom:.2rem}
        .item .more div{font-size:.2rem;text-align:center;line-height:.35rem;color:#fff;border-radius:.1rem;border-bottom-left-radius:0}
        .item .more .member{display:none;height:.35rem;margin-right:.08rem;margin-left:.1rem;background:-webkit-linear-gradient(left,#ff6687,#fd2f5b);background:-o-linear-gradient(right,#ff6687,#fd2f5b);background:-moz-linear-gradient(right,#ff6687,#fd2f5b);background:linear-gradient(to right,#ff6687,#fd2f5b)}
        .item .more .gold{display:none;height:.35rem;background:-webkit-linear-gradient(left,#ff4b72,#fd2f5b);background:-o-linear-gradient(right,#ff4b72,#fd2f5b);background:-moz-linear-gradient(right,#ff4b72,#fd2f5b);background:linear-gradient(to right,#ff4b72,#fd2f5b)}
        .givemember .more .member{display:block}
        .givegold .more .gold{display:block}
    </style>
</head>

<body>
	<header>
		<div class="headerdesc">
			<div>
				<div class="headerimg">
					<img src="<?php echo $user_info['user_avatar'];?>" alt="" />
				</div>
				<div class="desc">
					<p><?php echo $user_info['user_nickname'];?></p>
					<p style="font-size: .2rem;text-align: center;color: #FDC6D0">ID:<span><?php echo $user_info['user_id'];?></span></p>
				</div>
                <div class="coindesc">
                    <p style="font-size: .5rem;text-align: center;"><?php echo $user_info['user_coin'] + $user_info['user_free_coin'];?></p>
                    <p style="text-align: center;color: #FDC6D0">金币余额</p>
                </div>
			</div>
			<!-- <p class="rechange">更换账号</p> -->
		</div>
		<p>— 请确认是自己的泡泡账户哟 —</p>
	</header>
	<div class="pay-type payment">
		<div class="alipay selected" data-value="alipay">
			<span data="alipay"></span>
		</div>
		<div class="wechat" data-value="wechat">
			<span data="wechat"></span>
		</div>
	</div>
	<p class="paytip">请选择充值金额</p>
	<ul class="list">
		<?php
            foreach($res as $k=>$v){
                $checked = $acitve = '';
                if($k == 0){
                    $acitve = ' active';
                    $checked = 'checked';
                }
                $reward_li = '';
                $reward_day = 0;

                //首充套餐仅首充会员能够看到，否则不显示
                if($v['user_recharge_is_first'] == 'Y' && !$noPay){
					continue;
                }
				if($v['user_recharge_is_first'] == 'Y' && $noPay){
                    $reward_li = ' firstrecharge';
                }
                echo '<li class="item '.$acitve.$reward_li.' givemember givegold"><div><input style="display:none;" type="radio" name="chargemoney" value="'.$v['user_recharge_combo_id'].'" '.$checked.'>';
                echo '<img src="./res/new_charge/coin.png" alt="" /><span>'.$v['user_recharge_combo_coin'].'金币</span>';
                echo '<div class="more">';

                //首充送会员天数
                if($v['first_recharge_reward_vip_day'] > 0 && $noPay){
                    $reward_day = $v['first_recharge_reward_vip_day'];
                    echo '<div class="member">首充送'.$reward_day.'天会员</div>';
                }
                //VIP充值送金币
                if($v['user_recharge_vip_reward_coin'] > 0){
                    if($reward_day > 0){
                        echo '<div style="color:#FF6687">vip送'.$v['user_recharge_vip_reward_coin'].'金币</div>';
                    }else{
                        echo '<div class="member">vip送'.$v['user_recharge_vip_reward_coin'].'金币</div>';
                    }
                }
				echo '</div>';
                echo '</div><span class="payBtn" data-value="'.$v['user_recharge_combo_id'].'">￥'.$v['user_recharge_combo_fee'].'</span></li>';
            }
		?>
	</ul>
    <script>
        function selectuser(){
            var uid = $("#uid").val();
            var r = /^\+?[1-9][0-9]*$/;
            var result = r.test(uid);
            if(result == false || uid.length < 3) {
                $("#error").html('!!!用户编号位数不足');
                return false;
            }else{
                window.location.href = 'pay_new.php?uid='+uid+'';
                return false;
            }
        }
    </script>
	<?php
        if(isset($_GET['mod'])){
            echo '<div class="box">';
            if($_GET['mod'] == 'return') {
                echo '<div id="returnApp"><li><span class="payed">已完成付款</span></li><li><span class="payed">取消付款</span></li></div>';
            }elseif($_GET['mod'] == 'nouid'){
                echo '<div><div id="error" style="font-size:.16rem;color:#ff6687">'.$_GET['error'].'</div><li><span><input style="font-size:.2rem;height:.4rem;" type="number" maxlength="8" id="uid" name="uid" placeholder="输入用户编号"></span></li><li id="test"><span id="confirm_btn" style="cursor:pointer;" onclick="selectuser()">确认</span></li></div>';
            }
            echo '</div>';
        }
	?>
<div style="padding:10px;width:300px;font-size:0.3rem;height:60px;color:#000">温馨提示：充值如遇问题或投诉请拨打13125174361（服务时间工作日周一至周五9:30-19:00），非工作时间请联系微信:TTbaby02</div>
</body>
<script src="http://libs.baidu.com/jquery/2.1.1/jquery.min.js"></script>
<script>

</script>
<script type="text/javascript" src="res/js/fingerprint2.min.1.4.1.js"></script>
<script type="text/javascript" src="res/js/zepto.min.js"></script>
<script>
    var pay_type = 'alipay';
    $(function(){
        $('.pay-type div').on('click',function(){
            let typeValue = $(this).data();
            if(typeValue.value == 'alipay'){
                $(this).siblings().removeClass('selected').css({'background':'url(./res/new_charge/wechatBtn2.png) no-repeat','background-size':'contain'});
                $(this).addClass('selected').css({'background':'url(./res/new_charge/alipayBtn.png) no-repeat','background-size':'contain'});
            }else{
                $(this).siblings().removeClass('selected').css({'background':'url(./res/new_charge/alipayBtn2.png) no-repeat','background-size':'contain'});
                $(this).addClass('selected').css({'background':'url(./res/new_charge/wechatBtn.png) no-repeat','background-size':'contain'});
            }
            pay_type = typeValue.value;
        })
        $(".payBtn").click(function(){
            var money = $(this).data('value');
            getPayUrl(money);
        });
    });

    function selectuser(){
        var uid = $("#uid").val();
        var r = /^\+?[1-9][0-9]*$/;
        var result = r.test(uid);
        if(result == false || uid.length < 3) {
            $("#error").html('!!!用户编号位数不足');
            return false;
        }else{
            window.location.href = 'pay_new.php?uid='+uid+'';
            return false;
        }
    }

    $(function(){
        $(".item").on("click",function(){
            $(".item").removeClass("active");
            $(this).addClass("active");
        });
        $(".payed").on("click",function(){
            window.location.href = 'pay_new.php?uid=<?php echo $_SESSION['user_id'];?>';
        });
    });

    var fp=new Fingerprint2();
    function getPayUrl(chargemoney){
        // var chargemoney = $('input[name="chargemoney"]:checked').val();
        fp.get(function(result)
            {
                if(pay_type == 'wechat'){
                    let wxpay_type = randomNum(4,4);
                    switch(wxpay_type){
                        case 1:       //微信官方H5
                            $.getJSON("wechat/wechat_h5.php?pay_type="+pay_type+"&chargemoney="+chargemoney+"&code="+result, function(d){
                                if (d.errmsg == '') {
                                    window.location.href=d.mweb_url+'&redirect_url=<?php echo urlencode('http://charge.860051.cn/pay_new.php?mod=return&uid='.$_SESSION['user_id'].'')?>';
                                } else {
                                    alert(d.errmsg);
                                }
                            });
                            break;
                        case 2:       //elves H5
                            window.location.href = "elves/elves.php?pay_type="+pay_type+"&chargemoney="+chargemoney+"&code="+result+"";
                            break;
                        case 3:       //quanming H5
                            window.location.href = "quanmin/quanmin.php?pay_type="+pay_type+"&chargemoney="+chargemoney+"&code="+result+"";
                            break;
                        case 4:       //318211
                            window.location.href = "318211/318211.php?pay_type="+pay_type+"&chargemoney="+chargemoney+"&code="+result+"";
                            break;
                        default:
                            break;
                    }
                }else{
                    window.location.href="alipay/alipay_h5.php?pay_type="+pay_type+"&chargemoney="+chargemoney+"&code="+result;
                }
            }
        );
    }

    //生成从minNum到maxNum的随机数
    function randomNum(minNum,maxNum){
        switch(arguments.length){
            case 1:
                return parseInt(Math.random()*minNum+1,10);
                break;
            case 2:
                return parseInt(Math.random()*(maxNum-minNum+1)+minNum,10);
                break;
            default:
                return 0;
                break;
        }
    }
</script>
</html>
