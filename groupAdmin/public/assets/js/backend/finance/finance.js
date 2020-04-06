define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/finance/index',
                    detail_url: '',
                    multi_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true,},
                        {field: 'stat_time',title: __('Stat time'),sortable:true,operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index).slice(0,11);
                            }},
                        {field: 'divid_type', title: __('Divid type'), operate:false,
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                                if(value == '0'){
                                    return  __('Devid by anchor income');
                                }else{
                                    return __('Devid by anchor water');
                                }
                            }},
                        {field: 'anchor_time_income',title: __('Anchor income of live time')+'(￥)',operate: false,sortable:true},
                        {field: 'anchor_gift_income',title: __('Anchor income of get gift')+'(￥)',operate: false,sortable:true},
                        {field: 'invite_reward_income',title: __('Anchor income of invite reward')+'(￥)',operate: false,sortable:true},
                        {field: 'word_msg_income',title: __('主播私聊收益')+'(￥)',operate: false,sortable:true},
                        {field: 'video_income',title: __('主播视频收益')+'(￥)',operate: false,sortable:true},
                        {field: 'chat_game_income',title: __('视频游戏收益')+'(￥)',operate: false,sortable:true},
                        {field: 'group_divid_income',title: __('Group divid income')+'(￥)',operate: false,sortable:true},
                        {field: 'group_total_income',title: __('Group total income')+'(￥)',operate: false,sortable:true},
                    ]
                ],

                // templateView: true,
                // search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pagination: false,
                pageSize: 20,
                pk: 'id',
                sortName: 'stat_time',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        user: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/finance/user',
                    detail_url: '',
                    multi_url: '',
                }
            });

            var table = $("#table");

            // 指定搜索条件
            $(document).on("click", ".btn-singlesearch", function () {
                var options = table.bootstrapTable('getOptions');
                var queryParams = options.queryParams;
                options.pageNumber = 1;
                var data_type = $(this).attr('data-type');
                if(data_type == 'not_zero'){
                    $(this).html("<i class=\"fa fa-user\"></i> 全部");
                    $(this).attr('data-type','all');
                }else{
                    $(this).html("<i class=\"fa fa-user\"></i> 仅显示有收益统计");
                    $(this).attr('data-type','not_zero');
                }
                options.queryParams = function (params) {
                    //这一行必须要存在,否则在点击下一页时会丢失搜索栏数据
                    params = queryParams(params);

                    //如果希望追加搜索条件,可使用
                    var filter = params.filter ? JSON.parse(params.filter) : {};
                    var op = params.op ? JSON.parse(params.op) : {};
                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
                    params.not_zero = data_type;

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
                        {field: 'state',checkbox: true,},
                        {field: 'stat_time',title: __('Stat time'),sortable:true,operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index).slice(0,11);
                            }},
                        {field: 'user_id',title: __('Anchor ID'),sortable:true},
                        {field: 'user.user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_account.user_phone', title: __('Phone'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user.user_video_cover', title: __('User avatar'), operate: false, formatter: function (value, row, index, custom) {
                                if(row.user.user_is_certification == 'Y'){
                                    return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-lg img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                                }
                            }},
                        {field: 'time_total',title: __('主播直播时长')+'(分钟)',operate: 'BETWEEN',sortable:true},
                        {
                            field: 'online_duration',
                            title: __('在线时长'),
                            operate: false,
                            formatter: function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }
                        },
                        {field: 'anchor_time_income',title: __('Anchor income of live time')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'anchor_gift_income',title: __('Anchor income of get gift')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'word_msg_income',title: __('主播私聊收益')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'video_income',title: __('主播视频收益')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'invite_reward_income',title: __('主播邀请收益')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'chat_game_income',title: __('视频游戏收益')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'anchor_gift_income',title: __('Anchor total income')+'(￥)',operate: false, formatter: function (value, row, index, custom) {
                               return (parseFloat(row.anchor_gift_income) + parseFloat(row.anchor_time_income) + parseFloat(row.word_msg_income) + parseFloat(row.video_income) + parseFloat(row.invite_reward_income)).toFixed(2);
                            }},
                        // {field: 'invite_reward_income',title: __('Anchor income of invite reward')+'(￥)',operate: 'BETWEEN',sortable:true},
                        // {field: 'group_divid_income',title: __('Group divid income')+'(￥)',operate: 'BETWEEN',sortable:true},
                        // {field: 'group_total_income',title: __('Group total income')+'(￥)',operate: 'BETWEEN',sortable:true},
                    ]
                ],

                // templateView: true,
                // search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 20,
                pk: 'id',
                sortName: 'stat_time',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        withdraw: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/finance/withdraw',
                    del_url: '',
                    edit_url: '',
                    multi_url: '',
                }
            });

            var table = $("#table");


            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'id', title: 'ID'},
                        {field: 'order_no', title: __('Order number'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'anchor.user_nickname', title: __('Username'), operate: false},
                        {field: 'dot', title: __('Dot'), operate: 'BETWEEN' },
                        {field: 'account', title: __('Amount'), operate: 'BETWEEN'},
                        {field: 'create_time', title:__('Operate time'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true },
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
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            formatter: {
                formatSeconds:function(value){
                    let secondTime = parseInt(value);// 秒
                    if(!value){
                        return '0秒';
                    }
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
        },
    };
    return Controller;
});