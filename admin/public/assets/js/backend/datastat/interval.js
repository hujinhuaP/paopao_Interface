define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datastat/interval/index',
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
                        {
                            field: 'stat_time',
                            title: __('Stat time'),
                            sortable: true,
                            operate: 'BETWEEN',
                            type: 'datetime',
                            addclass: 'datetimepicker',
                            data: 'data-date-format="YYYY-MM-DD HH"',
                            formatter: function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index) + '--' + Table.api.formatter.datetime.call(this, parseInt(value) + 3600, row, index, row, index);
                            }
                        },

                        {field: 'user_count', title: __('用户在线人数'), operate: 'BETWEEN'},
                        {field: 'anchor_count', title: __('主播在线人数'), operate: 'BETWEEN'},
                        {field: 'new_device_count', title: __('新增设备数'), operate: false},
                        {field: 'first_recharge_count', title: __('首次充值数'), operate: false},
                        {field: 'recharge_user_count', title: __('充值人数'), operate: false},
                        {field: 'normal_video_chat_count', title: __('主播点播完成数'), operate: false},
                        {
                            field: 'new_device_count',
                            title: __('新用户充值转化率'),
                            operate: false,
                            formatter: function (value, row, index) {
                                if (value == 0) {
                                    return '-';
                                } else {
                                    return parseFloat(parseInt(row.first_recharge_count) / parseInt(value) * 100).toFixed(2) + '%';
                                }
                            }
                        },
                    ]],
                // templateView: true,
                search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pagination: true,
                pageSize: 12,
                pk: 'stat_time',
                sortName: 'stat_time',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));

        }, api: {
            formatter: {},
        }
    };
    return Controller;
});