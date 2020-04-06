define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/withdraw/index',
                    detail_url: '',
                    add_url: '',
                    del_url: '',
                    edit_url: 'user/withdraw/edit',
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
                        {field: 'user_withdraw_log_id', title: 'ID'},
                        {field: 'user_withdraw_log_number', title: __('Order number'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_id', title: __('Anchor id')},
                        {field: 'user_withdraw_cash', title: __('Amount'), operate: 'BETWEEN'},
                        {field: 'user_dot', title: __('Dot'), operate: 'BETWEEN'},
                        {field: 'user_realname', title: __('Real name')},
                        {field: 'user_withdraw_account', title: __('Withdraw account'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_withdraw_pay', title: __('Pay type'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_withdraw_log_check_status', title: __('Check'), searchList:{
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
                        {field: 'user_withdraw_log_status', title: __('Status'), searchList:{
                            'Y': __('Success'),
                            'N': __('Wait pay'),
                            'C': __('Cancel'),
                        }, formatter:function (value, row, index) {
                            if (row.user_withdraw_log_check_status == 'N') {
                                return __('Cancel');
                            }
                            return value == 'Y' ? __('Success') : __('Wait pay');
                        }},               
                        {field: 'user_withdraw_log_create_time', title: __('Apply time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
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
                pk: 'user_withdraw_log_id',
                sortName: 'user_withdraw_log_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
            $(".user_withdraw_log_check_status").on('click', function () {
                if ($(this).val() == 'Y') {
                    $(".user_withdraw_way").attr('disabled', false);
                    $(".user_withdraw_way").parent().parent().parent().removeClass('has-success').removeClass('has-error');
                } else {
                    $(".user_withdraw_way").attr('disabled', true).attr('checked', false).nextAll().remove();
                    $(".user_withdraw_way").parent().parent().parent().removeClass('has-success').removeClass('has-error');
                }
            });

            $("[type=reset]").on('click',function () {
                $(".user_withdraw_way").attr('disabled', true).val('').nextAll().remove();
                $(".user_withdraw_way").parent().parent().parent().removeClass('has-success').removeClass('has-error');
            });
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