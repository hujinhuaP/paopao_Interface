define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init();

            //绑定事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var panel = $($(this).attr("href"));
                if (panel.size() > 0) {
                    Controller.table[panel.attr("id")].call(this);
                    $(this).on('click', function (e) {
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                }
                //移除绑定的事件
                $(this).unbind('shown.bs.tab');
            });

            //必须默认触发shown.bs.tab事件
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");
        },
        table: {
            first: function () {
                // 表格1
                var table1 = $("#table1");
                table1.bootstrapTable({
                    url: 'task/log/dailylog',
                    toolbar: '#toolbar1',
                    sortName: 'daily_task_log_id',
                    search: false,
                    searchFormVisible: true,
                    columns: [
                        [
                            {field: 'state', checkbox: true},
                            {field: 'daily_task_log_id', title: 'ID',operate: false},
                            {field: 'daily_task_log_user_id', title: __('用户ID')},
                            {field: 'user.user_nickname', title: __('用户昵称'),operate: false},
                            {field: 'daily_task_id', title: __('任务ID')},
                            {field: 'daily_task_name', title: __('任务名称'),operate: false},
                            {field: 'daily_task_reward_coin', title: __('奖励金币'),operate: false},
                            {field: 'daily_task_reward_exp', title: __('奖励经验'),operate: false},
                            {field: 'daily_task_log_create_time', title: __('Create time'),
                                defaultValue:$default_start_datetime + '|' + $default_end_datetime,
                                sortable:true, operate: 'between', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                    return Table.api.formatter.datetime.call(this, value, row, index);
                                }},
                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(table1);

                table1.on('load-success.bs.table', function (e, json) {
                    $("#dailyStatTotalCoin").html(json.total_coin);
                });
            },
            second: function () {
                // 表格2
                var table2 = $("#table2");
                table2.bootstrapTable({
                    url: 'task/log/oncelog',
                    extend: {
                        index_url: '',
                        add_url: '',
                        edit_url: '',
                        del_url: '',
                        multi_url: '',
                        table: ''
                    },
                    toolbar: '#toolbar2',
                    sortName: 'once_task_log_id',
                    search: false,
                    searchFormVisible: true,
                    columns: [
                        [
                            {field: 'state', checkbox: true},
                            {field: 'once_task_log_id', title: 'ID',operate: false},
                            {field: 'once_task_log_user_id', title: __('用户ID')},
                            {field: 'user.user_nickname', title: __('用户昵称'),operate: false},
                            {field: 'once_task_id', title: __('任务ID')},
                            {field: 'once_task_name', title: __('任务名称'),operate: false},
                            {field: 'once_task_reward_coin', title: __('奖励金币'),operate: false},
                            {field: 'once_task_reward_exp', title: __('奖励经验'),operate: false},
                            {field: 'once_task_log_create_time', title: __('Create time'),
                                defaultValue:$default_start_datetime + '|' + $default_end_datetime,
                                sortable:true, operate: 'between', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                    return Table.api.formatter.datetime.call(this, value, row, index);
                                }},
                        ]
                    ]
                });

                // 为表格2绑定事件
                Table.api.bindevent(table2);

                table2.on('load-success.bs.table', function (e, json) {
                    $("#onceStatTotalCoin").html(json.total_coin);
                });
            },
            third: function () {
                // 表格2
                var table3 = $("#table3");
                table3.bootstrapTable({
                    url: 'task/log/levellog',
                    extend: {
                        index_url: '',
                        add_url: '',
                        edit_url: '',
                        del_url: '',
                        multi_url: '',
                        table: ''
                    },
                    toolbar: '#toolbar3',
                    sortName: 'level_reward_log_id',
                    search: false,
                    searchFormVisible: true,
                    columns: [
                        [
                            {field: 'state', checkbox: true},
                            {field: 'level_reward_log_id', title: 'ID',operate: false},
                            {field: 'level_reward_log_user_id', title: __('用户ID')},
                            {field: 'user.user_nickname', title: __('用户昵称'),operate: false},
                            {field: 'level_reward_log_level_value', title: __('任务等级'),operate: false},
                            {field: 'level_reward_log_coin', title: __('奖励金币'),operate: false},
                            {field: 'level_reward_log_create_time', title: __('Create time'),
                                defaultValue:$default_start_datetime + '|' + $default_end_datetime,
                                sortable:true, operate: 'between', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                    return Table.api.formatter.datetime.call(this, value, row, index);
                                }},
                        ]
                    ]
                });

                // 为表格2绑定事件
                Table.api.bindevent(table3);

                table3.on('load-success.bs.table', function (e, json) {
                    $("#levelStatTotalCoin").html(json.total_coin);
                });
            },
            fourth: function () {
                // 表格2
                var table4 = $("#table4");
                table4.bootstrapTable({
                    url: 'user/signinlog/index',
                    extend: {
                        index_url: '',
                        add_url: '',
                        edit_url: '',
                        del_url: '',
                        multi_url: '',
                        table: ''
                    },
                    toolbar: '#toolbar4',
                    sortName: 'user_signin_log_id',
                    search: false,
                    searchFormVisible: true,
                    columns: [
                        [
                            {field: 'state', checkbox: true},
                            {field: 'user_signin_log_id', title: 'ID',operate: false},
                            {field: 'user_id', title: __('用户ID')},
                            {field: 'user.user_nickname', title: __('用户昵称'),operate: false},
                            {field: 'user_signin_date', title: __('签到日期'),operate: false},
                            {field: 'user_signin_coin', title: __('奖励金币'),operate: false},
                            {field: 'user_signin_exp', title: __('奖励经验'),operate: false},
                            {field: 'user_signin_log_create_time', title: __('Create time'),
                                defaultValue:$default_start_datetime + '|' + $default_end_datetime,
                                sortable:true, operate: 'between', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                    return Table.api.formatter.datetime.call(this, value, row, index);
                                }},
                        ]
                    ]
                });

                // 为表格2绑定事件
                Table.api.bindevent(table4);

                table4.on('load-success.bs.table', function (e, json) {
                    $("#signinStatTotalCoin").html(json.total_coin);
                });
            },
            fifth: function () {
                // 表格1
                var table1 = $("#table5");
                table1.bootstrapTable({
                    url: 'task/log/anchordailylog',
                    toolbar: '#toolbar5',
                    sortName: 'anchor_daily_task_log_id',
                    search: false,
                    searchFormVisible: true,
                    columns: [
                        [
                            {field: 'state', checkbox: true},
                            {field: 'anchor_daily_task_log_id', title: 'ID',operate: false},
                            {field: 'anchor_daily_task_log_user_id', title: __('用户ID')},
                            {field: 'user.user_nickname', title: __('用户昵称'),operate: false},
                            {field: 'anchor_daily_task_id', title: __('任务ID')},
                            {field: 'anchor_daily_task_name', title: __('任务名称'),operate: false},
                            {field: 'anchor_daily_task_reward_dot', title: __('奖励佣金'),operate: false},
                            {field: 'anchor_daily_task_reward_exp', title: __('奖励经验'),operate: false},
                            {field: 'anchor_daily_task_log_create_time', title: __('Create time'),
                                defaultValue:$default_start_datetime + '|' + $default_end_datetime,
                                sortable:true, operate: 'between', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                    return Table.api.formatter.datetime.call(this, value, row, index);
                                }},
                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(table5);

            },
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
        }
    };
    return Controller;
});