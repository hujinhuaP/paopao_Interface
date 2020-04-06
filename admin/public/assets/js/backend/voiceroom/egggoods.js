define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'voiceroom/egggoods/index',
                    add_url: 'voiceroom/egggoods/add',
                    edit_url: 'voiceroom/egggoods/edit',
                    detail_url: '',
                    dragsort_url: 'ajax/apiweigh',
                    table: 'egg_goods',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                columns: [
                    [
                        {field: 'state', checkbox: true,},
                        {field: 'egg_goods_id', title: 'ID'},
                        {field: 'egg_goods_show_sort', title: '展示排序'},
                        {field: 'egg_goods_name', title: __('奖励名称')},
                        {
                            field: 'egg_goods_image',
                            title: __('图标'),
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image
                        },
                        {
                            field: 'egg_goods_category',
                            title: __('类型'),
                            searchList: {"coin": __('金币'), "vip": __('VIP'), "diamond": __('钻石礼物')},
                            formatter: function (value, row, index) {
                                switch (row.egg_goods_category) {
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
                            field: 'egg_goods_value', title: __('奖励价值'), formatter: function (value, row, index) {
                                switch (row.egg_goods_category) {
                                    case 'coin':
                                        return value + '金币';
                                    case 'vip':
                                        return value + '天VIP';
                                    case 'diamond':
                                        return value + '钻石';
                                }
                            }
                        },
                        {
                            field: 'egg_goods_notice_flg',
                            title: __('中奖飘屏'),
                            searchList: {"Y": __('是'), "N": __('否')},
                            formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'egg_goods_point', title: __('中奖概率'), formatter: function (value, row, index) {
                                return value + '%';
                            }
                        },
                        {
                            field: 'egg_goods_update_time',
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
                                return Table.api.formatter.operate.call(this, value, row, index);
                            }}
                    ]
                ],

                search: false,
                commonSearch: true,
                searchFormVisible: false,
                pagination: true,
                pageSize: 100,
                pk: 'egg_goods_id',
                sortName: 'egg_goods_show_sort',
                sortOrder: 'asc',
            });

            Table.api.bindevent(table);
            Table.config.dragsortfield = 'egg_goods_show_sort';


            Controller.api.bindevent();
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