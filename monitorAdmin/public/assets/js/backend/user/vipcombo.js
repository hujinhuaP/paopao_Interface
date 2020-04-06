define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/vipcombo/index',
                    detail_url: '',
                    add_url: 'user/vipcombo/add',
                    del_url: 'user/vipcombo/delete',
                    edit_url: 'user/vipcombo/edit',
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
                        {field: 'user_vip_combo_id', title: 'ID'},
                        {field: 'user_vip_combo_original_price', title: __('原价'), operate: false},
                        {field: 'user_vip_combo_fee', title: __('Amount'), operate: 'BETWEEN',sortable:true},
                        {field: 'user_vip_combo_discount', title: __('折扣'), operate: false, formatter:function (value, row, index) {
                                return __('%d 折', value);
                            }},
                        {field: 'user_vip_combo_average_daily_price', title: __('每日价格'), operate: false},
                        {field: 'user_vip_combo_month', title: __('Month'), operate: 'BETWEEN', formatter:function (value, row, index) {
                            return __('%d Month', value);
                        }},
                        {field: 'user_vip_combo_type', title: __('IOS'), operate: false, formatter: function (value, row, index) {
                                if (row.user_vip_combo_apple_id == '') {
                                    return __('No');
                                }
                                return __('Yes');
                            }},
                        {field: 'user_vip_combo_apple_id', title: __('Apple pay combo id'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_vip_combo_update_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
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
                pk: 'user_vip_combo_id',
                sortName: 'user_vip_combo_fee',
                sortOrder: 'asc'
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
            $("input[name='row[user_vip_combo_type]']").on('click', function () {
                if ($(this).val() == 'yes') {
                    $('#c-user_vip_combo_apple_id').attr('disabled', false);
                } else {
                    $('#c-user_vip_combo_apple_id').val('').attr('disabled', 'disabled');
                }
            });
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
            $("input[name='row[user_vip_combo_type]']").on('click', function () {
                if ($(this).val() == 'yes') {
                    $('#c-user_vip_combo_apple_id').attr('disabled', false);
                } else {
                    $('#c-user_vip_combo_apple_id').val('').attr('disabled', 'disabled');
                }
            });
        },
        api: {
            formatter: {
                
            },
        }
    };
    return Controller;
});