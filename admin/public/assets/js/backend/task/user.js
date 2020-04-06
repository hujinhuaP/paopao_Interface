define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'task/user/index',
                    detail_url: '',
                    edit_url: 'task/user/edit',
                    multi_url: '',
                    dragsort_url: 'task/user/sort',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'task_sort',title: __('排序'),operate: false },
                        {field: 'task_id',title: __('任务ID'),operate: false },
                        {field: 'task_name', title: __('名称'), operate: false },
                        {field: 'task_finish_times', title: __('完成所需次数'), operate: false },
                        {field: 'task_reward_coin', title: __('奖励金币（主播无效）'),operate: false },
                        {field: 'task_reward_dot', title: __('奖励佣金（用户无效）'),operate: false },
                        {field: 'task_reward_exp', title: __('奖励经验（主播为魅力值）'),operate: false },
                        {field: 'task_type', title: __('类型'),defaultValue: 'daily',searchList: {
                                'once': __('一次性'),
                                'daily': __('每日'),
                                'anchor_daily': __('主播每日'),
                            }, formatter: function (value, row, index) {
                                switch (value) {
                                    case 'once':
                                        return '一次性';
                                    case 'daily':
                                        return '每日';
                                    case 'anchor_daily':
                                        return '主播每日';
                                }
                            }},
                        {field: 'task_on', title: __('是否开启'),searchList: {
                                'Y': __('是'),
                                'N': __('否'),
                            }, formatter: function (value, row, index) {
                                switch (value) {
                                    case 'Y':
                                        return '是';
                                    case 'N':
                                        return '否';
                                }
                            }},
                        {field: 'task_create_time', title: __('Create time'), sortable:true, operate: false, type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'task_update_time', title: __('Operate time'), sortable:true, operate: false, type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                            ],
                            formatter: function (value, row, index) {
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
                searchFormVisible: true,
                pageSize: 20,
                pk: 'task_id',
                dragsortfield: 'task_sort',
                sortName: 'task_sort',
                sortOrder: 'asc',
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
        api: {
            formatter: {
            },
        }
    };
    return Controller;
});