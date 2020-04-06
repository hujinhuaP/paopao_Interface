define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'baidueditor'], function ($, undefined, Backend, Table, Form, UE) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'document/autoreply/index',
                    add_url: 'document/autoreply/add',
                    del_url: 'document/autoreply/delete',
                    edit_url: 'document/autoreply/edit',
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
                        {field: 'id', title: 'ID'},
                        {field: 'type', title: __('Type'), searchList: {
                                'first': __('半小时内首次发消息'),
                                'reply': __('匹配回复'),
                                'unmatch': __('未匹配到内容')
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case 'first':
                                        return __('半小时内首次发消息');
                                        break;
                                    case 'reply' :
                                        return __('匹配回复');
                                        break;
                                    case 'unmatch' :
                                    default :
                                        return __('未匹配到内容');
                                        break;
                                }
                            }},
                        {field: 'content', title: __('Content'), operate: false, formatter:function (value, row, index) {
                                return '<pre><p class="app_error_content">'+value+'</p></pre>'
                            }, width: 100},
                        {field: 'reply_flg', title: __('匹配内容'), formatter:function (value, row, index) {
                            if(row.type == 'reply'){
                                return value;
                            }else{
                                return '--';
                            }}},
                        {field: 'update_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
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
                pagination: false,
                pageSize: 12,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'asc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
            var editor = UE.getEditor('autoreply');
            editor.ready(function() {
                editor.setContent(autoreply_content);
            });
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
            var editor = UE.getEditor('autoreply');
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
                            row.autoreply_id + "' data-url='" + url + "' data-params='" + field + "=" + param +"'><i class='" + icon + "'></i> "
                            + title + "</a>";
                }
            },
        }
    };
    return Controller;
});