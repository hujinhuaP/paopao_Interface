<?php
error_reporting(0);
//ini_set('display_errors',1);            //错误信息
//ini_set('display_startup_errors',1);    //php启动错误信息
require_once 'config.php';
//支付方式：0所有，1仅微信、2仅支付宝
define("PAYOFF",0);
$res = $db->getChargeList();
$uid = isset($_GET['uid']) && $_GET['uid']!='' ? $_GET['uid'] : '';
$user_info = [
    'user_avatar'=>'',
    'user_nickname'=>'游客',
    'user_id'=>'',
    'user_coin'=>0,
    'user_free_coin'=>0
];
$_GET['error'] = '';
if($uid == ''){
    $_GET['mod'] = 'nouid';
}else{
    $user_info = $db->getUserInfo($uid);
    if($user_info == false){
        $_GET['mod'] = 'nouid';
        $_GET['error'] = '!!!用户编号不存在';
        echo json_encode(['result'=>'fail','datas'=>[]]);die;
    }else{
        $_SESSION['user_id'] = $uid;
		if(isset($_GET['mod']) && $_GET['mod']=='return'){
			
		}else{
            if(isset($_GET['mod']) && $_GET['mod']=='app'){
            }else{
                echo json_encode(['result'=>'success','datas'=>$user_info]);die;
            }
		}
    }
}
$noPay = $db->checkFirstPay($uid);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>充值</title>
    <link rel="stylesheet" href="wxpublic/style/reset.css">
    <link rel="stylesheet" href="wxpublic/style/common.css">
</head>
<body class="rel">
<div class="wrap">

    <header class="none">
        <div class="person">
            <div class="photo"><img src="" alt=""></div>
            <p><?php echo $user_info['user_nickname'];?>(ID:<span><?php echo $user_info['user_id'];?></span>)</p>
            <p>金币余额 <span class="extra"><?php echo $user_info['user_coin'] + $user_info['user_free_coin'];?></span></p>
        </div>
    </header>
    <header class="noAccount ">
        <div class="editInfo">
            <h2 class="title">充值账户</h2>
            <p class="showEdit" id="yuyinid"><img src="wxpublic/img/edit.png" alt=""><?php if($user_info['user_id'] == ''){echo '请输入您的ID';}else{echo $user_info['user_id'];}?></p>
        </div>
    </header>
    <section class="recharge">
        <h2>请选择充值金额</h2>
        <ul class="clearfix">
            <?php
            foreach($res as $k=>$v){
                $checked = $acitve = '';
                if($k == 0){
                    $acitve = 'cur';
                    $checked = 'checked';
                }
                $reward_li = '';
                $reward_day = 0;
                //首充套餐仅首充会员能够看到，否则不显示
                if($v['user_recharge_is_first'] == 'Y' && !$noPay){
                    continue;
                }
                if($v['user_recharge_is_first'] == 'Y' && $noPay){
                    $reward_li = ' 【限时】';
                }

                echo '<li class="'.$acitve.'" value_coin="'.$v['user_recharge_combo_coin'].'" value_money="'.$v['user_recharge_combo_fee'].'" value_id="'.$v['user_recharge_combo_id'].'">';
                echo '<div class="chargeInfo">';
                //首充送会员天数
                if($v['first_recharge_reward_vip_day'] > 0 && $noPay){
                    $reward_day = $v['first_recharge_reward_vip_day'];
                    echo '<div class="forVip">'.$reward_li.'首充送'.$reward_day.'天会员</div>';
                }
                echo '<p class="money">'.$v['user_recharge_combo_fee'].'元</p>';
                echo '<p>';
                echo '<img src="wxpublic/img/icon.png" alt="">';
                //VIP充值送金币
                if($v['user_recharge_vip_reward_coin'] > 0){
                    if($reward_day > 0){
                        echo $v['user_recharge_combo_coin'].'金币 <span>VIP送'.$v['user_recharge_vip_reward_coin'].'金币</span>';
                    }else{
                        echo $v['user_recharge_combo_coin'].'金币 <span>VIP送'.$v['user_recharge_vip_reward_coin'].'金币</span>';
                    }
                }else{
                    echo $v['user_recharge_combo_coin'].'金币';
                }
                echo '</p>';
                echo '</div>';
                echo '</li>';
            }
            ?>
        </ul>
    </section>
    <?php
    if(PAYOFF == 0){
        ?>
        <section class="choose">
            <h2><span class="fr">请确认是自己的账户哟</span>请选择支付方式</h2>
            <div class="way">
                <p class="alipay" onclick="changePayWay('alipay');"><img src="wxpublic/img/alipay.png" alt=""><span>支付宝支付</span><i class="check checked"></i></p>
                <p class="weixin" onclick="changePayWay('wxpay');"><img src="wxpublic/img/weixin.png" alt=""><span>微信支付</span><i class="check"></i></p>
            </div>
        </section>
        <?php
    }
    ?>
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
            <h3>确认充值</h3>
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
                    <span class="key">充值金额：</span>
                    <span class="value coins"><span id="show_coin">0</span>金币</span>
                </p>
                <p>
                    <span class="key">支付金额：</span>
                    <span class="value moneys"><span id="show_money">0.00</span>元</span>
                </p>
            </div>
            <div class="btnContainer clearfix">
                <span class="btn cancel">取消</span>
                <span class="btn confirm confirm1">确定</span>
            </div>
        </div>
    </div>
    <input type="hidden" id="selected_payid">
    <input type="hidden" id="selected_paymoney">
    <div class="diaContent diaContent2">
        <div class="noAccount">
            <h3>我的账户</h3>
            <div class="inputBox">
                <input class="account" id="accountid" name="accountid" type="text" placeholder="请输入您的ID" value="<?php echo $user_info['user_id'];?>">
            </div>
            <div class="btnContainer clearfix">
                <span class="btn cancel">取消</span>
                <span class="btn confirm confirm2">确定</span>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="wxpublic/js/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="wxpublic/js/rem.js"></script>
<script type="text/javascript" src="res/js/fingerprint2.min.1.4.1.js"></script>
<script type="text/javascript" src="res/js/zepto.min.js"></script>
<script>
	var pay_type = 'alipay';		//默认通道
    $(function() {
        function closeDialog() {
            $('.dialog').hide()
            $('.diaContent1').hide()
            $('.diaContent2').hide()
        }
        $(document).on("click", '.showEdit', (e) => {
            $('.dialog').show()
            $('.diaContent1').hide()
            $('.diaContent2').show()
        }).on('click','.cancel', () => {
            closeDialog()
        }).on('click', '.way>p',function(e) {
            e.stopPropagation()
            $(this).find('i').addClass('checked')
            $(this).siblings().find('i').removeClass('checked')
        });
        $('.recharge').on('click', 'li', function(e) {
            e.stopPropagation()
            $(this).addClass('cur').siblings("li").removeClass('cur')
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
                //提交请求验证用户是否存在
                $.getJSON("pay_v2.php?uid="+accountid+"",function(result){
                    if(result.result == 'success'){
                        $("#comfirm_account").html(result.datas.user_nickname);
                        $("#comfirm_userid").html(accountid);
                        $("#yuyinid").html('<img src="wxpublic/img/edit.png" alt="">'+accountid);
                        return false;
                    }else{
                        alert('请输入您的ID不存在');
                        return false;
                    }
                });
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
			//window.location.href = 'wxpay/callwxpay.php?pay_type=charge&id='+selected_payid;
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
            if(accountid != ''){
                $('.dialog').show();
                $('.diaContent1').show();
                $('.diaContent2').hide();
                $("#selected_payid").val(selected_id);
                $("#selected_paymoney").val(selected_money);
                $("#show_coin").html(selected_coin);
                $("#show_money").html(selected_money);
            }else{
                alert('请输入您的ID');
                return false;
            }
        });
    });
	
	var fp=new Fingerprint2();
	//pay_type支付通道类型、chargemoney产品
	function payWaySelect(chargemoney){
		fp.get(function(result){
			if(pay_type == 'alipay'){
				//支付宝
				window.location.href="alipay/alipay_h5.php?pay_type="+pay_type+"&chargemoney="+chargemoney+"&code="+result;
			}else{
				//微信
				//let wayNo = randomNum(1,2);		//随机通道
				let wayNo = 1;		//随机通道
				switch(wayNo){
					case 1:		//官方
						$.getJSON("wechat/wechat_h5.php?pay_type="+pay_type+"&chargemoney="+chargemoney+"&code="+result, function(d){
							if (d.errmsg == '') {
								window.location.href=d.mweb_url+'&redirect_url=<?php echo urlencode('http://charge.860051.cn/pay_v2.php?mod=return')?>';
							} else {
								alert(d.errmsg);
							}
						});
						break;
                    case 2:		//skycat
                        window.location.href = "skycat/skycat.php?pay_type="+pay_type+"&chargemoney="+chargemoney+"&code="+result+"";
                        break;
					case 3:		//elves
						window.location.href = "elves/elves.php?pay_type="+pay_type+"&chargemoney="+chargemoney+"&code="+result+"";
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
