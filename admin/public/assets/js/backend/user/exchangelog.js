define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/exchangelog/index',
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
                        {field: 'order_number', title: __('Order number'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px', formatter:function (value, row, index) {
                            return value != '' ? value : '-';
                        }},
                        {field: 'user_id', title: __('User id')},
                        {field: 'combo_coin', title: __('充值金币'), operate: 'BETWEEN' },
                        {field: 'combo_cash', title: __('消耗“现金”'), operate: 'BETWEEN' },
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
                commonSearch: false,
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