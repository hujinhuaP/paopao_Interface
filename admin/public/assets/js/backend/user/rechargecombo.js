define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/rechargecombo/index',
                    detail_url: '',
                    add_url: 'user/rechargecombo/add',
                    del_url: 'user/rechargecombo/delete',
                    edit_url: 'user/rechargecombo/edit',
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
                        {field: 'user_recharge_combo_id', title: 'ID'},
                        {field: 'user_recharge_combo_coin', title: __('Coin'), operate: 'BETWEEN'},
                        {field: 'user_recharge_combo_fee', title: __('Amount'), operate: 'BETWEEN'},
                        {field: 'first_recharge_reward_vip_day', title: __('首充赠送VIP天数'), operate: false},
                        {field: 'user_recharge_combo_type', title: __('IOS'), operate: false, formatter: function (value, row, index) {
                                if (row.user_recharge_combo_apple_id == '') {
                                    return __('No');
                                }
                                return __('Yes');
                            }},
                        {
                            field: 'user_recharge_is_first', title: __('首充套餐'), searchList: {
                                'Y': __('Yes'),
                                'N': __('No'),
                            }, formatter: function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }
                        },
                        {
                            field: 'user_recharge_combo_has_notify', title: __('是否有充值飘屏'), searchList: {
                                'Y': __('Yes'),
                                'N': __('No'),
                            }, formatter: function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }
                        },
                        {field: 'user_recharge_vip_reward_coin', title: __('VIP用户购买赠送金币'), operate: false},
                        {field: 'user_recharge_combo_apple_id', title: __('Apple pay combo id'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_recharge_combo_update_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
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
                pk: 'user_recharge_combo_id',
                sortName: 'user_recharge_combo_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
            $("input[name='row[user_recharge_combo_type]']").on('click', function () {
                if ($(this).val() == 'yes') {
                    $('#c-user_recharge_combo_apple_id').attr('disabled', false);
                } else {
                    $('#c-user_recharge_combo_apple_id').val('').attr('disabled', 'disabled');
                }
            });
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
            $("input[name='row[user_recharge_combo_type]']").on('click', function () {
                if ($(this).val() == 'yes') {
                    $('#c-user_recharge_combo_apple_id').attr('disabled', false);
                } else {
                    $('#c-user_recharge_combo_apple_id').val('').attr('disabled', 'disabled');
                }
            });
        },
        ratio: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                
            },
        }
    };
    return Controller;
});