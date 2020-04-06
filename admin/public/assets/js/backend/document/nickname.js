define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'baidueditor'], function ($, undefined, Backend, Table, Form, UE) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'document/nickname/index',
                    add_url: 'document/nickname/add',
                    del_url: 'document/nickname/delete',
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
                        {field: 'user_name_config_id', title: 'ID'},
                        {field: 'user_name_config_type', title: __('Type'), searchList: {
                                'prefix': __('前缀'),
                                'suffix': __('后缀'),
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case 'prefix':
                                        return __('前缀');
                                        break;
                                    case 'suffix' :
                                    default :
                                        return __('后缀');
                                }
                            }},
                        {field: 'user_name_config_value', title: '内容'},
                        {field: 'user_name_config_update_time', title: __('Operate time'), operate: false, type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [],
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
                pk: 'user_name_config_id',
                sortName: 'user_name_config_id',
                sortOrder: 'asc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
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