define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/invitelog/index',
                    invite_log_url: 'user/invitelog/detail',
                    reward_log_url: 'user/invitelog/rewardlog',
                    detail_url: '',
                    add_url: '',
                    del_url: '',
                    edit_url: '',
                    multi_url: '',
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
                        {field: 'parent_id', title: __('Parent id'), operate: false},
                        {field: 'user_invite_total', title: __('Level 1'), operate: false},
                        {field: 'user_invite_total2', title: __('Level 2'), operate: false},
                        {field: 'user_invite_total3', title: __('Level 3'), operate: false},
                        {field: 'user_invite_coin_total', title: __('Total'), operate: 'BETWEEN'},
                        {field: 'user_register_time', title: __('Register time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {name: 'invite_log', icon: 'fa fa-list', classname: 'btn btn-xs btn-info addtabsit', title:__('User invite'), text:__('Detail'), url:$.fn.bootstrapTable.defaults.extend.invite_log_url}
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
                pk: 'user_id',
                sortName: 'user_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        detail: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: window.location.href,
                    reward_log_url: 'user/invitelog/rewardlog',
                    detail_url: '',
                    add_url: '',
                    del_url: '',
                    edit_url: '',
                    multi_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'user_invite_relationship_ancestor', title: __('Parent id'), operate:false},
                        {field: 'user_id', title: 'ID'},
                        {field: 'user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'recharge_invite_coin', title: __('Reward coin'), operate: 'BETWEEN'},
                        {field: 'user_invite_relationship_distance', title: __('Invite level'), operate: 'BETWEEN'},
                        {field: 'user_register_time', title: __('Register time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {name: 'reward_log', icon: 'fa fa-list', classname: 'btn btn-xs btn-info addtabsit', title:__('User invite reward'), text:__('Detail'), url:$.fn.bootstrapTable.defaults.extend.reward_log_url}
                            ],
                            formatter: function (value, row, index) {
                                // this.table.data('operate-dragsort', false);
                                // row[Table.config.dragsortfield] = 0;
                                // return Table.api.formatter.operate.call(this, value, row, index);
                                return Controller.api.formatter.reward(value, row, index);
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
                pk: 'user_id',
                sortName: 'user_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        rewardlog: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: window.location.href,
                    detail_url: '',
                    add_url: '',
                    del_url: '',
                    edit_url: '',
                    multi_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'user_invite_reward_log_id', title: 'ID'},
                        {field: 'parent_user_id', title: __('Parent id'), operate:false},
                        {field: 'user_id', title: __('User id'), operate:false},
                        {field: 'user_nickname', title: __('Username'), operate:false},
                        {field: 'invite_level', title: __('Invite level'), operate: 'BETWEEN'},
                        {field: 'recharge_invite_coin', title: __('Reward coin'), operate: 'BETWEEN'},
                        {field: 'user_recharge_combo_fee', title: __('Amount'), operate: 'BETWEEN'},
                        {field: 'user_recharge_combo_coin', title: __('Coin'), operate: 'BETWEEN'},
                        {field: 'invite_ratio', title: __('Recharge ratio'), operate: 'BETWEEN'},
                        {field: 'user_invite_reward_log_create_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
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
                pk: 'user_invite_reward_log_id',
                sortName: 'user_invite_reward_log_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            
        },
        add: function () {
            
        },
        api: {
            formatter: {
                reward: function (value, row, index) {
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.reward_log_url+'/ids/'+row.user_invite_relationship_ancestor+'/user_id/'+row.user_id);
                    return "<a href='"+ url +"' class='btn btn-info btn-xs btn-detail btn-dialog' title='"+  __('User invite reward')+"'><i class='fa fa-list'></i>"+ __('Detail') +"</a> ";
                },
            },
        }
    };
    return Controller;
});