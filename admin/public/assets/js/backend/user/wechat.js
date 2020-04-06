define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/wechat/index',
                    detail_url: '',
                    edit_url: 'user/wechat/edit'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'wechat_certification_id', title: 'ID'},
                        {field: 'wechat_certification_user_id', title: '用户ID'},
                        {field: 'wechat_certification_value', title: '亲密度微信号',operate:false},
                        {field: 'wechat_certification_price', title: '亲密度微信值',operate:false},
                        {field: 'wechat_certification_status', title: __('Status'), searchList: {
                                'C': __('Checking'),
                                'Y': __('Pass'),
                                'N': __('Refuse'),
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case "Y" :
                                        return __('Pass');
                                        break;
                                    case "C" :
                                        return __('Checking');
                                        break;
                                        break;
                                    case "N" :
                                    default :
                                        return __('Refuse');
                                        break;
                                }
                            }},
                        {field: 'wechat_certification_remark', title: '备注',operate:false},
                        {field: 'wechat_certification_update_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                            ],
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
                pageSize: 12,
                pk: 'wechat_certification_id',
                sortName: 'wechat_certification_id',
                sortOrder: 'DESC',
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
        multi: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        log: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/wechat/log',
                    detail_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'wechat_log_id', title: 'ID'},
                        {field: 'wechat_log_value', title: '微信号'},
                        {field: 'wechat_log_key', title: '暗号'},
                        {field: 'wechat_log_price', title: '价格',operate:false},
                        {field: 'wechat_log_user_id', title: '购买用户id'},
                        {field: 'wechat_log_sale_user_id', title: '出售用户id'},
                        {field: 'wechat_log_user_check', title: __('购买用户状态'), searchList: {
                                'Y': __('已标记'),
                                'N': __('未标记'),
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case "Y" :
                                        return __('已标记');
                                        break;
                                    case "N" :
                                    default :
                                        return __('未标记');
                                        break;
                                }
                            }},
                        {field: 'wechat_log_sale_check', title: __('出售用户状态'), searchList: {
                                'Y': __('已标记'),
                                'N': __('未标记'),
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case "Y" :
                                        return __('已标记');
                                        break;
                                    case "N" :
                                    default :
                                        return __('未标记');
                                        break;
                                }
                            }},
                        {field: 'wechat_log_update_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},

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
                pk: 'wechat_log_id',
                sortName: 'wechat_log_id',
                sortOrder: 'asc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            formatter: {
                
            },
        }
    };
    return Controller;
});