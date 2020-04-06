define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/cashlog/index',
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
                        {field: 'id', title: 'ID'},
                        {field: 'flow_id', title: __('Flow id'), operate: 'BETWEEN',},
                        {field: 'flow_number', title: __('Flow number'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px', formatter:function (value, row, index) {
                            return value != '' ? value : '-';
                        }},
                        {field: 'user_id', title: __('User id'), operate: 'BETWEEN', },
                        {field: 'user_current_amount', title: __('Current amount'), operate: 'BETWEEN', },
                        {field: 'user_last_amount', title: __('Last amount'), operate: 'BETWEEN', },
                        {field: 'consume', title: __('Consume'), operate: 'BETWEEN', },
                        {field: 'consume_category', title: __('Consume category'), searchList:{
                                'register':__('邀请注册收益'),
                                'recharge':__('邀请充值收益'),
                                'withdraw':__('邀请提现收益'),
                                'exchange':__('兑换减少'),
                                'vip':__('邀请VIP收益'),
                                'withdraw_back':__('提现返回'),
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case 'register' :
                                        return __('邀请注册收益');
                                        break;
                                    case 'recharge' :
                                        return __('邀请充值收益');
                                    case 'withdraw' :
                                        return __('邀请提现收益');
                                    case 'exchange' :
                                        return __('兑换减少');
                                    case 'vip' :
                                        return __('邀请VIP收益');
                                        break;
                                    case 'budan_incr' :
                                        return __('补单增加');
                                        break;
                                    case 'budan_decr' :
                                        return __('补单减少');
                                        break;
                                    case 'withdraw_back' :
                                    default :
                                        return __('提现返回');
                                        break;
                                }
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
                pk: 'id',
                sortName: 'create_time',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
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