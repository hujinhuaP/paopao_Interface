define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/feedbackcategory/index',
                    add_url: 'general/feedbackcategory/add',
                    edit_url: 'general/feedbackcategory/edit',
                    del_url: 'general/feedbackcategory/del',
                    multi_url: 'general/feedbackcategory/multi',
                }
            });

            var table = $("#table");
            
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'user_feedback_category_id', title: 'ID'},
                        {field: 'user_feedback_category_name', title: __('Type name'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_feedback_category_status', title: __('Status'), searchList: {
                            'Y': __('Enable'),
                            'N': __('Disable'),
                        }, formatter: function (value, row, index) {
                            return value == 'Y' ? __('Enable') : __('Disable');
                        }},
                        {field: 'user_feedback_module', title: __('Module'), searchList: {
                            'S': __('Suggest'),
                            'C': __('Complain'),
                        }, formatter: function (value, row, index) {
                            return value == 'S' ? __('Suggest') : __('Complain');
                        }},
                        {field: 'user_feedback_category_update_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
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
                pk: 'user_feedback_category_id',
                sortName: 'user_feedback_category_id',
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