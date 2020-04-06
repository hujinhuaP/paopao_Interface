define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'anchor/chatanchor/index',
                    detail_url: '',
                    add_url: '',
                    del_url: '',
                    edit_url: '',
                    multi_url: '',
                    banlive_url: 'anchor/chatanchor/forbidden'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'user_id', title: 'ID'},
                        {field: 'user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_avatar', title: __('User avatar'), operate: false, formatter: function (value, row, index, custom) {
                            return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                        }},
                        {field: 'user_coin', title: __('Coin'), operate: false},
                        {field: 'user_dot', title: __('Dot'), operate: false},                        
                        {field: 'user_follow_total', title: __('Follow'), operate: false},                     
                        {field: 'user_fans_total', title: __('Fans'),operate: false},                     
                        {field: 'anchor_chat_status', title: __('Is chating'), searchList:{
                                2 : __('Yes'),
                                3 : __('No')
                            },
                            formatter:function (value, row, index) {
                                return value == 2 ? __('Yes') : __('No');
                            }},                     
                        {field: 'anchor_private_forbidden', title: __('Ban chat'), searchList:{
                                1: __('Yes'),
                                0 : __('No'),
                            },
                            formatter:function (value, row, index) {
                                return value == 1 ? __('Yes') : __('No');
                        }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                            ],
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index)+Controller.api.formatter.banlive(value, row, index);
                            }}

                    ]
                ],

                commonSearch: true,
                searchFormVisible: false,
                pageSize: 12,
                pk: 'user_id',
                sortName: 'user_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        multi: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                banlive: function (value, row, index) {
                    var btn = 'danger';
                    var title = __('Ban live');
                    var anchor_private_forbidden = 1;
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.banlive_url);

                    switch (row.anchor_private_forbidden) {
                        case 0:
                            btn   = 'danger';
                            title = __('Ban chat');
                            anchor_private_forbidden  = 1;
                            break;

                        default :
                            btn   = 'success';
                            title = __('Release');
                            anchor_private_forbidden  = 0;
                            break;
                    }

                    return " <a href='javascript:;' class='btn btn-" + btn + " btn-xs btn-change btn-tipsone' data-id='" + 
                            row.user_id + "' data-url='" + url + "' data-params='"+ anchor_private_forbidden +"'><i class='fa fa-video-camera'></i> "
                            + title + "</a>";
                }
            },
        }
    };
    return Controller;
});