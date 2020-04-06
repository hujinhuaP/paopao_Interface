define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/viplevel/index',
                    add_url: 'general/viplevel/add',
                    edit_url: 'general/viplevel/edit',
                    detail_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true,},
                        {field: 'vip_level_id', title: 'ID'},
                        {field: 'vip_level_name', title: __('VIP等级名称')},
                        {field: 'vip_level_value', title: __('VIP等级值')},
                        {field: 'vip_level_min_exp', title: __('VIP所需经验')},
                        {
                            field: 'vip_level_exhibition_discount', title: __('作品购买折扣'), formatter: function (value, row, index) {
                                return value + '折';
                            }
                        },
                        {
                            field: 'vip_level_video_chat_discount', title: __('1v1视频折扣'), formatter: function (value, row, index) {
                                return value + '折';
                            }
                        },
                        {
                            field: 'vip_level_update_time',
                            title: __('Operate time'),
                            operate: 'BETWEEN',
                            type: 'datetime',
                            addclass: 'datetimepicker',
                            data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',
                            formatter: function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }
                        },
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

                search: false,
                commonSearch: true,
                searchFormVisible: false,
                pagination: true,
                pageSize: 12,
                pk: 'vip_level_id',
                sortName: 'vip_level_value',
                sortOrder: 'asc',
            });

            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});