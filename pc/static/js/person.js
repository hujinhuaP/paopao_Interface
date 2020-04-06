$(function(){
	/*个人中心
	****************************************************/
	// 左侧的鼠标点解的效果
	$('.person-tab-block li a').on('click',function()
	{
		$('.person-tab-block li a').removeClass('active');
		$(this).addClass('active');
	});
	// $('.person-tab-block li a').hover(function()
	// {
	// 	$('.person-tab-block li a').removeClass('active');
	// 	$(this).addClass('active');
	// 	// $(this).css("backgroundColor","#f060a1");
	// });
	// 我的资料的右侧切换效果
	$('.my-profile ul li').on('click',function()
	{
		$('.my-profile ul li').removeClass('active');
		$(this).addClass('active');
		// 获取下标数
		var $index=$(this).index();
		$('.profile-box > div').addClass('hide');
		$('.profile-box > div').eq($index).removeClass('hide');
	});
	// 个人中心上传头像
	$(".keep-img").on("click",function()
	{    
		$.ajax({
			url: "/upload/index.php",
			type: "post",
			data:new FormData(document.getElementById("uploadImg")),
			contentType: false, //必须  告诉jq不要去设置content-type请求头
			processData: false, //必须  告诉jq不要去请求发送的数据
			success: function(data){
				data=$.parseJSON(data);
				var newurl="/upload/"+data.url;
				$(".uploadimg img").attr("src",newurl);
			}
		});
	})
	// 基本资料的保存
	// 传给服务端的用户昵称
	var $userNick="";
	$('#myProfile :input').blur(function()
	{
		// 昵称不能为空
		if( $(this).is("#userNick") ){
			$userNick=$('#userNick').val();
			if( this.value=="" )
			{
				var errorMsg = "昵称不能为空";
				$(this).next().removeClass("hide").text(errorMsg);
				return;
			}
			else
			{
				$(this).next().addClass("hide").text("");
			}
		}
	});
	$(".kep-info").on("click",function()
	{
		$('#myProfile :input').trigger("blur");
		// 获取男女
		var $sexlen=$("#myProfile :input[type='radio']").length;
		var $sex="";
		for(var i=0;i<$sexlen;i++){
			if($($("#myProfile :input[type='radio']")[i]).is(":checked")){
        		$sex=$($("#myProfile :input[type='radio']")[i]).val();
			}
		}
		// 用户昵称
		$userNick=$('#userNick').val();
		// 简介
		var $intro=$("#intro").val();
		// 用户基本资料地址的获取
		var $cmbProvince=$("#cmbProvince").val();
		var $cmbCity=$("#cmbCity").val();
		if($cmbProvince=='--请选择--')
		{
			$cmbProvince=" ";
		};
		if($cmbCity=='--请选择--'&&$cmbCity=='地级市')
		{
			$cmbCity=" ";
		};
		if($cmbCity==" " || $cmbProvince==" "){
			$(".profile-con ul li#address").find("p").removeClass("hide").text("地址不能为空");
			return;
		}else{
			var $addr=$cmbProvince+"-"+$cmbCity;
		}
		// 邀请码的获取
		var $userInvite=$("input[name='userInvite']").val();
		// 获取签名
		var $userHopit=$("textarea[name='userHopit']").val();
		$.ajax({
			type:"post",
			url:"/app/1/saveUserInfo",
			data:{"nick":$userNick,"intro":$intro,"addr":$addr,"invite_code":$userInvite},
			success:function(data){
				// data=$.parseJSON(data);
				console.log(data);
				if(data.c !=1){
					layer.confirm(data.m,{
						title:false,
						btn:"",
						content:data.m,
						yes:function(){

						}
					})
				}else{
					layer.confirm("",{
						title:"",
						btn:"",
						content:data.m,
						yes:function(){

						}
					})
				}
			}
		})
	});
	// 传给服务端的uid
	var $uid="" ;
	// 关注接口对接
	$(".is-atten").on("click",function()
	{
		$uid=$(this).data("type");
		$.ajax({
			type:"GET",
			url:"/app/1/addFollow?uid="+$uid,
			success:function(data){
				data=$.parseJSON(data);
				// 判断状态不是1
				if( data.c !=1 ){
					layer.confirm(data.m,{
						title:false,
						area:["300px","100px"],
						btn:false
					});
				}
				else{
					// 关注成功后页面变化
					window.location.reload();
					// $(this).parent().find("div").removeClass("hide");
					// $(this).addClass("hide");
				}
			}
		});
	});
	// 取消关注接口对接
	$(".not-atten").on("click",function()
	{
		$uid=$(this).data("type");
		$.ajax({
			type:"GET",
			url:"/app/1/cancelFollow?uid="+$uid,
			success:function(data){
				data=$.parseJSON(data);
				// 判断状态不是1
				if( data.c == 0 ){
					layer.confirm(data.m,{
						title:false,
						area:["300px","100px"],
						btn:false
					});
				}
				else{
					// 取消关注成功后页面变化
					window.location.reload();
				}
			}
		});
	});
	/* 我的设置页面
	*****************************************************/
	// 绑定是手机
	$("#bind").on("click", function () {
	    layer.confirm(" ", {
	        title: ["绑定手机", "font-size:16px"],
	        type: 1,
	        area: ["382px", "304px"],
	        content: $(".bind-iphone"),
	        btn: "绑定",
	        yes:function(index)
	        {
	        	// 传给服务端的参数手机号
	        	var $phone=$("#iphoneNum").val();
	        	// 验证码
	        	var $mcode=$("#iphoneCode").val();
	        	$.ajax({
	        		type:"post",
	        		url:"/app/1/bindMobile",
	        		data:{"phone":$phone,"mcode":$mcode},
	        		success:function(data){
	        			data=$.parseJSON(data);
	        			if($phone == "" || $mcode == ""){
	        				return;
	        			}
        				else{
		        			if( data.c == 0 ){
		        				layer.confirm(data.m,{
		        					title:false,
		        					area:["300px","100px"],
		        					btn:false
		        				})
		        			}
		        			else{
		        				layer.close(index);
		        				layer.confirm("绑定手机号码成功",{
		        					title:"",
		        					btn:["确定","取消"],
		        					yes:function(){
		        						layer.closeAll('dialog');
		        						window.location.reload();
		        						// $("#hasbindIphone").text($phone);
		        						// $("#bindPhone .bill-r > div > p").removeClass("hide");
		        						// $("#bindPhone .bill-r > div > p#bind").addClass("hide");
		        					}
		        				})	
		        			}
        				}
	        		}
	        	})
	        }
	    })
	});
	// 修改绑定手机
	$("#modifrIphone").on("click", function () {
	    layer.confirm(" ", {
	        title: ["更换绑定手机", "font-size:16px"],
	        type: 1,
	        area: ["382px", "304px"],
	        content: $(".modife"),
	        btn: ["下一步"],
	        yes: function (index) {
	        	// layer.close(index);
	        	// 传给服务端的参数旧手机号
	        	var $phone=$("#oldNum").val();
	        	// 验证码
	        	var $code=$("#oldIphoneCode").val();
	        	$.ajax({
	        		type:"post",
	        		url:"/app/1/changeMobile",
	        		data:{"code":$code},
	        		success:function(data){
	        			data=$.parseJSON(data);
	        			if($phone == "" || $code == ""){
	        				return;
	        			}else{
		        			if( data.c !=1 ){
		        				layer.confirm(data.m,{
		        					title:false,
		        					area:["300px","100px"],
		        					btn:false
		        				})
		        			}
		        			else{
		        				layer.close(index);
		        				layer.confirm(" ", {
		        				    title: ["验证手机", "font-size:16px"],
		        				    type: 1,
		        				    area: ["382px", "304px"],
		        				    content: $(".new-iphone"),
		        				    btn: ["验证"],
		        				    yes: function (index) {
		        				        // 传给服务端的参数旧手机号
				        	        	var $newphone=$("#newNum").val();
				        	        	// 验证码
				        	        	var $newcode=$("#newIphoneCode").val();
				        	        	$.ajax({
				        	        		type:"post",
				        	        		url:"/app/1/changeMobile2",
				        	        		data:{"old_code":$code,"new_phone":$newphone,"code":$newcode},
				        	        		success:function(data){
				        	        			data=$.parseJSON(data);
				        	        			if($newphone == "" || $newcode == ""){
				        	        				return;
				        	        			}else{
			    				        			if( data.c !=1 ){
			    				        				layer.confirm(data.m,{
			    				        					title:false,
			    				        					area:["300px","100px"],
			    				        					btn:false
			    				        				})
			    				        			}
			    				        			else{
			    				        				layer.close(index);
			    				        				layer.confirm("修改绑定手机号码成功",{
			    				        					title:"",
			    				        					btn:["确定","取消"],
			    				        					yes:function(){
			    				        						layer.closeAll('dialog');
			    				        						window.location.reload();
			    				        					}
			    				        				})	
			    				        			}
				        	        			}
				        	        		}
				        	        	})

		        				    }
		        				})			
		        			}
	        			}
	        		}
	        	})
	        }
	    })
	}); 
    // 绑定邮箱
    $("#bindEmail").on("click",function(){
    	layer.confirm(" ", {
    	    title: ["绑定邮箱", "font-size:16px"],
    	    type: 1,
    	    area: ["382px", "304px"],
    	    content: $(".bind-email"),
    	    btn: "绑定",
    	    yes:function(index)
    	    {
    	    	// 传给服务端的参数邮箱
    	    	var $email=$("#emailOne").val();
    	    	// 验证码
    	    	var $mcode=$("#emailCode").val();
    	    	$.ajax({
    	    		type:"post",
    	    		url:"/app/1/bindMail",
    	    		data:{"email":$email,"code":$mcode},
    	    		success:function(data){
	        			data=$.parseJSON(data);
	        			if($email == "" || $mcode == ""){
	        				return;
	        			}else{
		        			if( data.c !=1 ){
		        				layer.confirm(data.m,{
		        					title:false,
		        					area:["300px","100px"],
		        					btn:false
		        				})
		        			}
		        			else{
		        				layer.close(index);
		        				layer.confirm("绑定邮箱成功",{
		        					title:"",
		        					btn:["确定","取消"],
		        					yes:function(){
		        						layer.closeAll('dialog');
		        						window.location.reload();
		        					}
		        				})	
		        			}
	        			}
    	    		}
    	    	})
    	    }
    	})
    })
    // 更换绑定邮箱
    $("#modifrEmail").on("click",function(){
	    layer.confirm(" ", {
	        title: ["更换绑定邮箱", "font-size:16px"],
	        type: 1,
	        area: ["382px", "304px"],
	        content: $(".modifeemail"),
	        btn: ["下一步"],
	        yes: function (index) {
	        	// layer.close(index);
	        	// 传给服务端的参数旧邮箱
	        	var $oldemail=$("#oldemail").val();
	        	// 验证码
	        	var $oneOldcode=$("#oneOldcode").val();
	        	$.ajax({
	        		type:"post",
	        		url:"/app/1/changeMail",
	        		data:{"code":$oneOldcode},
	        		dataType:"json",
	        		success:function(data){
	        			if( $oldemail == "" || $oneOldcode == ""){
	        				return;
	        			}else{
		        			if( data.c == 0 ){
		        				layer.confirm(data.m,{
		        					title:false,
		        					area:["300px","100px"],
		        					btn:false
		        				})
		        			}
		        			else if( data.c == 1 ){
		        				layer.close(index);
		        				layer.confirm(" ", {
		        				    title: ["验证邮箱", "font-size:16px"],
		        				    type: 1,
		        				    area: ["382px", "304px"],
		        				    content: $(".oldEmail"),
		        				    btn: ["验证"],
		        				    yes: function (index) {
		        				        // 传给服务端的参数新邮箱的地址
				        	        	var $newEmail=$("#twoNewemail").val();
				        	        	// 验证码
				        	        	var $newcode=$("#twoNewcode").val();
				        	        	$.ajax({
				        	        		type:"post",
				        	        		url:"/app/1/changeMail2",
				        	        		data:{"old_code":$oneOldcode,"new_email":$newEmail,"code":$newcode},
				        	        		dataType:"json",
				        	        		success:function(data){
				        	        			if($newEmail == "" || $newcode == ""){
				        	        				return;
				        	        			}else{
					        	        			if( data.c == 0 ){
					        	        				console.log(data.m);
					        	        				layer.confirm(data.m,{
					        	        					title:false,
					        	        					area:["300px","100px"],
					        	        					btn:false
					        	        				})
					        	        			}
					        	        			else if( data.c == 1 ){
					        	        				layer.close(index);
					        	        				layer.confirm(data.m,{
					        	        					title:"",
					        	        					btn:["确定"],
					        	        					yes:function(){
					        	        						layer.closeAll('dialog');
					        	        						window.location.reload();
					        	        						// $("#hasbindIphone").text($phone);
					        	        						// $("#bindPhone .bill-r > div > p").removeClass("hide");
					        	        						// $("#bindPhone .bill-r > div > p#bind").addClass("hide");
					        	        					}
					        	        				})	
					        	        			}
				        	        			}
				        	        		}
				        	        	})

		        				    }
		        				})			
		        			}
	        			}
	        		}
	        	})
	        }
	    })
    })
    // 修改密码
	$("#modifePwd").on("click", function () {
	    layer.confirm(" ", {
	        title: ["修改密码", "font-size:16px"],
	        type: 1,
	        area: ["382px", "304px"],
	        content: $(".modife-pwd"),
	        btn: "完成",
	        yes:function(index){
	        	// 传给服务端的参数
	        	var $old_pwd=$("#oldPwd").val();
	        	var $password=$("#newPwd").val();
	        	$.ajax({
	        		type:"post",
	        		url:"/app/1/modifyPwd",
	        		data:{"old_pwd":$old_pwd,"password":$password},
	        		success:function(data){
	        			data=$.parseJSON(data);
	        			if($old_pwd == "" || $password == ""){
	        				return;
	        			}else{
		        			if( data.c !=1 ){
		        				layer.confirm(data.m,{
		        					title:false,
		        					area:["300px","100px"],
		        					btn:false
		        				})
		        			}else{
		        				layer.confirm(data.m,{
		        					title:false,
		        					area:["300px","100px"],
		        					btn:false
		        				})
		        				layer.close(index);
		        			}
	        			}
	        		}
	        	})
	        }
	    })
	});
	// 绑定手机验证码的点击效果
	$("#getTelCode").on("click",function()
	{
		var time=60;
		var _this=$(this);
		// 获取手机号
		var $phone=$("#iphoneNum").val();
		$.ajax({
			type:"GET",
			url:"/app/1/sendSms?phone="+$phone,
			success:function(data){
				data=$.parseJSON(data);
				if(data.c != 1){
					layer.confirm(data.m,{
	        					title:false,
	        					area:["300px","100px"],
	        					btn:false
	        				})
				}
				else{
					_this.attr("disabled","true");
					_this.addClass("active").text("60S");
			         setTime=setInterval(function(){
			            if(time<=0){
			                 clearInterval(setTime);
			                 _this.removeClass("active").text("重新获取验证码");
			                 _this.removeAttr("disabled");
			                 time=60; 
			                 return;
			            };
			            time--;
			           $("#getTelCode").text(time+"S");
			         },1000);
				}
			}
		})
	});
	// 更换手机的验证码点击效果
	$("#modifePhone").on("click",function()
	{
		var _this=$("#modifePhone");
		var time=60;
		// 获取旧手机号码
		var $phone=$("#oldNum").val();
		$.ajax({
			type:"GET",
			url:"/app/1/sendSms?type=unbind&phone="+$phone,
			success:function(data){
				data=$.parseJSON(data);
				console.log(data);
				if(data.c != 1){
					layer.confirm(data.m,{
						title:false,
						area:["300px","100px"],
						btn:false
					});
				}
				else{	
					_this.attr("disabled","true");
					_this.addClass("active").text("60S");
			         setTime=setInterval(function(){
			            if(time<=0){
			                 clearInterval(setTime);
			                 _this.removeClass("active").text("重新获取验证码");
			                 _this.removeAttr("disabled");
			                 time=60; 
			                 return;
			            };
			            time--;
			           $("#modifePhone").text(time+"S");
			         },1000);
				}
			}
		})
	}); 
	// 验证新手机的验证码的点击效果
	$("#newCode").on("click",function()
	{
		var _this=$("#newCode");
		var time=60;
		// 获取新手机号码
		var $phone=$("#newNum").val();
		$.ajax({
			type:"GET",
			url:"/app/1/sendSms?type=change_bind&phone="+$phone,
			success:function(data){
				data=$.parseJSON(data);
				console.log(data);
				if(data.c != 1){
					layer.confirm(data.m,{
						title:false,
						area:["300px","100px"],
						btn:false
					});
				}
				else{
					_this.attr("disabled","true");
					_this.addClass("active").text("60S");
			         setTime=setInterval(function(){
			            if(time<=0){
			                 clearInterval(setTime);
			                 _this.removeClass("active").text("重新获取验证码");
			                 _this.removeAttr("disabled");
			                 time=60; 
			                 return;
			            };
			            time--;
			           $("#newCode").text(time+"S");
			         },1000);
				}
			}
		})	
	});
	// 绑定邮箱的验证码点击效果
	$("#getEmailCode").on("click",function()
	{
		var _this=$("#getEmailCode");
		var time=60;
		// 获取邮箱
		var $email=$("#emailOne").val();
		$.ajax({
			type:"post",
			url:"/app/1/sendMail",
			data:{"type":"bind","email":$email},
			success:function(data){
				data=$.parseJSON(data);
				if(data.c != 1){
					layer.confirm(data.m,{
						title:false,
						area:["300px","100px"],
						btn:false
					});
				}
				else{
					
					_this.attr("disabled","true");
					_this.addClass("active").text("60S");
			         setTime=setInterval(function(){
			            if(time<=0){
			                 clearInterval(setTime);
			                 _this.removeClass("active").text("重新获取验证码");
			                 _this.removeAttr("disabled");
			                 time=60; 
			                 return;
			            };
			            time--;
			           $("#getEmailCode").text(time+"S");
			         },1000);
				}
			}
		})
	});
	// 更改绑定邮箱的第一步的操作点击效果
	$("#oneEmail").on("click",function()
	{
		var _this=$("#oneEmail");
		var time=60;
		// 获取邮箱
		var $email=$("#oneoldemail").val();
		$.ajax({
			type:"post",
			url:"/app/1/sendMail",
			data:{"type":"unbind","email":$email},
			success:function(data){
				data=$.parseJSON(data);
				if(data.c != 1){
					layer.confirm(data.m,{
						title:false,
						area:["300px","100px"],
						btn:false
					});
				}
				else{
					
					_this.attr("disabled","true");
					_this.addClass("active").text("60S");
			         setTime=setInterval(function(){
			            if(time<=0){
			                 clearInterval(setTime);
			                 _this.removeClass("active").text("重新获取验证码");
			                 _this.removeAttr("disabled");
			                 time=60; 
			                 return;
			            };
			            time--;
			           $("#oneEmail").text(time+"S");
			         },1000);
				}
			}
		})
	});
	// 更改绑定邮箱的第2步的操作点击效果
	$("#twoEmail").on("click",function()
	{
		var _this=$("#twoEmail");
		var time=60;
		// 获取邮箱
		var $email=$("#twoNewemail").val();
		$.ajax({
			type:"post",
			url:"/app/1/sendMail",
			data:{"type":"change_bind","email":$email},
			success:function(data){
				data=$.parseJSON(data);
				if(data.c != 1){
					layer.confirm(data.m,{
						title:false,
						area:["300px","100px"],
						btn:false
					});
				}
				else{
					_this.attr("disabled","true");
					_this.addClass("active").text("60S");
			         setTime=setInterval(function(){
			            if(time<=0){
			                 clearInterval(setTime);
			                 _this.removeClass("active").text("重新获取验证码");
			                 _this.removeAttr("disabled");
			                 time=60; 
			                 return;
			            };
			            time--;
			           $("#twoEmail").text(time+"S");
			         },1000);
				}
			}
		})
	});
	/* 我的直播页面
	*****************************************************/
	$(".limit :input").blur(function(){
		$(".limit > div p").removeClass("onError").text("");
		if($(this).is("#ldnum")){
			if(this.value==""){
				var errmsg="请填写乐点数";
				$(".limit > div p").addClass("onError").text(errmsg);
			}else{
				var reg = new RegExp("^[0-9]*$");
				if(!reg.test(this.value)){
					var errmsg="乐点格式不正确";
					$(".limit > div p").addClass("onError").text(errmsg);
				}
			}
		}
	}).keyup(function(){
		$(this).triggerHandler("blur");
	})
	// 直播类型的选择
	$(".live-set > .set-list ul li").on("click",function()
	{
		var _this=$(this);
		// 获取传给服务端的参数
		var $id=_this.parent().data("id");
		var limit=_this.data("type");
		
		// 如果是公开收费.获取乐点数
		if(limit == 1){
			layer.confirm("设置价格",{
				title:"设置价格",
				type:1,
				btn:["确定","取消"],
				content:$(".limit"),
				yes:function(index){
					$(".limit :input").trigger("blur");
					var ticket_price=$("#ldnum").val();
					if(ticket_price == ""){
						return;
					}else{
						layer.close(index);
						// 请求设置直播权限的接口对接
						$.ajax({
							type:"post",
							url:"/app/1/setMyReplay",
							data:{"id":$id,"limit":limit,"ticket_price":ticket_price},
							dataType:"json",
							async:true,
							success:function(data){
								if(data.c == 0){
									layer.confirm(data.m,{
										title:false,
										btn:false,
										area:["300px","100px"]
									})
								}
								else{
									layer.confirm(data.m,{
										title:false,
										btn:["确定","取消"],
										yes:function(index){
											// $(this).parent().parent().parent().siblings(".livemsg-top").find("span.fl").text($(this).text());
										layer.close(index);	
										window.location.reload();
										}
									})
								}

							}
						})
					}
				}
			})
		}else{
			var ticket_price="";
			// 请求设置直播权限的接口对接
			$.ajax({
				type:"post",
				url:"/app/1/setMyReplay",
				data:{"id":$id,"limit":limit,"ticket_price":ticket_price},
				dataType:"json",
				async:true,
				success:function(data){
					if(data.c == 0){
						layer.confirm(data.m,{
							title:false,
							btn:false,
							area:["300px","100px"]
						})
					}
					else{
						layer.confirm(data.m,{
							title:false,
							btn:["确定","取消"],
							yes:function(index){
								window.location.reload();
								layer.close(index);
							}
						})
					}
				}
			})
		}
	});
	// 全选效果的实现
	$(".del-box span.checkall input").on("click", function()
	{
		var $inputs = $(".lilst-box ul li a :input[type=checkbox]");
		if ($(this).is(":checked")) {
			$inputs.prop("checked", true)
		} else {
			$inputs.prop("checked", false)
		}
	});
	// 我的直播中的删除效果
	$(".del-live").on("click",function(){
		var playbackid="";
		var obj=$(".lilst-box ul li input[type=checkbox]");
		var objLen=$(".lilst-box ul li input[type=checkbox]").length;
		for(var i=0;i<objLen;i++){
			if( $($(".lilst-box ul li input[type=checkbox]")[i]).is(":checked") ){
				playbackid += $($(".lilst-box ul li input[type=checkbox]")[i]).val()+",";
			}
		}
		$.ajax({
			type:"post",
			url:"/app/1/deleteMyReplay",
			data:{"id":playbackid},
			dataType:"json",
			async:true,
			success:function(data){
				if(data.c == 0){
					layer.confirm(data.m,{
						title:false,
						btn:false,
						area:["300px","200px"],
					})
				}else{
					layer.confirm(data.m,{
						title:false,
						btn:["确定","取消"],
						yes:function(){
							window.location.reload();
						}
						
					})
				}
			}

		})
	})
	/* 我的实名认证页面
	*****************************************************/
	// tab切换
	$(".my-profile ul li").on("click",function()
	{
		$(".my-profile ul li").removeClass("active");
		$(this).addClass("active");
		// 获取目标下标
		var $index=$(this).index();
		$("#idapprove-box > div").addClass("hide");
		$("#idapprove-box > div").eq($index).removeClass('hide');
	});
	// 传给服务端的参数开始
	var $realname="";
	var $phoneApprove="";
	var $idnumber="";
	var $qq="";
	var birth="";
	var $addr="";
	var imgs="";
	var type=$("#liveType input[type=radio]").eq(0).data('type');
	var imgsr="";
	var imgsl="";
	// 传给服务端的参数结束
	var selectY="";
	var selectM="";
	var selectD="";
	// 第一步生日的参数传递
	$("#birth select").change(function()
	{
		if( $(this).is("#select_year") )
		{
			selectY=parseInt($(this).val());
		}
		if( $(this).is("#select_mouth") )
		{
			selectM=parseInt($(this).val());
		}
		if( $(this).is("#elect_day") )
		{
			selectD=parseInt($(this).val());
		}
		// if( selectY == 0 || selectM == 0 || selectD == 0 )
		// {
		// 	$("#birth").find("p").text("请选择生日");
		// 	return false;
		// }else
		// {
		// 	$("#birth").find("p").text("");
		// 	birth=selectY+"-"+selectM+"-"+selectD;	
		// };
		// console.log(birth);
	});
	// 第二部用户基本资料地址的获取
	var $cmbProvince="";
	var $cmbCity="";
	$("#address select").change(function()
	{
		if( $(this).is("#cmbProvince") )
		{
			$cmbProvince=$(this).val();
		}
		if( $(this).is("#cmbCity") )
		{
			$cmbCity=$(this).val();
		}
		if($cmbCity=='--请选择--'&&$cmbCity=='地级市')
		{
			$cmbCity=" ";
		}
		if($cmbCity==" " || $cmbProvince==" "){
			$("#address").find("p").text("地址不能为空");
			return;
		}else{
			$("#address").find("p").text("");
			$addr=$cmbProvince+"-"+$cmbCity;
			console.log($addr);
		}
	});
	// 第三部获取类别
	var objlivetype=$("#liveType input[type=radio]");
	var livetypeLen=$("#liveType input[type=radio]").length;
	$("#liveType input[type=radio]").change(function()
	{
		for(var i=0;i<livetypeLen;i++)
		{
			if( $($("#liveType input[type=radio]")[i]).is(":checked") )
			{
				type=$($("#liveType input[type=radio]")[i]).data("type");
			}
		};
	});
	$(document).keyup(function()
	{
		$realname=$("#userName").val();
		$phoneApprove=$("#userTel").val();
		$idnumber=$("#userId").val();
		$qq=$("#userQQ").val();
	})
	// 实名认证的基本资料的提交
	$("#approveBtn").on("click",function()
	{
		//1 是否上传身份证的验证
		if( imgsr == "" || imgsl == "")
		{
			layer.confirm("",{
				title:"",
				content:"请先进行身份验证",
				btn:"确定",
				yes:function(){
					layer.closeAll("dialog");
					$(".my-profile ul li").removeClass("active");
					$(".my-profile ul li").eq(1).addClass("active");
					$("#idApprove").addClass("hide");
					$(".authentication").removeClass("hide");
				}
			});
			return false;
		};
		imgs=imgsr+","+imgsl;
		$("#idApprove :input").trigger("blur");
        var numError = $("#idApprove .onError").length;
        if( numError ){
        	return false;
        }
		// 第一步生日的参数传递
		 selectY=parseInt($("#select_year").val());
		 selectM=parseInt($("#select_mouth").val());
		 selectD=parseInt($("#select_day").val());
		if( selectY == 0 || selectM == 0 || selectD == 0 )
		{
			$("#birth").find("p").text("请选择生日");
			return false;
		}else
		{
			$("#birth").find("p").text("");
			birth=selectY+"-"+selectM+"-"+selectD;	
		};
		$.ajax({
			url:"/app/1/applyAnchor",
			type:"post",
			data:{"realname":$realname,"birth":birth,"describe":$addr,"phone":$phoneApprove,"idnumber":$idnumber,"type":type,"qq":$qq,"imgs":imgs},
			success:function(data){

			}
		})
	});
	// 上传图片
	$("#rightPic").change(function()
	{
		$.ajax({
			url: "/upload/index.php",
			type: "post",
			data:new FormData(document.getElementById("idFormR")),
			contentType: false, //必须  告诉jq不要去设置content-type请求头
			processData: false, //必须  告诉jq不要去请求发送的数据
			success: function(data){
				data=$.parseJSON(data);
				var newsrc="/upload/"+data.url;
				$(".rightSide img").attr("src",newsrc);
				imgsr=data.url;
			}
		});
	});
	$("#leftPic").change(function()
	{
		$.ajax({
			url: "/upload/index.php",
			type: "post",
			data:new FormData(document.getElementById("idFormL")),
			contentType: false, //必须  告诉jq不要去设置content-type请求头
			processData: false, //必须  告诉jq不要去请求发送的数据
			success: function(data){
				data=$.parseJSON(data);
				var newsrc="/upload/"+data.url;
				$(".leftSide img").attr("src",newsrc);
				imgsl=data.url;
			}
		});		
	});
    // 实名认证的身份证图片的提交
    $("#idkeen-btn").on("click",function()
    {
    	selectY=parseInt($("#select_year").val());
		selectM=parseInt($("#select_mouth").val());
		selectD=parseInt($("#select_day").val());
		if( selectY == 0 || selectM == 0 || selectD == 0 )
		{
			$("#birth").find("p").text("请选择生日");
			return false;
		}else
		{
			$("#birth").find("p").text("");
			birth=selectY+"-"+selectM+"-"+selectD;	
		};
    	// 判断资料是否完善
    	if( $realname == "" || $phoneApprove == "" || $idnumber == "" || $addr == "" || birth == "")
    	{
    		layer.confirm("",{
    			title:"",
    			content:"请先完善信息",
    			btn:"确定",
    			yes:function(){
    				layer.closeAll("dialog");
    				$(".my-profile ul li").removeClass("active");
    				$(".my-profile ul li").eq(0).addClass("active");
    				$("#idApprove").removeClass("hide");
    				$(".authentication").addClass("hide");
    			}
    		});
    		return;
    	}
    	$(".authentication :input").trigger("blur");
    	// 判断是否符合提交条件
    	var onErrornum=$(".authentication .onError").length;
    	if( onErrornum ){
    		return false;
    	};
    	imgs=imgsr+","+imgsl;
    	$.ajax({
    		url:"/app/1/applyAnchor",
    		type:"post",
    		data:{"realname":$realname,"birth":birth,"describe":$addr,"phone":$phoneApprove,"idnumber":$idnumber,"type":type,"qq":$qq,"imgs":imgs},
    		success:function(data){

    		}
    	})
    });
	// 实名认证成功后的操作
	$("#backIndex").on("click",function(){
		window.location.href="/";
	});
	$("#reload").on("click",function(){
		window.location.reload();
	});
	// 删除私信的接口对接
	$(".delLetter").on("click",function(){
		// 获取id
		var letterId=$(this).data("type");
		var _this=$(this);
		$.ajax({
			type:"post",
			url:"/app/1/delSess",
			data:{"friend":letterId},
			async:true,
			dataType:"json",
			success:function(data){
				if(data.c == 0){
					layer.confirm(data.m,{
						title:false,
						btn:false,
						area:["300px","100px"]
					})
				}
				else if(data.c == 1){
					window.location.reload();
					// _this.parent().parent().remove();
				}
			}
		})
	});
	// 删除私信列表
	$(".delMsg").on("click",function(){
		// 获取id
		var msgId=$(this).data("type");
		var _this=$(this);
		$.ajax({
			type:"post",
			url:"/app/1/delSessMsg",
			data:{"id":msgId},
			async:true,
			dataType:"json",
			success:function(data){
				if(data.c == 0){
					layer.confirm(data.m,{
						title:false,
						btn:false,
						area:["300px","100px"]
					})
				}
				else if(data.c == 1){
					// _this.parent().parent().parent().remove();
					window.location.reload();
				}
			}
		})
	});

})