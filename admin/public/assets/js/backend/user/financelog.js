define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/financelog/index',
                    detail_url: '',
                    add_url: '',
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
                        {field: 'user_finance_log_id', title: 'ID'},
                        {field: 'flow_id', title: __('Flow id'), operate: 'BETWEEN',},
                        {field: 'flow_number', title: __('Flow number'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px', formatter:function (value, row, index) {
                            return value != '' ? value : '-';
                        }},
                        {field: 'user_id', title: __('User id'), operate: 'BETWEEN', },
                        {field: 'user_current_amount', title: __('Current amount'), operate: 'BETWEEN', },
                        {field: 'user_last_amount', title: __('Last amount'), operate: 'BETWEEN', },
                        {field: 'consume', title: __('Consume'), operate: 'BETWEEN', },
                        {field: 'user_amount_type', title: __('Amount')+__('Type'), searchList:{
                                'dot':__('Dot'),
                                'coin':__('Coin'),
                                'money':__('RMB'),
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case 'dot' :
                                        return __('Dot');
                                        break;
                                    case 'money' :
                                        return __('RMB');
                                        break;
                                    case 'coin' :
                                    default :
                                        return __('Coin');
                                        break;
                                }
                            }},
                        {field: 'consume_category_id', title: __('Consume category'), searchList: category, formatter:function (value, row, index) {
                                return category[value];
                            }},
                        {field: 'remark', title: __('Remark'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'admin_id', title: __('Admin id'), operate: 'BETWEEN', formatter:function (value, row, index) {
                            return value != 0 ? value : '-';
                        }},
                        {field: 'create_time', title: __('Operate time'), defaultValue:$default_start_datetime + '|' + $default_end_datetime, operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
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
                pk: 'user_finance_log_id',
                sortName: 'create_time',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        coin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/financelog/coin',
                    detail_url: 'user/financelog/detail',
                    add_url: '',
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
                        {field: 'user_finance_log_id', title: 'ID',operate: false},
                        {field: 'user_id', title: __('User id') },
                        {field: 'user_last_user_coin', title: __('Last user coin'), operate: false },
                        {field: 'user_current_user_coin', title: __('Current user coin'), operate: false },
                        {field: 'user_current_user_coin', title: __('Change user coin'), operate: false ,formatter:function (value, row, index) {
                                return row.user_current_user_coin - row.user_last_user_coin;
                            } },
                        {field: 'user_last_free_coin', title: __('Last user free coin'), operate: false},
                        {field: 'user_current_free_coin', title: __('Current user free coin'), operate: false },
                        {field: 'user_current_free_coin', title: __('Change user free coin'), operate: false,formatter:function (value, row, index) {
                                return row.user_current_free_coin - row.user_last_free_coin;
                            } },
                        {field: 'consume_category_id', title: __('Consume category'), searchList: category, formatter:function (value, row, index) {
                                return category[value];
                            }},
                        {field: 'create_time', title: __('Operate time'), defaultValue:$default_start_datetime + '|' + $default_end_datetime, operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'target_user_id', title: __('Target user id'), operate: false },
                        {field: 'remark', title: __('Remark'),  operate: false},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            formatter: function (value, row, index) {
                                this.table.data('operate-edit', false);
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
                pk: 'user_finance_log_id',
                sortName: 'create_time',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            table.on('load-success.bs.table', function (e, json) {
                $("#freeTotalSpan").html(json.stat.free_total);
                $("#rechargeTotalSpan").html(json.stat.recharge_total);
                $("#useFreeCoinSpan").html(json.stat.use_free_coin);
                $("#useCoinSpan").html(json.stat.use_coin);
                $("#giftConsumeSpan").html(json.stat.gift_consume);
                $("#chatConsumeSpan").html(json.stat.chat_consume);
            });
        },
        dot: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/financelog/dot',
                    detail_url: 'user/financelog/detail',
                    add_url: '',
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
                        {field: 'user_finance_log_id', title: 'ID',operate: false},
                        {field: 'user_id', title: __('User id') },
                        {field: 'user_current_amount', title: __('Current amount'), operate: false},
                        {field: 'user_last_amount', title: __('Last amount'), operate: false },
                        {field: 'consume', title: __('Change dot'), operate: false },
                        {field: 'consume_category_id', title: __('Consume category'), searchList: category, formatter:function (value, row, index) {
                                return category[value];
                            }},
                        {field: 'create_time', title: __('Operate time'), defaultValue:$default_start_datetime + '|' + $default_end_datetime, operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'target_user_id', title: __('Source user id') },
                        {field: 'remark', title: __('Remark'),  operate: false},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            formatter: function (value, row, index) {
                                this.table.data('operate-edit', false);
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
                pk: 'user_finance_log_id',
                sortName: 'create_time',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            table.on('load-success.bs.table', function (e, json) {
                $("#dotTotalSpan").html(json.stat.dot_total);
            });
        },
        edit: function () {
        },
        add: function () {
        },
        api: {
            formatter: {
                
            },
        }
    };
    return Controller;
});