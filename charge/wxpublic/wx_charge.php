<?php
    require_once '../config.php';
    //支付方式：0所有，1仅微信、2仅支付宝
    define("PAYOFF",1);
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
            echo json_encode(['result'=>'success','datas'=>$user_info]);die;
        }
    }
    $noPay = $db->checkFirstPay($uid);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>充值</title>
  <link rel="stylesheet" href="style/reset.css">
  <link rel="stylesheet" href="style/common.css">
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
        <p class="showEdit" id="yuyinid"><img src="img/edit.png" alt="">请输入泡泡ID</p>
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
                  $reward_li = ' firstrecharge';
              }

              echo '<li class="'.$acitve.'" value_coin="'.$v['user_recharge_combo_coin'].'" value_money="'.$v['user_recharge_combo_fee'].'" value_id="'.$v['user_recharge_combo_id'].'">';
              echo '<div class="chargeInfo">';
              //首充送会员天数
              if($v['first_recharge_reward_vip_day'] > 0 && $noPay){
                  $reward_day = $v['first_recharge_reward_vip_day'];
                  echo '<div class="forVip">送'.$reward_day.'天会员</div>';
              }
              echo '<p class="money">'.$v['user_recharge_combo_fee'].'元</p>';
              echo '<p>';
              echo '<img src="img/icon.png" alt="">';
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
            <h2><span class="fr">请确认是自己的泡泡账户哟</span>请选择支付方式</h2>
            <div class="way">
                <p class="alipay"><img src="img/alipay.png" alt=""><span>支付宝支付</span><i class="check"></i></p>
                <p class="weixin"><img src="img/weixin.png" alt=""><span>微信支付</span><i class="check"></i></p>
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
          <input class="account" id="accountid" name="accountid" type="text" placeholder="请输入泡泡ID">
        </div>
        <div class="btnContainer clearfix">
          <span class="btn cancel">取消</span>
          <span class="btn confirm confirm2">确定</span>
        </div>
      </div>
    </div>
  </div>
  <script src="js/jquery-3.2.1.min.js"></script>
  <script src="js/rem.js"></script>
  <script>
    $(function() {
      //var recharges = [[6,60],[30,300],[118,1180],[188,1880],[288,2880],[998,9980],[138,1380],[2000,20000]]
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
      // .on('click', '.confirmPay', function() {
      //   $('.dialog').show()
      //   $('.diaContent1').show()
      //   $('.diaContent2').hide()
      //
      //   //var index = $('.recharge').find('li').index($('.recharge').find('.cur'))
      //   //$('.coins').find('span').text(recharges[index][1])
      //   //$('.moneys').find('span').text(recharges[index][10])
      // });
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
              $.getJSON("wx_charge.php?uid="+accountid+"",function(result){
                    if(result.result == 'success'){
                        $("#comfirm_account").html(result.datas.user_nickname);
                        $("#comfirm_userid").html(accountid);
                        $("#yuyinid").html('<img src="img/edit.png" alt="">'+accountid);
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
            window.location.href = 'wxpay/callwxpay.php?pay_type=charge&id='+selected_payid;
            return false;
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
              alert('请输入您的泡泡ID');
              return false;
          }
      });
    });
  </script>
</body>
</html>
