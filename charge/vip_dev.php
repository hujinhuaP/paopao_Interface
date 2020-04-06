<?php
    require_once 'config_dev.php';
    $res = $db->getVipList();
    $uid = isset($_GET['uid']) && $_GET['uid']!='' ? $_GET['uid'] : '';
    $user_info = [
        'user_avatar'=>'',
        'user_nickname'=>'',
        'user_id'=>'',
        'user_member_expire_time'=>0
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>充值</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="./res/vip/main.css">
    <script type="text/javascript">
        // 高度适配说明：通过设置一个方便计算的数值，来设置根元素的font-size,所有元素通过rem为单位达到适配需求；
        //具体使用：
        //通过视觉稿宽度除以一个数获得一个方便计算的值100；
        //所有元素的高度可用{(视觉稿量的的高度)/100}rem获得适配的高度；
        !function () { function a() { document.documentElement.style.fontSize = document.documentElement.clientWidth / 7.5 + "px" } var b = null; window.addEventListener("resize", function () { clearTimeout(b), b = setTimeout(a, 300) }, !1), a() }(window);
    </script>
    <!-- 以下为openinstall集成代码，建议在html文档中尽量靠前放置，加快初始化过程 -->
    <!-- 强烈建议直接引用下面的cdn加速链接，以得到最及时的更新，我们将持续跟踪各种主流浏览器的变化，提供最好的服务；不推荐将此js文件下载到自己的服务器-->
    <script type="text/javascript" src="//res.cdn.openinstall.io/openinstall.js"></script>
    <script type="text/javascript">
        //openinstall初始化时将与openinstall服务器交互，应尽可能早的调用
        /*web页面向app传递的json数据(json string/js Object)，应用被拉起或是首次安装时，通过相应的android/ios api可以获取此数据*/
        var data = OpenInstall.parseUrlParams();//openinstall.js中提供的工具函数，解析url中的所有查询参数
        new OpenInstall({
            /*appKey必选参数，openinstall平台为每个应用分配的ID*/
            appKey : "q0a1rq",
            /*可选参数，自定义android平台的apk下载文件名，只有apk在openinstall托管时才有效；个别andriod浏览器下载时，中文文件名显示乱码，请慎用中文文件名！*/
            //apkFileName : 'com.fm.openinstalldemo-v2.2.0.apk',
            /*可选参数，是否优先考虑拉起app，以牺牲下载体验为代价*/
            preferWakeup:true,
            /*自定义遮罩的html*/
            //mask:function(){
            //  return "<div id='openinstall_shadow' style='position:fixed;left:0;top:0;background:rgba(0,255,0,0.5);filter:alpha(opacity=50);width:100%;height:100%;z-index:10000;'></div>"
            //},
            /*openinstall初始化完成的回调函数，可选*/
            onready : function() {
                <?php
                if(isset($_GET['mod']) && $_GET['mod'] == 'return') {?>
                var m = this, button = document.getElementsByClassName("payed");
                m.schemeWakeup();
                for(var i = 0;i<button.length;i++){
                    button[i].onclick = function() {
                        // window.location.href=''
                        m.wakeupOrInstall();
                        return false;
                    }
                }
                /*在app已安装的情况尝试拉起app*/
                // button.onclick = function() {
                //     m.schemeWakeup();
                //     return false;
                // }
                <?php
                }
                ?>
            }
        }, data);
    </script>
    <style>
        <?php
            if($user_info['user_member_expire_time'] == 0){
                echo 'header {background-image: url(./res/vip/images/hasrecharge.png)}';
            }else{
                echo 'header {background-image: url(./res/vip/images/recharge.png)}';
            }
        ?>
        .header-title .openvip {
            background: linear-gradient(to right, #f8e94e, #fbcf21);
            -webkit-background-clip: text;
            color: transparent;
            font-size: .24rem
        }
        .layerbox{
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
        .layerbox>div{
            width: 80%;
            height: 2rem;
            overflow: auto;
            margin: auto;
            position: absolute;
            top: 0; left: 0; bottom: 0; right: 0;
        }
        .layerbox>div>li{
            float:left;
            width:50%;
            list-style-type:none;
            text-align:center;
        }
        .layerbox>div>li>span{
            width:50%;
            background:#999;
            border-radius:8px;
            padding: .2rem .2rem .2rem .2rem;
            font-size:.3rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="header">
            <?php
                if($user_info['user_member_expire_time'] == 0){
            ?>

                    <!-- 非会员 -->
                    <div class="header-image">
                        <img src="<?php echo $user_info['user_avatar'];?>" alt="">
                        <img class="vip" src="./res/vip/images/vip-close.png" alt="">
                    </div>
                    <div class="header-title">
                        <p class="usernmae"><?php echo $user_info['user_nickname'];?></p>
                        <p>您尚未开通泡泡会员</p>
                    </div>
            <?php
                }else{
            ?>

                    <!-- 会员续费 -->

                    <div class="header-image">

                        <img src="<?php echo $user_info['user_avatar'];?>" alt="">

                        <img class="vip" src="./res/vip/images/vip-open.png" alt="">

                    </div>

                    <div class="header-title">

                        <p class="usernmae"><?php echo $user_info['user_nickname'];?></p>

                        <p class="openvip">您已开通泡泡年费会员</p>

                    </div>

            <?php

                }

            ?>

        </div>

        <?php

            if($user_info['user_member_expire_time'] > 0){

                echo '<p class="viptime">会员到期时间：'.date("Y-m-d H:i:s",$user_info['user_member_expire_time']).'</p>';

            }

        ?>

    </header>
    <div style="padding:10px;font-size:0.2rem;height:60px;color:#fff">温馨提示：充值如遇问题或投诉请拨打13125174361（服务时间工作日周一至周五9:30-19:00），非工作时间请联系微信:TTbaby02</div>
    <div class="payment">
        <span data="wechat">微信支付</span>
        <span class="active" data="alipay">支付宝支付</span>
    </div>
    <ul class="list">
        <?php
            foreach($res as $k=>$v){
                $hot = '';
                if($v['user_vip_combo_month'] == 12){
                    $hot = '<span class="hot"><img src="./res/vip/images/hot.png" alt="">热卖</span>';
                }
                if($user_info['user_member_expire_time'] == 0){
                    echo '<li onclick="getPayUrl(\''.$v['user_vip_combo_id'].'\');">
                        <div class="left">
                            <img src="./res/vip/images/2@3x.png" alt="">
                            <div>
                                <p class="title">'.cnMonth($v['user_vip_combo_month']).$hot.'</p>
                                <p class="content"><span class="price">￥'.$v['user_vip_combo_fee'].'</span><span><span class="oldprice">￥'.$v['user_vip_combo_original_price'].'</span> | 限时 '.$v['user_vip_combo_discount'].' 折</span></p>
                            </div>
                        </div>
                        <div class="right">
                            <div><p class="price">￥'.$v['user_vip_combo_average_daily_price'].'</p><p>每日仅需</p></div><img src="./res/vip/images/arrow-right.png" alt="">
                        </div>
                    </li>';
                }else{
                    echo '<li onclick="getPayUrl(\''.$v['user_vip_combo_id'].'\');">
                        <div class="left">
                            <img src="./res/vip/images/2@3x.png" alt="">
                            <div>
                                <p class="title">'.cnMonth($v['user_vip_combo_month']).$hot.'</p>
                                <p class="content"><span class="price">￥'.$v['user_vip_combo_fee'].'</span><span><span class="oldprice">￥'.$v['user_vip_combo_original_price'].'</span> | 限时 '.$v['user_vip_combo_discount'].' 折</span></p>
                            </div>
                        </div>
                        <div class="right"><span class="renew">续费</span><img src="./res/vip/images/arrow-right.png" alt=""></div>
                    </li>';
                }
            }
        ?>
    </ul>
    <p class="tips">会员权益介绍</p>
    <div class="box">
        <div class="fix">
            <div class="content">
                <img src="./res/vip/images/commont.png" alt="">
                <p>无线畅聊</p>

                <p class="explain">免费文字聊天条数无限制</p>

            </div>

        </div>

        <div class="fix">

            <div class="content">

                <img src="./res/vip/images/see.png" alt="">

                <p>私密视频大折扣</p>

                <p class="explain">尊享主播收费私密小视频会员折扣</p>

            </div>

        </div>

        <div class="fix">

            <div class="content">

                <img src="./res/vip/images/hi.png" alt="">

                <p>打招呼人数提升</p>

                <p class="explain">一键打招呼人数由10位提升至20位</p>

            </div>

        </div>

        <div class="fix">

            <div class="content">

                <img src="./res/vip/images/rank.png" alt="">

                <p>排名靠前</p>

                <p class="explain">整体排名靠前更获女神青睐</p>

            </div>

        </div>

        <div class="fix">

            <div class="content">

                <img src="./res/vip/images/X2@3x.png" alt="">

                <p>奖励双倍领</p>

                <p class="explain">所有签到包括累计签到奖励X2</p>

            </div>

        </div>

        <div class="fix">

            <div class="content">

                <img src="./res/vip/images/vip.png" alt="">

                <p>会员VIP标识尊享</p>

                <p class="explain">泡泡尊贵会员VIP象征标识</p>
                <p></p>

            </div>

        </div>

    </div>

    <p class="more">—— 更多特权敬请期待 ——</p>

    <?php



    if(isset($_GET['mod'])){



        echo '<div class="layerbox">';



        if($_GET['mod'] == 'return') {



            echo '<div id="returnApp"><li><span class="payed">已完成付款</span></li><li><span class="payed">取消付款</span></li></div>';
        }elseif($_GET['mod'] == 'nouid'){
            echo '<div><div id="error" style="font-size:.16rem;color:#ff6687">'.$_GET['error'].'</div><li><span><input style="font-size:.2rem;height:.4rem;" type="number" maxlength="8" id="uid" name="uid" placeholder="输入用户编号"></span></li><li id="test"><span id="confirm_btn" style="cursor:pointer;" onclick="selectuser()">确认</span></li></div>';
        }
        echo '</div>';
    }
    ?>
<div id="abc"></div>
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
    });

    function selectuser(){

        var uid = $("#uid").val();

        var r = /^\+?[1-9][0-9]*$/;

        var result = r.test(uid);

        if(result == false || uid.length != 8) {

            $("#error").html('!!!用户编号为8位数字');

            return false;

        }else{
            window.location.href = 'vip.php?uid='+uid+'';
            return false;
        }
    }

    var fp=new Fingerprint2();
    function getPayUrl(chargemoney){
        fp.get(function(result){
                if(pay_type == 'wechat'){
                    let wxpay_type = randomNum(4,4);
                    switch(wxpay_type){
                        case 1:       //微信官方H5
                            $.getJSON("wechat/wechat_h5.php?pay_type=vip&chargemoney="+chargemoney+"&code="+result, function(d){
                                if (d.errmsg == '') {
                                    $("#abc").html(d.mweb_url+'&redirect_url=<?php echo urlencode('http://charge.860051.cn/vip.php?mod=return&uid='.$_SESSION['user_id'].'')?>');
                                } else {
                                    alert(d.errmsg);
                                }
                            });
                            break;
                        case 2:       //elves H5
                            window.location.href = "elves/elves.php?pay_type=vip&chargemoney="+chargemoney+"&code="+result+"";
                            break;
                        case 3:       //quanming H5
                            window.location.href = "quanmin/quanmin.php?pay_type=vip&chargemoney="+chargemoney+"&code="+result+"";
                            break;
                        case 4:     //318211
                            window.location.href = "318211/318211.php?pay_type=vip&chargemoney="+chargemoney+"&code="+result+"";
                            break;
                        default:
                            break;
                    }
                }else{
                    window.location.href="alipay/alipay_h5.php?pay_type=vip&chargemoney="+chargemoney+"&code="+result;
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