define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'voiceroom/egglog/index',
                    detail_url: 'voiceroom/egglog/detail',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true,},
                        {field: 'user_egg_log_id',title: 'ID'},
                        {field: 'user_egg_log_user_id',title: __('用户ID')},
                        {field: 'user_egg_log_number',title: __('砸蛋次数')},
                        {field: 'user_egg_log_times', title: __('用户已砸次数')},
                        {field: 'user_egg_log_create_time',title: __('Create time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
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

                search: false,
                commonSearch: true,
                searchFormVisible: false,
                pagination: true,
                pageSize: 20,
                pk: 'user_egg_log_id',
                sortName: 'user_egg_log_id',
                sortOrder: 'desc',
            });

            Table.api.bindevent(table);
        },
        detail: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'voiceroom/egglog/detail/ids/'+ids,
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true,},
                        {field: 'user_egg_detail_id',title: 'ID'},
                        {field: 'user_egg_detail_user_id',title: __('用户ID')},
                        {field: 'user_egg_detail_name',title: __('中奖名称')},
                        {
                            field: 'user_egg_detail_goods_category',
                            title: __('类型'),
                            searchList: {"coin": __('金币'), "vip": __('VIP'), "diamond": __('钻石礼物')},
                            formatter: function (value, row, index) {
                                switch (value) {
                                    case 'coin':
                                        return '金币';
                                    case 'vip':
                                        return  'VIP';
                                    case 'diamond':
                                        return '钻石';
                                }
                            }
                        },
                        {
                            field: 'user_egg_detail_value', title: __('奖励价值'), formatter: function (value, row, index) {
                                switch (row.user_egg_detail_goods_category) {
                                    case 'coin':
                                        return value + '金币';
                                    case 'vip':
                                        return value + '天VIP';
                                    case 'diamond':
                                        return value + '钻石';
                                }
                            }
                        },
                        {field: 'user_egg_detail_reward_number', title: __('中奖数量'), operate: false},
                        {field: 'user_egg_detail_create_time',title: __('Create time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }}
                    ]
                ],
                search: false,
                commonSearch: true,
                searchFormVisible: false,
                pagination: true,
                pageSize: 20,
                pk: 'user_egg_detail_id',
                sortName: 'user_egg_detail_id',
                sortOrder: 'desc',
            });

            Table.api.bindevent(table);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});