define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'anchor/actionlog/index',
                    add_url: '',
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
                        {field: 'user_id', title: '用户ID'},
                        {field: 'admin_id', title: '管理员id'},
                        {field: 'admin_name', title: '管理员名称'},
                        {
                            field: 'action_type', title: __('操作内容'), searchList: {
                                'hot_man': __('红人'),
                                'hot_time': __('热门'),
                                'positive': __('积极'),
                                'new_hot': __('新人热推'),
                                'beauty': __('高颜值'),
                                'sign': __('签约'),
                                'show_index': __('首页显示'),
                            },
                            formatter: function (value, row, index) {
                                switch (value) {
                                    case 'hot_man':
                                        return __('红人');
                                    case 'hot_time':
                                        return __('热门');
                                    case 'positive':
                                        return __('积极');
                                    case 'new_hot':
                                        return __('新人热推');
                                    case 'beauty':
                                        return __('高颜值');
                                    case 'sign':
                                        return __('签约');
                                    case 'show_index':
                                        return __('首页显示');
                                }
                            }
                        },
                        {field: 'change_to_status', title: __('修改后状态'), searchList:{
                                'Y' : __('Yes'),
                                'N' : __('No'),
                            },
                            formatter:function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }},
                        {field: 'remark', title: '备注', operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},

                        {
                            field: 'update_time',
                            title: __('Operate time'),
                            operate: 'BETWEEN',
                            type: 'datetime',
                            addclass: 'datetimepicker',
                            data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',
                            formatter: function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
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
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        multi: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {},
        }
    };
    return Controller;
});