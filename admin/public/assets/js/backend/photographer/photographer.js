define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'photographer/photographer/index',
                    edit_url: 'photographer/photographer/edit',
                    detail_url: '',
                    multi_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true,},
                        {field: 'photographer_id',title: 'ID'},
                        {field: 'user_id', title: __('User ID')},
                        {field: 'user.user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user.user_avatar', title: __('User avatar'), operate: false, formatter: function (value, row, index, custom) {
                                return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                                // return Table.api.formatter.image.call(this, value, row, index, custom);
                            }},
                        {field: 'user.user_dot', title: __('当前收益'),operate: false},
                        {field: 'user.user_fans_total', title: __('累计总收益'),operate: false,formatter:function(value, row, index){
                                return (parseFloat(row.user.user_collect_total) + parseFloat(row.user.user_collect_free_total) + parseFloat(row.user.user_invite_dot_total)).toFixed(4);
                            }},
                        {field: 'photographer_update_time',title: __('Operate time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                        {field: 'photographer_create_time',title: __('Create time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table,events: Table.api.events.operate,formatter: function (value,row,index) {
                                this.table.data('operate-dragsort',false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this,value,row,index);
                            }}

                    ]
                ],

                // templateView: true,
                search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 20,
                pk: 'photographer_id',
                sortName: 'photographer_id',
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
        },
    };
    return Controller;
});