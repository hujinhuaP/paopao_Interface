define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/anchortitleconfig/index',
                    del_url: '',
                    edit_url: 'general/anchortitleconfig/edit',
                    multi_url: '',
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true,},
                        {field: 'anchor_title_id', title: 'ID'},
                        {
                            field: 'anchor_title_number',
                            title: __('称号值'),
                            style: 'width:200px'
                        },
                        {
                            field: 'anchor_title_name',
                            title: __('称号名称'),
                            style: 'width:200px'
                        },
                        {
                            field: 'anchor_title_update_time',
                            title: __('Operate time'),
                            operate: 'BETWEEN',
                            type: 'datetime',
                            addclass: 'datetimepicker',
                            data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',
                            formatter: function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }
                        },
                        {
                            field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index);
                            }
                        }
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
                pk: 'anchor_title_id',
                sortName: 'anchor_title_id',
                sortOrder: 'asc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        }
    };
    return Controller;
});