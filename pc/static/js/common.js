$(function(){
	/* 首页
	*****************************************************/
	//导航菜单
	$('#headid li').on('click', function()
	{
		var index = $(this).index();
		$('#headid li a').removeClass('active');
		$(this).find('a').addClass('active');
	});
	//直播类型切换
	$('#typrList span').on('click', function()
	{
		var index = $(this).index();
		$('#typrList span').removeClass('active');
		$(this).addClass('active');
		$('#liveTypeList ul').eq(index).show().siblings().hide();
		//获取主播分类
		var liveType = $(this).data('type');
		/*$.ajax({
			type:'GET',
			url: '/app/1/getNewAnchors?anchor_class=1&live_type='+liveType,
			dataType: 'json',
			async: true,
			success: function(data)
			{
				if(data.c == 1)
				{
					var item = data.d.items;
					if(item.length == 0)
					{
						//提示暂无数据
						//分页隐藏
						$('.page-box').hdie();
					}
					else if(item.length > 0)
					{
						var tpl = '';
						for(var i=0;i<item.length;i++)
						{
							tpl='<li>' +
									'<a href="javascript:;" class="dib">' +
										'<img src="assets/images/'+ item[i].avatar +'" alt="">' +
										'<div class="nickname nickname2">' +
											'<span class="dib fl">'+ item[i].nick +'</span>' +
											'<span class="dib fr"><i></i>'+ item[i].liveonlines +'</span>' +
										'</div>';
							if(item[i].live_device == 'app')
							{
								tpl+=	'<span class="isiphone dib"></span>';
							}			
							tpl+=  '</a>' +
								'</li>';
							$('#liveTypeList').append(tpl);
						}
						//分页
						$('.page-box').empty();
						var pagefoot = '<div class="tcdPageCode"></div>';
						$('.page-box').append(pagefoot);
					    $(".tcdPageCode").createPage({
					        pageCount: data.d.pagetotal,
					        current: 1,
					        backFn: function(page){
					            $.ajax({
					            	type: "GET",
					            	url: "/user/ajaxperson?page="+page+"&live_type="+liveType,
					            	dataType: "json",
					            	async: true,
					            	success: function(data){
					            		console.log(data);
					            	}
					            });
					        }
						});
					}
				}
				else
				{
					$(".page-box").hdie();
				}
			}
		});*/
	});
	//打开登录弹窗
	$('.loginon').on('click', function()
	{
		$('#loginLayer').fadeIn();
	});
	//打开注册弹窗
	$('.registeron').on('click', function()
	{
		$('#registerLayer').fadeIn();
	});
	//关闭登录注册弹窗
	$('.over-top span').on('click', function()
	{
		$('.masklayer').fadeOut();
		$('#loginForm :input').val();
		$('#loginForm').find('.formtips').removeClass('onError').text('');
		$('#registerForm :input').val('');
		$('#registerForm').find('.formtips').removeClass('onError').text('');
	});
	//立即注册
	$('#toRegister').on('click', function()
	{
		$('#loginLayer').hide();
		$('#registerLayer').show();
		$('#loginForm :input').val('');
		$('#loginForm').find('.formtips').removeClass('onError').text('');
	});
	//立即登录
	$('#toLogin').on('click', function()
	{
		$('#registerLayer').hide();
		$('#loginLayer').show();
		$('#registerForm :input').val('');
		$('#registerForm').find('.formtips').removeClass('onError').text('');
	});
	// 登录框的校正码
	$(".code-img img").on("click",function()
	{
		// 获取当前时间序列
		var nowtime=new Date().getTime();
		// 新的图片路径
		var newUrl="/user/captcha?k="+nowtime;
		$(".code-img img").attr("src",newUrl);
	});
	// 退出登录接口的对接
	$(".exit-btn").on("click",function()
	{
		$.ajax(
		{
			type:"GET",
			url:"/app/1/logout",
			data:"",
			success:function(data){
				data=$.parseJSON(data);
				if( data.c !=1){
					layer.confirm(data.m,{
						title:"",
						area:["300px"],
						btn:["确定","取消"],
					});
				}else{
					// $(".has-login").addClass("hide");
					// $(".loginbox").removeClass("hide")
					window.location.reload();
				}
			}
		})
	});
	//我要开播
	$('#beginShow').on('click', function()
	{
		$('.showOverlay').show();
	});
	$('.show-close').on('click', function()
	{
		$('.showOverlay').hide();
	});


	/* 排行榜
	*****************************************************/
	$('#rankSongLi a').on('click', function()
	{
		var index = $(this).index();
		$('#rankSongLi a').removeClass('active');
		$(this).addClass('active');
		$('.gifts-list .rank-list > div').eq(index).show().siblings().hide();
	});
	$('#rankCaiFu a').on('click', function()
	{
		var index = $(this).index();
		$('#rankCaiFu a').removeClass('active');
		$(this).addClass('active');
		$('.wealth-list .rank-list > div').eq(index).show().siblings().hide();
	});


	/* 账户中心
	*****************************************************/
	/*tab切换*/
	$('#accountTab li').on('click', function()
	{
		var index = $(this).index();
		$('#accountTab li a').removeClass('active');
		$(this).find('a').addClass('active');
		$('#accountTabContent > div').eq(index).show().siblings().hide();
	});
	/*充值中心*/
	var rechargeMoney = '';   //充值金额
	var rechargePaystyle = '';//充值方式
	//充值金额
	$('#rechargeMoney li').on('click', function()
	{
		$(this).parent().next().removeClass('onError').text('');
		$('#enterBox li').removeClass('active').find('.selected').remove();
		$('#enterBox li dt input').val('');
		$('#enterBox li dd span').text('0.00');

		$('#rechargeMoney li').removeClass('active').find('.selected').remove();
		$(this).addClass('active').append('<span class="selected"></span>');
		$('.pay-money span').text($('#rechargeMoney li.active dd span').text());
		//传给服务器参数
		rechargeMoney = $(this).find('input[name=rechargeMoney]').val();
	});
	//自定义金额
	$('#enterBox li').on('click', function()
	{
		$('#rechargeMoney li').removeClass('active');
		$('#rechargeMoney li').find('.selected').remove();
		$(this).addClass('active').append('<span class="selected"></span>');
		$('#enterBox li dt input').trigger('blur').focus();
	});
	$('input[name=enterMoney]').blur(function()
	{
		$('#rechargeMoney').next().removeClass('onError').text('');

		$(this).parent().next().find('span').text($('input[name=enterMoney]').val());
		$('.pay-money span').text($('#enterBox li dd span').text());

		var regExp = /^([1-9]\d{0,9}|0)([.]?|(\.\d{1,2})?)$/;
		if( this.value == '')
		{
			$(this).parent().next().find('span').text('0.00');
			$('.pay-money span').text('0.00');
			var errorMsg = '请选择充值金额套餐或自定义充值';
			$('#rechargeMoney').next().addClass('onError').text(errorMsg);
		}
		else if( !regExp.test(this.value) )
		{
			var errorMsg = '金额格式不正确';
			$('#rechargeMoney').next().addClass('onError').text(errorMsg);
		}
		else if( this.value < 200 )
		{
			var errorMsg = '充值金额不能小于200';
			$('#rechargeMoney').next().addClass('onError').text(errorMsg);
		}

		//传给服务器参数
		rechargeMoney = $(this).val();

	}).keyup(function()
	{
		$(this).triggerHandler('blur');
	});	
	//支付方式
	$('#rechargePayway li').on('click', function()
	{
		$(this).parent().next().removeClass('onError').text('');

		$('#rechargePayway li').removeClass('active').empty();
		$(this).addClass('active').append('<span class="selected"></span>');
		//获取支付类型
		rechargePaystyle = $('#rechargePayway li.active').data('type');
	});
	//立即充值
	$('#rechargeBtn').on('click', function()
	{
		//判断充值金额是否选中
		if(rechargeMoney == '' && $('input[name=enterMoney]').val() == '')
		{
        	var errorMsg = '请选择充值金额套餐或自定义充值';
        	$('#rechargeMoney').next().addClass('onError').text(errorMsg);
        }
        else
        {
        	$('#rechargeMoney').next().addClass('onError').text('');
        }
		//判断支付方式是否选中
		if(rechargePaystyle == '')
		{
        	var errorMsg = '请选择支付方式';
        	$('#rechargePayway').next().addClass('onError').text(errorMsg);
        }
        else
        {
        	$('#rechargePayway').next().addClass('onError').text('');
        }

		$('#rechargeForm :input').trigger('blur');
		var numError = $('#rechargeForm .onError').length;
		if(numError){
			return false;
		}

		//参数
		console.log(rechargeMoney);    //充值金额
		console.log(rechargePaystyle); //充值方式
		var url = '';
		if(rechargePaystyle == 'alipay')
		{
			url = '/app/1/alipay?money='+rechargeMoney;
		}
		else if(rechargePaystyle == 'wxpay')
		{
			url = 'http://192.168.1.66:8004/app/1/wxPay?money='+rechargeMoney;
		}
		console.log(url);
		$.ajax({
			type: "GET",
			url: url,
			dataType: "json",
			async: true,
			success: function(data)
			{
				console.log(data);
				if(data.c == 1)
				{
					//充值成功
					layer.confirm('', {
						title:' ',
						btn: ['确定'],
						// icon: 1,
						content: data.m,
						yes:function(){
							layer.closeAll('dialog');
							$('#rechargeMoney li').removeClass('active').find('.selected').remove();
							$('#enterBox li').removeClass('active').find('.selected').remove();
							$('#rechargePayway li').removeClass('active').empty();
						}
					});
				}
				else
				{
					//充值失败
					layer.confirm('', {
						title:' ',
						btn: 0,
						// icon: 1,
						content: data.m,
						yes:function(){
							layer.closeAll('dialog');
						}
					});
				}
			}
		});
	});

	/*提现中心*/
	var withdraw_type=$("#liveBalance option[selected]").data("type");// 提现类型
	$("#liveBalance").change(function(){
      $("#liveBalance").find("option").each(function(n,i){
        if($(i).is(":selected")){
        	withdraw_type=$(i).data("type");
        }
      }); 
    });
	var cashPaystyle = "";//提现方式
	$("#cashPayway li").on("click", function()
	{
		$(this).parent().next().removeClass("onError").text("");
		$("#cashPayway li").removeClass("active").empty();
		$(this).addClass("active").append('<span class="selected"></span>');
		//获取提现类型
		cashPaystyle = $("#cashPayway li.active").data("type");
	});
	$("#cashForm1 :input").blur(function()
	{
		var divObj = $(this).parent();
		divObj.find(".formtips").removeClass("onError").text("");
		//判断提现方式是否选中
		if(cashPaystyle == "")
		{
        	var errorMsg = "请选择提现方式";
        	$("#cashPayway").next().addClass("onError").text(errorMsg);
        }
        else
        {
        	$("#cashPayway").next().removeClass("onError").text("");
        }
        if( $(this).is("#accountName") )
		{
			// var regExp = /^[a-zA-z][a-zA-Z0-9_]$/;
			var num = $("#accountName").val();
			if( this.value =="" )
			{
				var errorMsg = "请输入提现账户";
				$(this).next().addClass("onError").text(errorMsg);
				// $("#accountName").val("").attr("placeholder","0.00");
			}
			// else if( !regExp.test(num) )
			// {
			// 	var errorMsg = "提现账户不正确";
			// 	$(this).next().addClass("onError").text(errorMsg);
			// }
		}
		if( $(this).is("#lePoint") )
		{
			var regExp = /^[0-9]*$/;
			var num = $("#lePoint").val();
			if( this.value =="" )
			{
				var errorMsg = "请输入乐点数";
				$(this).next().addClass("onError").text(errorMsg);
				$("#arrivalMoney").val("").attr("placeholder","0.00");
			}
			else if( !regExp.test(num) )
			{
				var errorMsg = "乐点数输入不正确";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else
			{
				$("#arrivalMoney").val($("#lePoint").val()/10);
			}
		}
		if( $(this).is("#arrivalMoney") )
		{
			if( this.value < 100 )
			{
				var errorMsg = "提现金额大于等于100方可提现";
				$(this).next().next().addClass("onError").text(errorMsg);
			}
		}
		if( $(this).is("#checkbox2") )
		{
			if( this.checked )
			{
				$(this).parent().parent().next().removeClass("onError").text("");
			}
			else
			{
				var errorMsg = "请勾选并同意提现规则";
				$(this).parent().parent().next().addClass("onError").text(errorMsg);
			}
		}
	}).keyup(function()
	{
		$(this).triggerHandler("blur");
	});
	var account = "";//账户
	var lePoint = ""; //乐点数
	$("#cashBtn1").on("click", function()
	{
		$("#cashForm1 :input").trigger("blur");
		var numError = $("#cashForm1 .onError").length;
		if(numError){
			return false;
		}
		//验证手机
		$(".recharge-overlay").show();
		account = $("#accountName").val();//账户
		lePoint = $("#lePoint").val(); //乐点数
		// $("#cashForm1 :input").val(""); //清空表单
	});
    	
	$("#cashForm2 :input").blur(function()
	{
		$(this).parent().next().removeClass("onError").text("");
		//手机号验证
		if( $(this).is("#rechargeIphone") )
		{
			var regExp = /^1[3|4|5|7|8]\d{9}$/;
			var num = $("#rechargeIphone").val();
			if( this.value == "" )
			{
				var errorMsg = "手机号不能为空";
				$(this).parent().next().addClass("onError").text(errorMsg);
			}
			else if( !regExp.test(num) )
			{
				var errorMsg = "手机号输入不正确";
				$(this).parent().next().addClass("onError").text(errorMsg);
			}
		}
		//验证码验证
		if( $(this).is("#rechargeCaptcha") )
		{
			var regExp = /^[0-9]{6}$/;
			var code = $("#rechargeCaptcha").val();
			if( this.value == "" )
			{
				var errorMsg = "验证码不能为空";
				// console.log($(this).parent().next());
				$(this).parent().next().addClass("onError").text(errorMsg);
			}
			else if( !regExp.test(code) )
			{
				var errorMsg = "验证码输入不正确";
				$(this).parent().next().addClass("onError").text(errorMsg);
			}
		}
	}).keyup(function()
	{
		$(this).triggerHandler("blur");
	});
	$("#nextCashBtn").on("click", function()
	{
		$("#cashForm2 :input").trigger("blur");
		var numError = $("#cashForm2 .onError").length;
		if(numError){
			return false;
		}
		//参数
		
		// var cashType = $("select[name=liveBalance] option:selected").data("type"); //提现类型
		//cashPaystyle 										//提现方式
		
		var phone = $("#rechargeIphone").val();  			//手机号
		var code = $("#rechargeCaptcha").val();  			//验证码
		// console.log(account +","+ cashType +","+ cashPaystyle +","+ lePoint);
		// console.log(phone +","+ code);

		$.ajax({
			type: "POST",
			url: "/app/1/applycash",
			data: {"account":account, "withdraw_type":withdraw_type, "account_type":cashPaystyle, "coin":lePoint, "phone":phone, "code":code},
			dataType: "json",
			async: true,
			success: function(data)
			{
				console.log(data);
				if(data.c == 1)
				{
					$("#cashForm2").hide();
					$("#cashForm2").next().show();
					$("#cashForm2 :input").val("");
					$("#cashForm1 :input").val(""); //清空表单
					$("#getRechargeCode").removeClass("active").text("获取验证码");
					$("#cashPayway li").removeClass("active").empty();
					$("#lePoint").val("");
					$("#arrivalMoney").val("0.00");
				}
				else
				{
					//手机号验证失败
					layer.confirm(data.m,{
						title:false,
						btn:false,
						area:["300px","100px"],
					});
				}
			}
		});
	});
	// 体现的时候手机的验证码的获取
	$('#getRechargeCode').on('click', function()
	{
		var regExp = /^1[3|4|5|7|8]\d{9}$/;
		if($("#rechargeIphone").val() == '')
		{
			var errorMsg = "手机号不能为空";
			$(this).parent().prev().addClass("onError").text(errorMsg);
			$("#rechargeIphone").focus();
		}
		else if(!regExp.test($("#rechargeIphone").val()))
		{
			var errorMsg = "手机号输入不正确";
			$(this).parent().prev().addClass("onError").text(errorMsg);
			$("#rechargeIphone").focus();
		}
		else
		{
			var time = 60;
			var timer = null;
			var phone = $('#rechargeIphone').val();
			$.ajax({
				type: "GET",
				url: "/app/1/sendSms?type=withdraw&phone="+phone, //乐点数没传过来
				dataType: 'json',
				async: true,
				success: function(data){
					if(data.c == 1)
					{
						var _this = $("#getRechargeCode");
						_this.attr("disabled",true);
						_this.addClass("active");
						_this.text("60s");
				        timer = setInterval(function(){
				            if(time <= 0){
				                 clearInterval(timer);
				                 timer = null;
				                 _this.removeClass("active").text("重新获取验证码");
				                 _this.removeAttr("disabled");
				                 time = 60;
				                 return;
				            };
				            time--;
				           $("#getRechargeCode").text(time+"s");
				         },1000);
					}
					else
					{
						layer.confirm(data.m, {
							title:false,
							btn: false,
							area:["300px","100px"]
						});
					}
				}
			});
		}
	});
	$('#rechargeBtn2').on('click', function()
	{
		$(this).parent().parent().hide();
		$("#cashForm2").show();
		$(".recharge-overlay").hide();
	});
	/*转卖中心*/
	$('.td-info li button').each(function(index)
	{
		$(this).on('click', function()
		{
			$('.resell-overlay').show();
		});
	});
	$('.resell-close, .recharge-close').on('click', function()
	{
		$('.personal-overlay').hide();
	});
	/*账单明细*/
	$('#billTab li').on('click', function()
	{
		var index = $(this).index();
		$('#billTab li a').removeClass('active');
		$(this).find('a').addClass('active');
		$('.bill-content > div').eq(index).show().siblings().hide();
	});



});