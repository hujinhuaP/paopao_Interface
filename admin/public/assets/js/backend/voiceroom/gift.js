define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'voiceroom/gift/index',
                    detail_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true,},
                        {field: 'user_gift_log_id',title: 'ID'},
                        {field: 'user_id',title: __('送礼用户ID')},
                        {field: 'anchor_user_id',title: __('收礼用户ID')},
                        {field: 'live_gift_name', title: __('礼物名称'), operate: false},
                        {field: 'consume_coin', title: __('消耗充值金币'), operate: false},
                        {field: 'consume_free_coin', title: __('消耗赠送金币'), operate: false},
                        {field: 'live_gift_dot', title: __('礼物收益'), operate: false},
                        {field: 'user_gift_log_create_time',title: __('Create time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }}
                    ]
                ],

                search: false,
                commonSearch: true,
                searchFormVisible: false,
                pagination: true,
                pageSize: 20,
                pk: 'user_gift_log_id',
                sortName: 'user_gift_log_id',
                sortOrder: 'desc',
            });

            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});