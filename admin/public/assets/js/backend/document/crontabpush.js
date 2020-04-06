define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'baidueditor'], function ($, undefined, Backend, Table, Form, UE) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'document/crontabpush/index',
                    add_url: 'document/crontabpush/add',
                    del_url: 'document/crontabpush/delete',
                    edit_url: 'document/crontabpush/edit',
                    status_url: 'document/crontabpush/status',
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
                        {field: 'crontab_push_id', title: 'ID'},
                        {field: 'crontab_push_hour', title: '执行时间',formatter:function (value, row, index) {
                                if(value < 10){
                                    return '0' + value + ':00';
                                }else{
                                    return value + ':00';
                                }
                            }},
                        {field: 'crontab_push_user_type', title: __('Type'), searchList: {
                                'user': __('选择用户'),
                                'anchor': __('所有主播'),
                                'all': __('全平台')
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case 'user':
                                        return __('选择用户');
                                        break;
                                    case 'anchor' :
                                        return __('所有主播');
                                        break;
                                    case 'all' :
                                    default :
                                        return __('全平台');
                                        break;
                                }
                            }},
                        {field: 'crontab_push_content', title: __('内容'), operate: false, formatter:function (value, row, index) {
                                return '<pre><p class="app_error_content">'+value+'</p></pre>'
                            }, width: 100},
                        {field: 'crontab_push_on_flg', title: __('状态'), searchList:{
                                'Y' : __('Go online'),
                                'N' : __('Go offline')
                            },
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.status(value, row, index,'crontab_push_on_flg');
                            }},
                        {field: 'crontab_admin_name', title: '管理员'},
                        {field: 'crontab_update_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
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
                search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pagination: false,
                pageSize: 12,
                pk: 'crontab_push_id',
                sortName: 'crontab_push_id',
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
            $("#c-crontab_push_user_type").on('change', function () {
                if ($(this).val() == 'user') {
                    $("#c-user_id").attr('disabled', false);

                } else {
                    $("#c-user_id").attr('disabled', true).val('').nextAll().remove();
                    $("#c-user_id").parent().parent().removeClass('has-success').removeClass('has-error');
                }
            });
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
            var editor = UE.getEditor('autoreply');
            $("#c-crontab_push_user_type").on('change', function () {
                if ($(this).val() == 'user') {
                    $("#c-user_id").attr('disabled', false);

                } else {
                    $("#c-user_id").attr('disabled', true).val('').nextAll().remove();
                    $("#c-user_id").parent().parent().removeClass('has-success').removeClass('has-error');
                }
            });
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
                            row.crontab_push_id + "' data-url='" + url + "' data-params='" + field + "=" + param +"'><i class='" + icon + "'></i> "
                            + title + "</a>";
                }
            },
        }
    };
    return Controller;
});