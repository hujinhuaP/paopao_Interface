define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'anchor/income/index',
                    detail_url: 'anchor/income/detail',
                    add_url: '',
                    del_url: '',
                    multi_url: ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'user_id', title: 'ID'},
                        {field: 'user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_avatar', title: __('User avatar'), operate: false, formatter: function (value, row, index, custom) {
                            return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>'; 
                        }},
                        {field: 'chat_total', title: __('Chat total'), operate: false},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                            ],
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index);
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
        stat: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'anchor/income/stat',
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
                        {field: 'group.group_name', title: __('Group name'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_id',title: __('Anchor ID'),sortable:true},
                        {field: 'user.user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'time_total',title: __('主播直播时长')+'(分钟)',operate: 'BETWEEN',sortable:true},
                        {field: 'anchor_time_income',title: __('Anchor income of live time')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'anchor_gift_income',title: __('Anchor income of get gift')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'word_msg_income',title: __('主播私聊收益')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'video_income',title: __('主播视频收益')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'invite_reward_income',title: __('主播邀请收益')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'anchor_guard_income',title: __('守护收益')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'chat_game_income',title: __('聊天游戏收益')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'total_income',title: __('Anchor total income')+'(￥)',operate: 'BETWEEN', formatter: function (value, row, index, custom) {
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
    };
    return Controller;
});