define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/timeintervalconfig/index',
                    add_url: 'general/timeintervalconfig/add',
                    del_url: 'general/timeintervalconfig/delete',
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
                        {field: 'start_hour', title: '开始时间（h）'},
                        {field: 'end_hour', title: '结束时间（h）'},
                        {
                            field: 'update_time',
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
                pk: 'id',
                sortName: 'id',
                sortOrder: 'asc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        }
    };
    return Controller;
});