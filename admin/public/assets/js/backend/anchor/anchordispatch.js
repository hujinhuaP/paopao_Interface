define(['jquery', 'bootstrap', 'backend', 'table', 'form','layer', 'upload'], function ($, undefined, Backend, Table, Form,Layer,Upload) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'anchor/anchor_dispatch/index',
                    add_url: '',
                    edit_url: 'anchor/anchor_dispatch/edit',
                    del_url: '',
                    multi_url: '',
                    hot_url : 'anchor/room/hot',
                }
            });

            var table = $("#table");
            table.on('post-common-search.bs.table', function (event, table) {
                var form = $("form",table.$commonsearch);
                $("input[name='user.user_group_id']", form).addClass("selectpage").data("source", "group/group/selectpage").data("primaryKey", "id").data("field", "group_name").data("orderBy", "status desc,id desc");
            });


            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true},
                        {field: 'user.user_id', title: __('用户ID')},
                        {field: 'user.user_group_id', title: __('Group name'), formatter:function (value, row, index) {
                                return group[value];
                            }},
                        {field: 'user.user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user.user_avatar', title: __('User avatar'), operate: false, formatter: function (value, row, index, custom) {
                            return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>'; 
                        }},
                        {field: 'anchor_hot_man', title: __('Hot man'), searchList:{
                                '1' : __('Yes'),
                                '0' : __('No'),
                            },
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.hotlive(value, row, index);
                            }},
                        {field: 'anchor_dispatch_total_dot', title: __('累计派单收益值'),operate: false},
                        {field: 'anchor_dispatch_today_duration', title: __('今日派单总时长'),operate: false,formatter:function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }},
                        {field: 'anchor_dispatch_total_duration', title: __('累计派单总时长'),operate: false,formatter:function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }},
                        {field: 'anchor_dispatch_total_number', title: __('派单接通率'),operate: false, formatter:function(value, row, index, custom){
                                if(value == 0){
                                    return '-';
                                }else{
                                    return ((parseInt(row.anchor_dispatch_success_number) / parseInt(row.anchor_dispatch_total_number)) * 100).toFixed(2) + '%';
                                }
                            }},
                        {field: 'anchor_dispatch_total_number', title: __('派单转化率'),operate: false, formatter:function(value, row, index, custom){
                                if(value == 0){
                                    return '-';
                                }else{
                                    return ((parseInt(row.anchor_dispatch_recharge_number) / parseInt(row.anchor_dispatch_total_number)) * 100).toFixed(2) + '%';
                                }
                            }},
                        {field: 'anchor.anchor_chat_status', title: __('Chat status'), searchList:{
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
                        {field: 'anchor_dispatch_today_times', title: __('今日派单数量'),
                            formatter:function (value, row, index) {
                                return row.anchor_dispatch_today_times + '(' + row.anchor_dispatch_max_day_times + ')';
                            }},
                        {field: 'anchor_dispatch_today_times', title: __('状态'),operate: false,
                            formatter:function (value, row, index) {
                                return value >= row.anchor_dispatch_max_day_times ? __('已派完') : __('可派单');
                            }},
                        {field: 'anchor_dispatch_open_flg', title: __('是否打开'), searchList:{
                                'Y' : __('开启'),
                                'N' : __('关闭'),
                            },
                            formatter:function (value, row, index) {
                                switch (value){
                                    case 'Y':
                                        return __('开启');
                                    case 'N':
                                        return __('关闭');
                                }
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
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
                pk: 'anchor_dispatch_id',
                sortName: 'anchor_dispatch_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function(){
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                status: function (value, row, index, field) {
                    var btn = 'success';
                    var title = __('Enable');
                    var param = 'Y';
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.status_url);
                    var icon = 'fa fa-long-arrow-up';
                    switch (value) {
                        case 'N':
                            btn   = 'warning';
                            title = __('No');
                            param  = 'Y';
                            icon = 'fa';
                            break;
                        case 'Y':
                        default :
                            btn   = 'danger';
                            title = __('Yes');
                            param  = 'N';
                            icon = 'fa';
                            break;
                    }
                    return " <a href='javascript:;' class='btn btn-" + btn + " btn-xs btn-change btn-tipsone' data-id='" +
                        row.user_id + "' data-url='" + url + "' data-params='" + field + "=" + param +"'> "
                        + title + "</a>";
                },
                hotlive: function (value, row, index) {
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.hot_url);
                    return "<a href='javascript:;' class='btn btn-" + (row.anchor_hot_man == 0 ? "warning" : "danger") + " btn-xs btn-prompt btn-disable' data-id='" +
                        row.user_id + "' data-url='" + url + "' data-params='" +
                        (row.anchor_hot_man == 0 ? 'Y' : 'N') + "'><i class='fa fa-dot'></i>"
                        + (row.anchor_hot_man > 0 ? __('Yes') : __('No')) + "</a> ";
                },
                formatSeconds: function (value) {
                    let secondTime = parseInt(value);// 秒
                    let minuteTime = 0;// 分
                    let hourTime = 0;// 小时
                    if (secondTime > 60) {//如果秒数大于60，将秒数转换成整数
                        //获取分钟，除以60取整数，得到整数分钟
                        minuteTime = parseInt(secondTime / 60);
                        //获取秒数，秒数取佘，得到整数秒数
                        secondTime = parseInt(secondTime % 60);
                        //如果分钟大于60，将分钟转换成小时
                        if (minuteTime > 60) {
                            //获取小时，获取分钟除以60，得到整数小时
                            hourTime = parseInt(minuteTime / 60);
                            //获取小时后取佘的分，获取分钟除以60取佘的分
                            minuteTime = parseInt(minuteTime % 60);
                        }
                    }
                    let result = "" + parseInt(secondTime) + "秒";

                    if (minuteTime > 0) {
                        result = "" + parseInt(minuteTime) + "分" + result;
                    }
                    if (hourTime > 0) {
                        result = "" + parseInt(hourTime) + "小时" + result;
                    }
                    return result;
                }
            },
        }
    };
    return Controller;
});