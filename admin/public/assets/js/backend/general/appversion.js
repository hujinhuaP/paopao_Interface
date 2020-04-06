define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/appversion/index',
                    add_url: 'general/appversion/add',
                    edit_url: 'general/appversion/edit',
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
                        {field: 'app_version_id', title: 'ID'},
                        {
                            field: 'app_id',
                            title: __('APP名称'),
                            searchList: applist,
                            formatter: function (value, row, index) {
                                return applist[value];
                            }
                        },
                        {
                            field: 'app_version_os', title: __('Operating system'), searchList: {
                                'ios': __('IOS'),
                                'android': __('Android'),
                            }, formatter: function (value, row, index) {
                                switch (value) {
                                    case 'ios' :
                                        return __('IOS');
                                        break;
                                    case 'android' :
                                    default :
                                        return __('Android');
                                        break;
                                }
                            }
                        },
                        {field: 'app_version_code', title: __('Version code'), operate: 'BETWEEN',},
                        {
                            field: 'app_version_name',
                            title: __('Version name'),
                            operate: 'LIKE %...%',
                            placeholder: __('Like search'),
                            style: 'width:200px'
                        },
                        {
                            field: 'app_version_content',
                            title: __('Content'),
                            operate: 'LIKE %...%',
                            placeholder: __('Like search'),
                            style: 'width:200px'
                        },
                        {
                            field: 'app_version_is_force', title: __('Force update'), searchList: {
                                'Y': __('Yes'),
                                'N': __('No'),
                            }, formatter: function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }
                        },
                        {
                            field: 'app_version_download_url',
                            title: __('Download url'),
                            operate: false,
                            formatter: function (value, row, index) {
                                return Table.api.formatter.url.call(this, value, row, index);
                            }
                        },
                        {
                            field: 'app_version_update_time',
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
                pk: 'app_version_id',
                sortName: 'app_version_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        history: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/appversion/history',
                    add_url: 'general/appversion/add',
                    edit_url: 'general/appversion/edit',
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
                        {field: 'app_version_id', title: 'ID'},
                        {
                            field: 'app_version_os', title: __('Operating system'), searchList: {
                                'ios': __('IOS'),
                                'android': __('Android'),
                            }, formatter: function (value, row, index) {
                                switch (value) {
                                    case 'ios' :
                                        return __('IOS');
                                        break;
                                    case 'android' :
                                    default :
                                        return __('Android');
                                        break;
                                }
                            }
                        },
                        {field: 'app_version_code', title: __('Version code'), operate: 'BETWEEN',},
                        {
                            field: 'app_version_name',
                            title: __('Version name'),
                            operate: 'LIKE %...%',
                            placeholder: __('Like search'),
                            style: 'width:200px'
                        },
                        {
                            field: 'app_version_content',
                            title: __('Content'),
                            operate: 'LIKE %...%',
                            placeholder: __('Like search'),
                            style: 'width:200px'
                        },
                        {
                            field: 'app_version_is_force', title: __('Force update'), searchList: {
                                'Y': __('Yes'),
                                'N': __('No'),
                            }, formatter: function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }
                        },
                        {
                            field: 'app_version_download_url',
                            title: __('Download url'),
                            operate: false,
                            formatter: function (value, row, index) {
                                return Table.api.formatter.url.call(this, value, row, index);
                            }
                        },
                        {
                            field: 'app_version_update_time',
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
                pk: 'app_version_id',
                sortName: 'app_version_code',
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
        },
        addmore: function () {
            Form.api.bindevent($("form[role=form]"));
        },
    };
    return Controller;
});