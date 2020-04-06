define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/robottalk/index',
                    detail_url: '',
                    add_url: 'user/robottalk/add',
                    del_url: 'user/robottalk/delete',
                    edit_url: 'user/robottalk/edit',
                    multi_url: 'user/robottalk/multi',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'isrobot_talk_id', title: 'ID'},
                        {field: 'isrobot_talk_content', title: __('Content'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'isrobot_talk_user_type', title: __('Type'), searchList:{
                                'anchor': __('主播打招呼'),
                                'female': __('用户打招呼'),
                            }, formatter: function (value, index, row) {
                                switch (value) {
                                    case 'anchor' :
                                        return  __('主播打招呼');
                                        break;
                                        
                                    case 'user' :
                                        return  __('用户打招呼');
                                        break;
                                }
                            }},
                        {field: 'isrobot_talk_update_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
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
                // search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 12,
                pk: 'isrobot_talk_id',
                sortName: 'isrobot_talk_id',
                sortOrder: 'desc',
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
        api: {
            formatter: {
                
            },
        }
    };
    return Controller;
});