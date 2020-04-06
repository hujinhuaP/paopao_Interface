define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/feedback/index',
                    add_url: '',
                    edit_url: '',
                    del_url: '',
                    multi_url: '',
                    category_url: 'general/feedback/category',
                }
            });

            var table = $("#table");
            
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'user_feedback_log_id', title: 'ID'},
                        {field: 'user_feedback_category_id', title: __('Feedback type'), searchList : category, formatter:function (value, row, index) {
                            return row.user_feedback_category_name;
                        }},
                        {field: 'user_feedback_module', title: __('Type'), searchList: {
                            'S':__('Suggest'),
                            'C':__('Complain'),
                        }, formatter: function(value, row, index) {
                            return value == 'S' ? __('Suggest') : __('Complain');
                        }},
                        {field: 'user_phone', title: __('User'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_feedback_create_time', title: __('Feedback time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[{
                                    name: 'detail',
                                    text: __('Detail'),
                                    icon: 'fa fa-list',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    url: 'general/feedback/detail'
                                }],
                            formatter: function (value, row, index) { 
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index);
                            }}
                    ]
                ],

                // templateView: true,
                // search: false,
                showColumns: false,
                showToggle: false,
                showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 12,
                pk: 'user_feedback_log_id',
                sortName: 'user_feedback_log_id',
                sortOrder: 'desc',
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