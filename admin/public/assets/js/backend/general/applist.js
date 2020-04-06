define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/applist/index',
                    add_url: 'general/applist/add',
                    edit_url: 'general/applist/edit',
                    del_url: '',
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
                        {field: 'id', title: 'ID'},
                        {
                            field: 'app_name',
                            title: __('APP名称'),
                            operate: 'LIKE %...%',
                            placeholder: __('Like search'),
                            style: 'width:200px'
                        },
                        {field: 'app_flg', title: __('APP标识')},
                        {
                            field: 'company_name',
                            title: __('公司名称'),
                            operate: 'LIKE %...%',
                            placeholder: __('Like search'),
                            style: 'width:200px'
                        },
                        {
                            field: 'on_publish', title: __('是否正在审核'), searchList: {
                                'Y': __('Yes'),
                                'N': __('No'),
                            }, formatter: function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }
                        },
                        {
                            field: 'app_os', title: __('系统类型'), searchList: {
                                'Android': __('安卓'),
                                'iOS': __('苹果'),
                            }, formatter: function (value, row, index) {
                                return value == 'Android' ? __('安卓') : __('苹果');
                            }
                        },
                        {
                            field: 'ios_pay', title: __('是否开启内购'), searchList: {
                                'Y': __('Yes'),
                                'N': __('No'),
                            }, formatter: function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }
                        },
                        {
                            field: 'app_guide_msg_flg', title: __('是否开启诱导'), searchList: {
                                'Y': __('Yes'),
                                'N': __('No'),
                            }, formatter: function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }
                        },
                        {
                            field: 'check_login_change_status', title: __('是否开启审核状态自动改变'), searchList: {
                                'Y': __('Yes'),
                                'N': __('No'),
                            }, formatter: function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }
                        },
                        {
                            field: 'send_posts_check_flg', title: __('发动态是否需要判断权限验证'), searchList: {
                                'Y': __('Yes'),
                                'N': __('No'),
                            }, formatter: function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }
                        },
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
                // search: false,
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
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        }
    };
    return Controller;
});