define(['jquery', 'bootstrap', 'backend', 'table', 'form','layer', 'upload'], function ($, undefined, Backend, Table, Form,Layer,Upload) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/anchor/index',
                    add_url: '',
                    play_url: 'user/anchor/play',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true},
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
                        {field: 'anchor_chat_status', title: __('通话状态'), searchList:{
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
                        {field: 'anchor_private_forbidden', title: __('禁止私聊'), searchList:{
                                '1' : __('Yes'),
                                '0' : __('No'),
                            },
                            formatter:function (value, row, index) {
                                return value == '1' ? __('Yes') : __('No');
                            }},
                        {field: 'anchor_today_match_duration', title: __('今日匹配时长'),operate: 'BETWEEN',sortable:true,
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }},
                        {field: 'anchor_total_match_duration', title: __('总匹配时长'),operate: 'BETWEEN',sortable:true,
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }},
                        {field: 'anchor_today_normal_duration', title: __('今日点播时长'),operate: 'BETWEEN',sortable:true,
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }},
                        {field: 'anchor_total_normal_duration', title: __('总点播时长'),operate: 'BETWEEN',sortable:true,
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }},
                        {field: 'anchor_today_called_times', title: __('今日有效点播呼叫次数'), operate: 'BETWEEN', sortable: true},
                        {field: 'anchor_today_normal_times', title: __('今日点播次数'), operate: 'BETWEEN', sortable: true},
                        {field: 'anchor_video_cover', title: __('封面'), operate: false, formatter:function(value, row, index, custom){
                                if(value){
                                    return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                                }else{
                                    return '--';
                                }
                            }},
                        {field: 'anchor_video', title: __('封面视频下载地址'), operate: false, formatter:function(value, row, index, custom){
                                return Table.api.formatter.url.call(this, value, row, index);
                            }},
                        {field: 'user.user_logout_time', title: __('最后下线时间'), sortable:true,operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {name: 'play', icon: 'fa fa-play-circle', classname: 'btn btn-xs btn-info btn-detail btn-dialog', url:$.fn.bootstrapTable.defaults.extend.play_url, title:__('Video')+__('播放'), text:__('播放')}
                            ],
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index);
                            }}

                    ]
                ],

                // templateView: true,
                search: false,
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
        multi: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
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