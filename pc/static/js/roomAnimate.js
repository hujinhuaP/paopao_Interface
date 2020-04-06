$(function(){
	/* 用户端
	*****************************************************/
	//获取系统当前时间
    var hours = new Date().getHours();
    var minutes = (new Date().getMinutes() >= 10 )?new Date().getMinutes():'0'+ new Date().getMinutes();
    var time = hours +':'+ minutes;

    //分享
    $('.share').mouseover(function()
    {
    	$('.share-box').fadeIn();
	}).mouseleave(function()
	{
    	$('.share-box').fadeOut();
    });
    //下载
    $('.download').mouseover(function()
    {
    	$('.download-box').fadeIn();
	}).mouseleave(function()
	{
    	$('.download-box').fadeOut();
    });
	//关注
	$('#concernBtn').on('click', function()
	{
		$.ajax({
			type: 'GET',
			url: '/app/1/addFollow?uid='+ ROOM_INFO.anchor.id,
			dataType: 'json',
			async: true,
			success: function(data){
				console.log(data);
				if(data.c == 0){
                    layer.confirm(data.m,
                    {
                        title:false,
                        area:["300px","100px"],
                        btn:false
                    });
				}
				else if(data.c == 1){
					$('#concernBtn').addClass('active').text('已关注').attr('disabled',true);
				}
			}
		});
	});
	//打开付费弹窗
	$('.payon').on('click', function()
	{
		$('#timingChargeLayer').fadeIn();
	});
	//计时收费-申请观看
	$('#applyviewBtn').on('click', function()
	{
		$.ajax({
			type: 'GET',
			url: '/app/1/applyPrivacyLive?rid=' + ROOM_INFO.rid,
			dataType: 'json',
			async: true,
			success: function(data)
			{
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
					$('#applyviewBtn').remove();
					$('#waitAnchorApply').append('<a href="javascript:;" id="waitagreeBtn">等待主播同意</a>');
				}
				else if(data.c == 10000)
				{
					$('#loginLayer').fadeIn();
				}
			}
		});
	});
	//礼物类别切换
	$('.gift-head li').on('click', function()
	{
		var index = $(this).index();
		$('.gift-head li a').removeClass('active');
		$(this).children().addClass('active');
		$('.gift-list-box > div').eq(index).show().siblings().hide();
	});
	//改变礼物乐点层的相对位置和箭头方向
	/*$('.gift-list').each(function(index) 
	{
 		$(this).find('li:eq(9) p').css({'left':'0'});
		$(this).find('li:gt(9) p').css(
		{
			'left':'0', 
			'bottom':'78px'
		});
		$(this).find('li:gt(9) p span').css(
		{
			'top':'30px',
			'border-top':'rgba(255,255,255,.8) 4px solid', 
			'border-bottom':'transparent 4px solid'
		});
	});*/
	//礼物选择
	setTimeout(function() //解决礼物层一开始隐藏时左右按钮无法点击切换bug
	{
		$('.gift-box').slideToggle();
	}, 1);
	$('#selectGiftBtn').on('click', function()
	{
		$('.gift-box').slideToggle(300);
		var span = $(this).find('span');
		if(span.hasClass('arrow-rotate'))
		{
			$(this).find('span').removeClass('arrow-rotate');
		}
		else
		{
			$('.gift-box').css('opacity','1');
			$(this).find('span').addClass('arrow-rotate');
		}
	});
	var giftID = '';   //礼物ID
	$('.gift-list li').on('click', function()
	{
		$('.gift-list li').removeClass('active');
		$(this).addClass('active');
		giftID = $(this).find('input[name=giftID]').val();
	});
	//赠送
	$('#sendGiftBtn').on('click', function()
	{
		if(giftID == '')
		{
            layer.confirm('请选择礼物！',
            {
                title:false,
                area:["300px","100px"],
                btn:false
            });
		}
		else
		{
			$.ajax({
				type: 'GET',
				url: '/app/1/sendGift?rid='+ROOM_INFO.rid+'&uid='+ROOM_INFO.rid+'&gid='+giftID,
				dataType: 'json',
				async: true,
				success: function(data)
				{
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
						$('.gift-bar li .ledian').text(data.d);
						$('#rewardLedian').text(data.d);
						$('#switchLedian').text(data.d);
						$('.gift-box').hide();
						if($('#selectGiftBtn span').hasClass('arrow-rotate'))
						{
							$('#selectGiftBtn span').removeClass('arrow-rotate');
						}
						else
						{
							$('#selectGiftBtn span').addClass('arrow-rotate');
						}
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
				}
			});
		}
	});
	//聊天/观众/贡献榜切换
	$('#mouseTab li').on('mousemove', function()
	{
		var index = $(this).index();
		$('#mouseTab li a').removeClass('active');
		$(this).children().addClass('active');
		$('.top-cont>div').eq(index).show().siblings().hide();
	});
	//点击空白区域隐藏用户昵称下拉菜单
	$(document).on('click', function()
	{
		$('#DropdownMenu').remove();
	});
	$('#DropdownMenu,.fuuser').on('click', function(event)
	{
		event.stopPropagation();
	});
	//选择发送对象
	$('.send-ways ul li a').on('click', function(){
		$('.send-ways ul li a').removeClass('active');
		$(this).addClass('active');
		$('.send-ways p').text($(this).text());
	});
	//打赏主播
	$('.reward-anchor').on('click', function()
	{
		$('#switchLayer').hide();
		$('#lianmaiLayer').hide();
		$('#rewardLayer').slideToggle();
	});
	//申请私密直播弹出层操作
	// switchLive();
	$('#switchBtn').on('click', function()
	{
		$.ajax({
			type: 'GET',
			url: '/app/1/applyAnchorChangePriLive?rid='+ROOM_INFO.rid,
			dataType: 'json',
			async: true,
			success: function(data){
				if(data.c == 0){
                    layer.confirm(data.m,
                    {
                        title:false,
                        area:["300px","100px"],
                        btn:false
                    });
				}
				else if(data.c == 1)
				{
					$(this).addClass('active').text('等待通过');
				}
				else if(data.c == 10000)
				{
					$('#loginLayer').fadeIn();
				}
			}
		});
		$('#switchLayer').slideToggle();
	});
	//申请连麦弹出层操作
	// switchLianmai();
	/*$('#lianmaiBtn').on('click', function()
	{
		if($(this).text() == '我要连麦')
		{
			$(this).text('取消连麦');
			//连麦时把数据添加到列表
			var tpl = '<li>' +
						'<dt><img src="assets/images/person_1.jpg"/></dt>' +
						'<dd>亚洲舞王尼古拉斯赵四</dd>' +
					  '</li>';
			$(".lianmai-content ul").append($(tpl));
			//计算有几个人申请连麦
			$(".lianmai-content ul li").each(function(index)
			{
				$('.lianmai-content h6 span').text(index+1);
			});
		}
		else
		{
			$(this).text('我要连麦');
			//取消连麦时删除列表中对应的数据
			$(".lianmai-content ul li:last-child").remove();
			//计算有几个人申请连麦
			$(".lianmai-content ul li").each(function(index)
			{
				$('.lianmai-content h6 span').text(index+1);
			});
		}
	});*/
	//主播申请与我连麦
	/*$('.user-lianmai-news').on('click', function()
	{
		$('#anchorLianmaiLayer').slideToggle();
	});*/
	//选择表情
	$.getJSON('/static/assets/json/emoji.json', function(data)
	{
		ROOM_INFO.emojis = data;
		for(var i in data)
		{
			var tpl = '<li name="'+ data[i].emojiText +'"><img src="'+ data[i].emojiImg +'" alt="'+ data[i].emojiText +'"></li>';
			$('.emoji-box ul').append($(tpl));
		}
		$('.emoji-box ul li').on('click', function()
		{
			var emojiTxt = $(this).attr('name');
			$('#sendContent').val($('#sendContent').val() + emojiTxt);
			$('.emoji-box').hide();
		});
	});
	$('#emojiBtn').on('click', function()
	{
		$('.emoji-box').slideToggle();
	});
	//发言
	$('#sendBtn').on('click', function()
	{
		var iptVal = $('#sendContent').val();
		if(iptVal == '')
		{
			$('#sendContent').focus();
		}
		else
		{
			var ptxt = $('.send-ways p').text();
    		//获取发送类型
			var sendtype = $('#sendType li a.active').data('type');
			console.log(sendtype);
			if(ptxt == '对所有人' || ptxt == '主播')
			{
				if(sendtype == 'all')
				{
					console.log('发送公聊');
					iptVal = $('#sendContent').val();
					console.log(iptVal);
		            $.ajax({
						type: 'GET',
						url: '/app/1/sendPubmsg?rid='+ ROOM_INFO.rid +'&msg='+ iptVal,
						dataType: 'json',
						async: true,
						success: function(data){
							console.log(data);
							if(data.c == 0){
			                    layer.confirm(data.m,
			                    {
			                        title:false,
			                        area:["300px","100px"],
			                        btn:false
			                    });
							}
							else if(data.c == 10000){
								layer.confirm(data.m,{
			                        title:' ',
			                        btn:['请登录'],
                    				area:['300px'],
			                        yes:function(){
			                            layer.closeAll('dialog');
			                            $('#loginLayer').fadeIn();
			                        }
			                    });
							}
						}
					});
				}
				else if(sendtype == 'anchor')
				{
					console.log('@主播');
					console.log(ROOM_INFO);
		            iptVal = '@'+ ROOM_INFO.anchor.nick +' '+ iptVal;
		            $.ajax({
						type: 'GET',
						url: '/app/1/sendPubmsg?rid='+ ROOM_INFO.rid +'&msg='+ iptVal,
						dataType: 'json',
						async: true,
						success: function(data){
							console.log(data);
							if(data.c == 0){
			                    layer.confirm(data.m,
			                    {
			                        title:false,
			                        area:["300px","100px"],
			                        btn:false
			                    });
							}
							else if(data.c == 10000){
								layer.confirm(data.m,{
			                        title:' ',
			                        btn:['请登录'],
                    				area:['300px','200px'],
			                        yes:function(){
			                            layer.closeAll('dialog');
			                            $('#loginLayer').fadeIn();
			                        }
			                    });
							}
						}
					});
				}
			}
			else
			{
				console.log('对TA公聊');
				iptVal = ptxt +' '+ iptVal;
				console.log(iptVal);
				$.ajax({
					type: 'GET',
					url: '/app/1/sendPubmsg?rid='+ ROOM_INFO.rid +'&msg='+ iptVal,
					dataType: 'json',
					async: true,
					success: function(data){
						console.log(data);
						if(data.c == 0){
		                    layer.confirm(data.m,
		                    {
		                        title:false,
		                        area:["300px","100px"],
		                        btn:false
		                    });
						}
					}
				});
			}
			$('#sendContent').val('');
		}
	});
	//弹幕
	$('#barrageBtn').on('click', function()
	{
		console.log('发送弹幕!');
		var iptVal = $('#sendContent').val();
		if(iptVal == '')
		{
			$('#sendContent').focus();
		}
		else
		{
			$.ajax({
				type:"GET",
				url:"/app/1/sendLaba?rid="+ROOM_INFO.rid+"&word="+iptVal,
				dataType:"json",
				async:true,
				success:function(data){
					if(data.c == 0)
					{
	                    layer.confirm(data.m,
	                    {
	                        title:false,
	                        area:["300px","100px"],
	                        btn:false
	                    });
					}
					// 判断是否登录
					else if(data.c == 10000)
					{
						$('#loginLayer').fadeIn();
					}
					else if(data.c == 20000)
					{
						layer.confirm(data.m, {
							title: ' ',
							btn: ['去充值'],
							area: ['300px', '200px'],
							yes: function(){
								layer.closeAll('dialog');
								window.location.href="/account/account";
							}
						});
					}
				}
			});
			$('#sendContent').val('');
		}
	});
	//关闭弹幕
	$('.barrage-btn').on('click', function()
	{
		if($(this).hasClass('active'))
		{
			$(this).removeClass('active');
			$('.barrage-box').hide();
		}
		else
		{
			$(this).addClass('active');
			$('.barrage-box').show();
		}
	});
	//点赞
	$('#dianzan-btn').on('click', function()
	{
		$.ajax({
			type:"GET",
			url:"/app/1/attitude?rid=" + ROOM_INFO.rid,
			dataType: 'json',
			async: true,
			success:function(data){
				console.log(data);
				if( data.c == 0 )
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
					var x = 50;
					var y = 1;
					var num = Math.floor(Math.random() * (3 - 1 + 1) + 1);
					var index = $('.dainzan-content').children('img').length;
					var rand = parseInt(Math.random() * ( x - y + 1) + y);
					$('.dainzan-content').append('<img src="">');
					$('.dainzan-content img').css('left',''+ rand +'px');
					$('.dainzan-content img:eq('+ index +')').attr('src','/static/assets/images/attitude/'+ num +'.png');
					setTimeout(function(){
						$('.dainzan-content img').addClass('imgscale').animate({
							left:rand,
							bottom:'400px',
							opacity:'0'
						},3000);
						if( $('.dainzan-content img').length > 10 )
						{
							$('.dainzan-content img')[0].remove();
						}
					}, 100);
				}
				// 判断是否登录
				else if(data.c == 10000)
				{
					$('#loginLayer').fadeIn();
				}
			}
		})

	});
	//关闭直播间弹窗
	$('.timing-close').on('click', function()
	{
		$('#timingChargeLayer').fadeOut();
	});
	$('.apply-close').on('click', function()
	{
		$('#applyPrivateLiveLayer').fadeOut();
	});


	/* 主播端
	*****************************************************/
	//确认结束直播
	$('#endliveBtn').on('click', function()
	{
		$('#confirmEndliveLayer').hide();
		$('#endliveLayer').show();
	});
	//申请切换至私密直播消息
	var lastPage;
	$('.have-apply-news').on('click', function()
	{
		$('#applyLianmaiLayer').hide();
		var display = $('#applyPrivateLiveLayer').css('display');
		if(display == 'none'){
			$('#applyPrivateLiveLayer').show();
			$('#privateAgree').empty();
			$.ajax({
				type: 'GET',
				url: '/app/1/getUserApplys?page=1&pagesize=50',
				dataType: 'json',
				async: true,
				success: function(data){
					if(data.c == 0){
	                    layer.confirm(data.m,
	                    {
	                        title:false,
	                        area:["300px","100px"],
	                        btn:false
	                    });
					}
					else if(data.c == 1){
						lastPage = data.d
						var arr = data.d.items;
						for(var i=0;i<arr.length;i++){
							var tpl='<ul>' +
										'<li class="hide">' +
											'<input type="checkbox" name="privateCheck" value="'+ arr[i].uid +'">' +
										'</li>' +
										'<li>' +
											'<dt><img src="/upload/'+ arr[i].avatar +'"/></dt>' +
											'<dd>'+ arr[i].nick +'</dd>' +
										'</li>' +
									'</ul>';
							$('#privateAgree').append($(tpl));
						}
						//单选
						$('input[name=privateCheck]').on('click', function()
						{
							// var chknum = $('input[name=privateCheck]').size(); //总条数
							var chk = 0;
							$('input[name=privateCheck]').each(function()
							{
								if($(this).prop('checked') == true)
								{
									chk++;
								}
							});
							if( chk == 0)
							{
								$('#partAgree').parent().hide();
								$('#allAgree').parent().show();
							}
							else
							{
								$('#allAgree').parent().hide();
								$('#partAgree').parent().show();
							}
						});
					}
				}
			});
		}else{
			$('#applyPrivateLiveLayer').hide();
			//初始化编辑状态
			$('#privateAgree ul').each(function()
			{
				$(this).find('li input[type=checkbox]').prop('checked',false);
				$(this).find('li').eq(0).hide();
			});
			$('#partAgree').parent().hide();
			$('#allAgree').parent().show();
			$('#allAgree').parent().parent().hide().prev().show();
		}
	});
	//申请切换至私密直播滚动分页加载
	$('#privateAgree').on('scroll', function()
	{
		var scrollTop = $(this)[0].scrollTop;      //滚动条距顶部的高度
		var divHeight = $(this).height();		   //可见区域的高度
		var scrollHeight = $(this)[0].scrollHeight;//整个#privateAgree的高度（包括屏幕外的高度）
		if( scrollTop + divHeight >= scrollHeight )
		{
			//如果当前页数=总页数，则提示加载完成
			if (lastPage.page >= lastPage.pagetotal) {
				$('.apply-content').append('<p class="page-loaded">数据已加载完成</p>');
				if($('.apply-content > p').length > 1)
				{
					$('.apply-content').find('.page-loaded').remove();
				}	
				else
				{
					setTimeout(function(){
						$('.apply-content').find('.page-loaded').remove();
					}, 1000);
				}
				return ;
			}
			$.ajax({
				type: 'GET',
				url: '/app/1/getUserApplys?page='+ lastPage.page+1 +'&pagesize=50',
				dataType: 'json',
				async: true,
				beforeSend: function()
				{
					$('.apply-content').append('<p class="page-loading">正在加载</p>');
				},
				success: function(data)
				{
					if(data != null && data.c == 1)
					{
						var arr = data.d.items;
						for(var i=0;i<arr.length;i++){
							var tpl='<ul>' +
										'<li class="hide">' +
											'<input type="checkbox" name="privateCheck" value="'+ arr[i].uid +'">' +
										'</li>' +
										'<li>' +
											'<dt><img src="/upload/'+ arr[i].avatar +'"/></dt>' +
											'<dd>'+ arr[i].nick +'</dd>' +
										'</li>' +
									'</ul>';
							$('#privateAgree').append($(tpl));
						}
						lastPage.page++;
					}
				},
				complete: function()
				{
					$('.apply-content').find('.page-loading').remove();
				}
			});
		}
	});
	//编辑
	$('#editAgree').on('click', function()
	{
		$(this).parent().parent().hide().next().show();
		$('#privateAgree ul').each(function()
		{
			$(this).find('li').eq(0).show();
		});
	});
	//获取主播开播时选择的直播类型
	//ROOM_INFO.livetype是全局变量
	$('.type-list ul li').on('click',function()
	{
		ROOM_INFO.livetype = $(this).data("type");
	});
	//全部同意
	$('#allAgree').on('click', function()
	{
		if(ROOM_INFO.livetype == 'private')
		{
			$.ajax({
				type: 'GET',
				url: '/app/1/handlePrivacyLiveRequest?ids=0',
				dataType: 'json',
				async: true,
				success: function(data)
				{
					if(data.c == 0)
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
		}
		else
		{
			ROOM_INFO.live.genlive_url = '/live/changePriLive?ids=0';
	        APP.flashPlayer.init();
	        ROOM_INFO.livetype = 'private';
		}
        //删除所有同意的用户数据且隐藏
        $('#privateAgree').empty().parent().parent().parent().hide();
		//更新申请切换至私密直播人数和消息条数
		$('#applyPriNum').text(0);
		$('.have-apply-news').text('0').hide();
		//初始化编辑状态
		$('#partAgree').parent().hide();
		$('#allAgree').parent().show();
		$('#allAgree').parent().parent().hide().prev().show();
	});
	//同意
	$('#partAgree').on('click', function()
	{
		var ids = [];
		$('input[name=privateCheck]').each(function()
		{
			if($(this).prop('checked') == true)
			{
				ids[ids.length] = $(this).val();
			}
		});
		if (ids.length == 0)
		{
			return layer.confirm('请选择要切换至私密直播的用户',{
                title:false,
                btn:false,
				area:['300px','100px']
            });
		}
		console.log('ids：' + ids);

		if(ROOM_INFO.livetype == 'private')
		{
			$.ajax({
				type: 'GET',
				url: '/app/1/handlePrivacyLiveRequest?ids=' + ids,
				dataType: 'json',
				async: true,
				success: function(data)
				{
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
						//更新申请切换至私密直播人数和消息条数
						var num = parseInt($('.have-apply-news').text()) - parseInt($('input[name=privateCheck]').length);
						$('#applyPriNum,.have-apply-news').text(num);
						//如果消息数<=0,则隐藏
				        if(num <= 0)
				        {
        					$('#privateAgree').empty().parent().parent().parent().hide();
				            $('.have-apply-news').hide();
				        }
					}
				}
			});
		}
		else
		{
	        ROOM_INFO.live.genlive_url = '/live/changePriLive?ids='+ ids.join(',');
	        APP.flashPlayer.init();
	        ROOM_INFO.livetype = 'private';

	        //删除所有同意的用户数据且隐藏
	        $('#privateAgree').empty().parent().parent().parent().slideToggle();
			//更新申请切换至私密直播人数和消息条数
			$('#applyPriNum').text(0);
			$('.have-apply-news').text('0').hide();
		}
		//初始化编辑状态
		$('#partAgree').parent().hide();
		$('#allAgree').parent().show();
		$('#allAgree').parent().parent().hide().prev().show();
	});
	//取消
	$('#cancelAgree').on('click', function()
	{
		$(this).parent().parent().hide().prev().show();
		$('#privateAgree ul').each(function()
		{
			$(this).find('li input[type=checkbox]').prop('checked',false);
			$(this).find('li').eq(0).hide();
		});
		$('#partAgree').parent().hide();
		$('#allAgree').parent().show();
	});
	//切换至私密直播
	$('.anchor-switch-live').on('click', function()
	{
		$('#privateChargeStandard').show();
	});
	//关闭直播间弹窗
	$('.private-close').on('click', function()
	{
		$('#privateChargeStandard').fadeOut();
	});
	$('.confirm-close').on('click', function()
	{
		$('#confirmEndliveLayer').fadeOut();
	});
	//申请连麦消息
	/*$('.anchor-lianmai-news').on('click', function()
	{
		$('#applyPrivateLiveLayer').hide();
		$('#applyLianmaiLayer').slideToggle();
	});*/
	//连接
	/*$('#lianmaiAgree ul').each(function()
	{
		$(this).find('button').on('click', function()
		{
			$('#lianmaiAgree ul li button').removeClass('active');
			$(this).addClass('active').text('已连接');
			$('.lianmai-box').show();
		});
	});*/
	//结束连麦
	/*$('.lianmai-close').on('click', function()
	{
		$(this).hide();
		$(this).prev().hide();
		$('.end-lianmai-ing').addClass('translateY-0');
	});
	$('#endlianmaiBtn').on('click', function()
	{
		$('.lianmai-close').hide();
		$('.lianmai-close').prev().hide();
		$('.end-lianmai-ing').hide();
		$('.end-lianmai').addClass('translateY-0');
	});
	$('#cancelEnd').on('click', function()
	{
		$('.end-lianmai-ing').removeClass('translateY-0');
		setTimeout(function()
		{
			$('.lianmai-close').show();
			$('.lianmai-close').prev().show();
		}, 300);
	});*/
	
	//联系人切换
	$('.contact-list > ul').on('click', function()
	{
		var index = $(this).index();
		$('.contact-list > ul').removeClass('active');
		$(this).addClass('active');
		$('.contact-box > div').eq(index).removeClass('hide').siblings().addClass('hide');
	});
	//关闭当前联系人窗口
	$('.close-this').on('click', function()
	{
		$(this).parent().remove();
		var ul_index = $(this).parent().index();
		$('.contact-box > div').each(function(index)
		{
			if(ul_index == index)
			{
				$('.contact-box > div').eq(index).remove();
			}
			else if(ul_index == 0)
			{
				$('.message-box').addClass('hide');
			}
		});

		if($('.contact-list > ul').length == 1)
		{
			$('.contact-list > ul').addClass('active');
		}

		
	});
	//关闭私信窗口
	$('.close-message').on('click', function()
	{
		$('.message-box').addClass('hide');
	});
});


//点击用户昵称添加下拉菜单
function addDropdownMenu(th,uid)
{
	if($('.fuuser ul').length > 0)
	{
		return ;
	}
	$.ajax({
		type: 'GET',
		url: '/app/1/getUserInfo?uid='+ uid,
		dataType: 'json',
		async: true,
		success: function(data)
		{
			console.log(data);
			if(data.c == 0)
			{
                layer.confirm(data.m,
                {
                    title:false,
                    area:['300px','100px'],
                    btn:false
                });
			}
			else if(data.c == 1)
			{
				$(th).find('ul').remove();
				var tpl = '';
				if(data.d.logintype == 'login')//游客
				{
					tpl='<ul id="DropdownMenu">';
							if(data.d.followed == true)
							{
							tpl += '<li><a href="javascript:void(0);" disabled="disabled">已关注</a></li>';
							}
							else
							{
			                tpl += '<li><a href="javascript:concernTa('+ data.d.id +');" class="concernTa">关注TA</a></li>';
							}
			                tpl += '<li><a href="javascript:toTaChat(\''+ data.d.nick +'\');">与TA公聊</a></li>' +
			                	   '<li><a href="javascript:;" onclick="privateMessage(this)" data-avatar="'+ data.d.avatar +'" data-nick="'+ data.d.nick +'">私信TA</a></li>' +
			                	   '<li><a href="/base/userinfo/'+data.d.id+'" target="_blank">查看主页</a></li>';
			            	if(ROOM_INFO.rid == ROOM_INFO.uid)  //如果该用户是主播，则有禁言和踢出操作
                            {    
                            	if(data.d.has_mute === false)
								{
								tpl += '<li class="addMute"><a href="javascript:addMute('+ ROOM_INFO.rid +','+ data.d.id +');">禁言</a></li>';
								}
								else
								{
								tpl += '<li class="cancelMute"><a href="javascript:cancelMute('+ ROOM_INFO.rid +','+ data.d.id +');">取消禁言</a></li>';
								}
                            	tpl += '<li><a href="javascript:addKick('+ ROOM_INFO.rid +','+ data.d.id +');">踢出房间</a></li>';
			                }
			        tpl+='</ul>';
				}
				$(th).append($(tpl));
			}
		}
	});
}
//私信TA
function privateMessage(obj)
{
	console.log("@@@@@");
	console.log(obj);
    console.log("@@@@@46456");
	var avatar = $(obj).data('avatar');
	var nick = $(obj).data('nick');

	$('.message-box').removeClass('hide');
	$('.contact-list > ul').removeClass('active');
	var tpl='<ul class="active">' +
				'<li>' +
					'<img src="/upload/'+ avatar +'"/>' +
					'<span class="message-num hide">消息提醒</span>' +
				'</li>' +
				'<li class="ml10">' +
					'<dt>'+ nick +'</dt>' +
					'<dd class="hide">有未读消息时提示在这里</dd>' +
				'</li>' +
				'<span class="close-this"></span>' +
			'</ul>';
	$('.contact-list').append($(tpl));

	//联系人切换
	$('.contact-list > ul').on('click', function()
	{
		var index = $(this).index();
		$('.contact-list > ul').removeClass('active');
		$(this).addClass('active');
		$('.contact-box > div').eq(index).removeClass('hide').siblings().addClass('hide');
	});
	//关闭当前联系人窗口
	$('.close-this').on('click', function()
	{
		$(this).parent().remove();
		var ul_index = $(this).parent().index();
		$('.contact-box > div').each(function(index)
		{
			if(ul_index == index)
			{
				$('.contact-box > div').eq(index).remove();
			}
		});

		//如果联系人=1，则添加选中样式
		if($('.contact-list > ul').length == 1)
		{
			$('.contact-list > ul').addClass('active');
		}
		if($('.contact-list > ul').index() == 0)
		{
			$('.message-box').addClass('hide');
		}
	});
}


//获取cookie中的用户ID
/*function getCookieUid()
{
	var arrCookie = document.cookie.split('; ');
	for(var i=0; i<arrCookie.length; i++)
	{
		var arr = arrCookie[i].split('=');
		if('uid' == arr[0])
		{
			return arr[1];
		}
	}
}*/

//滚动条置底
function scrollBottom(obj)
{
	$(obj).scrollTop( $(obj)[0].scrollHeight );
}

//初始化弹幕
function initScreen(w)
{
	var _top = 0;
	w = parseInt(w) + 150; //表示弹幕划出屏幕之后再向左滑动150px
	$('.barrage-box').find('div').show().each(function()
	{
		var _left = $('.barrage-box').width();
		var _height = $('.barrage-box').height();
		_top = _top + 40;
		if(_top >= _height)
		{
			_top = 0;
		}
		$(this).css(
		{
			left:_left,
			top:_top,
			color:getReandomColor()
		});
		var time = 5000;
		if($(this).index()%2 == 0)
		{
			time = 7000;
		}
		$(this).animate({left:'-'+ w +'px'}, time, function()
		{
			$(this).remove();
		});
	});
}
//弹幕随机获取颜色值
function getReandomColor()
{
	return '#' + (function(h)
	{
		return new Array(7 - h.length).join('0') + h;
	})((Math.random()*0x1000000<<0).toString(16));
}	

//关注TA
function concernTa(uid)
{
	$.ajax({
		type: 'GET',
		url: '/app/1/addFollow?uid='+ uid,
		dataType: 'json',
		async: true,
		success: function(data){
			console.log(data);
			if(data.c == 0){
                layer.confirm(data.m,
                {
                    title:false,
                    area:["300px","100px"],
                    btn:false
                });
			}
			else if(data.c == 1){
				$('.concernTa').text('已关注').attr('href','javascript:void(0);');
			}
		}
	});
}

//对TA公聊
function toTaChat(nick)//传参
{
	$('.send-ways p').text('@' + nick);
}

//禁言
function addMute(rid, uid)
{
	console.log('禁言');
	console.log(rid);
	console.log(uid);
	$.ajax({
		type: 'GET',
		url: '/app/1/addMute?rid='+rid+'&uid='+uid,
		dataType: 'json',
		async: true,
		success: function(data){
			if(data.c == 0){
                layer.confirm(data.m,
                {
                    title:false,
                    area:["300px","100px"],
                    btn:false
                });
			}
		}
	});
}

//取消禁言
function cancelMute(rid, uid)
{
	console.log('取消禁言');
	console.log(rid);
	console.log(uid);
	$.ajax({
		type: 'GET',
		url: '/app/1/cancelMute?rid='+rid+'&uid='+uid,
		dataType: 'json',
		async: true,
		success: function(data){
			if(data.c == 0){
                layer.confirm(data.m,
                {
                    title:false,
                    area:["300px","100px"],
                    btn:false
                });
			}
		}
	});
}

//踢出房间
function addKick(rid, uid)
{
	console.log('踢出房间');
	console.log(rid);
	console.log(uid);
	$.ajax({
		type: 'GET',
		url: '/app/1/addKick?rid='+rid+'&uid='+uid,
		dataType: 'json',
		async: true,
		success: function(data){
			if(data.c == 0){
                layer.confirm(data.m,
                {
                    title:false,
                    area:["300px","100px"],
                    btn:false
                });
			}
		}
	});
};

//当发送的信息超过200条时清空列表
function removeMsg()
{
	if($('.chat-box > ul > li').length > 200)
    {
        $('.chat-box > ul > li')[0].remove();
    }
}

//申请私密直播
function switchLive()
{
	$('#rewardLayer').hide();
	$('#lianmaiLayer').hide();
	$('#switchLayer').slideToggle();
}
//申请连麦
/*function switchLianmai()
{
	$('#rewardLayer').hide();
	$('#switchLayer').hide();
	$('#lianmaiLayer').slideToggle();
}*/