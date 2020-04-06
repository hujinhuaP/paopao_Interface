$(function(){
	// 已经注册的邀请的页面
	$('.user_content').on("click",function()
	{
		$(".loginRegBtn").removeClass("hide");
		$("#register_form,#login_form").addClass("hide");
	});
	// $(".reg-btn").on("click",function()
	// {
	// 	$(".loginRegBtn > button").addClass("active");
	// 	$(this).removeClass("active");
	// 	$(this).parent().addClass("hide");
	// 	$("#register_form,#login_form").removeClass("hide");
	// 	$("#register_form").addClass("hide");
	// });
	$(".login-btn").on("click",function()
	{
		$(".loginRegBtn > button").addClass("active");
		$(this).removeClass("active");
		$(this).parent().addClass("hide");
		$("#login_form").addClass("hide");
		$("#register_form").removeClass("hide");
	});
	$(".exit").on("click",function()
	{
		$(".loginRegBtn").removeClass("hide");
		$("#register_form,#login_form").addClass("hide");
		$("#login_form ul li :input").val("");
		$("#regAuthtoken,#regAuthsecret,#regCaptcha,#regNick").val("");
		$(".formbox").text("");

	});
	// 验证码的切换
	$(".captcha").on("click",function(){
		var nowtime=new Date().getTime();
		var nowsrc='/user/captcha?k='+nowtime;
		$(this).attr("src",nowsrc);
	});
	// 性别得切换
	$("#userSex > p > i").on("click",function(){
		$("#userSex > p > i").removeClass("active");
		$(this).addClass("active");
	});
	$(".captcha").on("click",function(){
		// 获取当前时间序列
		var nowtime=new Date().getTime();
		// 新的图片路径
		var newUrl="/user/captcha?k="+nowtime;
		$(this).attr("src",newUrl);
	});
	// $("#login_form :input").blur(function(){
	// 	// 清空提示信息
	// 	$(this).next().removeClass("onerror").text("");
	// 	if($(this).is("#username"))
	// 	{
	// 		var regExp=/^[a-zA-Z0-9\u4e00-\u9fa5]{5,30}$/;
	// 		if(this.value==""){
	// 			var errmsg="用户名不能为空";
	// 			$(this).next().addClass("onerror").text(errmsg);
	// 		}else if(!regExp.test(this.value)){
	// 			var errmsg="用户名格式不正确";
	// 			$(this).next().addClass("onerror").text(errmsg);
	// 		}
	// 	};
	// 	if($(this).is("#authsecret"))
	// 	{
	// 		var regExp=/^[a-zA-Z0-9\u4e00-\u9fa5]{5,30}$/;
	// 		if( this.value == "" )
	// 		{
	// 			var errorMsg = "密码不能为空";
	// 			$(this).next().addClass("onerror").text(errorMsg);
	// 		}
	// 		else if( this.value.length < 6 || this.value.length > 16 )
	// 		{
	// 			var errorMsg = "密码不正确";
	// 			$(this).next().addClass("onerror").text(errorMsg);
	// 		}
	// 	};
	// 	if($(this).is("#captcha"))
	// 	{
	// 		var regExp=/^[a-zA-Z0-9]{4}$/;
	// 		if( this.value == "" )
	// 		{
	// 			var errorMsg = "验证码不能为空";
	// 			$(this).next().addClass("onerror").text(errorMsg);
	// 		}
	// 		else if( !regExp.test(this.value) )
	// 		{
	// 			var errorMsg = "验证码不正确";
	// 			$(this).next().addClass("onerror").text(errorMsg);
	// 		}
	// 	}
	// }).keyup(function(){
	// 	$(this).triggerHandler("blur");
	// });
})