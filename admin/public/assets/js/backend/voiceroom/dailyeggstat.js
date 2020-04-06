define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'voiceroom/dailyeggstat/index',
                    detail_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                columns: [
                    [
                        {field: 'state',checkbox: true,},
                        {field: 'daily_egg_stat_date', title: __('统计日期'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'daily_egg_stat_user_number',title: __('砸蛋人数')},
                        {field: 'daily_egg_stat_1_egg_number',title: __('砸1锤次数')},
                        {field: 'daily_egg_stat_10_egg_number',title: __('砸10锤次数')},
                        {field: 'daily_egg_stat_100_egg_number',title: __('砸100锤次数')},
                        {field: 'daily_egg_stat_diamond', title: __('钻石礼物（钻石）'), operate: 'between'},
                        {field: 'daily_egg_stat_coin', title: __('奖励金币'), operate: 'between'},
                        {field: 'daily_egg_stat_vip', title: __('奖励VIP时长（天）'), operate: 'between'}
                    ]
                ],
                search: false,
                commonSearch: true,
                searchFormVisible: false,
                pagination: true,
                pageSize: 12,
                pk: 'daily_egg_stat_date',
                sortName: 'daily_egg_stat_date',
                sortOrder: 'desc',
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