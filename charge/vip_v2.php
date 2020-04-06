<?php
error_reporting(0);
//ini_set('display_errors',1);            //错误信息
//ini_set('display_startup_errors',1);    //php启动错误信息
require_once 'config.php';
//支付方式：0所有，1仅微信、2仅支付宝
define("PAYOFF",0);
$res = $db->getVipList();
$uid = isset($_GET['uid']) && $_GET['uid']!='' ? $_GET['uid'] : '';
$user_info = [
    'user_avatar'=>'',
    'user_nickname'=>'游客',
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
        exit('<script>alert(\'用户编号不存在\');</script>');
        //echo json_encode(['result'=>'fail','datas'=>[]]);die;
    }else{
        $_SESSION['user_id'] = $uid;
        //echo json_encode(['result'=>'success','datas'=>$user_info]);die;
    }
}
$noPay = $db->checkFirstPay($uid);
//会员特权
$member_special_power = [
    ['image'=>'wxpublic/img/vip1.png','desc1'=>'尊贵勋章','desc2'=>'专属勋章显示'],
    ['image'=>'wxpublic/img/vip2.png','desc1'=>'无限畅聊','desc2'=>'私聊永久免费'],
    ['image'=>'wxpublic/img/vip3.png','desc1'=>'双倍奖励','desc2'=>'签到、任务双倍奖励'],
    ['image'=>'wxpublic/img/vip4.png','desc1'=>'精选匹配','desc2'=>'优先匹配高颜值美女'],
    ['image'=>'wxpublic/img/vip5.png','desc1'=>'查看更多私密信息','desc2'=>'私密照片免费看、私密视频尊享5折'],
    ['image'=>'wxpublic/img/vip6.png','desc1'=>'更多特权','desc2'=>'敬请期待']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>VIP办理</title>
    <link rel="stylesheet" href="wxpublic/style/reset.css">
    <link rel="stylesheet" href="wxpublic/style/common.css">
    <link rel="stylesheet" href="wxpublic/style/vip.css">
</head>
<body class="rel">
<div class="wrap">
    <?php if($user_info['user_id'] > 0){?>
        <?php if($user_info['user_member_expire_time'] == 0){ ?>
            <header class="isVip noVip">
                <div class="person">
                    <div class="photo"><img src="<?php echo $user_info['user_avatar'];?>" alt=""></div>
                    <p class="name"><?php echo $user_info['user_nickname'];?></p>
                    <p>您尚未开通VIP会员</p>
                </div>
            </header>
        <?php }else{ ?>
            <header class="isVip">
                <div class="person">
                    <div class="photo"><img src="<?php echo $user_info['user_avatar'];?>" alt=""></div>
                    <p class="name"><?php echo $user_info['user_nickname'];?></p>
                    <p>您已开通会员</p>
                    <p>到期时间：<?php echo date("Y-m-d",$user_info['user_member_expire_time']);?></p>
                </div>
            </header>
        <?php }?>
    <?php }else{ ?>

        <header class="noAccount">
            <div class="editInfo">
                <h2 class="title">我的账户</h2>
                <p class="showEdit" id="yuyinid"><img src="wxpublic/img/edit.png" alt="">请输入您的ID</p>
            </div>
        </header>
    <?php } ?>
    <section class="recharge">
        <h2><span class="fr checkVip">查看会员功能特权>></span>请选择VIP类型</h2>
        <ul class="clearfix">
            <?php
            $default_money = 0;
            $default_org_money = 0;
            $default_id = 0;
            $default_type = '';
            foreach($res as $k=>$v){
                $active = '';
                if(count($res) == ($k+1)){
                    $active = 'cur';
                    $default_money = bcmul($v['user_vip_combo_fee'],1);
                    $default_org_money = bcmul($v['user_vip_combo_original_price'],1);
                    $default_id = $v['user_vip_combo_id'];
                    $default_type = cnMonth($v['user_vip_combo_month']);
                }

                echo '<li onclick="changeMonths(this);" class="'.$active.'" datas_type="'.cnMonth($v['user_vip_combo_month']).'" datas_org_price="'.bcmul($v['user_vip_combo_original_price'],1).'" datas_price="'.bcmul($v['user_vip_combo_fee'],1).'" datas_id="'.$v['user_vip_combo_id'].'">';
                echo '<div class="chargeInfo"><div class="forVip">限时'.$v['user_vip_combo_discount'].'折</div><p class="money">'.cnMonth($v['user_vip_combo_month']).'</p><p>每日仅需¥'.$v['user_vip_combo_average_daily_price'].'</p></div>';
                echo '</li>';
            }
            ?>
        </ul>
        <p class="price">价格<span class="now" id="txt_price">¥<?php echo $default_money;?>.<em>00</em></span><del class="ago" id="txt_org_price">¥<?php echo $default_org_money;?>.<em>00</em></del></p>
    </section>
    <?php if(PAYOFF == 0){ ?>
        <section class="choose">
            <h2><span class="fr">请确认是自己的账户哟</span>请选择支付方式</h2>
            <div class="way">
                <p class="alipay" onclick="changePayWay('alipay');"><img src="wxpublic/img/alipay.png" alt=""><span>支付宝支付</span><i class="check checked"></i></p>
                <p class="weixin" onclick="changePayWay('wxpay');"><img src="wxpublic/img/weixin.png" alt=""><span>微信支付</span><i class="check"></i></p>
            </div>
        </section>
    <?php } ?>
    <section class="tips">
        <h2>温馨提示</h2>
        <div class="tipList">
            <p>1.充值如遇问题或投诉请拨13125174361</p>
            <p>2.服务时间：周一至周五9:30-19:00</p>
            <p>3.非工作日请联系微信客服：<span>TTbaby02</span></p>
        </div>
    </section>
    <a href="javascript:;" id="submitPay" class="btn confirmPay">确认支付</a>
</div>
<div class="dialog">
    <div class="mask"></div>
    <div class="diaContent diaContent1">
        <div class="hasAccount">
            <h3>确认购买</h3>
            <p class="tip">此笔充值为虚拟商品，请核对账户无误后确认充值</p>
            <div class="order">
                <p>
                    <span class="key">充值账户：</span>
                    <span class="value id" id="comfirm_account"><?php echo $user_info['user_nickname'];?></span>
                </p>
                <p>
                    <span class="key">用户ID：</span>
                    <span class="value id" id="comfirm_userid"><?php echo $user_info['user_id'];?></span>
                </p>
                <p>
                    <span class="key">购买类型：</span>
                    <span class="value coins" id="show_type"><?php echo $default_type;?></span>
                </p>
                <p>
                    <span class="key">支付金额：</span>
                    <span class="value moneys"><span id="show_money"><?php echo bcmul($default_money,1,2);?></span>元</span>
                </p>
            </div>
            <div class="btnContainer clearfix">
                <span class="btn cancel">取消</span>
                <span class="btn confirm confirm1">确定</span>
            </div>
        </div>
    </div>
    <div class="diaContent diaContent2">
        <div class="noAccount">
            <h3>我的账户</h3>
            <div class="inputBox">
                <input class="account" type="text" id="accountid" name="accountid" placeholder="请输入您的ID" value="<?php if(isset($_GET['uid']) && $_GET['uid']!=''){echo $_GET['uid'];}?>">
            </div>
            <div class="btnContainer clearfix">
                <span class="btn cancel">取消</span>
                <span class="btn confirm confirm2">确定</span>
            </div>
        </div>
    </div>

    <div class="diaContent diaContent3">
        <span class="close"><img src="wxpublic/img/close.png" alt=""></span>
        <h3>会员功能特权</h3>
        <ul class="vips clearfix">
            <?php
            foreach($member_special_power as $k=>$v){
                echo '<li><img src="'.$v['image'].'" alt=""><p class="desc1">'.$v['desc1'].'</p><p class="desc2">'.$v['desc2'].'</p></li>';
            }
            ?>
        </ul>
    </div>
</div>
<input type="hidden" id="selected_payid" value="<?php echo $default_id;?>">
<input type="hidden" id="selected_paymoney" value="<?php echo $default_money;?>">
<script type="text/javascript" src="res/js/fingerprint2.min.1.4.1.js"></script>
<script type="text/javascript" src="res/js/zepto.min.js"></script>
<script type="text/javascript" src="wxpublic/js/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="wxpublic/js/rem.js"></script>
<script>
	var pay_type = 'alipay';		//默认通道
    $(function() {
        function closeDialog() {
            $('.dialog').hide()
            $('.diaContent').hide()
        }
        $(document).on("click", '.showEdit', (e) => {
            $('.dialog').show()
            $('.diaContent2').show().siblings('.diaContent').hide()
        }).on('click','.cancel', () => {
            closeDialog()
        }).on('click', '.way>p',function(e) {
            e.stopPropagation()
            $(this).find('i').addClass('checked')
            $(this).siblings().find('i').removeClass('checked')
        }).on('click', '.confirmPay', function() {
            $('.dialog').show()
            $('.diaContent1').show().siblings('.diaContent').hide()
            var index = $('.recharge').find('li').index($('.recharge').find('.cur'))
        }).on('click', '.checkVip', function() {
            $('.dialog').show()
            $('.diaContent3').show().siblings('.diaContent').hide()
        }).on("click", '.close', function(){
            closeDialog()
        })
        $('.recharge').on('click', 'li', function(e) {
            e.stopPropagation()
            $(this).addClass('cur').siblings("li").removeClass('cur')
        });

        //确认显示支付信息
        $("#submitPay").on("click",function(){
            var cur = $(".cur");
            var selected_id,selected_coin,selected_money;
            $.each(cur,function(index,item){
                selected_id = $(item).attr("value_id");
                selected_coin = $(item).attr("value_coin");
                selected_money = $(item).attr("value_money");
            });
            let accountid = $("#accountid").val();
            if(accountid == ''){
                alert('请输入您的ID');
                return false;
            }
        });

        //确认支付
        $(".confirm1").on("click",function(){
            closeDialog();
            $("#accountid").val('');
            let selected_payid = $("#selected_payid").val();
            $("#selected_payid").val('');
            $("#selected_paymoney").val('');
			payWaySelect(selected_payid);
            //window.location.href = 'wxpay/callwxpay.php?pay_type=vip&id='+selected_payid;
        });

        //输入ID
        $(".confirm2").on("click",function(){
            if(!$('.account').val()) {
                $('.inputBox').addClass('warn')
                return false;
            }
            $('.inputBox').removeClass('warn')
            closeDialog()
            let accountid = $("#accountid").val();
            if(accountid!=''){
                window.location.href='vip_v2.php?uid='+accountid;
            }
        });
    });

    //切换商品
    function changeMonths(obj){
        let datas_price = $(obj).attr("datas_price");
        let datas_org_price = $(obj).attr("datas_org_price");
        let datas_id = $(obj).attr("datas_id");
        let datas_type = $(obj).attr("datas_type");
        //传递值
        $("#selected_payid").val(datas_id);
        $("#selected_paymoney").val(datas_price);
        //显示当前商品价格
        $("#txt_price").html('¥'+datas_price+'.<em>00</em>');
        $("#txt_org_price").html('¥'+datas_org_price+'.<em>00</em>');
        //显示弹出窗口信息
        $("#show_type").html(datas_type);
        $("#show_money").html(parseFloat(datas_price).toFixed(2));
    }
	
	var fp=new Fingerprint2();
	//pay_type支付通道类型、chargemoney产品
	function payWaySelect(chargemoney){
		fp.get(function(result){
			if(pay_type == 'alipay'){
				//支付宝
				window.location.href="alipay/alipay_h5.php?pay_type=vip&chargemoney="+chargemoney+"&code="+result;
			}else{
				//微信
				let wayNo = randomNum(1,2);		//随机通道
				switch(wayNo){
					case 1:		//官方
						$.getJSON("wechat/wechat_h5.php?pay_type=vip&chargemoney="+chargemoney+"&code="+result, function(d){
							if (d.errmsg == '') {
								window.location.href=d.mweb_url+'&redirect_url=<?php echo urlencode('http://charge.860051.cn/vip_v2.php?mod=return')?>';
							} else {
								alert(d.errmsg);
							}
						});
						break;
                    case 2:		//skycat
                        window.location.href = "skycat/skycat.php?pay_type=vip&chargemoney="+chargemoney+"&code="+result+"";
                        break;
                    case 3:		//elves
                        window.location.href = "elves/elves.php?pay_type=vip&chargemoney="+chargemoney+"&code="+result+"";
                        break;
					default:
						alert('微信支付通道维护中...');return false;
						break;
				}
			}
		});
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
	
	//切换通道
    function changePayWay(payway){
        pay_type = payway;
    }
</script>
</body>
</html>
