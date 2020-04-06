define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'anchor/liveincome/index',
                    add_url: '',
                    del_url: '',
                    multi_url: ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,



                columns: [
                    [
                        {field: 'consume_category_id', title: __('Dot category'), operate:false, searchList:{
                                3 : __('Time consume'),
                                4 : __('Fare consume'),
                                6 : __('Get gift')
                            },
                            formatter:function (value, row, index) {
                                switch(value){
                                    case 3:
                                        return __('Time consume');
                                    case 4:
                                        return __('Fare consume');
                                    case 6:
                                        return __('Get gift');
                                }
                            }},
                        {field: 'consume', title: __('Dot consume'), operate:false},
                        {field: 'create_time', title: __('Date'), operate:false, type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                    ]
                ],

                search: false,
                showColumns: false,
                showToggle: false,
                showExport: false,
                commonSearch: false,
                searchFormVisible: false,
                pageSize: 4,
                pk: 'user_id',
                sortName: 'user_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        }
    };
    return Controller;
});