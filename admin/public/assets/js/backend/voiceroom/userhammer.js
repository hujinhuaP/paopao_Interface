define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'voiceroom/userhammer/index',
                    detail_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                columns: [
                    [
                        {field: 'user_id', title: __('用户ID')},
                        {field: 'user_hammer_number', title: __('用户锤子数'), operate:'BETWEEN'},
                        {field: 'user_hammer_total_number', title: __('累计购买锤子数'), operate:'BETWEEN'},
                        {field: 'user_buy_hammer_first_time', title: __('第一次购买时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'user_buy_hammer_last_time', title: __('最近购买时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'user_break_1_egg_number', title: __('砸1锤'), operate:'BETWEEN'},
                        {field: 'user_break_10_egg_number', title: __('砸10锤'), operate:'BETWEEN'},
                        {field: 'user_break_100_egg_number', title: __('砸100锤'), operate:'BETWEEN'},
                        {field: 'reward_diamond_total', title: __('钻石礼物（钻石）'), operate:'BETWEEN'},
                        {field: 'reward_coin', title: __('奖励金币'), operate:'BETWEEN'},
                        {field: 'reward_vip_day', title: __('奖励VIP时长（天）'), operate:'BETWEEN'},
                    ]
                ],

                search: false,
                commonSearch: true,
                searchFormVisible: false,
                pagination: true,
                pageSize: 12,
                pk: 'user_id',
                sortName: 'user_id',
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