define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'baidueditor'], function ($, undefined, Backend, Table, Form, UE) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'document/question/index',
                    detail_url: 'document/question/detail',
                    add_url: 'document/question/add',
                    del_url: 'document/question/delete',
                    edit_url: 'document/question/edit',
                    multi_url: '',
                    dragsort_url: 'document/question/sort',
                    status_url: 'document/question/status',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'question_id', title: 'ID'},
                        {field: 'question_category_id', title: __('Type'), searchList: category, formatter:function (value, row, index) {
                            return category[value];
                        }},
                        {field: 'question_title', title: __('Title'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'question_sort', title: __('Sort'), operate: 'BETWEEN'},
                        {field: 'question_status', title: __('Status'), searchList:{
                                'Y' : __('Go online'),
                                'N' : __('Go offline')
                            },
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                                return value == 'Y' ? __('Go online') : __('Go offline');
                            }},              
                        {field: 'question_update_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},              
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [],
                            formatter: function (value, row, index) {
                                return Table.api.formatter.operate.call(this, value, row, index)+Controller.api.formatter.status(row.question_status, row, index, 'question_status');
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
                pagination: false,
                pageSize: 12,
                pk: 'question_id',
                sortName: 'question_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
            var editor = UE.getEditor('question');
            editor.ready(function() {
                editor.setContent(question_content);
            });
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
            var editor = UE.getEditor('question');
        },
        multi: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                status: function (value, row, index, field) {
                    var btn = 'success';
                    var title = __('Go online');
                    var param = 'Y';
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.status_url);
                    var icon = 'fa fa-long-arrow-up';
                    switch (value) {
                        case 'N':
                            btn   = 'success';
                            title = __('Go online');
                            param  = 'Y';
                            icon = 'fa fa-long-arrow-up';
                            break;

                        case 'Y':
                        default :
                            btn   = 'danger';
                            title = __('Go offline');
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