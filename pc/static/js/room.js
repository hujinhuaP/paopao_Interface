var APP = {}

var WatchTimer = {
    "timerID": null,
    "HH": 0,
    "mm": 0,
    "ss": 0,
    "timeS": 0,
    "start": function (config, callback) {
        var _this = this;
        this.timerID = setInterval(function () {
            if (config.way == 1 && ++_this.timeS >= config.freeTime) {
                _this.stop()
            }
            var time = '';
            if(++_this.ss == 60) {

                if (++_this.mm == 60) {
                    _this.HH++;
                    _this.mm = 0;
                }
                _this.ss = 0;
                
                var $timeEm = $(config.coinDomID + " > em")
                var chargeLedian = parseInt($timeEm.text()) + config.price;
                $timeEm.text(chargeLedian);

                if (typeof callback == 'function') {
                    callback()
                }
            }
            time += _this.HH < 10 ? ('0' + _this.HH) : _this.HH;
            time += ':';
            time += _this.mm < 10 ? ('0' + _this.mm) : _this.mm;
            time += ':';
            time += _this.ss < 10 ? ('0' + _this.ss) : _this.ss;
            $(config.timerDomID).text(time);

        }, 1000)
    },
    "stop": function () {
        console.log('stop...')
        clearInterval(this.timerID)
    }
}

APP.socket = {
    'handle':undefined,
    'reconnectTimes':0,
    'init':function(url, notifyType, callback){
        this.callback = callback;
        this.connect(url, notifyType);
    },
    'connect':function(url, notifyType){
        var ws = new WebSocket(url);
        this.handle = ws;

        ws.onopen = function() {
            console.log("ws.onopen");
            APP.socket.callback &&  APP.socket.callback();

            //ws.send("Hello");  // Sends a message.

        };
        ws.onmessage = function(msg) {

            console.log(notifyType);
            var msg = $.parseJSON(msg.data);
            console.log(msg);
            // console.log(msg)
            if(!$.isArray(msg)){
                msg = [msg];
            }
            // console.log(APP[notifyType][msg.type]);
            for(var i in msg){
                var obj = msg[i];
                if(typeof APP[notifyType][obj.type] !== 'undefined'){
                    APP[notifyType][obj.type](obj.data);
                }
            }
        };
        ws.onclose = function() {
            console.log("closed");

            if(APP.socket.reconnectTimes < 5){
                setTimeout(function(){
                    APP.socket.connect();
                    APP.socket.reconnectTimes++;
                },5*1000);
            }
        };
    }
}

APP.flashPlayer = {
    'playSwf':undefined,
    'init':function() {
        var params = {
            "menu": "false",
            "scale": "noScale",
            "allowFullscreen": "true",
            "allowScriptAccess": "always",
            "bgcolor": "",
            "wmode": "transparent"
        };

        var attributesPlay = {
            "id" : "flash-player"
        };
        var flashvarsPlay = ROOM_INFO.live
        var playSwf = STATIC_PATH+"flash/player.swf";
        if(ROOM_INFO.live.type == 'play'){
            if(ROOM_INFO.live.live_device == "app"){
                flashvarsPlay.core = STATIC_PATH+"flash/liveV.jpg?v1.0.2";
            }
            else{
                flashvarsPlay.core = STATIC_PATH+"flash/live.jpg?v1.0.2";
            }
        }else{
            flashvarsPlay.core = STATIC_PATH+"flash/camera.jpg?v1.0.2";
        }

        swfobject.embedSWF(
            playSwf,
            "flash-player",
            "100%",
            "100%",
            "10.0.0",
            STATIC_PATH+"flash/expressInstall.swf",
            flashvarsPlay,
            params,
            attributesPlay,
            function(obj){
                if(obj.success){
                    APP.flashPlayer.playSwf = obj.ref;
                }else{
                    layer.confirm('<div class="layer_out_info">请先确认是否安装了最新版本的  Flash Player<br>如有疑问请联系客服</div>', {
                        title: false,
                        btn: ['确定'],
                        closeBtn: 0,
                        area: ['360px', '240px'],
                        yes: function(){
                            window.open('http://get.adobe.com/cn/flashplayer/');
                            layer.closeAll('dialog');
                        }
                    });

                }
            }
        );
    }

}

// 房间
APP.notify = {
    // 'last_time':0,
    // 'onlines':function(data) {
    //     console.log(data);
    //     // 当前观众列表的渲染
    //     var dataLen=data.length;
    //     // $(".mouseTab li a.aud-num").html('"观众("+dataLen+")"');
    //     for (var i=0;i<dataLen;i++) {
    //         var tpl = '<li>'+
    //                     '<dd><i>'+(i+1)+'</i></dd>'+
    //                     '<dd>'+
    //                         '<div><img src="'+data[i].avatar+'"/></div>'+
    //                     '</dd>'+
    //                     '<dd><a href="javascript:;">'+data[i].nick+'</a></dd>'+
    //                     '<dd><p>贡献乐点</p></dd>'+
    //                ' </li>';
    //         // var bargz = '<li id="id'+data[i].id+'">'+
    //         //                 '<dd>'+
    //         //                     '<div><img src="'+data[i].avatar+'"/></div>'+
    //         //                 '</dd>'+
    //         //                 '<dd><p>'+data[i].nick+'</p></dd>'+
    //         //                 '<dd class="lvluser fr"><div class="dib dengji"><span class="fl level"></span><div class="fr gardNum tr">'+data[i].richlvl+'</div></div></dd>'+
    //         //              '</li>';
    //         $(".audience-box ul").append(tpl);
    //     }
    // },
    //进入房间
    'join':function(data){
        /*console.log(data);
        console.log("进入房间的人的信息:");
        console.log(ROOM_INFO.rid);
        console.log(ROOM_INFO.uid);
        console.log(data.fuser.id);*/
        // 判断登录id和用户id是否一致
        if(data.fuser.id == ROOM_INFO.uid)
        {
            var tpl=
                        '<span>'+
                            '欢迎<em class="fuuser">'+ data.fuser.nick +'</em>进入房间'+
                        '</span>'+
                    
        }else
        {
            var tpl='<li>'+
                        '<span>欢迎'+
                            '<em class="fuuser ml5 mr5 hand" onclick="addDropdownMenu(this,'+ data.fuser.id +')">'+ data.fuser.nick;    
                            tpl += '</em>进入房间'+
                        '</span>'+
                    '</li>';
        };

        $('.chat-top  li .join').append($(tpl));


        //滚动条置底
        scrollBottom('.chat-box');
        //当发送的信息超过200条时清空列表
        removeMsg();

        /*// 获取进入用户的id
        joinid=data.fuser.id;
        // 用户进入直播间的效果
        // 判断用户是否是当前自己进入的用户
        if(joinid == cookieFun()){
            var joinhtm = '<li>'+
                            '<span>欢迎 '+
                                '<em class="nickname">'+data.fuser.nick+
                                '</em> 进入房间'+
                            '</span>'+
                          '</li>'; 
        }else{
            var joinhtm = '<li>'+
                            '<span>欢迎 '+
                                '<em class="nickname">'+data.fuser.nick;
                                if(joinid < 100000000){
                                joinhtm +=  '<ul>';
                                        joinhtm += '<li><a href="/user/studioAnchorDetails/'+ data.fuser.id +'" target="_blank">关注TA</a></li>' +
                                                   '<li><a href="javascript:toTaChat(\''+ data.fuser.nick +'\');">对TA公聊</a></li>';  
                                        
                                        if(ROOM_INFO.id == cookieFun()){
                                            joinhtm += '<li><a href="javascript:theGag('+ ROOM_INFO.id +','+ data.fuser.id +');">禁言</a></li>' +
                                                        '<li><a href="javascript:theOut('+ ROOM_INFO.id +','+ data.fuser.id +');">踢出房间</a></li>';
                                        }
                                joinhtm +=  '</ul>';
                                }
                    joinhtm +=  '</em> 进入房间'+
                            '</span>'+
                          '</li>';
        }
        joinhtm=$(joinhtm);
        $(".down_chat>ul").append(joinhtm);
        scrollBottom(".tab_item_con");
        objLi=$(".down_chat>ul li");
        removeMsg(objLi);
        // 判断观众列表是否存在该用户
        if ($(".viewer_list li#id"+joinid).index() == -1) {
             var bargz = '<li id="id'+joinid+'" onclick="window.open(\'/user/studioAnchorDetails/'+joinid+'\');">'+
                            '<dd>'+
                                '<div><img src="'+data.fuser.avatar+'"/></div>'+
                            '</dd>'+
                            '<dd><p>'+data.fuser.nick+'</p></dd>'+
                            '<dd class="lvluser fr">\
                                <div class="dib dengji"><span class="fl level"></span>\
                                <div class="fr gardNum tr">' + data.fuser.viplvl + '</div></div>\
                            </dd>'+
                         '</li>';
            $(".viewer_list").append(bargz);
        }
        //观众榜的实时
        var currTime = new Date().getTime();
        if ((currTime - this.last_time) > 1000) {
            $.ajax({
                type:"GET",
                url:"/app/1/getRoomUsers?rid="+roomId+"&limit=10",
                dataType:"json",
                async:false,
                success:function(data){
                    $(".middle_cont>div.tabgz").find("ul").html("");
                    console.log(data);
                  // 判断是否成功
                  if(data.c==0){
                    $(".middle_cont>div.tabgz").html("暂时没有数据");
                  }else{
                     $(".middle_cont>div.tabgz").find("ul").html("");
                      for(var i=0;i<data.d.items.length;i++){
                        var gxhtm= '<li>'+
                                        '<dd><i>'+(i+1)+'</i></dd>'+
                                        '<dd>'+
                                            '<div><img src="'+data.d.items[i].avatar+'"/></div>'+
                                        '</dd>'+
                                        '<dd><p>'+data.d.items[i].nick+'</p></dd>'+
                                        '<dd><div class="dib dengji"><span class="fl level"></span><div class="fr gardNum tr">'+data.d.items[i].richlvl+'</div></div></dd>'+
                                    '</li>';
                        $(".middle_cont>div.tabgz").find("ul").append(gxhtm);
                      }
                  }
                }
            });
            // $(".lock_btn").hide();
            scrollBottom(".tab_item_con");
            this.last_time = currTime;
        };
        // 更新在线人数
        APP.updOnlines();*/
    },
    //赠送礼物
    'sendgift':function(data){
        //获取系统当前时间
        var hours = new Date().getHours();
        var minutes = (new Date().getMinutes() >= 10 )?new Date().getMinutes():'0'+ new Date().getMinutes();
        var time = hours +":"+ minutes;
        var tpl = '';
        // 判断登录id和用户id是否一样
        if(data.fuser.id == ROOM_INFO.uid){
            tpl='<li>' +
                    '<span>' +
                        '<i>'+ time +'</i>' +
                        '<span class="fuuser mr5">我</span>赠送' +
                        '<span class="tuser">'+ data.tuser.nick +'</span>' +
                        '<span>'+ data.num +'</span>个' + data.gift.name + 
                        '<span><img src="/upload/'+ data.gift.icon +'" alt=""></span>' +
                    '</span>' +
                '</li>';
        }else{
            tpl='<li>' +
                    '<span>' +
                        '<i>'+ time +'</i>' +
                        '<span class="fuuser mr5 hand" onclick="addDropdownMenu(this,'+ data.fuser.id +')">'+ data.fuser.nick +'</span>发送给' +
                        '<span class="tuser">'+ data.tuser.nick +'</span>' +
                        '<span>'+ data.num +'</span>个' + data.gift.name + 
                        '<span><img src="/upload/'+ data.gift.icon +'" alt=""></span>' +
                    '</span>' +
                '</li>';
        }
        $('.chat-box > ul').append($(tpl));
        //主播乐点更新
        // $('.info-details ul li.anchor-ld').text('乐点：' + data.gain_dot_total);
        //滚动条置底
        scrollBottom('.chat-box');
        //当发送的信息超过200条时清空列表
        removeMsg();
    },
    //全局礼物
    /*'scrollgift':function(data){
        console.log(data);
        var div = '<div class="giftMsg">'+
                        '<p class="imgUser dib"><img src="'+data.fuser.avatar+'" alt=""></p>'+
                        '<ul class="userMSG dib">'+
                           '<li><span>'+data.fuser.nick+'</span></li>'+
                           '<li><span>送了'+data.num+'个'+data.gift.name+'</li>'+
                        '</ul>'+
                        '<p class="imgUser dib"><img src="'+data.gift.icon+'" alt=""></p>'+
                        '<span class="orange num">X'+data.num+'</span>'+
                  '</div>';
        var $div = $(div);
        $(".barrage_box").append($div);
        init_screen($div.width());
    },*/
    //打赏TA
    'reward':function(data){
        //获取系统当前时间
        var hours = new Date().getHours();
        var minutes = (new Date().getMinutes() >= 10 )?new Date().getMinutes():'0'+ new Date().getMinutes();
        var time = hours +":"+ minutes;
        var tpl='<li>' +
                    '<span>' +
                        '<i>'+ time +'</i>';
                        if(data.fuser.id == ROOM_INFO.uid){
                            tpl += '<span class="fuuser mr5">我</span>打赏';
                        }else{
                            tpl += '<span class="fuuser mr5 hand" onclick="addDropdownMenu(this,'+ data.fuser.id +')">'+ data.fuser.nick +'</span>打赏';
                        }
                        tpl += '<span class="tuser ml5 mr5">' + ROOM_INFO.anchor.nick + '</span>' +
                               '<span>'+ data.money +'</span>个乐点' +
                    '</span>' +
                '</li>';
        $(".chat-box > ul").append($(tpl));
        //滚动条置底
        scrollBottom('.chat-box');
        //当发送的信息超过200条时清空列表
        removeMsg();
    },
    //用户申请切换私密直播
    'apply_anchor_change_pri_live':function(data){
        console.log('用户申请切换私密直播');
        console.log(data);        
        //显示主播端消息提示层
        $('.have-apply-news').removeClass('hide');
        var msgCount = parseInt($('.have-apply-news').text());
        $('#applyPriNum,.have-apply-news').text(++msgCount);
    },
    //同意
    'change_pri_live':function(data){
        if(ROOM_INFO.is_anchor == true)
        {
            //显示主播端私密直播标识层
            $('.anchor-private-live').removeClass('hide');
            return ;
        }
        if($.inArray(ROOM_INFO.uid+'', data) >= 0)
        {
            location.href = '?is_confirm_charge=1';
        }
        else 
        {
            //给未同意的用户发送推送：主播被挖走
            layer.confirm('主播已被挖走',{
                title:' ',
                btn:['去申请'],
                area:['300px'],
                yes:function(){
                    location.href = '/live/user/' + ROOM_INFO.anchor.id;
                }
            });
        }
    },
    //用户申请观看私密直播
    'privacyRequest':function(data){
        //显示主播端消息提示层
        $('.have-apply-news').show();
        var msgCount = parseInt($('.have-apply-news').text());
        $('#applyPriNum,.have-apply-news').text(++msgCount);
    },
    //发言
    'sendpubmsg':function(data){
        // console.log(ROOM_INFO);
        //获取系统当前时间
        var hours = new Date().getHours();
        var minutes = (new Date().getMinutes() >= 10 )?new Date().getMinutes():'0'+ new Date().getMinutes();
        var time = hours +":"+ minutes;
        console.log(data);
        //表情替换
        var arr = data.msg.match(/\[[\u4e00-\u9fa5|A-Z|^\]].*?\]/g);
        console.log(arr);
        console.log(arr);
        for (var e in arr) {
            // console.log(arr[a])
            data.msg = data.msg.replace(arr[e], '<img class="emoji" src="' + ROOM_INFO.emojis[arr[e]]['emojiImg'] + '" />');
        }
        console.log(data.msg);
        //html
        var tpl='<li>' +
                    '<i>'+ time +'</i>' +
                    '<span>';
                        if(data.fuser.id == ROOM_INFO.uid){
                            tpl += '<em class="fuuser">我</em>';
                        }else{
                            tpl += '<em class="fuuser hand" onclick="addDropdownMenu(this,'+ data.fuser.id +')">' + data.fuser.nick;
                            tpl += '</em>';
                        }
                        tpl += '<small> : </small>' +
                    '</span>' +
                    '<span>'+ data.msg +'</span>' +
                '</li>';
        $('.chat-box > ul').append($(tpl));
        //滚动条置底
        scrollBottom('.chat-box');
        //当发送的信息超过200条时清空列表
        removeMsg();
    },
    //弹幕
    'sendlaba':function(data){
        var div = "<div>【"+ data.fuser.nick +"】说： "+ data.word +"</div>";
        div = $(div);
        $(".barrage-box").append(div);
        initScreen(div.width());
    },
    //禁言
    'addmute':function(data){
        var tpl =   '<li>'+
                        '<span>'+
                            '用户<em class="fuuser ml5 mr5">'+ data.tuser.nick +'</em>被禁言了'+
                        '</span>'+
                    '</li>';
        $('.chat-box > ul').append($(tpl));
        //滚动条置底
        scrollBottom('.chat-box');
        //当发送的信息超过200条时清空列表
        removeMsg();
    },
    //取消禁言
    'cancelmute':function(data){
        var tpl =   '<li>'+
                        '<span>'+
                            '用户<em class="fuuser ml5 mr5">'+ data.tuser.nick +'</em>已解除禁言'+
                        '</span>'+
                    '</li>';
        $('.chat-box > ul').append($(tpl));
        //滚动条置底
        scrollBottom('.chat-box');
        //当发送的信息超过200条时清空列表
        removeMsg();
    },
    //踢出房间
    'addkick':function(data){
        var tpl =   '<li>'+
                        '<span>'+
                            '用户<em class="fuuser ml5 mr5">'+ data.tuser.nick +'</em>被踢出了房间'+
                        '</span>'+
                    '</li>';
        $('.chat-box > ul').append($(tpl));
        //滚动条置底
        scrollBottom('.chat-box');
        //当发送的信息超过200条时清空列表
        removeMsg();
        
        if(data.tuser.id == ROOM_INFO.uid){
            layer.open({
                title:false,
                btn:false,
                area:["300px","100px"],
                content: '<div class="layer_out_info">你被管理员踢出了房间！<span id="kickoutTime"></span>秒后关闭</div>',
                cancel: function(){
                    layer.closeAll('dialog');
                    window.location = '/';
                    clearInterval(timer);
                    timer = null;
                }
            });
            var time = 5;
            var timer = null;
            timer = setInterval(function () {
                if(time == 0){
                    window.location = '/';
                    clearInterval(timer);
                    timer = null;
                }
                $('#kickoutTime').html(time--);
            },1000);
        }

    },
    //用户等级升级
    /*'richlvlup':function(data){
        console.log('我升级啦');
        // console.log("twst");
        var lvlhtm='<div class="dib dengji"><span class="fl level"></span><div class="fr gardNum tr">'+data.lvl+'</div></div>';
        // 通过id号找到观众列表的用户
        // 清空观众列表的等级
        $(".viewer_list li#id"+data.fuser.id+' dd.lvluser').html("");
        $(".viewer_list li#id"+data.fuser.id+' dd.lvluser').append(lvlhtm);
        // 用户等级升级的时候观众榜单的更新
        //观众榜的实时
        var currTime = new Date().getTime();
        if ((currTime - this.last_time) > 1000) {
            $.ajax({
                type:"GET",
                url:"/app/1/getRoomUsers?rid="+roomId+"&limit=10",
                dataType:"json",
                async:false,
                success:function(data){
                    $(".middle_cont>div.tabgz").find("ul").html("");
                    console.log(data);
                     console.log(this.url);
                  // 判断是否成功
                  if(data.c==0){
                    $(".middle_cont>div.tabgz").html("暂时没有数据");
                  }else{
                     $(".middle_cont>div.tabgz").find("ul").html("");

                      for(var i=0;i<data.d.items.length;i++){
                        var gxhtm= '<li>'+
                                        '<dd><i>'+(i+1)+'</i></dd>'+
                                        '<dd>'+
                                            '<div><img src="'+data.d.items[i].avatar+'"/></div>'+
                                        '</dd>'+
                                        '<dd><p>'+data.d.items[i].nick+'</p></dd>'+
                                        '<dd><div class="dib dengji"><span class="fl level"></span><div class="fr gardNum tr">'+data.d.items[i].richlvl+'</div></div></dd>'+
                                    '</li>';
                        $(".middle_cont>div.tabgz").find("ul").append(gxhtm);
                      }
                  }
                }
            });
            scrollBottom(".tab_item_con");
            this.last_time = currTime;
        };
    },*/
    //主播等级升级
    /*'anchorlvlup':function(data){
        console.log('主播升级啦');
        $(".info_details h4 div.dengji div.gardNum").html("");
        $(".info_details h4 div.dengji div.gardNum").text(data.lvl);
    },*/
    //停播
    'stopLive':function(data){
        console.log('主播停播啦');
        WatchTimer.stop()
        $(".stop-live").removeClass('hide');
    },
    //离开房间
    'leave':function(data){
        // 用户离开直播间的效果
        var tpl='<li>' +
                    '<span>用户<em class="fuuser ml5 mr5">'+data.fuser.nick+'</em>离开房间</span>'+ 
                '</li>';
        $(".chat-box > ul").append($(tpl));
        //滚动条置底
        scrollBottom('.chat-box');
        //当发送的信息超过200条时清空列表
        removeMsg();

        /*var leavehtm=   '<li>'+
                            '<span>用户 '+
                                '<em class="nickname">'+data.fuser.nick+'</em> 离开房间'+
                            '</span>'+
                        '</li>';
        leavehtm=$(leavehtm);
        $(".down_chat>ul").append(leavehtm);
        scrollBottom(".tab_item_con");
        // 离开用户的id
        var leaveID=data.fuser.id;
        $(".viewer_list li#id"+leaveID).remove();
        // 更新在线人数
        APP.updOnlines();

        //观众榜的实时
        var currTime = new Date().getTime();
        if ((currTime - this.last_time) > 1000) {
            $.ajax({
                type:"GET",
                url:"/app/1/getRoomUsers?rid="+roomId+"&limit=10",
                dataType:"json",
                async:false,
                success:function(data){
                    $(".middle_cont>div.tabgz").find("ul").html("");
                    console.log(data);
                  // 判断是否成功
                  if(data.c==0){
                    $(".middle_cont>div.tabgz").html("暂时没有数据");
                  }else{
                     $(".middle_cont>div.tabgz").find("ul").html("");
                      for(var i=0;i<data.d.items.length;i++){
                        var gxhtm= '<li>'+
                                        '<dd><i>'+(i+1)+'</i></dd>'+
                                        '<dd>'+
                                            '<div><img src="'+data.d.items[i].avatar+'"/></div>'+
                                        '</dd>'+
                                        '<dd><p>'+data.d.items[i].nick+'</p></dd>'+
                                        '<dd><div class="dib dengji"><span class="fl level"></span><div class="fr gardNum tr">'+data.d.items[i].richlvl+'</div></div></dd>'+
                                    '</li>';
                        $(".middle_cont>div.tabgz").find("ul").append(gxhtm);
                      }
                  }
                }
            });
            // $(".lock_btn").hide();
            scrollBottom(".tab_item_con");
            this.last_time = currTime;
        };*/
    },
    // 主播停止直播 主播结束页面效果
    'anchor_stop_live': function (data) {
        var $endliveLayer = $('#endliveLayer');
        var html = '<li> \
                        <dd>播放时长</dd> \
                        <dd>' + data.duration + '</dd> \
                    </li> \
                    <li> \
                        <dd>观看人数</dd> \
                        <dd>' + data.watch_num + '</dd> \
                    </li> \
                    <li> \
                        <dd>收货币</dd> \
                        <dd>' + data.coin + '</dd> \
                    </li>';
        $endliveLayer.find('ul').html(html);
        $endliveLayer.removeClass('hide');
    }
}


// 全局
APP.commonNotify = {
    //主播处理user申请观看私密直播的请求
    'privacyLiveRequest':function(data){
        if($.inArray(ROOM_INFO.uid+'', data) >= 0)
        {
            $('#waitagreeBtn').text('主播已同意，进入观看').attr('href','?is_confirm_charge=1');
        }
    }
    //私信
}



/*************************************************************/ 
