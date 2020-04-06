define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'anchor/reportlog/index',
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
                        {field: 'anchor_report_log_id', title: 'ID'},
                        {field: 'anchor_user_id', title: __('Anchor id')},
                        {field: 'user_id', title: __('User id')},
                        {field: 'anchor_report_log_content', title: __('Content'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'anchor_report_log_create_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
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
                pk: 'anchor_report_log_id',
                sortName: 'anchor_report_log_id',
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