define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'group/user/index',
                    detail_url: '',
                    add_url: 'group/user/add',
                    edit_url: 'group/user/edit',
                    multi_url: '',
                    del_url: 'group/user/delete',
                    forbid_url: 'user/user/forbid',
                    denyspeak_url: 'user/user/denyspeak',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true,},
                        {field: 'user_id',title: 'ID'},
                        {field: 'user_video_cover', title: __('User avatar'), operate: false, formatter: function (value, row, index, custom) {
                                if(row.user_is_certification == 'Y'){
                                    return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-lg img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                                }
                            }},
                        {field: 'user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'group.id', title: __('Group name'), searchList: group, formatter:function (value, row, index) {
                                return group[value];
                            }},
                        {field: 'user_is_certification', title: __('Certification status'), searchList: {
                                'Y': __('Pass'),
                                'N': __('No certification'),
                                'C': __('Checking'),
                                'D': __('Forbid certification'),
                                'R': __('Refuse'),
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case "Y" :
                                        return __('Pass');
                                        break;
                                    case "N" :
                                        return __('No certification');
                                        break;
                                    case "C" :
                                        return __('Checking');
                                        break;
                                    case "D" :
                                        return __('Forbid certification');
                                        break;
                                    case "R" :
                                    default :
                                        return __('Refuse');
                                }
                            }},
                        {field: 'user_is_forbid', title: __('Forbid'), searchList:{
                                'Y' : __('Yes'),
                                'N' : __('No'),
                            },
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.forbid(value, row, index);
                            }},
                        {field: 'user_is_deny_speak', title: __('Deny speak'), searchList:{
                                'Y' : __('Yes'),
                                'N' : __('No'),
                            },
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.denySpeak(value, row, index);
                            }},
                        {field: 'user_logout_time', title: __('最后下线时间'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'user_update_time',title: __('Operate time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                        {field: 'user_create_time',title: __('Create time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                        {field: 'operate',title: __('Operate'),table: table,
                            events: Table.api.events.operate,
                            buttons: [
                            ],
                            formatter: function (value,row,index) {
                                this.table.data('operate-dragsort',false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this,value,row,index);
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

        },api: {
            formatter: {
                forbid: function (value, row, index) {
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.forbid_url);
                    return "<a href='javascript:;' class='btn btn-" + (row.user_is_forbid == 'N' ? "warning" : "danger") + " btn-xs btn-change btn-tipsone' data-id='" +
                        row.user_id + "' data-url='" + url + "' data-params='" +
                        (row.user_is_forbid == 'N' ? 'Y' : 'N') + "'><i class='fa fa-dot'></i>"
                        + (row.user_is_forbid == 'Y' ? __('Yes') : __('No')) + "</a> ";
                },
                denySpeak: function (value, row, index) {
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.denyspeak_url);
                    return "<a href='javascript:;' class='btn btn-" + (row.user_is_deny_speak == 'N' ? "warning" : "danger") + " btn-xs btn-change btn-tipsone' data-id='" +
                        row.user_id + "' data-url='" + url + "' data-params='" +
                        (row.user_is_deny_speak == 'N' ? 'Y' : 'N') + "'><i class='fa fa-dot'></i>"
                        + (row.user_is_deny_speak == 'Y' ? __('Yes') : __('No')) + "</a> ";
                }
            },
        }
    };
    return Controller;
});