define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'video/msg/index',
                    detail_url: '',
                    add_url: '',
                    multi_url: '',
                    denyspeak_url: 'user/user/denyspeak',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'id', title: 'ID'},
                        {field: 'user_id', title: __('User id'),},
                        {field: 'user.user_nickname', title: __('Nickname'),operate:false},
                        {field: 'user.user_avatar', title: __('User avatar'), operate: false, formatter:function(value, row, index, custom){
                                return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                            }},
                        {field: 'content', title: __('Content'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'create_time', title: __('Create time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'user.user_is_deny_speak', title: __('Deny speak'), searchList:{
                                'Y' : __('Yes'),
                                'N' : __('No'),
                            },
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.denySpeak(value, row, index);
                            }},

                    ]
                ],
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 12,
                pk: 'user_id',
                sortName: 'id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },

        api: {
            formatter: {
                denySpeak: function (value, row, index) {
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.denyspeak_url);
                    return "<a href='javascript:;' class='btn btn-" + (row.user.user_is_deny_speak == 'N' ? "warning" : "danger") + " btn-xs btn-change btn-tipsone' data-id='" +
                        row.user.user_id + "' data-url='" + url + "' data-params='" +
                        (row.user.user_is_deny_speak == 'N' ? 'Y' : 'N') + "'><i class='fa fa-dot'></i>"
                        + (row.user.user_is_deny_speak == 'Y' ? __('Yes') : __('No')) + "</a> ";
                }
            },
        }
    };
    return Controller;
});