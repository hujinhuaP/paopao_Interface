define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        agent: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'finance/finance/agent',
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
                        {field: 'source_agent.id', title: __('Source agent ID')},
                        {field: 'source_agent.nickname', title: __('Source agent username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user.user_id', title: __('Source user id')},
                        {field: 'user.user_nickname', title: __('Source user nickname'), operate: 'LIKE ...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'source_type', title: __('Source')+__('Type'), searchList:{
                                'recharge':__('Recharge'),
                                'vip':__('VIP'),
                                'withdraw_back':__('Withdraw back'),
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case 'vip' :
                                        return __('VIP');
                                        break;
                                    case 'withdraw' :
                                        return __('Withdraw');
                                        break;
                                    case 'withdraw_back' :
                                        return __('Withdraw back');
                                        break;
                                    case 'recharge' :
                                    default :
                                        return __('Recharge');
                                        break;
                                }
                            }},
                        {field: 'distribution_source_value', title: __('Source') + '金额（元）',operate:false},
                        {field: 'distribution_profits', title: __('Divid precent'),operate:false},
                        {field: 'income', title: __('Income'),operate:false},
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
                        {field: 'withdraw_log_number', title: __('Order number'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_realname', title: __('Realname'), operate: false},
                        {field: 'withdraw_account', title: __('Withdraw account'), operate: false},
                        {field: 'withdraw_cash', title: __('Amount'), operate: 'BETWEEN'},
                        {field: 'check_status', title: __('Check'), searchList:{
                                'C': __('Checking'),
                                'Y': __('Pass'),
                                'N': __('Refuse'),
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case 'C':
                                        return __('Checking');
                                        break;
                                    case 'Y':
                                        return __('Pass');
                                        break;
                                    case 'N':
                                    default :
                                        return __('Refuse');
                                        break;
                                }
                            }},
                        {field: 'status', title: __('Status'), searchList:{
                                'Y': __('Success'),
                                'N': __('Wait pay'),
                                'C': __('Cancel'),
                            }, formatter:function (value, row, index) {
                                if (row.check_status == 'N') {
                                    return __('Cancel');
                                }
                                return value == 'Y' ? __('Success') : __('Wait pay');
                            }},
                        {field: 'remark', title: __('Check result'), operate: false},
                        {field: 'create_time', title:__('Operate time'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true },
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
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        addwithdraw:function(){
            Form.api.bindevent($("form[role=form]"));
        }
    };
    return Controller;
});