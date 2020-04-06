define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/feedbacklog/index',
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
                        {field: 'user_feedback_log_id', title: 'ID'},
                        {field: 'user_id', title: __('User id')},
                        {field: 'user_link', title: __('Contact way'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_feedback_log_content', title: __('Content'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_feedback_log_images', title: __('Image'), operate:false, formatter: function (value, row, index, custom) {
                                return Controller.api.formatter.images( value, row, index, custom);
                                // return Table.api.formatter.images.call(this, value, row, index, custom);
                            }},
                        {field: 'user_feedback_log_create_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
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
                pk: 'user_feedback_log_id',
                sortName: 'user_feedback_log_id',
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
                images: function (value, row, index, custom) {
                    var classname = typeof custom !== 'undefined' ? custom : 'img-sm img-center';
                    var arr = value.split(',');
                    var html = [];
                    $.each(arr, function (i, value) {
                        if (value) {
                            html.push('<a href="'+ Fast.api.cdnurl(value ? value : "/assets/img/loading100x100.png") +'" target="_blank"><img class="' + classname + '" src="' + Fast.api.cdnurl(value) + '"  onerror="this.src=\'/assets/img/loading100x100.png\'" /><a>');
                        } else {
                            html.push('-');
                        }
                    });
                    return html.join(' ');
                }
            },
        }
    };
    return Controller;
});