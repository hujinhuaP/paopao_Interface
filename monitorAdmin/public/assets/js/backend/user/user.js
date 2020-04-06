define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    detail_url: 'user/user/detail',
                    add_url: '',
                    del_url: '',
                    edit_url: 'user/user/edit',
                    multi_url: '',
                    forbid_url: 'user/user/forbid',
                    denyspeak_url: 'user/user/denyspeak',
                }
            });

            var table = $("#table");


            // 指定搜索条件
            $(document).on("click", ".btn-singlesearch", function () {
                var options = table.bootstrapTable('getOptions');
                var queryParams = options.queryParams;
                options.pageNumber = 1;
                var data_type = $(this).attr('data-type');
                if(data_type == 'vip'){
                    $(this).html("<i class=\"fa fa-user\"></i> 全部");
                    $(this).attr('data-type','all');
                }else{
                    $(this).html("<i class=\"fa fa-user\"></i> VIP用户");
                    $(this).attr('data-type','vip');
                }
                options.queryParams = function (params) {
                    //这一行必须要存在,否则在点击下一页时会丢失搜索栏数据
                    params = queryParams(params);

                    //如果希望追加搜索条件,可使用
                    var filter = params.filter ? JSON.parse(params.filter) : {};
                    var op = params.op ? JSON.parse(params.op) : {};
                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
                    params.vip_flg = data_type;

                    //如果希望忽略搜索栏搜索条件,可使用
                    //params.filter = JSON.stringify({url: 'login'});
                    //params.op = JSON.stringify({url: 'like'});
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;
            });

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
                        {field: 'user_account.user_phone', title: __('Phone'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_coin', title: __('剩余充值金币(赠送金币)'), operate: false,formatter: function (value, row, index, custom) {
                                return parseInt(value) +'('+ parseInt(row.user_free_coin) +')';
                            }},
                        {field: 'user_consume_total', title: __('累计消耗充值金币(赠送金币)'), operate: false,formatter: function (value, row, index, custom) {
                                return parseInt(value) +'('+ parseInt(row.user_consume_free_total) +')';
                            }},
                        {field: 'user_member_expire_time', title: __('VIP过期时间'), operate: false,sortable:true,formatter: function (value, row, index, custom) {
                            return Table.api.formatter.datetime.call(this, value, row, index);
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
                        {field: 'user_is_superadmin', title: __('特殊账号'), searchList:{
                                'Y' : __('官方账号'),
                                'N' : __('普通'),
                                'S' : __('不能消费'),
                                'C' : __('客服账号'),
                            },
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                            switch (value) {
                                case 'Y' :
                                    return '官方账号';
                                case 'S' :
                                    return '不能消费';
                                case 'C' :
                                    return '客服账号';
                                case 'N' :
                                default:
                                    return '普通';
                            }
                            }},
                        {field: 'user_is_anchor', title: __('是否为主播'),defaultValue:'N', searchList:{
                                'Y' : __('Yes'),
                                'N' : __('No'),
                            },
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }},
                        {field: 'user_online_status', title: __('在线状态'), searchList:{
                                'Online' : __('在线'),
                                'Offline' : __('离线'),
                                'PushOnline' : __('后台'),
                            },
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                                switch (value) {
                                    case 'Online' :
                                        return '在线';
                                    case 'Offline' :
                                        return '离线';
                                    case 'PushOnline' :
                                    default:
                                        return '后台';
                                }
                            }},
                        {field: 'user_logout_time', title: __('最后下线时间'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'user_create_time', title: __('Create time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {name: 'budan', icon: 'fa fa-yen', classname: 'btn btn-xs btn-primary btn-detail btn-dialog', url:'user/budan/add', title:__('Change in amount'), text:__('Change in amount')},
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
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
            // 给上传按钮添加上传成功事件
            $("#plupload-avatar").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $(".profile-img").prop("src", url);
                Toastr.success(__('Upload success'));
            });
        },
        multi: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
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