define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'agent/marking/index',
                    detail_url: '',
                    add_url: 'agent/marking/add',
                    edit_url: 'agent/marking/edit',
                    multi_url: '',
                    status_url: 'agent/marking/status',
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
                        {field: 'realname', title: __('Realname'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'status', title: __('Status'), defaultValue:'Y', searchList: {
                            'Y': __('Enable'),
                            'N': __('Disable'),
                        }, formatter: function (value, row, index) {
                            return value == 'Y' ? __('Enable') : __('Disable');
                        }},
                        {field: 'qq', title: __('QQ')},
                        {field: 'phone', title: __('Phone')},
                        {field: 'update_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'create_time', title: __('Create time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                            ],
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index)+Controller.api.formatter.status(row.status, row, index, 'status');
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
                pageSize: 20,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'asc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));

        },
        api: {
            formatter: {
                status: function (value, row, index, field) {
                    var btn = 'success';
                    var title = __('Enable');
                    var param = 'Y';
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.status_url);
                    var icon = 'fa fa-long-arrow-up';
                    switch (value) {
                        case 'N':
                            btn   = 'success';
                            title = __('Enable');
                            param  = 'Y';
                            icon = 'fa fa-long-arrow-up';
                            break;

                        case 'Y':
                        default :
                            btn   = 'danger';
                            title = __('Disable');
                            param  = 'N';
                            icon = 'fa fa-long-arrow-down';
                            break;
                    }

                    return " <a href='javascript:;' class='btn btn-" + btn + " btn-xs btn-change btn-tipsone' data-id='" + 
                            row.question_id + "' data-url='" + url + "' data-params='" + field + "=" + param +"'><i class='" + icon + "'></i> "
                            + title + "</a>";
                }
            },
        }
    };
    return Controller;
});