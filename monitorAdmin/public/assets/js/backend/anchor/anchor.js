define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'anchor/anchor/index',
                    add_url: '',
                    del_url: '',
                    multi_url: '',
                    banlive_url: 'anchor/chatanchor/forbidden',
                    hot_url : 'anchor/room/hot',
                    sign_url : 'anchor/anchor/sign',
                    play_url: 'anchor/anchor/play',
                    cancelsign_url : 'anchor/anchor/cancelsign',
                    status_url : 'anchor/anchor/status',
                    hotlive_url : 'anchor/room/hotlive'
                }
            });

            var table = $("#table");


            // 指定搜索条件
            $(document).on("click", ".btn-singlesearch", function () {
                var options = table.bootstrapTable('getOptions');
                var queryParams = options.queryParams;
                options.pageNumber = 1;
                var data_type = $(this).attr('data-type');
                if(data_type == 'hot'){
                    $(this).html("<i class=\"fa fa-user\"></i> 全部");
                    $(this).attr('data-type','all');
                }else{
                    $(this).html("<i class=\"fa fa-user\"></i> 红人主播");
                    $(this).attr('data-type','hot');
                }
                options.queryParams = function (params) {
                    //这一行必须要存在,否则在点击下一页时会丢失搜索栏数据
                    params = queryParams(params);

                    //如果希望追加搜索条件,可使用
                    var filter = params.filter ? JSON.parse(params.filter) : {};
                    var op = params.op ? JSON.parse(params.op) : {};
                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
                    params.hot_flg = data_type;

                    //如果希望忽略搜索栏搜索条件,可使用
                    //params.filter = JSON.stringify({url: 'login'});
                    //params.op = JSON.stringify({url: 'like'});
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;
            });
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'user_id', title: 'User id'},
                        {field: 'user.user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user.user_avatar', title: __('User avatar'), operate: false, formatter: function (value, row, index, custom) {
                            return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>'; 
                            // return Table.api.formatter.image.call(this, value, row, index, custom);
                        }},
                        {field: 'user.user_fans_total', title: __('累计总收益'),operate: false,formatter:function(value, row, index){
                                return (parseFloat(row.user.user_collect_total) + parseFloat(row.user.user_collect_free_total) + parseFloat(row.user.user_invite_dot_total)).toFixed(4);
                            }},
                        {field: 'user.user_collect_total', title: __('充值金币收益'),operate: false,visible:false},
                        {field: 'user.user_collect_free_total', title: __('赠送金币收益'),operate: false,visible:false},
                        {field: 'user.user_invite_dot_total', title: __('邀请收益'),operate: false,visible:false},
                        {field: 'anchor_called_count', title: __('接通率'),operate: false, formatter:function(value, row, index, custom){
                                if(value == 0){
                                    return '100%';
                                }else{
                                    return ((parseInt(row.anchor_chat_count) / parseInt(row.anchor_called_count)) * 100).toFixed(2) + '%';
                                }
                            }},
                        {field: 'anchor_called_count', title: __('有效被点播数'),operate: false,visible:false},
                        {field: 'anchor_chat_count', title: __('有效接受点播数'),operate: false,visible:false},
                        {field: 'anchor_chat_status', title: __('Chat status'), searchList:{
                                '0' : __('Off chat'),
                                '1' : __('Offline'),
                                '2' : __('On chat'),
                                '3' : __('Free chat'),
                            },
                            formatter:function (value, row, index) {
                                switch (value){
                                    case 0:
                                        return __('Off chat');
                                    case 1:
                                        return __('Offline');
                                    case 2:
                                        return __('On chat');
                                    case 3:
                                        return __('Free chat');
                                }
                            }},
                        {field: 'anchor_private_forbidden', title: __('Ban chat'), searchList:{
                                '1' : __('Yes'),
                                '0' : __('No'),
                            },
                            formatter:function (value, row, index) {
                                return value == '1' ? __('Yes') : __('No');
                            }},
                        {field: 'anchor_today_match_duration', title: __('今日匹配时长'),operate: false,sortable:true,
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }},
                        {field: 'anchor_total_match_duration', title: __('总匹配时长'),operate: false,sortable:true,
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }},
                        {field: 'anchor_hot_man', title: __('Hot man'), operate:false,sortable:true,
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.hotlive(value, row, index);
                            }},
                        {field: 'anchor_hot_time', title: __('热门'), operate:false,sortable:true,
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.hottime(value, row, index);
                            }},
                        {field: 'anchor_is_sign', title: __('Sign anchor'), searchList:{
                                'Y' : __('Yes'),
                                'N' : __('No'),
                            },
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.signanchor(value, row, index);
                            }},
                        {field: 'anchor_is_show_index', title: __('Show index'), searchList:{
                                'Y' : __('Yes'),
                                'N' : __('No'),
                            },
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.showindex(value, row, index,'anchor_is_show_index');
                            }},
                        {field: 'anchor_video_cover', title: __('Cover'), operate: false, formatter:function(value, row, index, custom){
                                if(value){
                                    return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                                }else{
                                    return '--';
                                }
                            }},
                        {field: 'anchor_video', title: __('Down url'), operate: false, formatter:function(value, row, index, custom){
                                return Table.api.formatter.url.call(this, value, row, index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {name: 'play', icon: 'fa fa-play-circle', classname: 'btn btn-xs btn-info btn-detail btn-dialog', url:$.fn.bootstrapTable.defaults.extend.play_url, title:__('Video')+__('Play'), text:__('Play')}
                            ],
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                if(row.anchor_video == ''){
                                    this.table.data('operate-play', false);
                                }else{
                                    this.table.data('operate-play', true);
                                }
                                return Table.api.formatter.operate.call(this, value, row, index)+Controller.api.formatter.banlive(value, row, index);
                            }}

                    ]
                ],

                // templateView: true,
                // search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 12,
                pk: 'user_id',
                sortName: 'user_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        signlist: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'anchor/anchor/signlist',
                    edit_url: 'anchor/anchor/sign',
                    multi_url: '',
                    sign_url : 'anchor/anchor/sign',
                    cancelsign_url : 'anchor/anchor/cancelsign',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'user_id', title: 'User id'},
                        {field: 'user.user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user.user_avatar', title: __('User avatar'), operate: false, formatter: function (value, row, index, custom) {
                                return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                                // return Table.api.formatter.image.call(this, value, row, index, custom);
                            }},

                        {field: 'anchor_sign_live_start_time', title: __('Sign online time'),operate:false,formatter: function (value, row, index, custom) {
                            let start_time = row.anchor_sign_live_start_time;
                            let end_time = row.anchor_sign_live_end_time;
                                if(start_time == 0){
                                    start_time = 24;
                                }
                                if(start_time < 10){
                                    start_time = '0' + start_time;
                                }
                                if(end_time == 0){
                                    end_time = 24;
                                }
                                if(end_time < 10){
                                    end_time = '0' + end_time;
                                }
                                return start_time + ':00 - ' + end_time + ':00';
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index)+Controller.api.formatter.signanchor(value, row, index);
                            }}

                    ]
                ],

                // templateView: true,
                // search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 12,
                pk: 'user_id',
                sortName: 'user_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
            // 给上传按钮添加上传成功事件
            $("#plupload-avatar").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $(".profile-img").prop("src", url);
                Toastr.success(__('Upload success'));
            });
        },
        sign: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        detail:function(){
            Table.api.init({
                search:false,
                showToggle:false,
                showColumns:false,
                showExport:false,
                extend: {
                    detail_url: 'anchor/anchor/detail'
                }
            });
            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.detail_url,

                columns: [
                    [
                        {field: 'live', title: __('Live time static'),operate:false},
                        {field: 'chat', title: __('Chat time'), operate: false},
                        {field: 'date', title: __('Date'), type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD"', formatter:function (value, row, index) {
                                return Table.api.formatter.date.call(this, value, row, index);
                        }},
                        {field: 'type', title: __('Query type'), searchList:{
                                '1' : __('Day'),
                                '2' : __('Week'),
                                '3' : __('Month')
                            },
                            formatter:function (value, row, index) {
                                switch(value){
                                    case '1':
                                        return __('Day');
                                    case '2':
                                        return __('Week');
                                    case '3':
                                        return __('Month');
                                    default:
                                        return __('Day');
                                }
                        }}
                    ]
                ]
            });
        },
        multi: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                banlive: function (value, row, index) {
                    var btn = 'danger';
                    var title = __('Ban live');
                    var anchor_private_forbidden = 1;
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.banlive_url);

                    switch (row.anchor_private_forbidden) {
                        case 0:
                            btn   = 'danger';
                            title = __('Ban chat');
                            anchor_private_forbidden  = 1;
                            break;

                        default :
                            btn   = 'success';
                            title = __('Release');
                            anchor_private_forbidden  = 0;
                            break;
                    }

                    return " <a href='javascript:;' class='btn btn-" + btn + " btn-xs btn-change btn-tipsone' data-id='" +
                        row.user_id + "' data-url='" + url + "' data-params='"+ anchor_private_forbidden +"'><i class='fa fa-video-camera'></i> "
                        + title + "</a>";
                },
                hotlive: function (value, row, index) {
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.hot_url);
                    return "<a href='javascript:;' class='btn btn-" + (row.anchor_hot_man == 0 ? "warning" : "danger") + " btn-xs btn-change btn-disable' data-id='" +
                        row.user_id + "' data-url='" + url + "' data-params='" +
                        (row.anchor_hot_man == 0 ? 'Y' : 'N') + "'><i class='fa fa-dot'></i>"
                        + (row.anchor_hot_man > 0 ? __('Yes') : __('No')) + "</a> ";
                },
                signanchor: function (value, row, index) {
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.cancelsign_url);
                    var sign_url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.sign_url) + '/ids/'+ row.user_id;
                    if (row.anchor_is_sign ==  'Y') {
                        btn   = 'danger';
                        title = __('Cancel sign anchor');
                        param  = 'N';
                        return " <a href='javascript:;' class='btn btn-" + btn + " btn-xs btn-change btn-tipsone' data-id='" +
                            row.user_id + "' data-url='" + url + "' data-params='"+ param +"'><i class='fa fa-cogs'></i> "
                            + title + "</a>";
                    }else{
                        title = __('Sign anchor');
                        return " <a href='"+ sign_url + "' title='"+ title +"' class='btn btn-xs btn-warning btn-detail btn-dialog'><i class='fa fa-cogs'></i> "
                            + title + "</a>";
                    }
                },showindex: function (value, row, index,field) {
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.status_url);
                    return "<a href='javascript:;' class='btn btn-" + (row.anchor_is_show_index == 'N' ? "warning" : "danger") + " btn-xs btn-change btn-tipsone' data-id='" +
                        row.user_id + "' data-url='" + url + "' data-params='" +
                        field + "=" + (row.anchor_is_show_index == 'N' ? 'Y' : 'N')  + "'><i class='fa fa-dot'></i>"
                        + (row.anchor_is_show_index == 'Y' ? __('Yes') : __('No')) + "</a> ";
                },
                hottime: function (value, row, index) {
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.hotlive_url);
                    return "<a href='javascript:;' class='btn btn-" + (row.anchor_hot_time == 0 ? "warning" : "danger") + " btn-xs btn-change btn-tipsone' data-id='" +
                        row.user_id + "' data-url='" + url + "' data-params='" +
                        (row.anchor_hot_time == 0 ? 'Y' : 'N') + "'><i class='fa fa-dot'></i>"
                        + (row.anchor_hot_time > 0 ? __('Yes') : __('No')) + "</a> ";
                },
                formatSeconds:function(value){
                    let secondTime = parseInt(value);// 秒
                    let minuteTime = 0;// 分
                    let hourTime = 0;// 小时
                    if(secondTime > 60) {//如果秒数大于60，将秒数转换成整数
                        //获取分钟，除以60取整数，得到整数分钟
                        minuteTime = parseInt(secondTime / 60);
                        //获取秒数，秒数取佘，得到整数秒数
                        secondTime = parseInt(secondTime % 60);
                        //如果分钟大于60，将分钟转换成小时
                        if(minuteTime > 60) {
                            //获取小时，获取分钟除以60，得到整数小时
                            hourTime = parseInt(minuteTime / 60);
                            //获取小时后取佘的分，获取分钟除以60取佘的分
                            minuteTime = parseInt(minuteTime % 60);
                        }
                    }
                    let result = "" + parseInt(secondTime) + "秒";

                    if(minuteTime > 0) {
                        result = "" + parseInt(minuteTime) + "分" + result;
                    }
                    if(hourTime > 0) {
                        result = "" + parseInt(hourTime) + "小时" + result;
                    }
                    return result;
                }
            },
        }
    };
    return Controller;
});