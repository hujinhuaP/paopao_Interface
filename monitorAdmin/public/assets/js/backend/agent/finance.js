define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        withdraw: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'agent/finance/withdraw',
                    del_url: '',
                    edit_url: 'agent/finance/withdrawedit',
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
                        {field: 'agent_id', title: __('Agent ID') },
                        {field: 'agent.level', title: __('Agent level'), searchList: {
                                '1': __('一级代理'),
                                '2': __('二级代理'),
                                '3': __('三级代理'),
                            }, formatter: function (value, row, index) {
                                switch (value) {
                                    case 1:
                                        return '一级代理';
                                    case 2:
                                        return '二级代理';
                                    case 3:
                                        return '三级代理';
                                }
                            }},
                        {field: 'user_realname', title: __('Realname'), operate: false},
                        {field: 'withdraw_account', title: __('Withdraw account'), operate: false},
                        {field: 'withdraw_cash', title: __('Amount'), operate: 'BETWEEN'},
                        {field: 'agent_money', title: __('剩余金额'), operate: false},
                        {field: 'total_withdraw_money', title: __('总提现成功金额'), operate: false},
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
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                            ],
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                if(row.check_status == 'C'){
                                    this.table.data('operate-edit', true);
                                }else{
                                    this.table.data('operate-edit', false);
                                }
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
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        withdrawedit: function () {
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
    };
    return Controller;
});