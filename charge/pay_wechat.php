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

	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0"/>

	<!--<link rel="stylesheet" type="text/css" href="./css/main.css">-->

	<script type="text/javascript">

    // 高度适配说明：通过设置一个方便计算的数值，来设置根元素的font-size,所有元素通过rem为单位达到适配需求；

    //具体使用：

    //通过视觉稿宽度除以一个数获得一个方便计算的值100；

    //所有元素的高度可用{(视觉稿量的的高度)/100}rem获得适配的高度；

    !function(){function a(){document.documentElement.style.fontSize=document.documentElement.clientWidth/7.5+"px"}var b=null;window.addEventListener("resize",function(){clearTimeout(b),b=setTimeout(a,300)},!1),a()}(window);

   </script>

    <!-- 以下为openinstall集成代码，建议在html文档中尽量靠前放置，加快初始化过程 -->

    <!-- 强烈建议直接引用下面的cdn加速链接，以得到最及时的更新，我们将持续跟踪各种主流浏览器的变化，提供最好的服务；不推荐将此js文件下载到自己的服务器-->


   <style>

   	*{

   		margin: 0;

   		padding: 0;

   	}

    body{
        min-height: 100%;
        background-color: #191919;
        font-size: .3rem;
        color: #fff;
    }

   	header{

   		border-bottom: 2px solid #505050;

   		padding: 0 .3rem;

   		padding-bottom: .35rem;
        height: 2.05rem;

   	}

   	header>p{

   		font-size: .2rem;

   		color: #505050;

   		text-align: center;

   	}

   	.headerdesc{

   		padding: .38rem 0;

   		display: flex;

   		justify-content: space-between;

   		align-items: center;

   	}

   	.rechange{

   		width: 1.45rem;

   		height: .6rem;

   		border: 1px solid #ff6687;

   		font-size: .24rem;

   		color: #ff6687;

   		text-align: center;

   		line-height: .6rem;

   		border-radius: .1rem;

   	}

   	.headerdesc>div{

   		display: flex;

   		align-items: center;



   	}

   	.headerimg{

   		width: 1.5rem;

   		height: 1.5rem;

   		border-radius: 1.5rem;

   		border: 1px solid #fff;

   		margin-right: .16rem;

   	}

   	.headerimg>img{

   		width: 1.5rem;

   		height: 1.5rem;

   		border-radius: 1.5rem;

   	}

   	.desc{

   		font-size: .28rem;

   	}

   	.desc>p:last-child{

   		margin-top: .1rem;

   		color: #999;

   	}

   	.paytip{

   		line-height: .88rem;

   		font-size: .3rem;

   		color: #999;

   		padding: 0 .3rem;

   	}

   	.list{}

   	.item{

   		line-height: .88rem;

   		border-bottom: 1px solid #282828;

   		display: flex;

   		justify-content: space-between;

   		align-items: center;

   		padding: 0 .3rem;

   		font-size: .3rem;

   		background-color: #1d1d1d;

   	}

   	.item>div{

   		display: flex;

   		align-items: center;

   	}

   	.item>div>img{

   		width: .44rem;

   		height: .44rem;

   		margin-right: .2rem;

   	}

   	.item>span{

   		width: 1.84rem;

   		height: .58rem;

   		display: inline-block;

   		text-align: center;

   		line-height: .58rem;

   		background-color: #999;

   		border-radius: .24rem;

   	}

   	.list>label>.active>span{

   		background-color: #ff6687;

   	}

   	.fixedbottom{

   		position: absolute;

   		height: .98rem;

   		width: 100%;

   	}

   	footer{

   		position: fixed;

   		width: 100%;

   		height: .58rem;

   		padding: .2rem 0;

   		bottom: 0;

   		left: 0;

   		display: flex;

   		justify-content: space-around;

   		align-items: center;

   		background-color: rgba(0,0,0,.8);

   	}

   	footer>div{

   		height: 100%;

   		/*width: 50%;*/

   		font-size: .3rem;

   		display: flex;

   		align-items: center;

   		flex: 1;

   	}

   	footer>div:first-child{

   		border-right: 1px solid #fff;

   	}

   	footer>div>img{

   		width: .6rem;

   		height: .6rem;

   		margin: 0 .16rem 0 .8rem;

   	}

    .box{

        z-index:99999999999999;

        width:100%;

        height:100%;

        background:transparent;

        background:rgba(0,0,0,0.55);

        border-radius:8px;

        position: fixed;

        margin:auto;left:0; right:0; top:0; bottom:0;

        z-index: 1;

    }

    .box>div{

        width: 80%;

        height: 2rem;

        overflow: auto;

        margin: auto;

        position: absolute;

        top: 0; left: 0; bottom: 0; right: 0;

    }

    .box>div>li{

        float:left;

        width:50%;

        list-style-type:none;

        text-align:center;

    }

    .box>div>li>span{

        width:50%;

        background:#999;

        border-radius:8px;

        padding: .2rem .2rem .2rem .2rem;

        font-size:.3rem;

    }
    .reward_vip>span{
        color: #999999;
        font-size: .2rem;
       }

       .coin_num{
           min-width: 64px;
       }
    .hot_pay {
        background:url('./html/hot.png') no-repeat;
        background-position:right top;
        background-size:20px 26px;
    }


    .payment{
        height: 1.05rem;
        width: 100%;
        display: flex;
        justify-content: space-around;
        align-items: center;
        color: #999;
    }
    .payment span{
        height: 100%;
        display: inline-block;
        line-height: 1.05rem;
        font-size: .3rem;
    }
    .payment span.active{
        color: #fff;
        border-bottom: 1px solid #fff;
    }

    header .header{
        display: flex;
        /* justify-content: center; */
        align-items: center;
        height: 100%;

    }
    header{
        background-image: url(./res/vip/images/hasrecharge.png)
    }
    .header-image{
        width: 1.3rem;
        height: 1.3rem;
        box-sizing: border-box;
        border: 1px solid #ffffff;
        border-radius: 1.3rem;
        margin-left: 1.85rem;
        margin-right: .3rem;
        position: relative;
    }
    .header-image img{
        width: 100%;
        height: 100%;
        display: inline-block;
        border-radius: 100%;
    }
    .header-image .vip{
        width: .3rem;
        height: .3rem;
        border-radius: inherit;
        position: absolute;
        left: 1rem;
        top: .88rem;
    }
    .header-title{
        color: #fff;
    }
    .header-title .usernmae{
        font-weight: 500;
        margin-bottom: .1rem;
    }

   </style>

</head>

<body>

	<header>

        <div class="header">

                <!-- 会员续费 -->

                <div class="header-image">

                    <img src="<?php echo $user_info['user_avatar'];?>" alt="">

                    <img class="vip" src="./res/vip/images/vip-open.png" alt="">

                </div>

                <div class="header-title">

                    <p class="usernmae"><?php echo $user_info['user_nickname'];?></p>

                    <p class="openvip">用户ID:<?php echo $user_info['user_id'];?></p>

                </div>

        </div>

	</header>

    <div class="payment">

        <span  class="active" data="alipay">支付宝支付</span>
        <span data="wechat">微信支付</span>


    </div>

	<p class="paytip">请选择充值金额（如遇充值问题，请及时联系客服）</p>

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
                if($v['first_recharge_reward_vip_day'] > 0 && $noPay){
                    $reward_li = ' hot_pay';
                    $reward_day = $v['first_recharge_reward_vip_day'];
                }

                echo '<label><li class="item'.$acitve. $reward_li .'"><div><input style="display:none;" type="radio" name="chargemoney" value="'.$v['user_recharge_combo_id'].'" '.$checked.'>';

                echo '<img src="./html/coin.png" alt="" /><span class="coin_num">'.$v['user_recharge_combo_coin'].'金币</span></div>';
                if($reward_day > 0){
                    echo '<div class="reward_vip"><span>首次充值送'. $v['first_recharge_reward_vip_day'] .'天会员</span></div>';
                }
                echo '<span class="payBtn" data-value="'.$v['user_recharge_combo_id'].'">￥'.$v['user_recharge_combo_fee'].'</span></li></label>';

            }

        ?>

	</ul>

	<div class="fixedbottom"></div>

<!--	<footer>-->
<!---->
<!--		<div class="wechat" onclick="getPayUrl('wechat');">-->
<!---->
<!--			<img src="./html/wechat.png" alt="" />-->
<!---->
<!--			<span>微信支付</span>-->
<!---->
<!--		</div>-->
<!--        <div class="alipay" onclick="getPayUrl('alipay');">-->
<!---->
<!--            <img src="./html/alipay.png" alt="" />-->
<!---->
<!--            <span>支付宝支付</span>-->
<!---->
<!--        </div>-->
<!---->
<!--	</footer>-->
    <script>

        function selectuser(){
            var uid = $("#uid").val();

            var r = /^\+?[1-9][0-9]*$/;

            var result = r.test(uid);

            if(result == false || uid.length < 3) {

                $("#error").html('!!!用户编号位数不足');

                return false;

            }else{

                window.location.href = 'pay.php?uid='+uid+'';

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

</body>

<script type="text/javascript" src="res/js/fingerprint2.min.1.4.1.js"></script>

<script type="text/javascript" src="res/js/zepto.min.js"></script>

<script>

    var pay_type = 'alipay';

    $(function(){

        $(".payment span").on("click",function(){

            $(".payment span").removeClass("active");

            $(this).addClass("active");

            pay_type = $(this).attr("data");

        });

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

            window.location.href = 'pay.php?uid='+uid+'';

            return false;

        }

    }

    $(function(){

        $(".item").on("click",function(){

            $(".item").removeClass("active");

            $(this).addClass("active");

        });

        $(".payed").on("click",function(){

            window.location.href = 'pay.php?uid=<?php echo $_SESSION['user_id'];?>';

        });


        //$("#confirm_btn").on("click",function(){

	//$("#test").on("click","#confirm_btn",function(){

        //    var uid = $("#uid").val();

        //    var r = /^\+?[1-9][0-9]*$/;

        //    var result = r.test(uid);

        //    if(result == false || uid.length != 8) {

        //        $("#error").html('!!!用户编号为8位数字');

        //        return false;

        //    }else{

        //        window.location.href = 'pay.php?uid='+uid+'';

        //        return false;

        //    }

        //});

    });

    var fp=new Fingerprint2();

    function getPayUrl(chargemoney){

        // var chargemoney = $('input[name="chargemoney"]:checked').val();

        fp.get(function(result)

            {

                if(pay_type == 'wechat'){

                    $.getJSON("wechat/wechat_h5.php?pay_type="+pay_type+"&chargemoney="+chargemoney+"&code="+result, function(d){

                        if(d.errmsg == ''){

                            window.location.href=d.mweb_url+'&redirect_url=<?php echo urlencode('http://charge.860051.cn/pay.php?mod=return&uid='.$_SESSION['user_id'].'')?>';

                            //$("#getBrandWCPayRequest").attr("href",d.mweb_url);

                        }else{

                            alert(d.errmsg);

                        }



                    });

                }else{

                    window.location.href="alipay/alipay_h5.php?pay_type="+pay_type+"&chargemoney="+chargemoney+"&code="+result;

                }

            }

        );

    }

</script>

</html>
