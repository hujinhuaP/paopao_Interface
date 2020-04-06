define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            Form.api.bindevent($("form[role=form]"));
            Controller.api.bindevent();
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        stat: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'voiceroom/room/stat',
                    table: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                searchFormVisible: true,
                searchFormTemplate: 'customformtpl',
                search:false,
                pagination:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'stat_date', title: __('统计日期'), operate: false},
                        {field: 'total_coin', title: __('总金币数')},
                        {field: 'total_dot', title: __('总收益佣金')},
                        {field: 'enter_max_user', title: __('最大进房人数')},
                        {field: 'enter_times', title: __('进房人次')},
                        {field: 'enter_user_number', title: __('进房人数')},
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);



        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});