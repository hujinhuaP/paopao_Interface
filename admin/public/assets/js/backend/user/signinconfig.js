define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/signinconfig/index',
                    add_url: '',
                    del_url: '',
                    edit_url: 'user/signinconfig/edit',
                    multi_url: 'user/signinconfig/multi',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'user_signin_config_id', title: 'ID'},
                        {field: 'user_signin_serial_total', title: __('Signin serial total'), operate: 'BETWEEN',},
                        {field: 'user_signin_coin', title: __('Signin serial coin'), operate: 'BETWEEN',},
                        {field: 'user_signin_extra_coin', title: __('Signin extra coin'), operate: 'BETWEEN',},
                        {field: 'user_signin_exp', title: __('Signin serial exp'), operate: 'BETWEEN',},
                        {field: 'user_signin_extra_exp', title: __('Signin extra exp'), operate: 'BETWEEN',},
                        {field: 'user_signin_config_update_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
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
                pk: 'user_signin_config_id',
                sortName: 'user_signin_serial_total',
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