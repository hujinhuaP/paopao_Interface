$(function(){
	//获取系统当前时间
    var hours = new Date().getHours();
    var minutes = (new Date().getMinutes() >= 10 )?new Date().getMinutes():'0'+ new Date().getMinutes();
    var time = hours +":"+ minutes;

	/* 登录验证
	*****************************************************/ 
	$("#loginForm :input").blur(function()
	{
		var ddObj = $(this).parent();
		ddObj.find(".formtips").removeClass("onError").text("");
		if( $(this).is("#logUname") )
		{
			var regExp = /^[a-zA-Z0-9_]{6,16}$/;
			var uname = $("#logUname").val();
			if( this.value == "" )
			{
				var errorMsg = "用户名不能为空";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( !regExp.test(uname) )
			{
				var errorMsg = "用户名格式不正确";
				$(this).next().addClass("onError").text(errorMsg);
			}
		}
		if( $(this).is("#logPwd") )
		{
			if( this.value == "" )
			{
				var errorMsg = "密码不能为空";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( this.value.length < 6 || this.value.length > 16 )
			{
				var errorMsg = "密码不正确";
				$(this).next().addClass("onError").text(errorMsg);
			}
		}
		if( $(this).is("#logCode") )
		{
			var regExp = /^[a-zA-Z0-9]{4}$/;
			var uname = $("#logCode").val();
			if( this.value == "" )
			{
				var errorMsg = "校验码不能为空";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( !regExp.test(uname) )
			{
				var errorMsg = "校验码不正确";
				$(this).next().addClass("onError").text(errorMsg);
			}
		}
	}).keyup(function()
	{
		$(this).triggerHandler("blur");
	});
	//登录提交
	$("#loginBtn").on("click", function()
	{
		$("#loginForm :input").trigger("blur");
		var numError = $("#loginForm .onError").length;
		if(numError)
		{
			return false;
		}
		// 获取传给服务端的参数
		// 用户名
		var $authtoken=$("#logUname").val();
		// 密码
		var $authsecret=$("#logPwd").val();
		// 校验码
		var $captcha=$("#logCode").val();		
		$.ajax({
			type:"post",
			url:"/app/1/login",
			data:{"authtoken":$authtoken,"authsecret":$authsecret,"captcha":$captcha},
			success:function(data){
				data=$.parseJSON(data);
				console.log(data);
				if( data.c != 1 ){
					layer.confirm(data.m,
					{
						title:false,
						area:["300px","100px"],
						btn:false,
						cancel:function()
						{
							layer.closeAll("dialog");
							// 获取当前时间序列
							var nowtime=new Date().getTime();
							// 新的图片路径
							var newUrl="/user/captcha?k="+nowtime;
							$(".code-img img").attr("src",newUrl);
						}
					});
				}else{
					$(".masklayer").fadeOut();
					$("#loginForm :input").val("");
					$("#loginForm").find(".formtips").removeClass("onError").text("");
					$("#registerForm :input").val("");
					$("#registerForm").find(".formtips").removeClass("onError").text("");
					window.location.reload();
				}
			}
		})
	});
    $(document).keydown(function(event)
    {
    	if(event.keyCode==13){ 
    	$("#loginBtn").click();
    	$("#registerBtn").click(); 
    	};
    })
	
	/* 注册验证
	*****************************************************/ 
	$("#registerForm :input").blur(function()
	{
		var ddObj = $(this).parent();
		ddObj.find(".formtips").removeClass("onError").text("");
		if( $(this).is("#regUname") )
		{
			var regExp = /^[a-zA-Z0-9_]{6,16}$/;
			var uname = $("#regUname").val();
			if( this.value == "" )
			{
				var errorMsg = "用户名不能为空";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( !regExp.test(uname) )
			{
				var errorMsg = "用户名格式不正确";
				$(this).next().addClass("onError").text(errorMsg);
			}
		}
		if( $(this).is("#regPwd") )
		{
			if( this.value == "" )
			{
				var errorMsg = "密码不能为空";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( this.value.length < 6 || this.value.length > 16 )
			{
				var errorMsg = "密码不正确";
				$(this).next().addClass("onError").text(errorMsg);
			}
		}
		if( $(this).is("#regNickname") )
		{
			if( this.value == "" )
			{
				var errorMsg = "昵称不能为空";
				$(this).next().addClass("onError").text(errorMsg);
			}
		}
		if( $(this).is("#regCode") )
		{
			var regExp = /^[a-zA-Z0-9]{4}$/;
			var uname = $("#regCode").val();
			if( this.value == "" )
			{
				var errorMsg = "校验码不能为空";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( !regExp.test(uname) )
			{
				var errorMsg = "校验码不正确";
				$(this).next().addClass("onError").text(errorMsg);
			}
		}
	}).keyup(function()
	{
		$(this).triggerHandler("blur");
	});
	//注册提交
	$("#registerBtn").on("click", function()
	{
		$("#registerForm :input").trigger("blur");
		var numError = $("#registerForm .onError").length;
		if(numError)
		{
			return false;
		}
		// 获取传给服务端的参数用户名:$authtoken 密码:$authsecret 图片校验码:$captcha 昵称$nick性别$sex 邀请码$invite_code
		var $authtoken=$("#regUname").val();
        var $authsecret=$("#regPwd").val();
        var $captcha=$("#regCode").val();
        var $op="pc";
        var $nick=$("#regNickname").val();
        var $sex="";
        var $invite_code=$("#invite_code").val();
        var sexObj=$("input[name='sex']").length;
        for(var i=0;i<sexObj;i++){
        	if( $($("input[name='sex']")[i]).is(":checked") )
        	{
        		$sex=$($("input[name='sex']")[i]).data("type");
        	}
        };
        console.log($sex);
        $.ajax(
        {
        	type:"post",
        	url:"/app/1/register",
        	data:{"authtoken":$authtoken,"authsecret":$authsecret,"captcha":$captcha,"op":$op,"nick":$nick,"sex":$sex,"invite_code":$invite_code},
        	success:function(data){
        		console.log(data);
        		data=$.parseJSON(data);
        		console.log("data");
        		// 判断不为1状态
        		if( data.c != 1)
        		{
					layer.confirm(data.m,
					{
						title:false,
						area:["300px","100px"],
						btn:false,
						cancel:function()
						{
							layer.closeAll("dialog");
        					$("#loginForm :input").val("");
        					$("#loginForm").find(".formtips").removeClass("onError").text("");
        					$("#registerForm :input").val("");
        					$("#registerForm").find(".formtips").removeClass("onError").text("");
        					// 获取当前时间序列
        					var nowtime=new Date().getTime();
        					// 新的图片路径
        					var newUrl="/user/captcha?k="+nowtime;
        					$(".code-img img").attr("src",newUrl);
						}
					});
        		}
        		else
        		{
        			layer.confirm(data.m,
        			{
        				title:false,
        				area:["300px","100px"],
        				btn:false,
        				cancel:function()
        				{
        					layer.closeAll("dialog");
        					$(".masklayer").fadeOut();
        					$("#loginForm :input").val("");
        					$("#loginForm").find(".formtips").removeClass("onError").text("");
        					$("#registerForm :input").val("");
        					$("#registerForm").find(".formtips").removeClass("onError").text("");
        				}

        			})
        		}
        	}
        })
	});

	/* 找回密码验证
	*****************************************************/ 
	// 个人中心找回密码的方式1,2之间的切换默认是邮箱
	var $type="email";
	$(".style-title span").on("click",function()
	{
		 // 获取传给服务端的type
		 $type=$(this).data("type");
		 if( !$("#"+$type).hasClass("hide") ){
		 	// 当前是收起状态
		 	$(".style-concent").removeClass("hide");
		 	$("#"+$type).addClass("hide");
		 	$(".style-title span").html("<em></em>收起");
		 	$(this).html("<em></em>展开");
		 }else{
		 	$(".style-concent").addClass("hide");
		 	$("#"+$type).removeClass("hide");
		 	$(".style-title span").html("<em></em>展开");
		 	$(this).html("<em></em>收起");
		 }
	});
	// 方式1的验证
	$("#styleOne ul li :input").blur(function()
	{
	   $(this).next().removeClass("onError").text("");
       if( $(this).is("#userNameOne") ){
	       	// 定义用户名的正则
	       	var regExp =/^[a-zA-Z0-9_]{6,16}$/;
	       	var uname = $("#userNameOne").val();
	       	// null 处理
	       	if( this.value == "" ){
	       		var errormsg="用户名不能为空";
	       		$(this).next().addClass("onError").text(errormsg);
	       	}else if( !regExp.test(uname) ){
	       		var errormsg="用户名格式不正确,请重新输入";
	       		$(this).next().addClass("onError").text(errormsg);
	       	}
       }
       // 邮箱的验证
       if( $(this).is("#emailOne") ){
	       	// 定义邮箱的正则
	       	var regExp =/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	       	var uname = $("#emailOne").val();
	       	// null 处理
	       	if( this.value == "" ){
	       		var errormsg="邮箱不能为空";
	       		$(this).next().addClass("onError").text(errormsg);
	       	}else if( !regExp.test(uname) ){
	       		var errormsg="邮箱格式不正确,请重新输入";
	       		$(this).next().addClass("onError").text(errormsg);
	       	}
       }
       //校验码的验证
       if( $(this).is("#checkCodeone") ){
	       	// 定义校验码的正则
	       	var regExp =/^[a-zA-Z0-9]{4}$/;
	       	var uname = $("#checkCodeone").val();
	       	// null 处理
	       	if( this.value == "" ){
	       		var errormsg="验证码不能为空";
	       		$(this).next().addClass("onError").text(errormsg);
	       	}else if( !regExp.test(uname) ){
	       		var errormsg="验证码不正确,请重新输入";
	       		$(this).addClass("onError").text(errormsg);
	       	}
       }
	}).keyup(function()
	{
		$(this).triggerHandler("blur");
	});
	var style_box=$(".style-box-con").length;
	for(var i=0;i<style_box;i++){
		if(!$($(".style-box-con")[i]).hasClass("hide")){
			$type=$($(".style-box-con")[i]).attr("id");
		}
	};
    $("#next-btn").on("click",function()
    {
    	if( $type=="email" )
    	{
		    // 校验码
		    var $captcha=$("#checkCodeone").val();
		    // 用户名
		    var $username=$("#userNameOne").val();
		    // 邮箱
		    var $email=$("#emailOne").val();
			$.ajax({
				type:"post",
				url:"/app/1/forgetPwd",
				data:{"type":$type,"captcha":$captcha,"username":$username,"email":$email},
				success:function(data){
					data=$.parseJSON(data);
					console.log("test");
					if(data.c != 1)
					{
	                    layer.confirm(data.m,
	                    {
	                        title:false,
	                        area:["300px","100px"],
	                        btn:false
	                    });
					}
					else
					{
						$(".top-list").addClass("two");
						$(".style-one").addClass("hide");
						$(".style-two").removeClass("hide");
						$(".top-list ul li").removeClass("white");
						$(".top-list ul li").index(2).addClass("white");
					}
				}
			});
			return;
    	}
    	else
    	{
    		// 校验码
		    var $captcha=$("#checkCodeTwo").val();
		    // 用户名
		    var $username=$("#userNameTwo").val();
		    // 手机
		    var $phone=$("#phoneTwo").val();
			$.ajax({
				type:"post",
				url:"/app/1/forgetPwd",
				data:{"type":$type,"captcha":$captcha,"username":$username,"phone":$phone},
				success:function(data){
		           data=$.parseJSON(data);
					console.log("test");
					if(data.c != 1)
					{
	                    layer.confirm(data.m,
	                    {
	                        title:false,
	                        area:["300px","100px"],
	                        btn:false
	                    });
					}
					else
					{
						$(".top-list").addClass("two");
						$(".style-one").addClass("hide");
						$(".style-two").removeClass("hide");
						$(".top-list ul li").removeClass("white");
						$(".top-list ul li").index(2).addClass("white");
					}
				}
			});
			return;
    	}
    });
	// 方式2的验证
	$("#styleTwo ul li :input").blur(function()
	{
	    $(this).next().removeClass("onError").text("");
	    // 找回密码方式2的验证
	    if( $(this).is("#userNameTwo") ){
		       	// 定义用户名的正则
		       	var regExp = /^[a-zA-Z0-9_]{6,16}$/;
		       	var uname = $("#userNameTwo").val();
		       	// null 处理
		       	if( this.value == "" ){
		       		var errormsg="用户名不能为空";
		       		$(this).next().addClass("onError").text(errormsg);
		       	}else if( !regExp.test(uname) ){
		       		var errormsg="用户名格式不正确,请重新输入";
		       		$(this).next().addClass("onError").text(errormsg);
		       	}
	    }
        // 手机号码的验证
        if( $(this).is("#phoneTwo") ){
        	$(this).parent().siblings("").removeClass("onError").text("");
   	       	// 定义手机号的正则
   	       	var regExp =/^1[3|4|5|7|8]\d{9}$/;
   	       	var uname = $("#phoneTwo").val();
   	       	// null 处理
   	       	if( this.value == "" ){
   	       		var errormsg="手机号码不能为空";
   	       		$(this).parent().siblings("").addClass("onError").text(errormsg);
   	       		// $(this).next().addClass("onError").text(errormsg);
   	       	}else if( !regExp.test(uname) ){
   	       		var errormsg="手机号码格式不正确,请重新输入";
   	       		$(this).parent().siblings("").addClass("onError").text(errormsg);
   	       	}
        }
        //校验码的验证
        if( $(this).is("#checkCodeTwo") ){
   	       	// 定义用户名的正则
   	       	var regExp =/^[a-zA-Z0-9]{4}$/;
   	       	var uname = $("#checkCodeTwo").val();
   	       	// null 处理
   	       	if( this.value == "" ){
   	       		var errormsg="验证码不能为空";
   	       		$(this).next().addClass("onError").text(errormsg);
   	       	}else if( !regExp.test(uname) ){
   	       		var errormsg="验证码不正确,请重新输入";
   	       		$(this).next().addClass("onError").text(errormsg);
   	       	}
        }
	}).keyup(function()
	{
		$(this).triggerHandler("blur");
	});
	// 第二步找回密码的验证
	$("#styleThere ul li :input").blur(function()
	{
 	    $(this).next().removeClass("onError").text("");
	    // 新密码的验证
	    if( $(this).is("#newPwd") ){
	       	var uname = $("#newPwd").val();
	       	// null 处理
	       	if( this.value == "" ){
	       		var errormsg="密码不能为空";
	       		$(this).next().addClass("onError").text(errormsg);
	       	}
       	    else if( this.value.length < 6 || this.value.length > 16 )
       		{
       			var errorMsg = "密码不正确";
       			$(this).next().addClass("onError").text(errorMsg);
       		}
	    }
	    // 确认密码的验证
        if( $(this).is("#qrPwd") ){
	       	var uname = $("#qrPwd").val();
	       	// null 处理
	       	if( this.value == "" ){
	       		var errormsg="确认密码不能为空";
	       		$(this).next().addClass("onError").text(errormsg);
	       	}
       	    else if( this.value.length < 6 || this.value.length > 16 )
       		{
       			var errorMsg = "确认密码不正确";
       			$(this).next().addClass("onError").text(errorMsg);
       		}
        }
        //校验码的验证
        if( $(this).is("#iphoneCode") ){
   	       	// 定义验证码的正则
   	       	var regExp =/^[a-zA-Z0-9]{4}$/;
   	       	var uname = $("#iphoneCode").val();
   	       	// null 处理
   	       	if( this.value == "" ){
   	       		var errormsg="验证码不能为空";
   	       		$(this).next().addClass("onError").text(errormsg);
   	       	}else if( !regExp.test(uname) ){
   	       		var errormsg="验证码不正确,请重新输入";
   	       		$(this).next().addClass("onError").text(errormsg);
   	       	}
        }
	}).keyup(function()
	{
		$(this).triggerHandler("blur");
	});
	// 第二步接口的对接
	$(".fish-btn").on("click",function()
	{
		var $code=$("#iphoneCode").val();
		var $new_pwd=$("#newPwd").val();
		var $confirm_pwd=$("#qrPwd").val();
		$.ajax({
			type:"post",
			data:{"type":$type,"code":$code,"new_pwd":$new_pwd,"confirm_pwd":$confirm_pwd},
			success:function(data){
				data=$.parseJSON(data);
				if(data.c != 1)
				{
                    layer.confirm(data.m,
                    {
                        title:false,
                        area:["300px","100px"],
                        btn:false
                    });
				}
				else
				{
					$(".top-list").removeClass("two").addClass("there");
					$(".style-one").addClass("hide");
					$(".style-two").addClass("hide");
					$(".style-there").removeClass("hide");
					$(".top-list ul li").removeClass("white");
					$(".top-list ul li").index(3).addClass("white");
				}
			}
		});
	});

	/* 直播间验证
	*****************************************************/
	//打赏主播
	$("#rewardForm :input").blur(function()
	{
		var divObj = $(this).parent().parent();
		divObj.find(".formtips").removeClass("onError").text("");
		if( $(this).is("#rewardNum") )
		{
			var regExp = /^[1-9]\d*$/;
			var ldNum = $("#rewardNum").val();
			if(ldNum == "")
			{
				var errorMsg = "请输入乐点个数";
				$(this).parent().next().addClass("onError").text(errorMsg);
			}
			else if( !regExp.test(ldNum) )
			{
				var errorMsg = "乐点数输入不正确";
				$(this).parent().next().addClass("onError").text(errorMsg);
			}
		}
	}).keyup(function()
	{
		$(this).triggerHandler("blur");
	});
	$("#rewardBtn").on("click", function()
	{
		$("#rewardForm :input").trigger("blur");
		var numError = $("#rewardForm .onError").length;
		if(numError){
			$("#rewardNum").focus();
			return false;
		}
		var money = $("#rewardNum").val();
		console.log(ROOM_INFO.rid +','+money);
		$.ajax({
			type: 'GET',
			url: '/app/1/reward?rid='+ROOM_INFO.rid+'&money='+money,
			dataType: 'json',
			async: true,
			success: function(data){
				if(data.c == 0)
				{
                    layer.confirm(data.m,
                    {
                        title:false,
                        area:["300px","100px"],
                        btn:false
                    });
				}
				else if(data.c == 1)
				{
					$('#rewardLedian').text(data.d.coin);
					$('#switchLedian').text(data.d.coin);
					$('.gift-bar li .ledian').text(data.d.coin);
				}
				else if(data.c == 10000)
				{
					$('#loginLayer').fadeIn();
				}
				else if(data.c == 20000)
				{
                    layer.confirm(data.m,
                    {
                        title:false,
                        area:["300px","100px"],
                        btn:false
                    });
				}
				$('#rewardLayer').slideToggle();
			}
		});
		$("input[name=rewardNum]").val("");
	});
	//添加直播主题
	$("#settopicForm textarea,#settopicForm input").blur(function(){
        $(this).parent().find(".formtips").removeClass("onError").text("");
		var regExp = /^\+?[1-9][0-9]*$/;
		if( $(this).is("#topicContent") ){
			if( this.value == "" ){
				var errorMsg = "请输入直播标题";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( this.value.length > 20 ){
				var errorMsg = "标题名称不能超过20个字符";
				$(this).next().addClass("onError").text(errorMsg);
			}
		}
		if( $(this).is("#liveType") ){
			if( this.value == "" ){
				var errorMsg = "请选择直播类型";
				$(this).parent().next().addClass("onError").text(errorMsg);
			}
			else
			{
				$(this).parent().next().removeClass("onError").text("");
			}
		}
		if( $(this).is("#leNum") ){
			var num = $("#leNum").val();
			if( this.value == "" ){
				var errorMsg = "请输入乐点数";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( !regExp.test(num) )
			{
				var errorMsg = "乐点数输入不正确";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( num < min || num > max)
			{
				var errorMsg = "乐点数不在范围内";
				$(this).next().addClass("onError").text(errorMsg);
			}
		}
		if( $(this).is("#liveNum") ){
			var num = $("#liveNum").val();
			if( this.value == "" ){
				var errorMsg = "";
				$(this).parent().find(".formtips").text(errorMsg);
			}
			else if( !regExp.test(num) )
			{
				var errorMsg = "乐点数输入不正确";
				$(this).parent().find(".formtips").addClass("onError").text(errorMsg);
			}
			else if( num < 4000 || num > 9999 )
			{
				var errorMsg = "乐点数不在范围内";
				$(this).parent().find(".formtips").addClass("onError").text(errorMsg);
			}
		}
	}).keyup(function(){
		$(this).triggerHandler("blur");
	});
	$(".change-type").on("click",function(){
		$(".type-list").slideToggle();
	})
	// 开始直播的类型选择
	var settings = [];
	settings[0] = 'public';                 // 类型设置
	var min='';                             // 乐点最小值
	var max='';                             // 乐点最大值
	$('.type-list ul li').on('click',function()
	{
		if( !$(this).hasClass('has-leave'))
		{
			return;
		}
		else
		{
			// 获取选择的类型
			var liveType=$(this).find('p').text();
			// 判断是否是主题直播
			if(liveType=="主题直播")
			{
				$(".fees-item").fadeOut();
			}
			else
			{
				$(".fees-item").fadeIn();
				min=$(this).data('min');
				max=$(this).data('max');
				var tollVal='输入乐点数('+min+'-'+max+'之间的数字)';
				$(".fees-item input").attr('placeholder',tollVal);
			}
			$("#liveType").val(liveType);
			settings[0]=$(this).data("type");
		}	
	});
	$("#startLiveBtn").on("click", function(){
		$("#settopicForm textarea,#settopicForm input").trigger("blur");
		var numError = $("#settopicForm .onError").length;
		if(numError){
			return false;
		}      
        settings[1] = $('#leNum').val();        // 收费标准(乐点/分钟)
       	settings[2] = $('#liveNum').val();      // 切换私密直播所需费用	
        // settings[3] = $('#topicContent').val(); // 直播标题

        ROOM_INFO.live.genlive_url = '/live/startlive/' + settings.join('/') + '?live_title=' + $('#topicContent').val();
        APP.flashPlayer.init();

        setTimeout(function () {
            $('#liveTitleLayer').addClass('hide');
        }, 800);

	});
	//提示切换至私密直播
	var sm_min = $("#ledianNum").data("min");
	var sm_max = $("#ledianNum").data("max");
	$(".sm-live-concent input").blur(function(){
        $(this).next().removeClass("onError").text("");
		if( $(this).is("#ledianNum") ){
			var regExp = /^\+?[1-9][0-9]*$/;
			var num = $("#ledianNum").val();
			if( this.value == "" ){
				$("#ledianNum").focus();
				var errorMsg = "请输入乐点数";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( !regExp.test(num) )
			{
				var errorMsg = "乐点数输入不正确";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( num < sm_min || num > sm_max)
			{
				var errorMsg = "乐点数不在范围内";
				$(this).next().addClass("onError").text(errorMsg);
			}
		}
	}).keyup(function(){
		$(this).triggerHandler("blur");
	});
	$("#lefinishBtn").on("click", function(){
		$(".sm-live-concent input").trigger("blur");
		var numError = $(".sm-live-concent .onError").length;
		if(numError){
			return false;
		}
		//成功则执行下面的操作
		var ledianNum = $("#ledianNum").val();
		ROOM_INFO.live.genlive_url = '/live/changePriLive/initiative/' + ledianNum;
	    APP.flashPlayer.init();
	    ROOM_INFO.livetype = 'private';
	    $("#ledianNum").val("");
		$("#privateChargeStandard").hide();
		$(".anchor-switch-live").removeClass("anchor-switch-live").removeClass("active");
	});

	/* 账户中心
	*****************************************************/
	//充值中心
	$("#rechargeForm :input").blur(function()
	{
		if( $(this).is("#checkbox1") )
		{
			if( this.checked )
			{
				$(this).parent().parent().next().removeClass("onError").text("");
			}
			else
			{
				var errorMsg = "请勾选并同意用户协议";
				$(this).parent().parent().next().addClass("onError").text(errorMsg);
			}
		}
	});
	//转卖中心
	var uid = "";
	$(".td-info li button").each(function(index)
	{
		$(this).on("click", function()
		{
			uid = $(this).parent().parent().find("input").val(); //用户ID
			$("#resell_nickname").html($(this).siblings('.dib').html());
		});
	});
	$("#resellForm :input,#resellForm textarea").blur(function()
	{
		var divObj = $(this).parent();
		divObj.find(".formtips").removeClass("onError").text("");
		if( $(this).is("#reselllePoint") )
		{
			var regExp = /^[0-9]*$/;
			var num = $("#reselllePoint").val();
			if( this.value =="" )
			{
				var errorMsg = "请输入乐点数";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( !regExp.test(num) )
			{
				var errorMsg = "乐点数输入不正确";
				$(this).next().addClass("onError").text(errorMsg);
			}
		}
		if( $(this).is("#resellleNote") )
		{
			if( this.value =="" )
			{
				var errorMsg = "请输入备注";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( this.value.length > 20 )
			{
				var errorMsg = "备注只能在20个字以内";
				$(this).next().addClass("onError").text(errorMsg);
			}
		}
	}).keyup(function()
	{
		$(this).triggerHandler("blur");
	});
	$("#resellBtn").on("click", function()
	{
		$("#resellForm :input").trigger("blur");
		var numError = $("#resellForm .onError").length;
		if(numError){
			return false;
		}
		//参数
		//uid //用户ID
		var coin = $("#reselllePoint").val(); //乐点数
		var note = $("#resellleNote").val();  //备注
		$.ajax({
			type: "POST",
			url: "/app/1/resell",
			data: {"uid":uid, "coin":coin, "note":note},
			dataType: "json",
			async: true,
			success: function(data)
			{
				if(data.c == 1)
				{
					layer.confirm("", {
						title: " ",
						btn: ["确定"],
						// icon: 1,
						content: data.m,
						yes:function(){
							layer.closeAll("dialog");
							$(".resell-overlay").hide();
							window.location.reload();
						}
					});
				}
				else
				{
                    layer.confirm(data.m,
                    {
                        title:false,
                        area:["300px","100px"],
                        btn:false
                    });
				}
			}
		});
		//提交数据后清空表单
		$("#resellForm :input").val("");
		$("#resellForm textarea").val("");
	});

	/* 账号设置
	*****************************************************/
	// 绑定手机的验证
	// 手机号码的验证
	$(".bind-iphone :input").blur(function()
	{
		$(this).siblings("p").removeClass("onError").text("");
		if( $(this).is("#iphoneNum") )
		{
			var regExp =/^1[3|4|5|7|8]\d{9}$/;
			var $val=$("#iphoneNum").val();
			// null处理
			if( this.value == "" )
			{
				var errmsg="手机号不能为空";
				$(this).siblings("p").addClass("onError").text(errmsg);
			}else if( !regExp.test($val)){
				var errmsg="手机号格式不正确";
				$(this).siblings("p").addClass("onError").text(errmsg);
			}
		}
		if( $(this).is("#iphoneCode") )
		{
			var regExp =/^[a-zA-Z0-9]{6}$/;
			var $val=$("#iphoneCode").val();
			// null处理
			if( this.value == "" )
			{
				var errmsg="验证码不能为空";
				$(this).siblings("p").addClass("onError").text(errmsg);
			}else if( !regExp.test($val)){
				var errmsg="验证码格式不正确";
				$(this).siblings("p").addClass("onError").text(errmsg);
			}
		}
	}).keyup(function()
	{
		$(this).triggerHandler("blur");
	});
	// 绑定邮箱的验证
	$(".bind-email :input").blur(function()
	{
       	if( $(this).is("#emailOne") ){
       		$(this).next().removeClass("onError").text("");
	       	// 定义邮箱的正则
	       	var regExp =/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	       	var uname = $("#emailOne").val();
	       	// null 处理
	       	if( this.value == "" ){
	       		var errormsg="邮箱不能为空";
	       		$(this).next().addClass("onError").text(errormsg);
	       	}else if( !regExp.test(uname) ){
	       		var errormsg="邮箱格式不正确,请重新输入";
	       		$(this).next().addClass("onError").text(errormsg);
	       	}
       	}
		if( $(this).is("#emailCode") )
		{
			$(this).siblings("p").removeClass("onError").text("");
			var regExp =/^[a-zA-Z0-9]{6}$/;
			var $val=$("#emailCode").val();
			// null处理
			if( this.value == "" )
			{
				var errmsg="验证码不能为空";
				$(this).siblings("p").addClass("onError").text(errmsg);
			}else if( !regExp.test($val)){
				var errmsg="验证码格式不正确";
				$(this).siblings("p").addClass("onError").text(errmsg);
			}
		}
	}).keyup(function()
	{
		$(this).triggerHandler("blur");
	});
	// 修改绑定邮箱第一步的验证
	$(".modifeemail :input").blur(function()
	{
       	if( $(this).is("#oneoldemail") ){
       		$(this).next().removeClass("onError").text("");
	       	// 定义邮箱的正则
	       	var regExp =/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	       	var uname = $("#oneoldemail").val();
	       	// null 处理
	       	if( this.value == "" ){
	       		var errormsg="邮箱不能为空";
	       		$(this).next().addClass("onError").text(errormsg);
	       	}else if( !regExp.test(uname) ){
	       		var errormsg="邮箱格式不正确,请重新输入";
	       		$(this).next().addClass("onError").text(errormsg);
	       	}
       	}
		if( $(this).is("#oneOldcode") )
		{
			$(this).siblings("p").removeClass("onError").text("");
			var regExp =/^[a-zA-Z0-9]{6}$/;
			var $val=$("#oneOldcode").val();
			// null处理
			if( this.value == "" )
			{
				var errmsg="验证码不能为空";
				$(this).siblings("p").addClass("onError").text(errmsg);
			}else if( !regExp.test($val)){
				var errmsg="验证码格式不正确";
				$(this).siblings("p").addClass("onError").text(errmsg);
			}
		}
	}).keyup(function()
	{
		$(this).triggerHandler("blur");
	});
	// 修改绑定邮箱第2步的验证
	$(".oldEmail :input").blur(function()
	{
       	if( $(this).is("#twoNewemail") ){
       		$(this).next().removeClass("onError").text("");
	       	// 定义邮箱的正则
	       	var regExp =/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	       	var uname = $("#twoNewemail").val();
	       	// null 处理
	       	if( this.value == "" ){
	       		var errormsg="邮箱不能为空";
	       		$(this).next().addClass("onError").text(errormsg);
	       	}else if( !regExp.test(uname) ){
	       		var errormsg="邮箱格式不正确,请重新输入";
	       		$(this).next().addClass("onError").text(errormsg);
	       	}
       	}
		if( $(this).is("#twoNewcode") )
		{
			$(this).siblings("p").removeClass("onError").text("");
			var regExp =/^[a-zA-Z0-9]{6}$/;
			var $val=$("#twoNewcode").val();
			// null处理
			if( this.value == "" )
			{
				var errmsg="验证码不能为空";
				$(this).siblings("p").addClass("onError").text(errmsg);
			}else if( !regExp.test($val)){
				var errmsg="验证码格式不正确";
				$(this).siblings("p").addClass("onError").text(errmsg);
			}
		}
	}).keyup(function()
	{
		$(this).triggerHandler("blur");
	});
	// 修改密码的修旧密码的验证
	$(".modife-pwd :input").blur(function()
	{
		$(this).next().removeClass("onError").text("");
		var pwdtext=$(this).val();
		var regExp =/^[a-zA-Z0-9]{6}$/;
		if( $(this).is("#oldPwd") )
		{
			// null处理
			if(this.value==""){
				var errmsg="旧密码不能为空";
				$(this).next().addClass("onError").text(errmsg);
			}else if( !regExp.test(pwdtext) ){
				var errmsg="旧密码格式不正确";
				$(this).next().addClass("onError").text(errmsg);
			}
		}
		if( $(this).is("#newPwd") )
		{
			// null处理
			if(this.value==""){
				var errmsg="新密码不能为空";
				$(this).next().addClass("onError").text(errmsg);
			}else if( !regExp.test(pwdtext) ){
				var errmsg="新密码格式不正确";
				$(this).next().addClass("onError").text(errmsg);
			}
		}
	}).keyup(function()
	{
		$(this).triggerHandler("blur");
	});

	/* 实名认证
	*****************************************************/
	// 完善资料的验证
	$("#idApprove :input").blur(function()
	{
		// 真实姓名的验证
		if( $(this).is("#userName") )
		{
			// 清空提示信息的内容
			$(this).next().removeClass("onError").text("");
			var regExp=/^[\u4e00-\u9fa5]{1,20}$/;
			var msgval=$("#userName").val();
			if( this.value == "" )
			{
				var errorMsg = "姓名不能为空";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( !regExp.test(msgval) )
			{
				var errorMsg = "请输入真实姓名";
				$(this).next().addClass("onError").text(errorMsg);
			}
		}
		// 手机号码的验证
		if( $(this).is("#userTel") )
		{
			// 清空提示信息的内容
			$(this).next().removeClass("onError").text("");
			var regExp=/^1[3|4|5|7|8]\d{9}$/;
			var msgval=$("#userTel").val();
			if( this.value == "" )
			{
				var errorMsg = "手机号不能为空";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( !regExp.test(msgval) )
			{
				var errorMsg = "手机号格式不正确";
				$(this).next().addClass("onError").text(errorMsg);
			}
		}
		// qq的验证
		// if( $(this).is("#userQQ") )
		// {
		// 	// 清空提示信息的内容
		// 	$(this).next().removeClass("onError").text("");
		// 	var regExp=/^\d{5,10}$/;
		// 	var msgval=$("#userQQ").val();
		// 	if( this.value == "")
		// 	{
		// 		var errorMsg = "QQ不能为空";
		// 		$(this).next().addClass("onError").text(errorMsg);
		// 	}
		// 	else if( !regExp.test(msgval) )
		// 	{
		// 		var errorMsg = "QQ格式不正确";
		// 		$(this).next().addClass("onError").text(errorMsg);
		// 	}
		// }
		// 身份证号的验证 
		if( $(this).is("#userId") )
		{
			// 清空提示信息的内容
			$(this).next().removeClass("onError").text("");
			var regExp=/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/;
			var msgval=$("#userId").val();
			if( this.value == "")
			{
				var errorMsg = "身份证不能为空";
				$(this).next().addClass("onError").text(errorMsg);
			}
			else if( !regExp.test(msgval) )
			{
				var errorMsg = "身份证格式不正确";
				$(this).next().addClass("onError").text(errorMsg);
			}
		}
	}).keyup(function()
	{
		$(this).triggerHandler("blur");
	});
	// 实名认证的上传身份证认证
	$(".authentication :input").blur(function()
	{
		// 清空提示信息的内容
		$(this).siblings("p.put_t").removeClass("onError");
		// 上传身份证的验证
		if( $(this).is("#rightPic") )
		{
			if( this.value == "" )
			{
				var errorMsg = "请上传身份证正面照";
				$(this).siblings("p.put_t").addClass("onError").text(errorMsg);
			}
		}
		if( $(this).is("#leftPic") )
		{
			if( this.value == "" )
			{
				var errorMsg = "请上传身份证反面照";
				$(this).siblings("p.put_t").addClass("onError").text(errorMsg);
			}
		}	
	});
	/* 邀请页面的验证
	*****************************************************/
	
});