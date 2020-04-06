define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/rechargeaction/index',
                    detail_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true},
                        {field: 'user_id', title: 'ID'},
                        {field: 'user.user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user.user_avatar', title: __('User avatar'), operate: false, formatter: function (value, row, index, custom) {
                                return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                            }},
                        {field: 'recharge_times', title: __('充值次数'),operate: 'BETWEEN',sortable:true},
                        {field: 'recharge_total_coin', title: __('充值总金币'),operate: 'BETWEEN',sortable:true},
                        {field: 'recharge_total_money', title: __('充值总金额'),operate: 'BETWEEN',sortable:true},
                        {field: 'user.user_coin', title: __('充值余额'),sortable:true},
                        {field: 'recharge_first_time', title: __('第一次充值时间'),sortable:true, operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'recharge_last_time', title: __('最近充值时间'),sortable:true, operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'vip_times', title: __('获得VIP次数'),sortable:true,operate: 'BETWEEN'},
                        {field: 'vip_total_day', title: __('VIP总天数'),sortable:true,operate: 'BETWEEN'},
                        {field: 'vip_total_money', title: __('VIP总金额'),sortable:true,operate: 'BETWEEN'},
                        {field: 'vip_first_time', title: __('第一次获得VIP时间'),sortable:true, operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'vip_last_time', title: __('最近获得VIP时间'),sortable:true, operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},

                    ]
                ],

                // templateView: true,
                search: true,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 12,
                pk: 'user_id',
                sortName: 'recharge_total_money',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        multi: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                
            },
        }
    };
    return Controller;
});