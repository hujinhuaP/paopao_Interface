define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'auth/adminlog/index',
                    detail_url: 'auth/adminlog/detail',
                    edit_url: '',
                    del_url: 'auth/adminlog/del',
                    multi_url: 'auth/adminlog/multi',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'id', title: 'ID', operate: false},
                        {field: 'username', title: __('Username'), formatter: Table.api.formatter.search},
                        {field: 'title', title: __('Title'), operate: 'LIKE %...%', placeholder: '模糊搜索，*表示任意字符', style: 'width:200px'},
                        {field: 'url', title: __('Url'), align: 'left', formatter: Table.api.formatter.url},
                        {field: 'ip', title: __('IP'), events: Table.api.events.ip, formatter: Table.api.formatter.search},
                        {field: 'browser', title: __('Browser'), operate: false, formatter: Controller.api.formatter.browser},
                        {field: 'createtime', title: __('Create time'), formatter: Table.api.formatter.datetime, operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"'},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                browser: function (value, row, index) {
                    return '<a class="btn btn-xs btn-browser">' + row.useragent.split(" ")[0] + '</a>';
                },
            },
        }
    };
    return Controller;
});