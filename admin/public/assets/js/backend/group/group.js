define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'group/group/index',
                    detail_url: '',
                    add_url: 'group/group/add',
                    edit_url: 'group/group/edit',
                    multi_url: '',
                    status_url: 'group/group/status',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true,},
                        {field: 'id',title: 'ID'},
                        {field: 'group_name',title: __('Group name'),operate: 'LIKE %...%',placeholder: __('Like search'),style: 'width:200px'},
                        {field: 'group_type',title: __('Group type'),searchList: {
                            0: __('Official group'),
                            1: __('Third group'),
                        },formatter: function (value,row,index) {
                            return value == 0 ? __('Official group') : __('Third group');
                        }},
                        {field: 'divid_type',title: __('Divid type'),searchList: {
                                0: __('Devid by anchor income'),
                                1: __('Devid by anchor water'),
                            },formatter: function (value,row,index) {
                                return value == 0 ? __('Devid by anchor income') : __('Devid by anchor water');
                            }},
                        {field: 'divid_precent',title: __('Divid precent')+'(%)',operate: 'BETWEEN'},
                        {field: 'divid_time_precent',title: __('Divid precent of live time')+'(%)',operate: 'BETWEEN'},
                        {field: 'divid_gift_precent',title: __('Divid precent of get gift')+'(%)',operate: 'BETWEEN'},
                        {field: 'divid_video_precent',title: __('Divid precent of video')+'(%)',operate: 'BETWEEN'},
                        {field: 'divid_chat_precent',title: __('Divid precent of chat')+'(%)',operate: 'BETWEEN'},
                        {field: 'divid_chat_game',title: __('游戏分成比例')+'(%)',operate: 'BETWEEN'},
                        {field: 'money',title: __('Amount'),operate: 'BETWEEN'},
                        {field: 'status', title: __('Status'),defaultValue:'Y', searchList: {
                                'Y': __('Enable'),
                                'N': __('Disable'),
                            }, formatter: function (value, row, index) {
                                return value == 'Y' ? __('Enable') : __('Disable');
                            }},
                        {field: 'update_time',title: __('Operate time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                        {field: 'create_time',title: __('Create time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                        {field: 'operate',title: __('Operate'),table: table,
                            events: Table.api.events.operate,
                            buttons: [
                            ],
                            formatter: function (value,row,index) {
                                this.table.data('operate-dragsort',false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this,value,row,index)+Controller.api.formatter.status(row.status,row,index,'status');
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
                pageSize: 20,
                pk: 'id',
                sortName: 'id',
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
                status: function (value,row,index,field) {
                    var btn = 'success';
                    var title = __('Enable');
                    var param = 'normal';
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.status_url);
                    var icon = 'fa fa-long-arrow-up';
                    switch (value) {
                        case 'N':
                            btn   = 'success';
                            title = __('Enable');
                            param  = 'normal';
                            icon = 'fa fa-long-arrow-up';
                            break;

                        case 'normal':
                        default :
                            btn   = 'danger';
                            title = __('Disable');
                            param  = 'N';
                            icon = 'fa fa-long-arrow-down';
                            break;
                    }

                    return " <a href='javascript:;' class='btn btn-" + btn + " btn-xs btn-change btn-tipsone' data-id='" +
                        row.question_id + "' data-url='" + url + "' data-params='" + field + "=" + param +"'><i class='" + icon + "'></i> "
                        + title + "</a>";
                }
            },
        },
        finance: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'group/group/finance',
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
                        {field: 'state',checkbox: true,},
                        {field: 'stat_time',title: __('Stat time'),sortable:true,operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index).slice(0,11);
                            }},
                        {field: 'group.id',title: __('Group ID'),operate:false},
                        {field: 'group.id', title: __('Group name'), searchList: group, formatter:function (value, row, index) {
                                return group[value];
                            }},
                        {field: 'anchor_time_income',title: __('Anchor income of live time')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'anchor_gift_income',title: __('Anchor income of get gift')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'word_msg_income',title: __('主播聊天收益')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'video_income',title: __('主播视频收益')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'invite_reward_income',title: __('Anchor income of invite reward')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'anchor_guard_income',title: __('主播守护收益')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'chat_game_income',title: __('聊天游戏收益')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'group_divid_income',title: __('Group divid income')+'(￥)',operate: 'BETWEEN',sortable:true},
                        {field: 'group_total_income',title: __('Group total income')+'(￥)',operate: 'BETWEEN',sortable:true},
                    ]
                ],

                // templateView: true,
                // search: false,
                // showColumns: false,
                // showToggle: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 20,
                pk: 'id',
                sortName: 'stat_time',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        }
    };
    return Controller;
});